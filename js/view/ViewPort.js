(function () {

	function ViewPort() {
		window.EventEmitter.call(this);
		this._currentView = null;
		this._stack = [];
		this._views = {};
		this._tweens = {}; // canvas view only
		this._canvas = null; // canvas view only
		this._root = null;
		this._core = null;
		this._type = null;
		this.DOM = 'div';
		this.CANVAS = 'canvas';
		// on resize/orientation change
		var resizeEvent = 'resize';
		if ('onorientationchange' in window) {
			resizeEvent = 'orientationchange';
		}
		var that = this;
		window.addEventListener(resizeEvent, function () {
			that.emit('resize');
			if (that._currentView) {
				that._views[that._currentView].emit('resize');
			}
		}, false);
		// hide address bar
		window.addEventListener('load', function () {
			window.setTimeout(function () {
				window.scrollTo(0, 0);
			}, 0);
		}, false);
	}

	window.inherits(ViewPort, window.EventEmitter);
	window.ViewPort = ViewPort;

	// create Viewport with DOM
	ViewPort.prototype.createDom = function (parentDom) {
		this._core = new window.Dom(document.createElement(this.DOM));
		this._core.setStyle({
			WebkitUserSelect: 'none'
		});
		this._core.appendTo(parentDom);
		this._core.setClassName('viewport');
		this._type = this.DOM;
		this._root = this._core.createChild('div');
	};
	
	// create Viewport with Canvas
	ViewPort.prototype.createCanvas = function (parentDom, fps) {
		this._core = new window.Dom(document.createElement(this.CANVAS));
		this._core.setStyle({
			WebkitUserSelect: 'none'
		});
		this._core.appendTo(parentDom);
		this._type = this.CANVAS;
		this._root = new window.Sprite();
		this._size = {};
		var canvas = new window.Canvas(this._core._src);
		this._root.context = canvas.getContext('2d');
		this._canvas = canvas;
		canvas.setFrameRate(fps);
		this.on('pause', function () {
			canvas.pause();
		});
		this.on('resume', function () {
			canvas.resume();
		});
		var that = this;
		canvas.on('frameUpdate', function () {
			runTweens(that._tweens);
			that._root.render();
			that.emit('update');
		});
	};

	// canvas view only
	ViewPort.prototype.pause = function () {
		if (!this._canvas) {
			return;
		}
		this._canvas.pause();
		this.emit('pause');
	};

	// canvas view only
	ViewPort.prototype.resume = function () {
		if (!this._canvas) {
			return;
		}
		this._canvas.resume();
		this.emit('resume');
	};

	// canvas view only
	ViewPort.prototype.addTween = function (name, tween) {
		this._tweens[name] = tween;
		var that = this;
		tween.once('finish', function () {
			delete that._tweens[name];
			tween.on('start', function () {
				that.addTween(name, tween);
			});
		});
	};

	// canvas view only
	ViewPort.prototype.removeTween = function (name) {
		delete this._tweens[name];
	};

	ViewPort.prototype.getRoot = function () {
		return this._root;
	};

	ViewPort.prototype.getSize = function () {
		return this._size;
	};

	ViewPort.prototype.setSize = function (width, height) {
		if (this._type === this.DOM) {
			this._root.setStyle({ width: width + 'px', height: height + 'px' });
		} else if (this._type === this.CANVAS) {
			this._root.width = width;
			this._root.height = height;
			this._canvas.setSize(width, height);
		}
		this._size = { width: width, height: height };
	};

	ViewPort.prototype.addView = function (name, View) {
		if (this._views[name]) {
			return window.log.debug(name, 'has already been added'); 
		}
		if (!View) {
			return window.log.error(name, 'not found');
		}
		this._views[name] = new View();
		if (this._type === this.DOM) {
			this._views[name].setClassName(name);
			this._views[name].appendTo(this._root);
			this._views[name].setStyle({ position: 'absolute', top: 0, left: 0, display: 'none' });
		} else if (this._type === this.CANVAS) {
			this._root.appendChild(this._views[name]);
			this._views[name].hide();
		}
		this._views[name].emit('addView', this);
	};

	ViewPort.prototype.open = function (name, params) {
		if (this._currentView) {
			var that = this;
			var prevView = this._views[this._currentView];
			prevView.once('closed', function () {
				if (that._type === that.DOM) {
					prevView.setStyle({ display: 'none' });
				}
				emitOpen(that, name, 'open', params);
			});
			prevView.emit('close', this);
		} else {
			emitOpen(this, name, 'open', params);
		}
	};

	ViewPort.prototype.close = function () {
		if (this._currentView) {
			this._views[this._currentView].emit('close', this);
			this._currentView = null;
		}
	};

	ViewPort.prototype.openPopup = function (name) {
		if (this._stack.indexOf(name) !== -1) {
			return window.log.debug(name, 'has already been opened');
		}
		this._stack.push(name);
		this._views[name].emit('open', this);
		if (this._type === this.DOM) {
			this._views[name].setStyle({ zIndex: 1000, display: '' });
		}
	};

	ViewPort.prototype.closePopup = function (name) {
		// if name is given, it will close the given popup.
		if (!name) {
			// if no name is given, it will close the oldest popup
			name = this._stack.shift();
		}
		var index = this._stack.indexOf(name);
		if (index === -1) {
			return window.log.debug(name, 'has not been opened as a popup');
		}
		this._views[name].emit('close', this);
		this._stack.splice(index, 1);
		if (this._type === this.DOM) {
			this._views[name].setStyle({ zIndex: 0, display: 'none' });
		}
		
	};

	function emitOpen(that, name, eventName, params) {
		if (that._currentView !== name) {
			that._views[name].emit(eventName, params);
			that._currentView = name;
			if (this._type === this.DOM) {
				that._views[name].setStyle({ display: '' });
			}
		}	
	}

	function runTweens(tweens) {
		for (var name in tweens) {
			if (tweens[name]) {
				tweens[name].update();
			}
		}
	}

}());
