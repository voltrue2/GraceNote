
// Loader
(function (window) {

var self = null;
var timeout = null; // timeout for ajax
var correctResponse = {
	200: true,
	201: true,
	301: true,
	302: true
};
var waiting = false;
var pending = [];

function Loader() {
	EventEmitter.call(this);
	self = this;
}

window.inherits(Loader, EventEmitter);
window.Loader = Loader;

Loader.prototype.setTimeout = function (millisec) {
	timeout = millisec;
};

// single ajax call
Loader.prototype.ajax = function (pathSrc, options, cb) {
	if (waiting) {
		return pending.push({ path: pathSrc, options: options, callback: cb });
	}
	waiting = true;
	var timer = null;
	var requestType = (options && options.requestType) ? options.requestType : 'POST';
	var path = preparePath(pathSrc);
	var getParams = createGetParams(options, requestType);
	var postParams = createPostParams(options, requestType);
	var req = new window.XMLHttpRequest();
	var response = null;
	var responded = false;
	var error = null;
	var that = this;
	self.emit('ajax.call', null, path);
	req.overrideMimeType('text');
	req.open(requestType, path + getParams, true);
	req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	req.onreadystatechange = function () {
		if (req.readyState === 4) {
			try {
				responded = true;
				response = JSON.parse(req.responseText);
				// check for errors
				if (correctResponse[req.status]) {
					// success
					if (timer) {
						window.clearTimeout(timer);
					}
					self.emit('ajax.response', null, path, options);
					cb(null, path, response);
				} else {
					// error req.status 0 is aborted request > timeout
					error = JSON.stringify({ status: req.status, path: path, response: response });
					throw new Error(error);
				}
					
			} catch (Exception) {
				if (timer) {
					window.clearTimeout(timer);
				}
				cb(Exception, path);
				console.error('Loader.ajax: ', path, Exception);
				console.trace();
				try {
					error = json.parse(error);    
				} catch (exception) {
					// do nothing    
				}
				self.emit('ajax.error', error, path, response);
			}
			// response back
			waiting = false;
			if (pending.length) {
				var request = pending.shift();
				that.ajax(request.path, request.options, request.callback);
			} else {
				that.emit('ajax.complete');
			}
		}
	};
	self.emit('ajax.send');
	req.send(postParams);
	// set up timeout
	if (timeout > 0) {
		timer = window.setTimeout(function () {
			if (responded) {
				window.clearTimer(timer);
			} else {
				req.abort();
			}		
		}, timeout);
	}
}; 

// multiple synchronous ajax calls
Loader.prototype.batchAjax = function (pathList, optionList, cb) {
	var listLen = pathList.length;
	var counter = 0;
	var responseList = {};
	var errorList = {};
	var load = function (error, path, response) {
		counter += 1;
		if (error) {
			errorList[path] = response;
		} else {
			var path
			responseList[path] = response;
		}
		if (counter === listLen) {
			var error = null;
			if (Object.keys(errorList).length) {
				error = errorList;
			}
			self.emit('ajax.loadComplete', error, responseList);
			cb(error, responseList);
		} else {
			self.ajax(pathList[counter], optionList[counter], load);
		}
	};
	this.ajax(pathList[counter], optionList[counter], load);
};

// synchronous load
Loader.prototype.img = function (imageList, cb) {
	var listLen = imageList.length;
	var counter = 0;
	var loadedList = [];
	var errorList = [];
	var load = function (error, loadedPath) {
		counter += 1;
		if (error) {
			errorList.push(loadedPath);
			self.emit('img.error', error, loadedPath);
		} else {
			loadedList.push(loadedPath);
			self.emit('img.load', null, loadedPath);
		}
		if (counter < listLen) {
			loadImage(imageList[counter], load);
		} else {
			var error = null;
			if (errorList.length) {
				error = errorList;
			}
			self.emit('img.loadComplete', error, loadedList);
			cb(error, loadedList); // done
		}
	};
	loadImage(imageList[counter], load); 
};

// asynchronous load
Loader.prototype.asyncImg = function (imgList, cb) {
	var listLen = imgList.length;
	var counter = 0;
	var loadedList = [];
	var errorList = [];
	var load = function (error, loadedPath) {
		counter += 1;
		if (error) {
			errorList.push(loadedPath);
			self.emit('asyncImg.error', error, loadedPath);
		} else {
			loadedList.push(loadedPath);
			self.emit('asyncImg.load', null, loadedPath);
		}
		if (counter === listLen) {
			var error = null;
			if (errorList.length) {
				error = errorList;
			}
			self.emit('asyncImage.loadComplete', error,  loadedList);
			cb(error, loadedList); // done
		}
	};
	for (var i = 0; i < listLen; i++) {
		loadImage(imgList[i], load);
	}
};

// synchronous load only
Loader.prototype.js = function (jsList, cb) {
	var listLen = jsList.length;
	var counter = 0;
	var loadedList = [];
	var errorList = [];
	var load = function (error, loadedFile) {
		counter += 1;
		if (error) {
			errorList.push(loadedFile);
			self.emit('js.error', error, loadedFile);
		} else {
			loadedList.push(loadedFile);
			self.emit('js.load', null, loadedFile);
		}
		if (counter < listLen) {
			loadJs(jsList[counter], load);
		} else {
			var error = null;
			if (errorList.length) {
				error = errorList;
			}
			self.emit('js.loadComplete', error, loadedList);
			cb(error, loadedList); // done
		}
	};
	loadJs(jsList[counter], load);
};

function loadImage(pathSrc, cb) {
	var path = preparePath(pathSrc);
	var img = new Image();
	var loaded = function (event) {
		img.removeEventListener('load', this, false);
		img.removeEventListener('error', this, false);
		delete img;
		cb(null, path);	
	};
	var errored = function (event) {
		img.removeEventListener('load', this, false);
		img.removeEventListener('error', this, false);
		delete img;
		cb(event, path);
	};
	img.addEventListener('load', loaded, false);
	img.addEventListener('error', errored, false);	
	img.setAttribute('src', path);
}

function loadJs(pathSrc, cb) {
	var path = preparePath(pathSrc);
	var script = document.createElement('script');
	var loaded = function (event) {
		script.removeEventListener('load', loaded, false);
		script.removeEventListener('error', errored, false);
		delete script;
		cb(null, path);
	};
	var errored = function (event) {
		script.removeEventListener('load', loaded, false);
		script.removeEventListener('error', errored, false);
		delete script;
		cb(event, path);
	};
	script.addEventListener('load', loaded, false);
	script.addEventListener('error', errored, false);
	script.setAttribute('type', 'text/javascript');
	script.setAttribute('src', path);
	if (document.body){
		document.head.appendChild(script);
	} else {
		document.head.appendChild(script);	
	}
}

function createGetParams(obj, type) {
	if (type.toLowerCase() !== 'get') {
		return '';
	}
	var str = '';
	var first = true;
	for (var key in obj) {
		if (!first) {
			str += '&';
		} else {
			str += '?';
		}
		str += key + '=' + prepareValue(obj[key]);
		first = false;
	}
	return str;
}

function createPostParams(obj, type) {
	if (type.toLowerCase() !== 'post') {
		return null;
	}
	/*
	var fd = new FormData();
	for (var key in obj) {
		fd.append(key, obj[key]);
	}
	*/
	return createGetParams(obj, 'get').replace('?', '');
}

function prepareValue(value) {
	if (typeof value === 'object') {
		return JSON.stringify(value);
	}
	return value;
}

function preparePath(path) {
	return window.encodeURI(path);
}

}(window));
