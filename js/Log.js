(function () {

	var loader = new Loader();

	var types = {
		'verbose': { enable: true, send: false },
		'debug': { enable: true, send: false },
		'info': { enable: true, send: false },
		'warn': { enable: true, send: false },
		'error': { enable: true, send: false },
		'fatal': { enable: true, send: false }
	};

	window.onerror = function (message, url, line) {
		window.log.error(url + ' [line#: ' + line + ']', message);
		window.log.emit('error', message);
	};

	function Log() {
		window.EventEmitter.call(this);

		this.enable = function (title) {
			if (types[title] !== undefined) {
				typoes[title].enable = true;
			}
		};

		this.disable = function (title) {
			if (types[title] !== undefined) {
				types[title].enable = false;
			}
		};		

		this.remoteLogging = function (title, uri) {
			if (types[title]) {
				types[title].send = uri;
			}
		};

		this.verbose = function () {
			logger('log', 'verbose', arguments);
		};
		
		this.debug = function () {
			logger('log', 'debug', arguments);
		};

		this.info = function () {
			logger('log', 'info', arguments);
		};
		
		this.warn = function () {
			logger('warn', 'warn', arguments);
		};

		this.error = function () {
			logger('error', 'error', arguments);
		};
		
		this.fatal = function () {
			logger('error', 'fatal', arguments);
		};
	}

	window.inherits(Log, window.EventEmitter);
	window.log = new Log();

	function logger(fn, title, args) {
		if (types[title].enable) {
			var argList = ['<' + title + '>'];
			for (var i = 0, len = args.length; i < len; i++) {
				argList.push(args[i]);
			}
			console[fn].apply(console, argList);
			if (title === 'error' || title === 'fatal') {
				console.trace();
			}
			// send log message to remote server
			if (types[title].send) {
				var uri = types[title].send;
				var params = {
					type: title,
					msg: args
				};
				loader.ajax(uri, params, function () {

				});
			}
		}
	}

}());
