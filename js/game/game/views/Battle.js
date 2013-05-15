function Battle (parent) {
	this.bg = [
		'/img/contents/src/background/dark-flame.jpg', 
		'/img/contents/src/background/red-space.jpg', 
		'/img/contents/src/background/other-dimension.jpg', 
		'/img/contents/src/background/fire.jpg', 
		'/img/contents/src/background/mechanical.jpg',
		'/img/contents/src/background/smoke.jpg',
		'/img/contents/src/background/dark-light.jpg',
		'/img/contents/src/background/time.jpg',
		'/img/contents/src/background/stars.jpg',
		'/img/contents/src/background/frozen.jpg',
		'/img/contents/src/background/blue-orange.jpg',
		'/img/contents/src/background/ice.jpg',
		'/img/contents/src/background/green-bubbles.jpg',
		'/img/contents/src/background/star-storm.jpg',
		'/img/contents/src/background/green-blocks.jpg',
		'/img/contents/src/background/green-orange.jpg',
		'/img/contents/src/background/fog.jpg',
		'/img/contents/src/background/light-fog.jpg',
		'/img/contents/src/background/red-rings.jpg',
		'/img/contents/src/background/lines.jpg'
	];
	
	this.img = {
		base: '/img/contents/src/ui/base.png',
		vs: '/img/contents/src/ui/vs-big.png',
		fire: '/img/contents/src/ui/fire.png',
		elec: '/img/contents/src/ui/elec.png',
		ice: '/img/contents/src/ui/ice.png',
		wind: '/img/contents/src/ui/wind.png',
		light: '/img/contents/src/ui/light.png',
		dark: '/img/contents/src/ui/dark.png',
		almighty: '/img/contents/src/ui/almighty.png',
		phys: '/img/contents/src/ui/phys.png',
		weekness: '/img/contents/src/ui/big-hit.png',
		exc: '/img/contents/src/ui/exclamation-mark.png'
	};
	
	this.grid = '/img/contents/src/background/grid2.png';
	
	this.parent = parent;
	this.parent.style.WebkitBackgroundSize = '100% 100%';
	this.fps = null;
	this.back = null;
	this.me = null;
	this.opponent = null;
	this.prevWinner = null;
	this.result = null;
	this.initialTurn = null;
	this.mf = null;
	this.of = null;
	this.mHp = null;
	this.oHp = null;
	this.mWins = 0;
	this.oWins = 0;
	this.battleCounter = 0;
	this.damageStyle = '1px 1px 0 #900, -1px -1px 0 #900, 1px -1px 0 #900, -1px 1px #900';
	//this.hpBarAnim = null;
	var self = this;
	var bk = null;
	// setup stage
	this.container = this.parent.create('div');
	this.container.css({ width: '320px', height: '100%', position: 'absolute', top: '0%', left: 0 });
	this.stage = this.container.create('div');
	this.stage.css({ width: '320px', height: '420px', position: 'absolute', top: '50%', left: 0, marginTop: '-210px' });
	this.msgBox = this.stage.create('div');
	this.msgBox.style.background = 'rgba(255, 255, 255, 0.6)';
	this.msgBox.style.width = '140px';
	this.msgBox.style.height = '25px';
	this.msgBox.style.WebkitBorderRadius = '5px';
	this.msgBox.style.textAlign = 'left';
	this.msgBox.style.padding = '5px';
	this.msgBox.style.color = '#000';
	this.msgBox.style.fontWeight = 'bold';
	this.msgBox.style.fontSize = '15px';
	this.msgBox.style.lineHeight = '20px';
	this.msgBox.style.position = 'relative';
	this.msgBox.style.zIndex = 40;
	this.lightbox = this.parent.create('div');
	this.lightbox.style.width = '200px';
	this.lightbox.style.minHeight = '100px';
	this.lightbox.style.padding = '10px';
	this.lightbox.style.fontWeight = 'bold';
	this.lightbox.style.WebkitBorderRadius = '10px';
	this.lightbox.style.margin = 'auto';
	this.lightbox.style.WebkitTransform = 'translate3d(0, 128px, 1px)';
	this.lightbox.style.opacity = 0;
	this.lightbox.style.display = 'none';
	this.lightbox.style.textAlign = 'center';
	this.lightbox.style.fontSize = '30px';
	this.lightbox.style.lineHeight = '100px';
	this.lightbox.style.textShadow = '1px 1px 0 #fff, -1px -1px 0 #fff, 1px -1px 0 #fff';
	this.lightbox.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#FFFFFF), to(#C9D8FF))';
	this.lightbox.style.WebkitBoxShadow = '0 0 6px 1px #000'; 
	this.lightbox.style.zIndex = 500;
	this.lightbox.style.position = 'absolute';
	this.lightbox.style.top = '50%';
	this.lightbox.style.left = '15%';
	this.lightbox.style.marginTop = '-200px';
	// cutscene
	this.overlay = this.container.create('div');
	this.overlay.style.width = '100%';
	this.overlay.style.height = '100%';
	this.overlay.style.background = 'rgba(0, 0, 0, 0.7)';
	this.overlay.style.position = 'absolute';
	this.overlay.style.top = 0;
	this.overlay.style.left = 0;
	this.overlay.style.zIndex = 30;
	this.overlay.style.display = 'none';
	this.exc = this.stage.create('div');
	this.exc.style.background = 'url(' + this.img.exc + ') no-repeat';
	this.exc.style.width = '60px';
	this.exc.style.height = '60px';
	this.exc.style.WebkitBackgroundSize = '100%';
	this.exc.style.display = 'none';
	this.cutBg = this.container.create('div')
	this.cutBg.style.borderTop = '2px solid #fff';
	this.cutBg.style.borderBottom = '2px solid #fff';
	this.cutBg.style.width = '100%';
	this.cutBg.style.height = '80px';
	this.cutBg.style.position = 'absolute';
	this.cutBg.style.top = '160px';
	this.cutBg.style.left = 0;
	this.cutBg.style.zIndex = 50;
	this.cutBg.style.display = 'none';
	this.cutBg.sprite = this.cutBg.create('div');	
	this.cutBg.sprite.style.backgroundPosition = '30% 30%';
	this.cutBg.sprite.style.WebkitBackgroundSize = '100% auto';
	this.cutBg.sprite.style.position = 'absolute';
	this.cutBg.sprite.style.left = '85px';
	this.cutBg.sprite.style.width = '140px';
	this.cutBg.sprite.style.height = '80px';
	// souls
	this.mSouls = this.stage.create('div');
	this.oSouls = this.stage.create('div');
	// vs zoom in
	var vs = this.container.create('div');
	vs.style.background = 'url(' + this.img.vs + ') no-repeat';
	vs.style.WebkitBackgroundSize = '100%';
	vs.style.width = '120px';
	vs.style.height = '100px';
	vs.style.opacity = 0;
	vs.style.zIndex = 700;
	vs.style.display = 'none';
	vs.style.position = 'absolute';
	vs.style.top = '50%';
	vs.style.left = '100px';
	vs.style.marginTop = '-50px';
	vs.style.WebkitTransform = 'translate3d(0, 0, 1px)';
	var vsa = new FrameAnimation(vs);
	var p = '0, 0';
	vsa.addKeyFrame(0, {opacity: 0, WebkitTransform: 'translate3d(0, 0, 1px) scale(2.5)'});
	vsa.addKeyFrame(2, {opacity: 1, WebkitTransform: 'translate3d(0, 0, 1px) scale(1)'});
	vsa.addKeyFrame(15, {opacity: 1, WebkitTransform: 'translate3d(0, 0, 1px) scale(1)'});
	vsa.addKeyFrame(17, {opacity: 0, WebkitTransform: 'translate3d(0, 0, 1px) scale(2.5)'});
	vsa.addKeyFrame(22, {opacity: 0, WebkitTransform: 'translate3d(0, 0, 1px) scale(2.5)'});
	vsa.onFinish = function () {
		vs.style.display = 'none';
		if (!self.initialTurn.cutscene) {
			self.initialTurn.action.start();
		}
		else {
			self.cutscene(self.initialTurn);
		}
	};
	// white fade in
	this.white = this.parent.create('div');
	this.white.style.width = '100%';
	this.white.style.height = '100%';
	this.white.style.background = '#fff';
	this.white.style.position = 'absolute';
	this.white.style.top = 0;
	this.white.style.left = 0;
	this.white.style.zIndex = 900;
	this.white.style.WebkitTransform = 'translate3d(0, 0, 1px)';
	this.fa = new FrameAnimation(this.white);
	this.fa.addKeyFrame(0, {opacity: 1, WebkitTransform: 'translate3d(0, 0, 1px)'});
	this.fa.addKeyFrame(10, {opacity: 0, WebkitTransform: 'translate3d(0, 0, 1px)'});
	this.fa.setup({easing: 'ease-in-out'});
	var self = this;
	this.fa.onFinish = function () {
		self.white.style.display = 'none';
		vs.style.display = 'block';
		window.game.playAudio('battle');
		vsa.start();
	};
	// damage number animation	
	this.damage = new FrameAnimation(null, this.fps);
	this.damage.addKeyFrame(0, {opacity: 1, WebkitTransform: 'translate3d(0, 0, 1px)'});
	this.damage.addKeyFrame(2, {opacity: 1, WebkitTransform: 'translate3d(0, -10px, 1px)'});
	this.damage.addKeyFrame(4, {opacity: 1, WebkitTransform: 'translate3d(0, -20px, 1px)'});
	this.damage.addKeyFrame(12, {opacity: 0, WebkitTransform: 'translate3d(0, -30px, 1px)'});
	this.damage.setup({easing: 'ease-out', keepLastState: false});
	// big damage number animation	
	this.bigDamage = new FrameAnimation(null, this.fps);
	this.bigDamage.addKeyFrame(0, {opacity: 0, WebkitTransform: 'translate3d(0, 0, 1px) scale(1.3, 1.7)'});
	this.bigDamage.addKeyFrame(2, {opacity: 1, WebkitTransform: 'translate3d(0, -5px, 1px) scale(1.3, 1.7)'});
	this.bigDamage.addKeyFrame(4, {opacity: 1, WebkitTransform: 'translate3d(0, -15px, 1px) scale(1)'});
	this.bigDamage.addKeyFrame(12, {opacity: 0, WebkitTransform: 'translate3d(0, -25px, 1px) scale(1)'});
	this.bigDamage.setup({easing: 'ease-out'});
	// attack animations
	var n = 30;
	this.attackL = new FrameAnimation(null, this.fps);
	this.attackL.addKeyFrame(0, {WebkitTransform: 'scale(1) translate3d(0, 0, 0)'});
	this.attackL.addKeyFrame(1, {WebkitTransform: 'scale(1.1) translate3d(' + n + 'px, ' + n + 'px, 0)'});
	this.attackL.addKeyFrame(3, {WebkitTransform: 'scale(1) translate3d(0, 0, 0)'});	
	this.attackR = new FrameAnimation(null, this.fps);
	this.attackR.addKeyFrame(0, {WebkitTransform: 'scale(1) translate3d(0, 0, 0)'});
	this.attackR.addKeyFrame(1, {WebkitTransform: 'scale(0.9) translate3d(' + n + 'px, -' + n + 'px, 0)'});
	this.attackR.addKeyFrame(3, {WebkitTransform: 'scale(1) translate3d(0, 0, 0)'});
	// fighter fade in
	this.fighterIn = new FrameAnimation(null, this.fps);
	this.fighterIn.addKeyFrame(0, {opacity: 0, WebkitTransform: 'scale(0, 3)'});
	this.fighterIn.addKeyFrame(4, {opacity: 1, WebkitTransform: 'scale(1.5, 0.7)'});
	this.fighterIn.addKeyFrame(6, {opacity: 1, WebkitTransform: 'scale(1, 1)'});
	this.fighterIn.addKeyFrame(9, {opacity: 1, WebkitTransform: 'scale(1, 1)'});
	this.fighterIn.onFinish = function () {
		if (!self.initialTurn.cutscene) {
			self.initialTurn.action.start();
		}
		else {
			self.cutscene(self.initialTurn);
		}
	};
	// fighter fade out
	this.fighterOutLeft = new FrameAnimation(null, this.fps);
	this.fighterOutLeft.addKeyFrame(0, {opacity: 1, WebkitTransform: 'translate3d(0, 0, 1px) scale(1)'});
	this.fighterOutLeft.addKeyFrame(6, {opacity: 0, WebkitTransform: 'translate3d(-25px, 35px, 1px) scale(1.1)'});
	this.fighterOutLeft.setup({easing: 'ease-out'});
	this.fighterOutRight = new FrameAnimation(null, this.fps);
	this.fighterOutRight.addKeyFrame(0, {opacity: 1, WebkitTransform: 'translate3d(0, 0, 1px) scale(1)'});
	this.fighterOutRight.addKeyFrame(6, {opacity: 0, WebkitTransform: 'translate3d(-25px, -35px, 1px) scale(0.9)'});
	this.fighterOutRight.setup({easing: 'ease-out'});
	// lightbox
	this.lightboxFade = new FrameAnimation(this.lightbox);
	this.lightboxFade.addKeyFrame(0, {opacity: 0, WebkitTransform: 'translate3d(0, 148px, 2px)'});
	this.lightboxFade.addKeyFrame(3, {opacity: 1, WebkitTransform: 'translate3d(0, 128px, 2px)'});
	this.lightboxFade.setup({easing: 'ease-in-out'});
	this.lightboxFade.onFinish = function () {
		button.enable(self.lightbox);
	};
	this.lightboxFadeOut = new FrameAnimation(this.lightbox);
	this.lightboxFadeOut.addKeyFrame(0, {opacity: 1, WebkitTransform: 'translate3d(0, 128px, 2px) scale(1)'});
	this.lightboxFadeOut.addKeyFrame(3, {opacity: 0, WebkitTransform: 'translate3d(0, 128px, 2px) scale(1.2)'});
	this.lightboxFadeOut.addKeyFrame(6, {opacity: 0, WebkitTransform: 'translate3d(0, 128px, 2px) scale(1.2)'});
	this.lightboxFadeOut.setup({easing: 'ease-in-out'});
	this.lightboxFadeOut.onFinish = function () {
		// check for the reward(s)
		if (self.result.rewards && self.result.rewards.creature) {
			var c = self.result.rewards.creature;
			// this play reward popup 
			var text = '<p style="maring: 0; padding: 0; font-size: 15px; line-height: 19px;">Captured&nbsp; ';
			text += '<strong style="color: #66f;">' + c.name + '</strong><br />';
			text += '<img src="' + c.sprite + '" width="60px" /></p>';
			self.createLightBox(text, '#fc6');
			self.result.rewards.creature = null;
		}
		else if (self.result.levelUp) {
			// level up
			var text = 'Level Up!';
			self.createLightBox(text, '#fc6');
			self.result.levelUp = false;
		}
		else {
			// close the battle view and go back to where you came from
			viewPort.open(self.back);
		}
	};
	button.create(this.lightbox, function (e) {
		button.disable(self.lightbox);
		self.lightboxFadeOut.start();
	}, { onStart: function (e) { e.preventDefault(); } });
	button.disableDefault(this.lightbox);
	// close view 
	this.closeAnimation = new FrameAnimation(this.stage);
	this.closeAnimation.addKeyFrame(0, {opacity: 1});
	this.closeAnimation.addKeyFrame(20, {opacity: 0});
	this.closeAnimation.setup({keepLastState: false});
	// my name and opponent name
	this.myName = this.stage.create('div');
	this.myName.style.position = 'absolute';
	this.myName.style.top = '382px';
	this.myName.style.left = '173px';
	this.myName.style.color = '#fff';
	this.myName.style.fontSize = '12px';
	this.myName.style.width = '130px';
	this.myName.style.fontWeight = 'bold';
	this.myName.style.padding = '4px';
	this.myName.style.textAlign = 'center';
	this.myName.style.borderTop = '1px solid rgba(255, 255, 255, 0.5)';
	this.myName.style.borderBottom = '1px solid rgba(255, 255, 255, 0.5)';
	this.myName.style.textShadow = '1px 1px 0 #090, -1px -1px 0 #090, 1px -1px 0 #090, -1px 1px 0 #090';
	this.myName.style.background = '-webkit-gradient(linear, 100% 100%, 0% 100%, from(rgba(255, 255, 255, 0)), to(rgba(255, 255, 255, 0)), color-stop(0.5, rgba(255, 255, 255, 0.5)))';
	this.oppName = this.stage.create('div');
	this.oppName.style.position = 'absolute';
	this.oppName.style.top = '6px';
	this.oppName.style.left = '16px';
	this.oppName.style.color = '#fff';
	this.oppName.style.fontSize = '12px';
	this.oppName.style.width = '130px';
	this.oppName.style.fontWeight = 'bold';
	this.oppName.style.padding = '4px';
	this.oppName.style.textAlign = 'center';
	this.oppName.style.borderTop = '1px solid rgba(255, 255, 255, 0.5)';
	this.oppName.style.borderBottom = '1px solid rgba(255, 255, 255, 0.5)';
	this.oppName.style.textShadow = '1px 1px 0 #600, -1px -1px 0 #600, 1px -1px 0 #600, -1px 1px 0 #600';
	this.oppName.style.background = '-webkit-gradient(linear, 100% 100%, 0% 100%, from(rgba(255, 255, 255, 0)), to(rgba(255, 255, 255, 0)), color-stop(0.5, rgba(255, 255, 255, 0.5)))';

	this.error = null;
}

Battle.prototype.onOpen = function (params, cb) {
	// set back
	this.back = params.back;
	// get battle result
	var self = this;
	server.send('demo.' + params.battleType, params, function (error, data) {
		if (error) {
			console.warn(error);
		}
		if (data.notEnoughEnergy) {
			window.game.openLightBox('<p style="font-size: 15px; line-height: 45px;">Not Enough Energy</p>', '#666');
			return viewPort.open(self.back);
		}
		// user gen error
		if (data.error) {
			self.error = data.error;
			return cb();
		}
		var result = data.result;
		// update session
		if (result.newSession) {
			window.game.session = result.newSession;
		}
		// my name
		if (window.game.session) { 
			self.myName.textContent = window.game.session.name;
		} 
		else { 
			self.myName.textContent = 'Unkown';
		}
		// opponent name
		self.oppName.textContent = result.opponentName;
		// set up variables
		self.result = result;
		self.me = result.me;
		self.opponent = result.opponent;
		// setup the battle
		self.startBattle(self.battleCounter);
		self.createTeam(self.mSouls, self.result.myTeam, 1);
		self.createTeam(self.oSouls, self.result.opponentTeam, -1);
		// pick background
		if (params.bgList) {
			self.bk = params.bgList[0, rand(0, params.bgList.length - 1)];
		} else {
			self.bk = self.bg[0, rand(0, self.bg.length - 1)];
		}
		// preload images
		preloader = new Preloader();
		preloader.addImage(self.bk);
		preloader.addImage(self.grid);
		for (var i in self.img) {
			preloader.addImage(self.img[i]);
		}
		for (var i = 0; i < result.myTeam.length; i++) { 
			preloader.addImage(result.myTeam[i].sprite);
		}
		for (var i = 0; i < result.opponentTeam.length; i++) { 
			preloader.addImage(result.opponentTeam[i].sprite);
		}
		preloader.onComplete = function () {
			viewPort.closeOverlay('TopMenu');
			viewPort.views.TopMenu.once('close', cb);
		};
		preloader.loadImage(false);	
	});
	this.white.style.opacity = 1;
	this.white.style.display = 'block';
};

Battle.prototype.onOpenComplete = function (params) {
	if (this.error) {
		window.game.openLightBox('<p style="maring: 0; padding: 0; font-size: 15px; line-height: 30px;">Your level is too low for this area</p>', '#666');
		return window.viewPort.open(this.back);
	}
	if (params.background) {
		this.parent.style.background = 'url(' + this.bk + ') no-repeat';
	}
	else {
		this.parent.style.background = '#333';
	}
	this.parent.style.WebkitBackgroundSize = '100% 100%';
	this.parent.style.backgroundPosition = 0; 
	this.container.style.background = 'url(' + this.grid + ')';
	this.container.style.WebkitBackgroundSize = '100% 100%';
	this.mf.sprite.style.opacity = 1;
	this.of.sprite.style.opacity = 1;
	// fade in
	this.fa.start();
};

Battle.prototype.onCloseComplete = function () {
	if (!this.error) {
		this.newSession = null;
		this.result = null;
		this.me = null;
		this.opponent = null;
		this.initialTurn = null;
		this.prevWinner = null;
		this.bk = null;
		this.mWins = 0;
		this.oWins = 0;
		this.battleCounter = 0;
		this.lightbox.style.opacity = 0;
		this.mf.style.WebkitTransform = this.mf.org;
		this.of.style.WebkitTransform = this.of.org;
		this.lightbox.style.display = 'none';
		this.oHp.enduranceColor = null;
		this.mHp.enduranceColor = null;
	}
	this.error = null;
};

Battle.prototype.startBattle = function () {
	// extract fight log
	var log = this.result.log[this.battleCounter];
	// extract teams
	var myTeam = this.result.myTeam;
	var oppTeam = this.result.opponentTeam;
	// extract fighters
	var m = null;
	var o = null;
	for (var i in myTeam) {
		if (myTeam[i].id == log.myTeam) {
			m = myTeam[i];
			break;
		}
	}
	for (var i in oppTeam) {
		if (oppTeam[i].id == log.opponentTeam) {
			o = oppTeam[i];
			break;
		}
	}	
	// create fighters
	var newFighter = null;
	if (!this.prevWinner || this.prevWinner === 'opp') {
		this.mf = this.createFighter(m, 1, this.mf);
		// hp bar
		this.mHp = this.createHpBar(m, 1, this.mHp);
		newFighter = this.mf;
	}
	if (!this.prevWinner || this.prevWinner === 'me') {
		this.of = this.createFighter(o, -1, this.of);
		// hp bar
		this.oHp = this.createHpBar(o, -1, this.oHp);
		newFighter = this.of;
	}
	// fight now
	this.initialTurn = this.createTurn(log.fight, 0, this.mf, this.of);
	if (this.battleCounter > 0) {
		// new figther entering
		this.fighterIn.setTarget(newFighter.sprite);
		this.fighterIn.start();
	}
};

Battle.prototype.createTurn = function (fightLog, counter, mf, of) {
	var attacker = null;
	var defender = null;
	var self = this;
	var offset = -1;
	var n = 30;
	var myTurn = false;
	var hpBar = null;
	if (mf.object.id == fightLog[counter].attacker) {
		attacker = mf;
		defender = of;
		hpBar = this.oHp;
		myTurn = true;
	}
	else {
		attacker = of;
		defender = mf;
		hpBar = this.mHp;
		offset = 1;
	}
	attacker.offset = offset;
	// display skill name
	this.msgBox.style.opacity = 0;
	this.msgBox.style.display = 'block';
	if (offset === 1) {
		this.msgBox.style.WebkitTransform = 'translate3d(160px, 10px, 1px)';
	}
	else {
		this.msgBox.style.WebkitTransform = 'translate3d(10px, 370px, 1px)';
	}	
	var style = 'background: url(' + this.img[fightLog[counter].action.element] + ') no-repeat; width: 20px; height: 25px; line-height: 25px; -webkit-background-size: 100%;';
	var icon = '<span style="' + style + '">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
	this.msgBox.innerHTML = icon + '  ' + fightLog[counter].action.name;
	// attacker action
	if (offset === 1) {
		var a = this.attackL;
	}
	else {
		var a = this.attackR;
	}
	a.setTarget(attacker.sprite);
	a.onStart = function () {
		// show skill name now
		self.msgBox.style.opacity = 1;
		// update hp bar
		var hp = fightLog[counter].defenderHp;
		if (hp < 1 && hp > 0) {
			hp = 1;
		}
		else if (hp < 0) {
			hp = 0;
		}
		var ratio = hp / hpBar.maxHp;
		hpBar.update(hp, ratio, fightLog[counter].endure);
		// show damage number
		if (!defender.damageNum) {
			var d = defender.create('div');
			defender.damageNum = d;
			d.style.color = '#fff';
			d.style.fontSize = '45px';
			d.style.fontWeight = 'bold';
			d.style.textShadow = self.damageStyle;
			d.style.textAlign = 'center';
			d.style.position = 'relative';
			d.style.top = '-85px';
			d.style.opacity = 0;
		}
		else {
			var d = defender.damageNum;
		}
		var damage = fightLog[counter].damage;
		var dstyle = 'text-shadow: 1px 1px 0 #f00, -1px -1px 0 #f00, 1px -1px 0 #f00; font-size: 13px; opacity: 0.6;';
		var rstyle = 'text-shadow: 1px 1px 0 #f00, -1px -1px 0 #f00, 1px -1px 0 #f00; font-size: 13px; opacity: 0.6;';
		var wstyle = 'text-shadow: 1px 1px 0 #99f, -1px -1px 0 #99f, 1px -1px 0 #99f; font-size: 13px; opacity: 0.6;';
		var pstyle = 'text-shadow: 1px 1px 0 #f99, -1px -1px 0 #f99, 1px -1px 0 #f99; font-size: 13px; opacity: 0.6;';
		if (fightLog[counter].weakness) {
			damage = '<span style="' + wstyle + ' color: #00f;">WEAKNESS</span><br />' + damage;
		}
		else if (fightLog[counter].block) {
			damage = '<span style="' + dstyle + ' color: #999;">BLOCKED</span><br />' + damage;
		}
		else if (fightLog[counter].charge) {
			damage = '<span style="' + dstyle + ' color: #fc0;">CHARGE</span><br />' + damage;
		}
		else if (fightLog[counter].break) {
			damage = '<span style="' + pstyle + ' color: #f00;">PIERCED</span><br />' + damage;
		}
		else if (fightLog[counter].revenge) {
			damage = '<span style="' + rstyle + ' color: #000;">REVENGE</span><br />' + damage;
		}
		else if (fightLog[counter].hitType < 1) {
			damage = '<span style="' + dstyle + ' color: #fff;">RESISTED</span><br />' + damage;
		}
		if (fightLog[counter].weakness || fightLog[counter].critical) {
			// weakness
			var dd = self.bigDamage;
			d.innerHTML = '<div style="text-align: center; background: url(' + self.img.weekness + ') 50% no-repeat; width: 100%; height: 53px; margin: 0 auto;">' + damage + '</div>';
		}
		else {
			var dd = self.damage;
			d.innerHTML = damage;
		}
		dd.setTarget(d);
		dd.onFinish = function () {
			self.msgBox.style.display = 'none';
			counter++;
			if (fightLog[counter]) {
				// next turn
				self.initialTurn = self.createTurn(fightLog, counter, mf, of);
				if (!self.initialTurn.cutscene) {
					self.initialTurn.action.start();
				}
				else {
					self.cutscene(self.initialTurn);
				}
			}
			else {
				var fighterOut = self.fighterOutLeft;
				if (defender.dir < 1) {
					fighterOut = self.fighterOutRight;
				}
				// defender dies...
				fighterOut.setTarget(defender.sprite);
				fighterOut.onStart = function () {
					attacker.style.WebkitTransform = attacker.adjust;
					defender.style.WebkitTransform = defender.adjust;
				};
				fighterOut.onFinish = function () {
					if (myTurn) {
						self.prevWinner = 'me';
						self.mWins++;
						self.createTeam(self.oSouls, self.result.opponentTeam, -1);
					}
					else {
						self.prevWinner = 'opp';
						self.oWins++;
						self.createTeam(self.mSouls, self.result.myTeam, 1);
					}
					attacker.style.WebkitTransform = attacker.org;
					defender.style.WebkitTransform = defender.org;
					// start next battle
					self.battleCounter++;
					if (self.result.log[self.battleCounter]) {
						self.startBattle();
					}
					else {
						attacker.style.WebkitTransform = attacker.adjust;
						defender.style.WebkitTransform = defender.adjust;
						// battle is over > declear the winner
						var text = '<p style="line-height: 15px;">You Win</p>';
						text += '<p style="font-size: 12px; color: #66f; line-height: 14px;">You earned <strong>' + self.result.xp + '</strong>XP</p>';
						text += '<p style="font-size: 12px; color: #66f; line-height: 14px;">+<strong>' + self.result.credit + '</strong> Credit</p>';
						var c = '#fc0';
						if (!Boolean(self.result.youWin)) {
							text = '<p style="line-height: 15px;">You Lose</p>';
							text += '<p style="font-size: 12px; color: #66f; line-height: 14px;">+<strong>' + self.result.credit + '</strong> Credit</p>';
							c = '#666';
						}
						// light box
						self.createLightBox(text, c);
					}
				};
				fighterOut.start();
			}
		};
		dd.start();
	};
	var cutscene = false;
	if (fightLog[counter].critical > 0) {
		cutscene = true;
	}
	return {attacker: attacker, action: a, cutscene: cutscene};
};

Battle.prototype.cutscene = function (nextTurn) {
	var attacker = nextTurn.attacker;
	var action = nextTurn.action;
	var params = {};
	if (attacker.offset < 0) {
		params.top = {
			sprite: attacker.object.sprite,
			color: 'blue',
			direction: 'left'
		};
	} else {
		params.bottom = {
			sprite: attacker.object.sprite,
			color: 'red',
			direction: 'right'
		};
	}
	viewPort.overlay('CutIn', params);
	viewPort.once('closeOverlay', function () {
		action.start();
	});
};

Battle.prototype.createFighter = function (f, dir, fighter) {
	if (fighter) {
		var mf = fighter;
	}
	else {
		var mf = this.stage.create('div');
		mf.style.position = 'absolute';
		if (dir >= 1) {
			mf.style.WebkitTransform = 'translate3d(18px, 210px, 0)';
			mf.org = 'translate3d(18px, 210px, 0)';
			mf.adjust = 'translate3d(18px, 245px, 0)';
		}
		else {
			mf.style.WebkitTransform = 'translate3d(165px, 8px, 0)';
			mf.org = 'translate3d(165px, 8px, 0)';
			mf.adjust = 'translate3d(165px, 43px, 0)';
		}
		var h = mf.create('div');
		h.style.WebkitTransform = 'scale(' + dir + ', 1)';
		h.style.position = 'relative';
		var s = h.create('div');
		//s.style.width = '120px';
		//s.style.height = '130px';
		//s.style.WebkitBackgroundSize = '100%';
		s.style.WebkitTransformOrigin = '50% 100%';
		s.style.opacity = 1;
		var img = s.create('img');
		img.setAttribute('width', '130px');
		s.img = img;
		mf.sprite = s;
		mf.dir = dir;
	}
	mf.sprite.style.WebkitTransform = 'scale(1, 1)';
	mf.sprite.style.opacity = 1;
	mf.sprite.img.setAttribute('src', f.sprite);
	mf.direction = dir;
	mf.object = f;	
	return mf;
};

Battle.prototype.createHpBar = function (f, dir, hpBar) {
	var w = 130;
	var h = 10;
	if (hpBar) {
		var holder = hpBar;
	}
	else {
		var holder = this.stage.create('div');
		holder.enduranceColor = null;
		holder.style.position = 'absolute';
		if (dir < 0) {
			holder.style.top = '100px';
			holder.style.left = '15px';
		}
		else {
			holder.style.top = '290px';
			holder.style.left = '170px';
		}
		holder.className = 'hp-bar';
		var base = holder.create('div');
		base.style.width = w + 'px';
		base.style.height = h + 'px';
		base.style.background = '#300';
		base.style.WebkitBorderRadius = '5px';
		base.style.position = 'relative';
		base.style.top = '-10px';
		base.style.zIndex = 10;
		base.style.WebkitTransform = 'translate3d(0, 0, 1px)';
		var cover = holder.create('div');
		cover.style.border = '3px solid #600';
		cover.style.width = (w - 2) + 'px';
		cover.style.height = (h -2) + 'px';
		cover.style.WebkitBorderRadius = '5px';
		cover.style.WebkitTransform = 'translate3d(-2px, -22px, 1px)';
		cover.style.WebkitBoxShadow = '0 0 2px #000';
		cover.style.zIndex = 12;
		cover.style.position = 'relative';
		var num = holder.create('div');
		num.style.fontSize = '20px';
		num.style.fontWeight = 'bold';
		num.style.textShadow = '1px 1px 0 #600, -1px -1px 0 #600, 1px -1px 0 #600, -1px 1px 0 #600';
		if (dir > 0) {
			num.style.textAlign = 'right';
			num.style.paddingRight = '10px';
		}
		else {
			num.style.textAlign = 'left';
			num.style.paddingLeft = '5px';
		}
		num.style.zIndex = 20;
		num.style.position = 'relative';
		num.style.color = '#fff';
		num.style.WebkitTransform = 'translate3d(0, -51px, 1px)';
		holder.num = num;
		var name = holder.create('div');
		name.style.color = '#fff';
		name.style.textShadow = '1px 1px 0 #333, -1px -1px 0 #333, 1px -1px 0 #333, -1px 1px 0 #333';
		name.style.textAlign = 'left';
		name.style.fontSize = '16px';
		name.style.fontWeight = 'bold';
		name.style.WebkitTransform = 'translate3d(0, -36px, 1px)';
		holder.name = name;
		// bar animation
		holder.update = function (hp, newVal, endure) {
			var a = new FrameAnimation(holder.bar);
			var c = ['FFEF0D', '997309', 'F78C11'];
			if (holder.enduranceColor || endure) {
				c = ['FA6A5A', '780000', 'FF2D0D'];
			}
			a.addKeyFrame(0, {WebkitTransform: 'scale(' + holder.current + ', 1) translate3d(0, 0, 1px)'});
			if (endure) {
				newVal = 1; // endured HP will be max HP 
				a.addKeyFrame(3, {WebkitTransform: 'scale(0, 1) translate3d(0, 0, 1px)'});
				a.addKeyFrame(6, {WebkitTransform: 'scale(0, 1) translate3d(0, 0, 1px)'});
				a.addKeyFrame(9, {WebkitTransform: 'scale(' + newVal + ', 1) translate3d(0, 0, 1px)'});
				holder.enduranceColor = true;
			}
			else {
				a.addKeyFrame(3, {WebkitTransform: 'scale(' + newVal + ', 1) translate3d(0, 0, 1px)'});
			}
			a.setup({easing: 'ease-out'});
			a.onFinish = function (event, target) {
				var style = target.style.WebkitTransform;
				holder.bar.remove();
				holder.bar = holder.create('div');
				holder.bar.style.WebkitTransform = 'translate3d(0, 0, 1px)';
				holder.bar.style.width = w + 'px';
				holder.bar.style.height = h + 'px';
				holder.bar.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#' + c[0] + '), to(#' + c[1] + '), color-stop(0.6, #' + c[2] + '))';
				holder.bar.style.WebkitTransform = style;
				if (dir < 0) {
					holder.bar.style.WebkitTransformOrigin = w + 'px 0';
				}
				else {
					holder.bar.style.WebkitTransformOrigin = '0 0';
				}
				holder.bar.style.position = 'relative';
				holder.bar.style.top = '-95px';
				holder.bar.style.zIndex = 10;
				holder.bar.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#' + c[0] + '), to(#' + c[1] + '), color-stop(0.6, #' + c[2] + '))';
			};
			a.start();
			holder.current = newVal;
			holder.num.hp = hp;
			holder.num.innerHTML = hp;
			if (hp == 0) {
				// reset
				holder.enduranceColor = null;
			}
		};
	}
	if (holder.bar) {
		holder.bar.remove();
	}
	var bar = holder.create('div');
	bar.setAttribute('data-id', f.id);
	bar.style.width = w + 'px';
	bar.style.height = h + 'px';
	bar.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#FFEF0D), to(#997309), color-stop(0.6, #F78C11))';
	if (dir < 0) {
		bar.style.WebkitTransformOrigin = w + 'px 0';
	}
	else {
		bar.style.WebkitTransformOrigin = '0 0';
	}
	var level = '<p style="font-size: 14px; margin: 0; padding: 0;"><span style="color: #24D69B;">Level&nbsp;</span>' + f.lvl + '</p>';
	bar.style.position = 'relative';
	bar.style.top = '-95px';
	bar.style.zIndex = 11;
	bar.style.WebkitTransform = 'translate3d(0, 0, 1px)';
	holder.bar = bar;
	holder.name.innerHTML = level + '  ' + f.name;
	holder.num.hp = Number(f.hp);
	holder.num.innerHTML = f.hp;
	holder.maxHp = f.hp;
	holder.current = 1;
	return holder;
};

Battle.prototype.createTeam = function (souls, team, dir) {
	var s = 'blue';
	var t = '345px';
	var l = 180;
	var wins = this.oWins; // calculate against opposite team's wins
	if (dir < 0) {
		s = 'red';
		t = '37px';
		l = 20;
		wins = this.mWins; // calculate against opposite team's wins
	}
	if (souls.icons) {
		for (var i = 0; i < souls.icons.length; i++) {
			souls.icons[i].remove();
		}
	}
	if (team.length > 4) {
		souls.css({
			WebkitTransformOrigin: '0',
			WebkitTransform: 'scale(' + Math.min( 1, 4 / team.length ) + ')'
		});
		l += 5;
	} else {
		souls.css({
			WebkitTransformOrigin: '',
			WebkitTransform: 'scale(1)'
		});
	}
	souls.icons = [];
	souls.className = 'souls';
	souls.style.position = 'absolute';
	souls.style.top = t;
	souls.style.left = l + 'px';
	souls.style.height = '20px';
	var num = team.length - wins;
	var rcounter = team.length;
	for (var i = 0; i < team.length; i++) {
		var b = souls.create('div');
		b.style.WebkitTransform = 'scale(' + (dir * 1) + ', 1)';
		b.style.margin = '3px';
		b.style.padding = '3px';
		b.style.WebkitBorderRadius = '3px';
		if (num > rcounter) {
			//b.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
			//b.style.border = '1px solid #666';
			//b.style.opacity = 0.4;
		}
		else if (num == rcounter) {
			//b.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
			//b.style.border = '1px solid #fff';
		}
		else {
			//b.style.backgroundColor = 'rgba(150, 0, 0, 0.7)';
			//b.style.border = '1px solid #c00';
			//b.style.opacity = 0.4;
			b.style.opacity = 0;
		}
		b.style.background = 'url(' + team[i].sprite + ') no-repeat';
		b.style.backgroundSize = '100% auto';
		b.style.width = '20px';
		b.style.height = '20px';
		b.style.cssFloat = 'left';
		souls.icons[souls.icons.length] = b;
		rcounter--;
	}
};

Battle.prototype.createLightBox = function (text, borderColor) {
	var self = this;
	var bb = self.lightbox;
	bb.style.display = 'block';
	bb.style.border = '4 solid ' + borderColor;
	bb.style.opacity = 0;
	bb.innerHTML = text;
	self.lightboxFade.start();
};
