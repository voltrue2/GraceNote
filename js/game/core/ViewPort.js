(function () {
/****** 
Dependencies: extensions, FrameAnimation, iScroll, FileLoader
******/
function ViewPort (viewPortParent, css) {
	this.viewDepth = 0;
	this.parent = viewPortParent;
	for (var i in css) {
		this.parent.style[i] = css[i];
	}
	this.views = {};
	this.viewSrc = {};
	this.viewOptions = {};
	this.currentView = null;
	this.screenLock = null;
	this.lockCounter = 0;
	this.onPageShowCallbacks = [];
	this.onPageHideCallbacks = [];
	this.css = css;
	var self = this;
	if (!this.parent) {
		console.error('Viewport.constructor: view port parent DOM "' + viewPortParent + '" not found');
	}
	// disable native scroll
	document.addEventListener('touchmove', function (event) {
		event.preventDefault();
	}, false);
	// set up event listener for page show
	window.addEventListener('pageshow', function (event) {
		for (var i = 0, len = self.onPageShowCallbacks.length; i < len; i++) {
			self.onPageShowCallbacks[i](event);
		}
	}, false);
	// set up event listener for page hide
	window.addEventListener('pagehide', function (event) {
		for (var i = 0, len = self.onPageHideCallbacks.length; i < len; i++) {
			self.onPageHideCallbacks[i](event);
		}
	}, false);
}

ViewPort.prototype = new EventEmitter();

/******************************************************/
// disable/enable all click/tap events while opening view/overlay
ViewPort.prototype.disableAll = function () {
	this.parent.style.pointerEvents = 'none';
};

ViewPort.prototype.enableAll = function () {
	this.parent.style.pointerEvents = '';
};
/******************************************************/

ViewPort.prototype.onPageShow = function (cb) {
	this.onPageShowCallbacks.push(cb);
};

ViewPort.prototype.onPageHide = function (cb) {
	this.onPageHideCallbacks.push(cb);
};

ViewPort.prototype.addView = function (srcPath, viewName, options) {
	if (this.viewSrc[srcPath]) {
		console.warn('ViewPort.addView: "' + srcPath + '" has already been added.');
	}
	else {
		this.viewSrc[srcPath] = {name: viewName, options: options};
	}
};

ViewPort.prototype.loadViews = function (cb) {
	var self = this; 
	var preloader = new Preloader();
	for (var path in this.viewSrc) {
		preloader.addJs(path);
	}
	preloader.onComplete = function (type, dataMap) {
		for (var path in dataMap) {
			if (self.viewSrc[path].name) {
				self.setupView(self.viewSrc[path].name, self.viewSrc[path].options);
			}
		}
		if (typeof cb == 'function') {
			cb();
		}
		self.emit('loadViewsComplete');
	};
	preloader.loadJs(false);
	/*
	var preloader = [];
	for (var path in this.viewSrc) {
		preloader.push(path);
	}
	loader.js(preloader, function (type, list) {
		for (var i = 0, len = list.length; i < len; list) {
			var path = list[i];
			if (self.viewSrc[path].name) {
				self.setupView(self.viewSrc[path].name, self.viewSrc[path].options);
			}
		}
		if (typeof cb == 'function') {
			cb();
		}
		self.emit('loadViewsComplete');
	});
	*/
};

/**********
params: Object (optional)
> if the view has onOpen method, the view change will happen AFTER onOpen
- onOpen(params, callback)
- onOpenComplete(params)
- onClose(params, callback)
- onCloseComplete
**********/
ViewPort.prototype.open = function (viewName, params) {
	// disable all pointer events while opening a new view
	this.disableAll();
	this.lock();
	var self = this;
	window.setTimeout(function () {
		//check if we have the view created alread or not
		if (!self.views[viewName]) {
			self.setupView(viewName, self.viewOptions[viewName]);
		}
		var newView = self.views[viewName];
		if (!newView) {
			console.error('viewPort.open: failed open "' + viewName + '"');
			console.trace();
			self.unlock();
			self.enableAll();	
			return;
		}
		if (newView.onOpen) {
			self.onOpen();
			newView.onOpen(params, function () {
				window.setTimeout(function () {
					self.openInit(newView, params);
				}, 0);
			});
		}
		else {
			self.openInit(newView, params);
		}
	}, 0);
};

/*
* this function should only be used with overlay function
* bring a view to the top most depth(z-index)
* automatically resets depth when the view is closed
*/
ViewPort.prototype.lockToTop = function (viewName) {
	var view = this.views[viewName];
	if (view && view.overlay && !view.lockedToTop) {
		view.locckedToTop = true;
		view.getElement().style.zIndex = 999;
	}
};

function resetTopLock(viewObject) {
	if (viewObject && viewObject.lockedToTop) {
		delete viewObject.lockedToTop;
		viewObject.getElement().style.zIndex = '';
	}
}

/*
* opens a view on top of another without closing the view
*/
ViewPort.prototype.overlay = function (viewName, params) {
	var newView = this.views[viewName];
	if (newView.overlay) {
		return false;
	}
	newView.overlay = true;
	if (newView.onOpen) {
		var self = this;
		newView.onOpen(params, function () {
			self.overlayInit(newView, params);
		});
	}
	else {
		this.overlayInit(newView, params);
	}
};

ViewPort.prototype.closeOverlay = function (viewName) {
	var view = this.views[viewName];
	var self = this;
	if (!view){
		return logger.error('ViewPort.closeOverlay: unknown view "' + viewName + '"');
	}
	if (!view.overlay) {
		return false;
	}
	delete view.overlay;
	// reset top lock if necessary
	resetTopLock(view);
	// start closing
	if (view.onClose) {
		var self = this;
		view.onClose(null, function () {
			self.viewDepth -= 1;
			view.getElement().style.zIndex = '';
			view.getElement().style.display = 'none';
			if (view.onCloseComplete) {
				view.onCloseComplete();
			}
			self.emit('closeOverlayComplete', viewName);
		});
	} else {
		this.viewDepth -= 1;
		view.getElement().style.zIndex = '';
		view.getElement().style.display = 'none';
		if (view.onCloseComplete) {
			view.onCloseComplete();
		}
		self.emit('closeOverlayComplete', viewName);
	}
	this.emit('closeOverlay', viewName);
};

ViewPort.prototype.onOpen = function () {};

ViewPort.prototype.onOpenComplete = function () {};

ViewPort.prototype.setupScreenLock = function (viewName) {
	//check if we have the view created alread or not
	if (!this.views[viewName]) {
		this.setupView(viewName, this.viewOptions[viewName]);
	}
	this.screenLock = viewName;
};

ViewPort.prototype.lock = function (params) {
	if (!this.screenLock) {
		return logger.error('ViewPort.lock: screenLock has not been setup.');
	}
	this.lockCounter += 1;
	if (this.lockCounter === 1) {
		this.overlay(this.screenLock, params);
	}
};

ViewPort.prototype.unlock = function () {
	this.lockCounter -= 1;
	if (this.lockCounter <= 0) {
		this.lockCounter = 0;
		this.closeOverlay(this.screenLock);
	}
};

ViewPort.prototype.forceUnlock = function () {
	this.enableAll();
	this.lockCounter = 0;
	this.closeOverlay(this.screenLock);
};

/*************
Private Initialize view DOM element and object
> options.noScroll: Boolean
*************/
ViewPort.prototype.setupView = function (viewName, options) {
	if (window[viewName]) {
		var self = this;
		var view = this.parent.create('div');
		if (this.css) {
			view.css(this.css);
		}
		view.addClass(this.formatName(viewName));
		view.style.display = 'none';
		var viewObj = new window[viewName](view);
		viewObj.prototype = new EventEmitter();
		viewObj.name = viewName;
		setupMethods(view, viewObj);
		this.views[viewName] = viewObj;
	}
	else {
		console.error('ViewPort.setupView: view object "' + viewName + '" not found');
	}
};

function setupMethods(view, viewObj) {
	// method to get view DOM element
	viewObj.getElement = function () {
		return view;
	};
	// method to get view object property
	viewObj.getProperty = function (propName) {
		if (viewObj[propName]) {
			var prop = viewObj[propName];
			if (typeof prop === 'object') {
				var res = prop.constructor();
				for (var i in prop) {
					res[i] = prop[i];
				}
				prop = res;
			}
			return prop;
		} else {
			return null;
		}
	};
	// TODO: not good. this should be handled by each view object
	// method to set view object property
	viewObj.setProperty = function (propName, value) {
		viewObj[propName] = value;
	};
}


/* Private */
ViewPort.prototype.openInit = function (newView, params) {
	if (this.currentView && this.currentView.onClose) {
		var self =  this;
		this.currentView.onClose(params, function () {
			self.closeView(newView);
		});
	}
	else {
		this.closeView(newView);
	}
	this.openView(newView, params);
	this.emit('open', newView, params);
};

/* Private */
ViewPort.prototype.overlayInit = function (newView, params) {
	newView.getElement().style.display = '';
	this.viewDepth += 1;
	newView.getElement().style.zIndex = this.viewDepth;
	if (newView.onOpenComplete) {
		newView.onOpenComplete(params);
		this.onOpenComplete();
	}
	this.emit('overlay', newView, params);
};

/* Private */
ViewPort.prototype.closeView = function (newView) {
	if (this.currentView) {
		this.currentView.getElement().style.display = 'none';
		if (this.currentView.onCloseComplete) {
			this.currentView.onCloseComplete();
		}
		this.unlock();
		// reset top lock if necessary
		resetTopLock(this.currentView);
		// update current view
		this.currentView = newView;
	}
	else {
		this.unlock();
		// reset top lock if necessary
		resetTopLock(this.currentView);
		// update current view
		this.currentView = newView;
	}
	this.emit('close', newView);
};

/* Private */
ViewPort.prototype.openView = function (newView, params) {
	newView.getElement().style.display = '';
	if (newView.onOpenComplete) {
		newView.onOpenComplete(params);
		this.onOpenComplete();
	}
	var self = this;
	window.setTimeout(function () {
		self.unlock();
		self.enableAll();
	}, 0);
	this.emit('openComplete', newView);
};

/* Private */
ViewPort.prototype.formatName = function (name){
	var key = '-';
	var index = name.indexOf(key);
	while(index > -1){
		var head = name.substring(0, index);
		var middle = name.substring(index + 1, index + 2).toUpperCase();
		var tail = name.substring(index + 2);
		name = head + middle + tail;
		index = name.indexOf(key);
	}
	return name.toLowerCase();
};

window.ViewPort = ViewPort;

}());
