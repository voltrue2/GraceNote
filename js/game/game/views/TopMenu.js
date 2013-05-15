(function () {

var level = null;
var xp = null;
var name = null;
var huntEnergy = null;
var credit = null;
var dropIn = null;
var riseIn = null;
var closed = 0;
var timedData = null;
var huntTimer = null;

function TopMenu (viewElement) {
	this.parent = viewElement;
	this.parent.css({ pointerEvents: 'none' });
	this.header = this.parent.create('div');
	this.header.addClass('header');
	this.header.css({
		width: '100%',
		height: '29px',
		minHeight: '29px',
		background: '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#7A8F68), to(#556644), color-stop(.6,#2B331E))',
		borderBottom: '1px solid #999',
		WebkitTransform: 'translate(0, -29px)'
	});
	level = this.header.create('div');
	level.css({
		height: '21px',
		background: '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#7A8F68), to(#556644), color-stop(.6,#2B331E))',
		WebkitShadow: '0 0 0 #999',
		WebkitBorderRadius: '4px',
		border: '1px solid #444',
		position: 'relative',
		top: '3px',
		textAlign: 'center',
		color: '#fff',
		fontSize: '12px',
		lineHeight: '21px',
		textShadow: '#000 1px 1px 0',
		fontWeight: 'bold'
	});
	level.style.width = '90px';
	level.style.marginLeft = '10px';
	level.textContent = '';
	xp = this.header.create('div');
	xp.style.width = '170px';
	xp.style.height = '6px';
	xp.style.margin = '-8px 102px';
	xp.style.background = '#000';
	xp.style.webkitBorderRadius = '0px';
	xp.progress = xp.create('div');
	xp.progress.style.width = '170px';
	xp.progress.style.height = '4px';
	xp.progress.style.borderTop = '1px solid #2B331E';
	xp.progress.style.borderBottom = '1px solid #2B331E';
	xp.progress.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#FFDD00), to(#A36D02))';
	xp.progress.style.WebkitBorderRadius = '0px';
	xp.progress.style.WebkitTransformOrigin = '0';
	xp.progress.style.WebkitTransform = 'scale(0, 1)'
	xp.num = xp.create('div');
	xp.num.style.color = '#fff';
	xp.num.style.textAlign = 'center';
	xp.num.style.margin = '-16px';
	xp.num.style.fontWeight = 'bold';
	xp.num.style.fontSize = '8px';
	xp.num.style.textShadow = '1px 1px 0 #333, -1px -1px 0 #333, 1px -1px 0 #333, -1px 1px 0 #333';
	dropIn = new FrameAnimation(this.header);
	dropIn.addKeyFrame(0, { WebkitTransform: 'translate(0, -29px)' });
	dropIn.addKeyFrame(4, { WebkitTransform: 'translate(0, 0)' });
	dropIn.setup({ easing: 'ease-in-out' });
	// footer
	this.footer = this.parent.create('div');
	this.footer.addClass('footer');
	this.footer.css({
		width: '100%',
		height: '29px',
		minHeight: '29px',
		background: '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#7A8F68), to(#556644), color-stop(.6,#2B331E))',
		borderBottom: '1px solid #999',
		WebkitTransform: 'translate(0, -29px)',
		position: 'absolute',
		bottom: '-29px'	
	});
	name = this.footer.create('span');
	name.css({
		width: '150px',
		color: '#fff',
		fontWeight: 'bold',
		color: '#fff',
		fontSize: '15px',
		lineHeight: '32px',
		textShadow: '#000 1px 1px 0',
		paddingLeft: '10px'
	});
	huntEnergy = {};
	huntEnergy.energy = this.footer.create('div');
	huntEnergy.energy.css({
		color: '#fff',
		fontWeight: 'bold',
		fontSize: '10px',
		lineHeight: '15px',
		textShadow: '#000 1px 1px 0',
		position: 'absolute',
		top: 0,
		right: '10px',
	});
	huntEnergy.clock = this.footer.create('div');
	huntEnergy.clock.css({
		color: '#fff',
		fontWeight: 'bold',
		fontSize: '10px',
		lineHeight: '15px',
		textShadow: '#000 1px 1px 0',
		position: 'absolute',
		top: '14px',
		right: '10px',
	});
	credit = this.footer.create('div');
	credit.css({
		width: '200px',
		color: '#fff',
		fontWeight: 'bold',
		color: '#fff',
		fontSize: '12px',
		lineHeight: '32px',
		textShadow: '#000 1px 1px 0',
		position: 'absolute',
		right: '0',
		top: 0
	});
	riseIn = new FrameAnimation(this.footer);
	riseIn.addKeyFrame(0, { WebkitTransform: 'translate(0, 0)' });
	riseIn.addKeyFrame(4, { WebkitTransform: 'translate(0, -29px)' });
	riseIn.setup({ easing: 'ease-in-out' });
}

TopMenu.prototype = new EventEmitter();

TopMenu.prototype.update = updateUserData;

TopMenu.prototype.onOpen = function (params, cb) {
	dropIn.onFinish = null;
	riseIn.onFinish = null;
	closed = 0;
	updateUserData();
	cb();
};

TopMenu.prototype.onOpenComplete = function (params) {
	dropIn.start();
	riseIn.start();
};

TopMenu.prototype.onClose = function (params, cb) {
	var self = this;
	dropIn.onFinish = function () {
		if (closed === 0) {
			closed += 1;
			cb();
			self.emit('close');
		}
	};
	dropIn.reverse();
	riseIn.onFinish = function () {
		if (closed === 0) {
			closed += 1;
			cb();
			self.emit('close');
		}
	};
	riseIn.reverse();
};

function updateHuntEnergy() {
	if (window.game.session.data.huntEnergy && window.game.session.data.huntEnergy.value !== undefined) {
		var he = window.game.session.data.huntEnergy;
		huntEnergy.energy.textContent = '';
		huntEnergy.clock.textContent = '';
		// setup
		if (!timedData) {
			timedData = new TimedNumber(he);
		} else {
			timedData.update(he);
		}
		if (huntTimer) {
			clearInterval(huntTimer);
			huntTimer = null;
		}
		huntEnergy.clock.textContent = '';
		if (!huntTimer) {
			huntTimer = setInterval(function () {
				var now = Date.now();
				var lastUpdate = timedData.getLastUpdate();
				var diff = now - lastUpdate;
				var d = new Date(diff);
				var total = new Date(timedData.getInterval());
				var time = total.getTime() - d.getTime();
				var anchorSec = Math.floor(time / 1000);
				huntEnergy.energy.textContent = 'Energy: ' + timedData.getValue();
				if (time >= 0 && timedData.getValue() < timedData.getMaxValue()) { 
					var min = 0;
					if (anchorSec > 60) {
						min = Math.floor(anchorSec / 60);
					}
					var sec = anchorSec - (min * 60);
					huntEnergy.clock.textContent = pad(min) + ':' + pad(sec);
				} else {
					huntEnergy.clock.textContent = '';
				}
			}, 1000);
		}
	}
};

function updateUserData(){
	if (window.game.session) {
		level.textContent = 'Level ' + window.game.session.data.lvl;
		xp.num.textContent = 'Exp : ' + comma(game.session.data.xp) + ' / ' + comma(game.session.data.next_xp);
		xp.progress.style.WebkitTransform = 'scale(' + game.session.data.xp / game.session.data.next_xp + ', 1)';
		name.textContent = window.game.session.name;
		updateHuntEnergy();
		credit.textContent = 'Credit: ' + comma(window.game.session.data.credit || 0);
	}
}

window.TopMenu = TopMenu;

}());
