/* Dependencies: Loader */
(function (window) {

function Server (pathPrefix) {
	if (pathPrefix) {
		// pathPrefix needs to end with a slash (/)
		this.pathPrefix = pathPrefix;
	}
	else {
		this.pathPrefix = '';
	}
	this.loader = new Loader();
}

window.Server = Server;

Server.prototype.setTimeout = function (timeout) {
	this.loader.setTimeout(timeout);
};

Server.prototype.onSend = function () {};

Server.prototype.onReponse = function (error, response, path, sendObj, cb) { cb(); };

Server.prototype.onCallback = function () {};

Server.prototype.onError = function (error, response, path) {};

Server.prototype.send = function (method, params, cb) {
	this.onSend();
	var self = this;
	this.loader.ajax(this.getPath(method, null), params, function (error, path, response) {
		if (error) {
			return self.onError(error, response, {method: method, params: params, cb: cb});
		}
		self.onResponse(error, response, path, {method: method, params: params, cb: cb}, function () {
			cb(error, response, path);
			self.onCallback();
		});
	});
};

/* Private */
Server.prototype.getPath = function (method, params) {
	var p = '';
	if (params) {
		p = '?';
		var c = 0;
		for (var key in params) {
			head = '';
			if (c) {
				head = '&';
			}
			else {
				c = 1;
			}
			p += head + key + '=' + params[key];
		}
	}
	return this.pathPrefix + method.replace('.', '/') + '/' + p;
};

}(window));
