(function () {

var btnOn = null;
var btnOff = null;
var boss = null;
var bossBk = [
	{background: '/img/contents/src/background/blood.jpg'},
	{background: '/img/contents/src/background/burst.jpg'},
	{background: '/img/contents/src/background/dark-blue-water.jpg'},
	{background: '/img/contents/src/background/purple-fog.jpg'},
	{background: '/img/contents/src/background/laser.jpg'}
];

function HuntArea (viewElement) {
	this.parent = viewElement;
	this.parent.style.background = 'url(/img/contents/src/background/colorful.jpg)';
	this.parent.css({ backgroundSize: '100% 100%' });
	this.updateInterval = 60 * 60 * 0.5; // 30 minutes
	// back
	var self = this;
	var btn = this.parent.create('div');
	btn.style.textAlign = 'center';
	btn.style.padding = '4px auto';
	btn.style.color = '#fff';
	btn.style.border = '2px solid #600';
	btn.style.WebkitBorderRadius = '8px';
	btn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#FF9C9C), to(#CF3232))';
	btn.style.width = '60px';
	btn.style.height = '30px';
	btn.style.lineHeight = '30px';
	btn.textContent = 'Back';
	btn.style.margin = '0';
	btn.style.top = '39';
	btn.style.left = '5px';
	btn.style.position = 'absolute';
	this.backBtn = btn;
	button.create(btn, function () {
		window.viewPort.open('Main');
	});
	
	// team management
	var tbtn = this.parent.create('div');
	tbtn.style.textAlign = 'center';
	tbtn.style.padding = '4px auto';
	tbtn.style.color = '#fff';
	tbtn.style.border = '2px solid #030';
	tbtn.style.WebkitBorderRadius = '8px';
	tbtn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#0c0), to(#060))';
	tbtn.style.width = '150px';
	tbtn.style.height = '30px';
	tbtn.style.lineHeight = '30px';
	tbtn.textContent = 'Manage Team';
	tbtn.style.margin = '0';
	tbtn.style.top = '39';
	tbtn.style.left = '160px';
	tbtn.style.position = 'absolute';
	button.create(tbtn, function () {
		window.viewPort.open('TeamManagement', { back: 'HuntArea' });
	});

	var container = this.parent.create('div');
	container.style.background = 'rgba(0, 0, 0, 0.7)';
	container.style.borderTop = '2px solid rgba(255, 255, 255, 1)';
	container.style.borderBottom = '2px solid rgba(255, 255, 255, 1)';
	container.style.top = '79px';
	container.style.left = '0';
	container.style.position = 'absolute';
	container.style.width = '100%';
	container.style.height = '73%';
	var list = container.create('div');
	this.creatureBox = list.create('div');
	this.creatureBox.list = [];
	this.scroller = new iScroll(container);
	
	this.list = [];
	this.updateDisplay = false;
	/*
	btnOn = new FrameAnimation();
	btnOn.addKeyFrame(0, { opacity: 1 });
	btnOn.addKeyFrame(2, { opacity: 0.8 });
	btnOn.setup({ easing: 'ease-in-out' });
	btnOff = new FrameAnimation();
	btnOff.addKeyFrame(0, { opacity: 0.8 });
	btnOff.addKeyFrame(2, { opacity: 1 });
	btnOff.setup({ easing: 'ease-in-out' });
	*/
}

HuntArea.prototype.onOpen = function (params, cb) {
	var lastUpdate = localStorage.getItem('lastUpdate');
	var now = Date.now();
	if (!lastUpdate || now - Number(lastUpdate) >= this.updateInterval) {
		localStorage.setItem('lastUpdate', lastUpdate);
		var self = this;
		window.server.send('demo.get_areas', null, function (error, list) {
			if (error) {
				return logger.error(error);
			}
			self.list = list;
			self.update();
			self.updateList(cb);
		});
	} else {
		this.updateList(cb);
	}
	this.creatureBox.css({ display: 'none' });
};

HuntArea.prototype.onOpenComplete = function (params) {
	viewPort.overlay('TopMenu');
	var self = this;
	window.setTimeout(function () {
		self.displayView();
	}, 0);
};

HuntArea.prototype.getBoss = function () {
	return boss;
};

HuntArea.prototype.displayView = function () {
	var self = this;
	window.game.fadeIn.setTarget(this.creatureBox);
	window.game.fadeIn.once('start', function () {
		self.creatureBox.css({ display: 'block' });
		window.setTimeout(function () {
			self.scroller.refresh();
		}, 0);
	});
	window.game.fadeIn.start();
};

HuntArea.prototype.update = function (bossIn) {
	if (bossIn !== undefined) {
		boss = bossIn;
	}
	this.updateDisplay = true;
};

HuntArea.prototype.updateList = function (cb) {
	if (!this.updateDisplay) {
		return cb();
	}
	var self = this;
	var preloader = new Preloader();
	for (var i = 0, len = this.list.length; i < len; i++) {
		preloader.addImage(this.list[i].image);
	}
	if (boss) {
		var bossData = viewPort.views.Dictionary.getCreature(boss);
		if (bossData) {
			preloader.addImage(bossData.sprite);
		}
	}
	preloader.onComplete = function () {
		clean(self.creatureBox, function () {
			self.updateDisplay = false;
			displayList(self.creatureBox, self.list);
		});
		window.setTimeout(cb, 0);
	};
	preloader.loadImage(false);
};

function clean(container, cb) {
	container.innerHTML = '';
	window.setTimeout(cb);
}

function displayList(container, listSrc) {
	var gradient = '-webkit-gradient(linear, 0% 0%, 56% 40%, from(rgba(255, 255, 255, 0)), to(rgba(255, 255, 255, 0.8)))';
	var shadow = '1px 1px 0 #666, -1px -1px 0 #666, 1px -1px 0 #666, -1px 1px 0 #666';
	var red = '1px 1px 0 #600, -1px -1px 0 #600, 1px -1px 0 #600, -1px 1px 0 #600';
	var list = listSrc.concat([]);
	if (boss) {
		var bossData = viewPort.views.Dictionary.getCreature(boss);
		if (bossData) {
			list.push({
				image: bossData.sprite,
				background: bossBk,
				description: 'Boss Hunt',
				name: bossData.name,
				cost: ' Energy: -3',
				id: 'boss'
			});
		}
		// show lightbox
		var html = '<p style="maring: 0; padding: 0; font-size: 15px; line-height: 19px;">';
		html += '<strong style="color: #66f;">' + bossData.name + '</strong><br /> has appeared!<br />';
		html += '<img src="' + bossData.sprite + '" width="60px" /></p>';
		window.game.openLightBox(html, '#f80');
	}
	for (var i = list.length - 1; i >= 0; i--) {
		var cell = container.create('div');
		var bkSize = '100%';
		var bkPos = 'center';
		var ts = shadow;
		if (list[i].id === 'boss') {
			bkSize = 'auto 200%';
			bkPos = '10%';
			ts = red;
		}
		cell.css({
			width: '100%',
			height: '60px',
			backgroundImage: gradient + ', url(' + list[i].image + ')',
			backgroundSize: bkSize,
			backgroundPosition: bkPos,
			backgroundRepeat: 'no-repeat',
			color: '#fff',
			fontSize: '20px',
			fontWeight: 'bold',
			textAlign: 'right',
			lineHeight: '30px',
			borderTop: '1px solid #ccc',
			borderBottom: '1px solid #ccc',
			textShadow: ts
		});
		var desc = (list[i].description || '') + (list[i].cost || ' Energy: -1');
		cell.innerHTML = list[i].name + '&nbsp;&nbsp;<br />' + '<p style="font-size: 12px; line-height: 13px;">' + desc + '&nbsp;&nbsp;&nbsp;&nbsp;</p>';
		var bgList = [];
		for (var k = 0, klen = list[i].background.length; k < klen; k++) {
			bgList.push(list[i].background[k].background);
		}
		button.disableDefault(cell);
		button.create(cell, delegate(this, startHunt, list[i].id, bgList), 
		{ 
			onStart: function (e) { 
				e.preventDefault();
				/*
				btnOn.setTarget(e.srcElement);
				btnOff.setTarget(e.srcElement);
				btnOn.stop();
				btnOff.stop();
				btnOn.start();
				*/
				e.srcElement.style.opacity = 0.8;
			},
			onEnd: function (e) { 
				e.preventDefault();
				/*
				btnOn.setTarget(e.srcElement);
				btnOff.setTarget(e.srcElement);
				btnOn.stop();
				btnOff.stop();
				btnOff.start();
				*/
				e.srcElement.style.opacity = 1;
				window.game.playAudio('click');
			},
			onCancel: function (e) { 
				e.preventDefault();
				/*
				btnOn.setTarget(e.srcElement);
				btnOff.setTarget(e.srcElement);
				btnOn.stop();
				btnOff.stop();
				btnOff.start();
				*/
				e.srcElement.style.opacity = 1;
			}
		});
	}
}

function startHunt(id, bgList) {
	if (id === 'boss') {
		window.viewPort.open('Battle', { background: true, bgList: bgList, battleType: 'boss_hunt', back: 'HuntArea' });
	} else {
		window.viewPort.open('Battle', { background: true, bgList: bgList, area_id: id, battleType: 'get_hunt_creatures', back: 'HuntArea' });
	}
}

window.HuntArea = HuntArea;

}());











