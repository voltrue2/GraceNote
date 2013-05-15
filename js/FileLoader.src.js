/** File Load Management Object **/

function FileLoader () {
	this.correctResponse = {
		200: true,
		201: true,
		302: true,
		
	};
};

FileLoader.prototype.img = function (path, cb, options) {
	path = path.replace(/\ /g, '%20');
	var self = this;
	var img = new Image();
	var loaded = function (event) {
		if (img.removeEventListener) {
			img.removeEventListener('load', loaded, false);
		}
		else {
			img.detachEvent('onload', loaded);
		}
		delete img;
		if (typeof cb == 'function'){
			cb(path, options);
		}
	};
	if (img.addEventListener){
		img.addEventListener('load', loaded, false);
	}
	else {
		img.attachEvent('onload', loaded);
	}
	img.setAttribute('src', path);
};

FileLoader.prototype.audio = function (path, cb, options) {
	path = path.replace(/\ /g, '%20');
	var self = this;
	var audio = document.createElement('audio');
	var loaded = function (event) {
		audio.removeEventListener('canplay', loaded);
		delete audio;
		if (typeof cd === 'function') {
			cb(path, options);
		}
	};
	audio.setAttribute('src', path);
	audio.setAttribute('preload', 'none');
	audio.addEventListener('canplay', loaded);
};

FileLoader.prototype.js = function (path, cb, options) {
	path = path.replace(/\ /g, '%20');
	var self = this;
	var js = document.createElement('script');
	var loaded = function (event) {
		delete js;
		if (typeof cb == 'function'){
			cb(path, options);
		}
	};
	var ready = function () {
		if (js.readyState && js.readyState == 'loaded'){
			self.loaded.js[path] = path;
			delete js;
			if (typeof cb == 'function'){
				cb(path, options);
			}
		}
	};
	js.setAttribute('type', 'text/javascript');
	js.setAttribute('src', path);
	if (!js.readyState){
		js.onload = loaded;
	}
	else {
		js.onreadystatechange = ready;
	}
	if (document.body){
		document.head.appendChild(js);
	}
	else {
		document.head.appendChild(js);	
	}
};

/** This method is a little different because we do not need a callback **/
FileLoader.prototype.css = function (path) {
	path = path.replace(/\ /g, '%20');
	var css = document.createElement('link');
	css.setAttribute('rel', 'stylesheet');
	css.setAttribute('type', 'text/css');
	css.setAttribute('href', path);
	if (document.body){
		document.head.appendChild(css);
	}
	else {
		document.head.appendChild(css);	
	}
};

/** Ajax method: the first argument for a callback is an error object > error object = null IS success **/
/** Reads JSON format ONLY **/
FileLoader.prototype.ajax = function (path, callback) {
	path = path.replace(/\ /g, '%20');
	var self = this;
	var req = false;
	if (window.XMLHttpRequest){
		req = new XMLHttpRequest();
		if (req.overrideMimeType){
			req.overrideMimeType('text');
		}
	}
	else if (window.ActiveXObject){
		try {
			req = new ActiveXObject('Msxml2.XMLHTTP');
		}
		catch (e){
			req = new ActiveXObject('Microsoft.XMLHTTP');
		}
	}
	if (req){
		req.open('POST', path, true);
		req.onreadystatechange = function (){
			if (req.readyState == 4){
				var response = null;
				var error = null;
				if (!self.correctResponse[req.status]){
					/* response error */
					response = null;
					error = {path: path, status: req.status, type: 'server', response: req.responseText};
				}
				else {
					/* succss */
					try {
						response = eval('(' + req.responseText + ')');
					}
					catch (e) {
						/* string to json error */
						response = null;
						error = {path: path, status: req.status, type: e, response: req.responseText};
					}
				}
				if (typeof(callback) == 'function'){
					callback(error, response, path);
				}
			}
		};
		req.send(null);
	}
	else {
		/* error */
		callback({error: 'noRequestObject'});
	}
};

FileLoader.prototype.get = function (path) {
	if (typeof path !== 'string'){
		return this.loaded;
	}
	path = path.replace(/\ /g, '%20');
	var js = path.toLowerCase().lastIndexOf('.js');
	var type = 'img';
	if (js > -1){
		type = 'js';
	}
	else if (path.toLowerCase().lastIndexOf('.css') > -1){
		type = 'css';
	}
	if (this.loaded[type][path]){
		return this.loaded[type][path];
	}
	else {
		return false;
	}
};

/** File preloader object extends FileLoader object (can NOT be reused) **/
/** image, javascript, ajax **/
Preloader.prototype = new FileLoader();
Preloader.prototype.constructor = FileLoader;
Preloader.superclass = FileLoader.prototype;

function Preloader () {
	this.load = {};
	this.load.img = [];
	this.load.js = [];
	this.load.css = [];
	this.load.ajax = [];
	this.load.audio = [];
	this.loaded = {};
	this.loaded.img = [];
	this.loaded.js = [];
	this.loaded.ajax = [];
	this.loaded.audio = [];
}

Preloader.prototype.onComplete = function (type, loadedFilesMap) {};

Preloader.prototype.addImage = function (path) {
	this.load.img[this.load.img.length] = path;
};

Preloader.prototype.addJs = function (path) {
	this.load.js[this.load.js.length] = path;
};

Preloader.prototype.addAjax = function (path) {
	this.load.ajax[this.load.ajax.length] = path;
};

Preloader.prototype.addAudio = function (path) {
	this.load.audio[this.load.audio.length] = path;
};

/* synchronus: Boolean (default true) > synchronus load OR asynchronus load */
Preloader.prototype.loadImage = function (synchronus) {
	if (synchronus == undefined){
		synchronus = true;
	}
	/** load images **/
	if (this.load.img.length > 0) {
		var self = this;
		var countImage = function (path) {
			self.countLoaded('img', path);
		};
		if (synchronus){
			this.synchronusLoad(this, 'img', countImage, 0);
		}
		else {
			var len = this.load.img.length;
			for (var i = 0; i < len; i++){
				this.img(this.load.img[i], countImage);
			}
		}
	}
};

/* synchronus: Boolean (default true) > synchronus load OR asynchronus load */
Preloader.prototype.loadJs = function (synchronus) {
	if (synchronus == undefined){
		synchronus = true;
	}
	/** load javascripts **/
	if (this.load.js.length > 0) {
		var self = this;
		var countJs = function (path) {
			self.countLoaded('js', path);
		};
		if (synchronus){
			this.synchronusLoad(this, 'js', countJs, 0);
		}
		else {
			var len = this.load.js.length;
			for (var i = 0; i < len; i++){
				this.js(this.load.js[i], countJs);
			}
		}
	}
};

/* synchronus: Boolean (default true) > synchronus load OR asynchronus load */
Preloader.prototype.loadAjax = function (synchronus) {
	if (synchronus == undefined){
		synchronus = true;
	}
	/** load javascripts **/
	if (this.load.ajax.length > 0) {
		var self = this;
		var countAjax = function (error, response, path) {
			self.countLoaded('ajax', {error: error, response: response, path: path});
		};
		if (synchronus){
			this.synchronusLoad(this, 'ajax', countAjax, 0);
		}
		else {
			var len = this.load.ajax.length;
			for (var i = 0; i < len; i++){
				this.ajax(this.load.ajax[i], countAjax);
			}
		}
	}
};

/* synchronus: Boolean (default true) > synchronus load OR asynchronus load */
Preloader.prototype.loadAudio = function (synchronus) {
	if (synchronus == undefined){
		synchronus = true;
	}
	/** load audio **/
	if (this.load.audio.length > 0) {
		var self = this;
		var countAudio = function (error, response, path) {
			self.countLoaded('audio', {error: error, response: response, path: path});
		};
		if (synchronus){
			this.synchronusLoad(this, 'audio', countAudio, 0);
		}
		else {
			var len = this.load.audio.length;
			for (var i = 0; i < len; i++){
				this.audio(this.load.audio[i], countAudio);
			}
		}
	}
};

Preloader.prototype.synchronusLoad = function (self, fileType, countFunc, counter) {
	self[fileType](self.load[fileType][counter], function (type, response, option) {
		countFunc.apply({}, arguments);
		counter++;
		if (self.load[fileType][counter]){
			self.synchronusLoad(self, fileType, countFunc, counter);
		}
	});
};

Preloader.prototype.countLoaded = function (type, path) {
	this.loaded[type][this.loaded[type].length] = path;
	if (this.loaded[type].length >= this.load[type].length){
		this.complete(type);
	}
	
};

Preloader.prototype.complete = function (type) {
	var map = {};
	var tmp = this.loaded[type]; 
	var len = tmp.length;
	for (var i = 0; i < len; i++){
		var stored = tmp[i];
		if (typeof stored == 'string'){
			map[stored] = stored;
		}
		else if (stored && stored.path){
			map[stored.path] = stored;
		}
	}
	this.load[type] = [];
	this.onComplete(type, map);
};
