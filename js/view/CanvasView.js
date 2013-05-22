(function () {

	function CanvasView() {
		window.Sprite.call(this);
		var that = this;
		this.on('close', function () {
			that.hide();
		});
		this.on('open', function () {
			that.show();
		});
		this.on('openAfterClose', function () {
			that.show();
		});
		this.once('add', function (viewPort) {
			var size = viewPort.getSize();
			that.width = size.width;
			that.height = size.height;
		});
	}

	window.inherits(CanvasView, window.Sprite);
	window.CanvasView = CanvasView;

}());
