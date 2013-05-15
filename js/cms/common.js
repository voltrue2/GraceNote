(function () {
	
	// globally set event name with alias
	Dom.setEventNameAlias({
		mousedown: 'tapstart',
		touchstart: 'tapstart',
		mouseup: 'tapend',
		touchend: 'tapend',
		mousemove: 'tapmove',
		touchmove: 'tapmove'
	});
	// globally allow events
	Dom.allowEvents(['mousedown', 'mouseup', 'touchstart', 'touchend']);

}());
