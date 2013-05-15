(function () {

	var spriteList = [];
	var spriteLen = 0;

	function button(sprite) {
		if (spriteList.indexOf(sprite) === -1) {
			spriteList.push(sprite);
			spriteLen += 1;
			sprite.__enable = true;
		}
	}

	function setupButtonStart(event) {
		var isDown = true;
		initEvent('tapstart', event, isDown);
	}

	function setupButtonEnd(event) {
		var isDown = false;
		initEvent('tapend', event, isDown);
	}

	function setupButtonMove(event) {
		initEvent('tapmove', event, null);
	}

	function setupButtonCancel(event) {
		var isDown = false;
		initEvent('tapcancel', event, isDown);
	}

	function buttonEnable(sprite) {
		if (spriteList.indexOf(sprite) === -1) {
			return false;
		}
		sprite.__enable = true;
	}

	function buttonDisable(sprite) {
		if (spriteList.indexOf(sprite) === -1) {
			return false;
		}
		sprite.__enable = false;
	}

	if (!window.behaviors) {
		window.behaviors = {};
	}
	window.behaviors.button = button;
	window.behaviors.setupButtonStart = setupButtonStart;
	window.behaviors.setupButtonEnd = setupButtonEnd;
	window.behaviors.setupButtonMove = setupButtonMove;
	window.behaviors.setupButtonCancel = setupButtonCancel;
	window.behaviors.buttonEnable = buttonEnable;
	window.behaviors.buttonDisable = buttonDisable;

	function initEvent(eventName, event, isDown) {
		var touchPos = getTouchPos(event);
		for (var i = 0; i < spriteLen; i++) {
			triggerEvent(eventName, spriteList[i], event, touchPos, isDown);
		}
	}

	function getTouchPos(event) {
		var x = 0;
		var y = 0;
		if (event.changedTouches) {
			x = event.changedTouches[0].pageX;
			y = event.changedTouches[0].pageY;
		} else {
			x = event.offsetX;
			y = event.offsetY;
		}

		return { x: x, y: y };
	}

	function isInBounds(sprite, positions) {
		var localCoordinate = sprite._getLocalCoordinate(positions.x, positions.y);
		positions.localX = localCoordinate.x;
		positions.localY = localCoordinate.y;
		if (localCoordinate.x >= 0 && localCoordinate.x <= sprite.width) {
			if (localCoordinate.y >= 0 && localCoordinate.y <= sprite.height) {
				return true;
			}
		}
		return false;
	}

	function triggerEvent(eventName, sprite, event, positions, isDown) {
		if (sprite.__enable && sprite.isVisible() && isInBounds(sprite, positions)) {
			if (isDown !== null) {
				sprite.__isDown = isDown;
			}
			emitEvent(eventName, sprite, event, positions);
		} else if (sprite.__isDown && eventName === 'tapend') {
			delete sprite.__isDown;
			sprite.emit('tapendoutside', event, positions);
		} else if (sprite.__isDown && eventName === 'tapmove') {
			sprite.emit('tapmoveoutside', event, positions);
		}
	}

	function emitEvent(eventName, sprite, event, positions) {

		var emit = false;
		switch (eventName) {
			case 'tapstart':
				if (sprite.__isDown) {
					emit = true;
				}
				break;
			case 'tapmove':
				if (sprite.__isDown) {
					emit = true;
				}
				break;
			case 'tapend':
				if (!sprite.__isDown) {
					emit = true;
				}
				break;
			case 'tapcancel':
				if (sprite.__isDown) {
					emit = true;
				}
				break;
		}
		if (emit) {
			sprite.emit(eventName, event, positions);
		}
	}

}());