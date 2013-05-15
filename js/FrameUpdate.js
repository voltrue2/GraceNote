(function () {

function FrameUpdate() {
	EventEmitter.call(this);
	this._timer = null;
	this._updateInterval = 20; // FPS: 50 by default
}

window.inherits(FrameUpdate, EventEmitter);
window.FrameUpdate = FrameUpdate;

FrameUpdate.prototype.setFPS = function (fps) {
	this._updateInterval = Math.floor(1000 / fps);
	this.emit('setFPS', fps, this._updateInterval);
};

FrameUpdate.prototype.start = function () {
	if (!this._timer) {
		this._setUpdate();
	}
};

FrameUpdate.prototype.stop = function () {
	if (this._timer) {
		window.clearInterval(this._timer);
		this._timer = null;
		this.emit('stop');
	}
};

FrameUpdate.prototype._setUpdate = function () {
	if (this._timer) {
		window.clearInterval(this._timer);
		this._timer = null;
	}
	var that = this; 
	var start  = Date.now();
	this._timer = window.setInterval(function () {
		that.emit('update');
	}, this._updateInterval);
	this.emit('start');
};

}());
