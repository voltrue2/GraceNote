(function (window) {

	function Timer() {
		window.EventEmitter.call(this);
	}

	window.inherits(Timer, window.EventEmitter);
	window.Timer = Timer;

	Timer.prototype.update = function () {
		this.emit('_update', Date.now());
	};

	Timer.prototype.wait = function (duration, cb) {
		var startTime = Date.now();
		var that = this;
		this.once('_update', timeCheck);

		function timeCheck(now) {
			if ((now - startTime) / 1000 >= duration) {
				return cb();
			} else {
				that.once('_update', timeCheck);
			}
		}
	};

	Timer.prototype.loop = function (interval, cb) {
		var startTime = Date.now();
		this.on('_update', timeCheck);
		function timeCheck(now) {
			if ((now - startTime) / 1000 >= interval) {
				startTime = now;
				return cb();
			}
		}
		return { _loopSrc: this, _callback: timeCheck };
	};

	Timer.prototype.clearLoop = function (loopObj){
		loopObj._loopSrc.removeListener('_update', loopObj._callback);
	};

}(window));