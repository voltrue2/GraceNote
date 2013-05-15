(function() {

var top = null;
var bottom = null;
var blue = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(rgba(51, 102, 255, 0.4)), to(rgba(51, 102, 255, 0.4)), color-stop(.5, rgba(255, 255, 255, 0.6)))';
var red = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(rgba(255, 77, 0, 0.4)), to(rgba(255, 77, 0, 0.4)), color-stop(.5, rgba(255, 255, 255, 0.6)))';
var closed = false;

function CutIn (viewElement) {
	viewElement.css({
		zIndex: 500,
		WebkitTransform: 'translate3d(0, 0, 3px)'
	});
	var barHeight = 80 + 'px';
	this.shade = viewElement.create('div');
	this.shade.css({
		width: '100%',
		height: '100%',
		background: 'rgba(0, 0, 0, 0.6)',
		WebkitTransform: 'translate3d(0, 0, 3px)',
		zIndex: 600
	});
	top = viewElement.create('div');
	top.css({
		width: '100%',
		height: barHeight,
		borderTop: '2px solid #fff',
		borderBottom: '2px solid #fff',
		background: '#000',
		position: 'absolute',
		display: 'none',
		WebkitTransform: 'translate3d(0, 0, 3px)',
		zIndex: 610
	});
	top.sprite = top.create('div');
	top.sprite.css({
		backgroundPosition: '30% 30%',
		WebkitBackgroundSize: '100% auto',
		position: 'absolute',
		left: '85px',
		width: '140px',
		height: '80px',
		opacity: 0
	});
	bottom = viewElement.create('div');
	bottom.css({
		width: '100%',
		height: barHeight,
		borderTop: '2px solid #fff',
		borderBottom: '2px solid #fff',
		background: '#000',
		position: 'absolute',
		display: 'none',
		WebkitTransform: 'translate3d(0, 0, 3px)',
		zIndex: 610
	});
	bottom.sprite = bottom.create('div');
	bottom.sprite.css({
		backgroundPosition: '30% 30%',
		WebkitBackgroundSize: '100% auto',
		position: 'absolute',
		left: '85px',
		width: '140px',
		height: '80px',
		opacity: 0
	});
	// intro
	this.cutsceneAnim1 = new FrameAnimation(top, this.fps);
	this.cutsceneAnim1.addKeyFrame(0, {opacity: 0, WebkitTransform: 'translate3d(0, 0, 3px) scale(1, 0)'});
	this.cutsceneAnim1.addKeyFrame(3, {opacity: 1, WebkitTransform: 'translate3d(0, 0, 3px) scale(1, 1)'});
	this.cutsceneAnim1.addKeyFrame(6, {opacity: 1, WebkitTransform: 'translate3d(0, 0, 3px) scale(1, 1)'});
	this.cutsceneAnim2 = new FrameAnimation(bottom, this.fps);
	this.cutsceneAnim2.addKeyFrame(0, {opacity: 0, WebkitTransform: 'translate3d(0, 0, 3px) scale(1, 0)'});
	this.cutsceneAnim2.addKeyFrame(3, {opacity: 1, WebkitTransform: 'translate3d(0, 0, 3px) scale(1, 1)'});
	this.cutsceneAnim2.addKeyFrame(6, {opacity: 1, WebkitTransform: 'translate3d(0, 0, 3px) scale(1, 1)'});
	// sprite slides
	this.slideL1 = new FrameAnimation(top.sprite, this.fps);
	this.slideL1.addKeyFrame(0, {opacity: 0, WebkitTransform: 'translate3d(-80px, 0px, 3px) scale(1, 1)'});
	this.slideL1.addKeyFrame(2, {opacity: 1, WebkitTransform: 'translate3d(-20px, 0px, 3px) scale(1, 1)'});
	this.slideL1.addKeyFrame(6, {opacity: 1, WebkitTransform: 'translate3d(0px, 0px, 3px) scale(1, 1)'});
	this.slideL1.addKeyFrame(8, {opacity: 0, WebkitTransform: 'translate3d(80px, 0px, 3px) scale(1, 1)'});
	this.slideL1.setup({easing: 'ease-out', keepLastState: false});
	this.slideL2 = new FrameAnimation(top.sprite, this.fps);
	this.slideL2.addKeyFrame(0, {opacity: 0, WebkitTransform: 'translate3d(-80px, 0px, 3px) scale(1, 1)'});
	this.slideL2.addKeyFrame(2, {opacity: 1, WebkitTransform: 'translate3d(-20px, 0px, 3px) scale(1, 1)'});
	this.slideL2.addKeyFrame(6, {opacity: 1, WebkitTransform: 'translate3d(0px, 0px, 3px) scale(1, 1)'});
	this.slideL2.addKeyFrame(8, {opacity: 0, WebkitTransform: 'translate3d(80px, 0px, 3px) scale(1, 1)'});
	this.slideL2.setup({easing: 'ease-out', keepLastState: false});
	this.slideR1 = new FrameAnimation(bottom.sprite, this.fps);
	this.slideR1.addKeyFrame(0, {opacity: 0, WebkitTransform: 'translate3d(80px, 0px, 3px) scale(-1, 1)'});
	this.slideR1.addKeyFrame(2, {opacity: 1, WebkitTransform: 'translate3d(20px, 0px, 3px) scale(-1, 1)'});
	this.slideR1.addKeyFrame(6, {opacity: 1, WebkitTransform: 'translate3d(0px, 0px, 3px) scale(-1, 1)'});
	this.slideR1.addKeyFrame(8, {opacity: 0, WebkitTransform: 'translate3d(-80px, 0px, 3px) scale(-1, 1)'});
	this.slideR1.setup({easing: 'ease-out', keepLastState: false});
	this.slideR2 = new FrameAnimation(bottom.sprite, this.fps);
	this.slideR2.addKeyFrame(0, {opacity: 0, WebkitTransform: 'translate3d(80px, 0px, 3px) scale(-1, 1)'});
	this.slideR2.addKeyFrame(2, {opacity: 1, WebkitTransform: 'translate3d(20px, 0px, 3px) scale(-1, 1)'});
	this.slideR2.addKeyFrame(6, {opacity: 1, WebkitTransform: 'translate3d(0px, 0px, 3px) scale(-1, 1)'});
	this.slideR2.addKeyFrame(8, {opacity: 0, WebkitTransform: 'translate3d(-80px, 0px, 3px) scale(-1, 1)'});
	this.slideR2.setup({easing: 'ease-out', keepLastState: false});
}

/*
* params.top: { color: blue/red, sprite: String, direction: left/right }
* params.bottom: { color: blue/red, sprite: String, direction: left/right }
*/
CutIn.prototype.onOpen = function (params, cb) {
	top.style.display = 'none';
	bottom.style.display = 'none';
	closed = false;
	if (params) {
		var showBoth = false;
		if (params.top && params.bottom) {
			showBoth = true;
			two(); // display both top and bottom
		}
		if (params.top) {
			top.style.display = 'block';
			setColor(top, params.top.color);
			setSprite(top.sprite, params.top.sprite, params.top.direction);
			if (!showBoth) {
				one(top);
			}
		}
		if (params.bottom) {
			bottom.style.display = 'block';
			setColor(bottom, params.bottom.color);
			setSprite(bottom.sprite, params.bottom.sprite, params.bottom.direction);
			if (!showBoth) {
				one(bottom);
			}
		}
		cb();
	} else {
		logger.error('CutIn.onOpen: params.top or params.bottom required');
	}
};

CutIn.prototype.onOpenComplete = function (params) {
	if (params.top) {
		this.cutsceneAnim1.start();
		var slider1 = this.slideL1; // slide to left
		if (params.top.direction === 'right') {
			slider1 = this.slideR1; // slide to right
		}
		this.cutsceneAnim1.onFinish = function () {
			slider1.start();
			self.cutsceneAnim1.onFinish = function () {
				if (!closed) {
					window.viewPort.closeOverlay('CutIn');
					closed = true;
				}
			};
		}
		self = this;
		slider1.onFinish = function () {
			self.cutsceneAnim1.reverse();
		};
	}
	if (params.bottom) {
		this.cutsceneAnim2.start();
		var slider2 = this.slideL2; // slide to left
		if (params.bottom.direction === 'right') {
			slider2 = this.slideR2; // slide to right
		}
		this.cutsceneAnim2.onFinish = function () {
			slider2.start();
		}
		self = this;
		slider2.onFinish = function () {
			self.cutsceneAnim2.reverse();
			self.cutsceneAnim2.onFinish = function () {
				if (!closed) {
					window.viewPort.closeOverlay('CutIn');
					closed = true;
				}
			};
		};
	}
};

CutIn.prototype.onClose = function (params, cb) {
	cb();
	if (this.onCloseCallback) {
		this.onCloseCallback();
		this.onCloseCallback = null;
	}
};

// override from outside of this object
CutIn.prototype.onCloseCallback = function () {};

function two() {
	top.css({ top: '25%' });
	bottom.css({ top: '50%' });
}

function one(bar) {
	bar.css({ top: '40%' });
}

function setColor(bar, colorString) {
	if (colorString === 'blue') {
		colorString = blue;
	} else if (colorString === 'red') {
		colorString = red
	} else {
		return logger.error('CutIn.setColor: colorString needs to be either blue or red, but ' + colorString + ' given');
	}
	bar.css({ background: colorString });
}

function setSprite(sprite, image, direction) {
	var dir = -1; // face left
	if (direction === 'right') {
		// face right
		dir = 1;
	}
	sprite.css({ WebkitTransform: 'scale(' + dir + ', 1)', background: 'url(' + image + ') no-repeat' });
}

window.CutIn = CutIn;

}());
