function Main (viewElement) {
	this.parent = viewElement;
	this.parent.style.background = '#fff';
	this.bg = '/img/contents/src/background/colorful.jpg';
	// build the UI
	this.stage = this.parent.create('div');
	this.stage.style.background = 'rgba(255, 255, 255, 0) no-repeat';
	this.stage.style.height = '100%';
	// hunting area
	var btn = this.stage.create('div');
	btn.style.textAlign = 'center';
	btn.style.padding = '4px 10px';
	btn.style.color = '#fff';
	btn.style.border = '2px solid rgba(0, 0, 0, 0.4)';
	btn.style.WebkitBorderRadius = '8px';
	btn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#9C9CFF), to(#3232CF))';
	btn.style.width = '100px';
	btn.style.height = '30px';
	btn.style.lineHeight = '30px';
	btn.textContent = 'Go Hunting';
	btn.style.top = '190px';
	btn.style.left = '13px';
	btn.style.position = 'relative';
	button.create(btn, function () {
		window.viewPort.open('HuntArea');
	});
	// my team
	this.team = this.stage.create('div');
	this.team.list = [];
	this.team.css({ 
		position: 'absolute', 
		top: '60px', 
		left: '0',
		width: '100%'
	});
	for (var i = 0; i < 4; i++) {
		var item = this.team.create('div');
		item.css({
			cssFloat: 'left',
			width: '80px',
			height: '120px'
		});
		this.team.list.push(item);
	}
	// duel
	var btn = this.stage.create('div');
	btn.style.textAlign = 'center';
	btn.style.padding = '4px 10px';
	btn.style.color = '#fff';
	btn.style.border = '2px solid rgba(0, 0, 0, 0.4)';
	btn.style.WebkitBorderRadius = '8px';
	btn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#FF9C9C), to(#CF3232))';
	btn.style.width = '100px';
	btn.style.height = '30px';
	btn.style.lineHeight = '30px';
	btn.textContent = 'Duel Hunt';
	btn.style.margin = '0 auto';
	btn.style.top = '148px';
	btn.style.left = '85px';
	btn.style.position = 'relative';
	button.create(btn, function () {
		
	});
	button.disable(btn);
	// dictionary
	var btn = this.stage.create('div');
	btn.style.textAlign = 'center';
	btn.style.padding = '4px 10px';
	btn.style.color = '#fff';
	btn.style.border = '2px solid rgba(0, 0, 0, 0.4)';
	btn.style.WebkitBorderRadius = '8px';
	btn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#E06919), to(#661E0C))';
	btn.style.width = '150px';
	btn.style.height = '30px';
	btn.style.lineHeight = '30px';
	btn.textContent = 'Hunt Record';
	btn.style.margin = '0 auto';
	btn.style.top = '155px';
	btn.style.position = 'relative';
	button.create(btn, function () {
		window.viewPort.open('Dictionary');
	});
	
	// team management
	var btn = this.stage.create('div');
	btn.style.textAlign = 'center';
	btn.style.padding = '4px 10px';
	btn.style.color = '#fff';
	btn.style.border = '2px solid rgba(0, 0, 0, 0.4)';
	btn.style.WebkitBorderRadius = '8px';
	btn.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#0c0), to(#060))';
	btn.style.width = '150px';
	btn.style.height = '30px';
	btn.style.lineHeight = '30px';
	btn.textContent = 'Manage Team';
	btn.style.margin = '0 auto';
	btn.style.top = '165px';
	btn.style.position = 'relative';
	button.create(btn, function () {
		window.viewPort.open('TeamManagement', { back: 'Main' });
	});
}

Main.prototype.onOpen = function (params, cb) {
	var self = this;
	preloader = new Preloader();
	preloader.addImage(this.bg);
	preloader.onComplete = function (error, data) {
		self.parent.style.background = 'url(' + self.bg + ') no-repeat';
		self.parent.style.WebkitBackgroundSize = '100% 100%';
		cb();
	};
	preloader.loadImage(false);
	viewPort.overlay('TopMenu');
};

Main.prototype.onOpenComplete = function (params) {
	var teamList = window.viewPort.views.TeamManagement.teamList;
	var creatureList = window.viewPort.views.TeamManagement.list.creature_list;
	if (!creatureList) {
		return viewPort.open('TeamManagement');
	}
	var clen = creatureList.length;
	for (var i = 0, len = this.team.list.length; i < len; i++) {
		var item = this.team.list[i];
		var data = teamList[i];
		if (data) {
			item.css({
				background: 'url(' + data.creature.sprite + ') no-repeat',
				backgroundSize: '100% auto',
				backgroundPosition: '50%'
			});
			button.destroy(item);
			button.create(item, bind(null, function (event, creatureId) {
				var creatureData = null;
				for (var j = 0; j < clen; j++) {
					if (creatureList[j].id === creatureId) {
						creatureData = creatureList[j];
						break;
					}
				}
				viewPort.open('Creature', { creatureData: creatureData, back: 'Main' });
			}, data.creature_id));
		} else {
			item.css({
				background: ''
			});
			button.destroy(item);
		}
	}
};

Main.prototype.onClose = function (params, cb) {
	cb();
};
