(function () {
	// globally allow events
	Dom.allowEvents({
		mousedown: 'tapstart', 
		mouseup: 'tapend',
		touchstart: 'tapstart',
		touchend: 'touchend'
	});

}());
