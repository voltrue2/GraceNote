(function () {

	function ImageBox(width, height, srcPath) {
		var image = new Dom(document.createElement('img'));
		image.allowEvents(['load']);
		image.on('load', function (event) {
			var w = this.get('width');
			var h = this.get('height');
			if (w > width || h > height) {
				var ratio = w / h;
				if (ratio > 1) {
					// wide
					w = width;
					h = w / ratio;
				} else if (ratio < 1) {
					// tall
					h = height;
					w = h * ratio;
				}
			}
			window.lightbox.show(w, h + 20, function (bar, box, close) {
				var container = box.createChild('div', { background: 'url(' + window.assets['triangle-pattern'] + ')', width: w + 'px', height: h + 'px' });
				var img = container.createChild('div', { width: w + 'px', height: h + 'px'});
				img.drawImage(srcPath);
			});
		});
		image.set('src', srcPath);	
	}

	window.ImageBox = ImageBox;

}());
