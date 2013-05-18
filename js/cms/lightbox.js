(function () {

var lightbox = {};

window.lightbox = lightbox;

lightbox.show = function (width, height, callback) {
	var x = width / 2;
	var y = height / 2;
	var parent = Dom.create('div');
	parent.appendTo(document.body);
	parent.setStyle({
		position: 'fixed',
		top: 0,
		left: 0,
		width: '100%',
		height: '100%',
		zIndex: 999
	});
	var bg = parent.createChild('div');
	bg.setStyle({
		width: '100%',
		height: '100%',
		background: 'rgba(0, 0, 0, 0.3)'
	});
	bg.on('tapstart', function () {
		parent.remove();
	});
	var box = parent.createChild('div');
	box.setStyle({
		position: 'fixed',
		top: '50%',
		left: '50%',
		margin: '-' + y + 'px -' + x + 'px',
		background: '#fff',
		border: '4px solid #666',
		width: width + 'px',
		height: height + 'px',
		webkitBoxShadow: '0 0 6px #000',
		mozBoxShadow: '0 0 6px #000',
		boxShadow: '0 0 6px #000'
	});
	var bar = box.createChild('div');
	bar.setClassName('menu title');
	bar.setStyle({
		height: '20px',
	});
	var close = bar.createChild('div');
	close.setClassName('cancel-button menu-item');
	close.setStyle({
		width: '12px',
		height: '12px'
	});
	close.on('tapend', function () {
		parent.remove();
	});
	if (typeof callback === 'function') {
		callback(bar, box, function close() {
			parent.remove();
		});
	}
	parent.on('remove', function () {
		parent.emit('close');
	});
	return parent;
};

}());
