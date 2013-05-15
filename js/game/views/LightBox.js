function LightBox (viewElement) {
	this.parent = viewElement;
	this.parent.css({
		WebkitTransform: 'translate3d(0, 0, 4px)',
		zIndex: 999	
	});
	this.lightBoxCallback = null;
	this.onCloseCall = null;
	var self = this;
	var lightbox = this.parent.create('div');
	lightbox.style.width = '200px';
	lightbox.style.minHeight = '100px';
	lightbox.style.padding = '10px';
	lightbox.style.fontWeight = 'bold';
	lightbox.style.WebkitBorderRadius = '10px';
	lightbox.style.margin = 'auto';
	lightbox.style.WebkitTransform = 'translate3d(0, 28px, 1px)';
	lightbox.style.opacity = 0;
	lightbox.style.textAlign = 'center';
	lightbox.style.fontSize = '30px';
	lightbox.style.lineHeight = '100px';
	lightbox.style.background = '-webkit-gradient(linear, 0% 0%, 0% 100%, from(#FFFFFF), to(#C9D8FF))';
	lightbox.style.textShadow = '1px 1px 0 #fff, -1px -1px 0 #fff, 1px -1px 0 #fff';
	lightbox.style.WebkitBoxShadow = '0 0 6px 1px #000'; 
	lightbox.style.position = 'absolute';
	lightbox.style.top = '30%';
	lightbox.style.left = '15%';
	lightbox.style.zIndex = 500;
	this.lightboxFade = new FrameAnimation(lightbox);
	this.lightboxFade.addKeyFrame(0, {opacity: 0, WebkitTransform: 'translate3d(0, 28px, 2px)'});
	this.lightboxFade.addKeyFrame(3, {opacity: 1, WebkitTransform: 'translate3d(0, 0, 2px)'});
	this.lightboxFade.setup({easing: 'ease-out'});
	this.lightboxFade.onFinish = function () {
		button.enable(lightbox);
	};
	this.lightboxFadeOut = new FrameAnimation(lightbox);
	this.lightboxFadeOut.addKeyFrame(0, {opacity: 1, WebkitTransform: 'translate3d(0, 0, 2px) scale(1)'});
	this.lightboxFadeOut.addKeyFrame(3, {opacity: 0, WebkitTransform: 'translate3d(0, 0, 2px) scale(1.2)'});
	this.lightboxFadeOut.addKeyFrame(6, {opacity: 0, WebkitTransform: 'translate3d(0, 0, 2px) scale(1.2)'});
	this.lightboxFadeOut.setup({easing: 'ease-out'});
	this.lightboxFadeOut.onFinish = function () {
		if (typeof self.lightBoxCallback === 'function') {
			self.lightBoxCallback();
			self.lightBoxCallback = null;
		}
		if (self.onCloseCall) {
			self.onCloseCall();
		}
		window.game.closeLightBox();
	};
	button.create(lightbox, function (e) {
		window.game.closeLightBox();
	}, { onStart: function (e) { e.preventDefault(); } });
	button.disableDefault(lightbox);
	button.create(this.parent, function (e) {
		window.game.closeLightBox();
	}, { onStart: function (e) { e.preventDefault(); } });
	button.disableDefault(this.parent);
	this.lightbox = lightbox;
}

LightBox.prototype.onOpen = function (params, cb) {
	if (params) {
		if (params.border) {
			this.lightbox.style.border = params.border;
		} else {
			this.lightbox.style.border = '#9f9';
		}
		if (params.text) {
			this.lightbox.innerHTML = params.text;
		}
	} else {
		this.lightbox.style.border = '#9f9';
	}
	cb();
};

LightBox.prototype.onOpenComplete = function (params) {
	this.lightboxFade.start();
};

LightBox.prototype.onClose = function (params, cb) {
	if (params && params.onClose) {
		this.lightBoxCallback = params.onClose;
	}
	button.disable(this.lightbox);
	this.onCloseCall = cb;
	this.lightboxFadeOut.start();
};
