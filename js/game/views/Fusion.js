function Fusion (viewElement) {
	this.parent = viewElement;
	this.list = {
		glow: '/img/contents/src/ui/glowing-ball.png',
		sparkle: '/img/contents/src/ui/sparkle.png',
		bgFusion: '/img/contents/src/background/orange-stripes.jpg',
		bgBoost: '/img/contents/src/background/dark-blue.jpg',
	};
	// build the UI
	this.stage = this.parent.create('div');
	this.stage.style.height = '100%';
	this.stage.style.WebkitBackgroundSize = '100%'
	// creature left
	this.cl = this.stage.create('div');
	this.cl.style.width = '130px';
	this.cl.style.height = '150px';
	this.cl.style.position = 'absolute';
	this.cl.style.left = '20px';
	this.cl.style.bottom = '150px';
	this.cl.img = this.cl.create('img');
	this.cl.img.setAttribute('width', '130px');
	// creature right
	this.cr = this.stage.create('div');
	this.cr.style.width = '130px';
	this.cr.style.height = '150px';
	this.cr.style.position = 'absolute';
	this.cr.style.right = '20px';
	this.cr.style.bottom = '150px';
	this.cr.img = this.cr.create('img');
	this.cr.img.setAttribute('width', '130px');
	// glowing ball
	this.ball = this.stage.create('div');
	this.ball.style.background = 'url(' + this.list.sparkle + ')';
	this.ball.style.width = '320px';
	this.ball.style.height = '320px';
	this.ball.style.backgroundRepeat = 'no-repeat';
	this.ball.style.backgroundSize = '100%';
	this.ball.style.WebkitTransform = 'translate3d(0, 0, 1px)';
	this.ball.style.opacity = 0;
	this.ball.style.position = 'absolute';
	this.ball.style.top = '0';
	// creature fused
	this.cf = this.stage.create('div');
	this.cf.style.width = '130px';
	this.cf.style.height = '150px';
	this.cf.style.position = 'absolute';
	this.cf.style.right = '95px';
	this.cf.style.bottom = '150px';
	this.cf.img = this.cf.create('img');
	this.cf.img.setAttribute('width', '130px');
	// white screen
	this.white = this.parent.create('div');
	this.white.style.width = '100%';
	this.white.style.height = '100%';
	this.white.style.background = '#fff';
	this.white.style.position = 'absolute';
	this.white.style.top = 0;
	this.white.style.left = 0;
	this.white.style.zIndex = 900;
	this.white.style.WebkitTransform = 'translate3d(0, 0, 1px)';
	// ************* animations ************
	this.fps = null;
	// white fade in/out
	this.fadeIn = new FrameAnimation(this.white, this.fps);
	this.fadeIn.addKeyFrame(0, {opacity: 0, WebkitTransform: 'translate3d(0, 0, 1px)'});
	this.fadeIn.addKeyFrame(10, {opacity: 1, WebkitTransform: 'translate3d(0, 0, 1px)'});
	this.fadeIn.setup({easing: 'ease-in-out'});
	this.fadeOut = new FrameAnimation(this.white, this.fps);
	this.fadeOut.addKeyFrame(0, {opacity: 1, WebkitTransform: 'translate3d(0, 0, 1px)'});
	this.fadeOut.addKeyFrame(10, {opacity: 0, WebkitTransform: 'translate3d(0, 0, 1px)'});
	this.fadeOut.setup({easing: 'ease-in-out'});
	// fusion candidate creature falling from top
	this.creatureIn = new FrameAnimation(null, this.fps);
	this.creatureIn.addKeyFrame(0, {opacity: 0, WebkitTransform: 'scale(0, 3)'});
	this.creatureIn.addKeyFrame(4, {opacity: 1, WebkitTransform: 'scale(1.5, 0.7)'});
	this.creatureIn.addKeyFrame(6, {opacity: 1, WebkitTransform: 'scale(1, 1)'});
	this.creatureIn.addKeyFrame(9, {opacity: 1, WebkitTransform: 'scale(1, 1)'});
	this.creatureIn.setup({ easing: 'ease-in-out' });
	// fuesd creature in
	this.fuseIn = new FrameAnimation(this.cf);
	this.fuseIn.addKeyFrame(0, { opacity: 0, WebkitTransform: 'scale(5)'});
	this.fuseIn.addKeyFrame(4, { opacity: 1, WebkitTransform: 'scale(1)'});
	this.fuseIn.setup({ easeing: 'ease-in-out' });
	// fusion
	this.leftF = new FrameAnimation(this.cl, this.fps);
	this.leftF.addKeyFrame(0, {WebkitTransform: 'translate(0, 0) scale(1)', opacity: 1});
	this.leftF.addKeyFrame(6, {WebkitTransform: 'translate(0, 0) scale(1)', opacity: 1});
	this.leftF.addKeyFrame(8, {WebkitTransform: 'translate(0, 0) scale(1)', opacity: 1});
	this.leftF.addKeyFrame(12, {WebkitTransform: 'translate(100px, -100px) scale(0.5)', opacity: 1});
	this.leftF.addKeyFrame(14, {WebkitTransform: 'translate(170px, 0) scale(1)', opacity: 1});
	this.leftF.addKeyFrame(20, {WebkitTransform: 'translate(170px, 0) scale(1)', opacity: 1});
	this.leftF.addKeyFrame(28, {WebkitTransform: 'translate(100px, 0) scale(1)', opacity: 1});
	this.leftF.addKeyFrame(34, {WebkitTransform: 'translate(100px, 0) scale(1)', opacity: 1});
	this.leftF.addKeyFrame(38, {WebkitTransform: 'translate(50px, 0) scale(1)', opacity: 0});
	this.rightF = new FrameAnimation(this.cr, this.fps);
	this.rightF.addKeyFrame(0, {WebkitTransform: 'translate(0, 0) scale(1)', opacity: 1});
	this.rightF.addKeyFrame(6, {WebkitTransform: 'translate(0, 0) scale(1)', opacity: 1});
	this.rightF.addKeyFrame(8, {WebkitTransform: 'translate(0, 0) scale(1)', opacity: 1});
	this.rightF.addKeyFrame(12, {WebkitTransform: 'translate(-100px, 100px) scale(1.5)', opacity: 1});
	this.rightF.addKeyFrame(14, {WebkitTransform: 'translate(-170px, 0) scale(1)', opacity: 1});
	this.rightF.addKeyFrame(20, {WebkitTransform: 'translate(-170px, 0) scale(1)', opacity: 1});
	this.rightF.addKeyFrame(28, {WebkitTransform: 'translate(-100px, 0) scale(1)', opacity: 1});
	this.rightF.addKeyFrame(34, {WebkitTransform: 'translate(-100px, 0) scale(1)', opacity: 1});
	this.rightF.addKeyFrame(38, {WebkitTransform: 'translate(-50px, 0) scale(1)', opacity: 0});
	this.glow = new FrameAnimation(this.ball);
	this.glow.addKeyFrame(0, {WebkitTransform: 'translate3d(0, 70px, 1px) scale(0.1, 0.1) rotate(0deg)', opacity: 0});
	this.glow.addKeyFrame(6, {WebkitTransform: 'translate3d(0, 70px, 1px) scale(0.1, 0.1) rotate(360deg)', opacity: 0});
	this.glow.addKeyFrame(10, {WebkitTransform: 'translate3d(0, 70px, 1px) scale(2.5, 2.5) rotate(0deg)', opacity: 1});
	this.glow.addKeyFrame(12, {WebkitTransform: 'translate3d(0, 70px, 1px) scale(5, 5) rotate(360deg)', opacity: 0});
	//************** animations ************
	
	// return view name
	this.returnView = null;
	
	this.params = null;
}

/*
* params.creatureA: image
* params.creatureB: image
* params.creatureResult: image
* params.returnView: { name, params }
* type: boost/fusion
*/
Fusion.prototype.onOpen = function (params, cb) {
	this.params = params;
	this.returnView = params.returnView;
	var loader = new Loader();
	var preloader = [];
	for (var i in this.list){
		preloader.push(this.list[i]);
	}
	preloader.push(params.creatureA);
	preloader.push(params.creatureB);
	preloader.push(params.creatureResult);
	var self = this;
	loader.asyncImg(preloader, function (error, data) {
		self.cl.img.setAttribute('src', params.creatureA);
		self.cr.img.setAttribute('src', params.creatureB);
		self.cf.img.setAttribute('src', params.creatureResult);
		if (params.type === 'boost') {
			self.ball.style.background = 'url(' + self.list.sparkle + ') no-repeat';
			self.stage.style.background = 'url(' + self.list.bgBoost + ') no-repeat';
		}
		else {
			self.ball.style.background = 'url(' + self.list.glow + ') no-repeat';
			self.stage.style.background = 'url(' + self.list.bgFusion + ') no-repeat';
		}
		viewPort.closeOverlay('TopMenu');
		viewPort.views.TopMenu.once('close', cb);
	});
};

Fusion.prototype.onOpenComplete = function (params) {
	this.init();
	this.start();
};

Fusion.prototype.init = function () {
	this.cl.style.opacity = 0;
	this.cl.style.WebkitTransformOrigin = '50% 100%';
	this.cr.style.opacity = 0;
	this.cr.style.WebkitTransformOrigin = '50% 100%';
	this.cf.style.opacity = 0;
	this.ball.style.opacity = 0;
};

Fusion.prototype.start = function () {
	var self = this;
	this.fadeOut.setTarget(this.white);
	this.fadeOut.onFinish = function () {
		self.creaturesIn(self);
	};
	this.fadeOut.start();
};

Fusion.prototype.creaturesIn = function (self) {
	this.creatureIn.setTarget(this.cl);
	this.creatureIn.onFinish = function () {
		self.creatureIn.setTarget(self.cr);
		self.creatureIn.start();
		self.creatureIn.onFinish = function () {
			self.fuseInit(self);
		};
	};
	this.creatureIn.start();
};

Fusion.prototype.fuseInit = function (self) {
	self.cl.style.WebkitTransformOrigin = '0% 100%';
	self.leftF.start();
	self.cr.style.WebkitTransformOrigin = '0% 100%';
	self.rightF.start();
	self.rightF.onFinish = function () {
		viewPort.overlay('CutIn', { 
			top: { color: 'blue', sprite: self.params.creatureA, direction: 'left' },
			bottom: { color: 'blue', sprite: self.params.creatureB, direction: 'right' },
		});
	};
	viewPort.views.CutIn.onCloseCallback = function () {
		self.glow.start();
		window.setTimeout(function () {
			self.fadeIn.start();
		}, 500);
	};
	self.fadeIn.onFinish = function () {
		self.fusionFinish(self);
	};
};

Fusion.prototype.fusionFinish = function (self) {
	self.ball.style.opacity = 0;
	self.fuseIn.start();
	self.fadeOut.onFinish = function () {
		window.setTimeout(function () {
			viewPort.open(self.returnView.name, self.returnView.params);
		}, 2000);
	};
	window.game.playAudio('fanfare');
	self.fadeOut.start();
};
