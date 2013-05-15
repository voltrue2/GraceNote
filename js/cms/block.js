(function () {
	
	var loader = window.loader || null;
	var body = new Dom(document.body);
	var block = Dom.create('div');
	var waiting = false;
	var duration = 1;
	var timer = null;
	var visible = false;
	var altSpinner = null;
	
	block.setStyle({ 
		background: 'rgba(0, 0, 0, 0.4)', 
		position: 'fixed', 
		top: 0, 
		left: 0, 
		zIndex: 999, 
		width: '100%', 
		height: '100%',
		opacity: 0,
		display: 'none'
	});	
	body.appendChild(block);

	window.block = block;
	
	var tweenIn = new Tween(Tween.StrongOut, { a: 0 }, { a: 1 }, 0, duration);
	tweenIn.on('change', function (v) {
		block.setStyle({ opacity: v.a });
	});
	tweenIn.on('finish', function () {
		window.clearInterval(timer);
		timer = null;
	});
	
	var tweenOut = new Tween(Tween.StrongOut, { a: 1 }, { a: 0 }, 0, duration);
	tweenOut.on('change', function (v) {
		block.setStyle({ opacity: v.a });
	});
	tweenOut.on('finish', function () {
		block.setStyle({ display: 'none' });
		window.clearInterval(timer);
		timer = null;
		if (altSpinner) {
			altSpinner.remove();
			altSpinner = null;
		}
	});
	
	var spinner = block.createChild('div', { 
		mozBorderRadius: '25px', 
		borderRadius: '25px', 
		opacity: 0.8, 
		margin: '-50px -50px',
		position: 'fixed',
		top: '50%',
		left: '50%', 
		width: '100px', 
		height: '100px'
	});
	spinner.drawImage('/img/preloaders/preloader-blue.gif');

	function setup(loader) {
		if (loader) {
			loader.on('ajax.send', function () {
				show();
			});
			loader.on('ajax.complete', function () {
				hide();
			});
			loader.on('ajax.response', function () {
				hide();
			});
			loader.on('ajax.error', function () {
				hide();
			});
		}
	}

	function show() {
		visible = +1;
		if (visible === 1) {
			tweenOut.stop();
			block.setStyle({ display: 'block', opacity: 0 });
			timer = window.setInterval(function () {
				tweenIn.update();
			}, 0);
			tweenIn.start();
		}
	}

	function hide() {
		visible -= 1;
		if (visible <= 0) {
			tweenIn.stop();
			visible = 0;
			timer = window.setInterval(function () {
				tweenOut.update();
			}, 0);
			tweenOut.start();
		}
	}

	window.blocker = {};
	window.blocker.setup = setup;
	window.blocker.show = show;
	window.blocker.hide = hide;

}());
