/* Dependencies: EventEmitter */
(function () {
	
	// event: string: 'change' > listen for all property changes, 'change.propertyToListenFor > listen for the given property's changes only'
	function watch(objToWatch) {
		var that = this;
		return new Watcher(objToWatch);
	}

	window.watch = watch;
	
	function Watcher(objToWatch) {
		window.EventEmitter(this);
		this._obj = this.setupSetter(objToWatch);
	}

	window.inherits(Watcher, EventEmitter);

	Watcher.prototype.setupSetter = function (obj) {
		var that = this;
		for (var prop in obj) {
			console.log(obj, prop);
			obj.__defineGetter__(prop, function (val) {
				return val;
			});
			obj.__defineSetter__(prop, function (newVal) {
				var original = obj[prop];
				obj[prop] = newVal;
				that.emit('change', original, newVal);
				that.emit('change.' + prop, original, newVal);
			});
		}
	};

}());
