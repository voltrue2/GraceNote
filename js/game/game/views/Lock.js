function Lock (viewElement) {
	this.parent = viewElement;
	this.parent.style.background = 'rgba(0, 0, 0, 0)';
	this.parent.style.WebkitTransform = 'translate3d(0, 0, 2px)';
	// spiner holder
	var holder = this.parent.create('div');
	holder.css({
		background: '-webkit-gradient(linear, 100% 100%, 0% 100%, from(rgba(0, 0, 0, 0)), to(rgba(0, 0, 0, 0)), color-stop(0.5, rgba(0, 0, 0, 1)))',
		width: '100%',
		height: '30px',
		position: 'absolute',
		top: '45%',
		left: 0,
		borderTop: '3px solid rgba(255, 255, 255, 0.1)',
		borderBottom: '3px solid rgba(255, 255, 255, 0.1)',
		opacity: 0
	});
	// spinner 
	var preloader = holder.create('div');
	preloader.style.position = 'absolute';
	preloader.style.top = '32%';
	preloader.style.left = '48%';
	preloader.style.width = '25px';
	preloader.style.height = '25px';
	preloader.style.WebkitTransform = 'scale(0.6)';
	this.preloader = preloader;
	var opts = {
  		lines: 13, // The number of lines to draw
  		length: 7, // The length of each line
  		width: 3, // The line thickness
  		radius: 10, // The radius of the inner circle
  		rotate: 0, // The rotation offset
  		color: '#fff', // #rgb or #rrggbb
  		speed: 1, // Rounds per second
  		trail: 60, // Afterglow percentage
  		shadow: false, // Whether to render a shadow
  		hwaccel: false, // Whether to use hardware acceleration
  		className: 'spinner', // The CSS class to assign to the spinner
  		zIndex: 2e9, // The z-index (defaults to 2000000000)
  		top: '24px', // Top position relative to parent in px
  		left: '24px' // Left position relative to parent in px
	};
	var spinner = new Spinner(opts).spin(preloader);
	// spinner holder ranimation
	this.closer = new FrameAnimation(holder);
	this.closer.addKeyFrame(0, { opacity: 0, WebkitTransform: 'scale(1, 0)' });
	this.closer.addKeyFrame(1, { opacity: 0, WebkitTransform: 'scale(1, 0.4)' });
	this.closer.addKeyFrame(4, { opacity: 1, WebkitTransform: 'scale(1, 1)' });
	this.closer.setup({ easing: 'ease-in-out' });
	this.holder = holder;
}

Lock.prototype.onOpen = function (params, cb) {
	this.preloader.css({ opacity: 1 });
	if (this.closer.running) {
		var self = this;
		this.closer.onFinish = function () {
			self.closer.onFinish = null;
			self.closer.start();
			cb();
		};
	}
	else {
		this.closer.onFinish = null;
		this.closer.start();
		cb();
	}
};

Lock.prototype.onClose = function (params, cb) {
	if (this.closer.running) {
		var self = this;
		this.closer.onFinish = function () {
			self.closer.onFinish = cb;
			self.closer.reverse();
		};
	}
	else {
		this.closer.onFinish = cb;
		this.closer.reverse();
	}
};
