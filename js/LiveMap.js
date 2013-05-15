(function () {

	function LiveMap(obj) {
		EventEmitter.call(this);
		this._src = obj;
	}

	window.inherits(LiveMap, EventEmitter);
	window.LiveMap = LiveMap;

	LiveMap.prototype.update = function (obj) {
		if (this._src != obj) {
			this._handleUpdate(obj);
		}
	};

	LiveMap.prototype._handleUpdate = function (obj) {
		
	};

}());
