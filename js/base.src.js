/* Dependencies: EventEmitter */
(function (window) {

var self = null;
var target = null;
var events = {
	start: 'tapStart',
	end: 'tapEnd',
	move: 'tapMove',
	cancel: 'tapCancel'
};
var eventNames = {
	touch: {
		start: 'touchstart',
		end: 'touchend',
		move: 'touchmove',
		cancel: 'touchcancel'
	},
	mouse: {
		start: 'mousedown',
		end: 'mouseup',
		move: 'mousemove',
		cancel: 'mouseout'
	}
};
var disables = {}; // list of disabled event names
	
function Gesture(targetElement) {
	self = this;
	target = targetElement;
}

window.Gesture = Gesture;
Gesture.prototype = new EventEmitter();

Gesture.prototype.tap = on;

// direction: left, right, top, bottom
Gesture.prototype.swipe = function (direction, cb) {
	var start = false;
	var dist = 10; // at least dist px to be considered as swipe 
	var anchorX = 0;
	var anchorY = 0;
	var touch = canTouch();
	this.tap('start', function (event) {
		start = true;
		var src = null;
		if (touch) {
			src = event.touches[0];
		} else {
			src = event;
		}
		if (src) {
			anchorX = src.pageX;
			anchorY = src.pageY;
		}
	});
	this.tap('end', function (event) {
		if (start) {
			var src = null;
			if (touch) {
				src = event.changedTouches[0];
			} else {
				src = event;
			}
			if (src) {
				var diffX = src.pageX - anchorX;
				var diffY = src.pageY - anchorY;
				if (direction === 'left') {
					if (diffX < 0 && diffX <= (-1 * dist)) {
						cb();
					}
				} else if (direction === 'right') {
					if (diffX > 0 && diffX >= dist) {
						cb();
					}
				} else if (direction === 'top') {
					if (diffY < 0 && diffY <= (-1 * dist)) {
						cb();
					}
				} else if (direction === 'bottom') {
					if (diffY > 0 && diffY >= dist) {
						cb();
					}
				}
			}
			start = false;
			anchorX = 0;
			anchorY = 0;
		}
	});
};

Gesture.prototype.disable = function (event) {
	if (!disables[event]) {
		disables[event] = true;
	}
	
};

Gesture.prototype.enable = function (event) {
	if (disables[event]) {
		delete disables[event];
	}
};

function on(event, cb) {
	self.on(event, cb);
	var gestureEvent = getEventName(event);
	addEvent(gestureEvent, function (eventParams) {
		handleEvent(eventParams, event);
	});
};

function getEventName(tapEventName) {
	var eventName = null;
	if (canTouch()) {
		eventName = eventNames.touch[tapEventName];
	} else {
		eventName = eventNames.mouse[tapEventName];
	}
	if (eventName) {
		return eventName;
	} else {
		console.warn('Gesture.getEventName: Invalid event: ', tapEventName);
		console.trace();
		return null;
	}
}

function addEvent(eventName, cb) {
	if (typeof cb === 'function') {
		target.addEventListener(eventName, cb, false);
	} else {
		console.warn('Gesture.addEvent: cb is expected to be a function.', cb);
		console.trace();
	}
}

function handleEvent(params, eventName) {
	if (!disables[eventName]) {
		self.emit(eventName, params);
	}
}

function canTouch() {
	if (window && window.ontouchstart !== undefined) {
		return true;
	} else {
		return false;
	}
}

}(window));

(function (window) {

var target = null;
var parent = null;
var self = null;
var classNames = [];

// targetElement: query string or DOM element
function Ui(targetElement, parentUi) {
	self = this;
	if (parentUi) {
		if (typeof parentUi === 'Ui') {
			parent = parentUi;
		}
	}
	target = getTarget(targetElement);
	if (!target) {
		console.error('Ui.constructor: invalid targetElement: ', targetElement);
		console.trace();
		return false;
	}
	target.__ui = self;
	classNames = target.className.split(' ') || [];
}

Ui.prototype = new EventEmitter();
window.Ui = Ui;

Ui.prototype.getDOM = function () {
	return target;
};

Ui.prototype.create = function (elementType) {
	var child = document.createElement(elementType);
	if (child) {
		target.appendChild(child);
		var ui = new window.Ui(child, self);
		return ui;
	} else {
		console.error('Ui.create: Failed to create "' + elementType + '"', elementType);
		console.trace();
		return null;
	}
};

Ui.prototype.destroy = function (ui) {
	var elm = ui.getDOM();
	elm.parentNode.removeChild(elm);
	delete ui;
};

Ui.prototype.css = function (styles) {
	for (var name in styles) {
		target.style[name] = styles[name];
	}
};

Ui.prototype.addClassName = function (clsList) {
	classNames = classNames.concat(clsList);
	target.className = classNames.join(' ');
};

Ui.prototype.removeClassName = function (clsList) {
	var newList = classNames.map(function (item) {
		if (clsList.indexOf(item) === -1) {
			return item;
		}
	});
	target.className = classNames.join(' ');
};

Ui.prototype.replaceClassName = function (removeList, addList) {
	self.removeClassName(removeList);
	self.addClassName(addList);
};

Ui.prototype.find = function (queryString) {
	var res = target.queryselector(queryString);
	if (res && res.__ui) {
		return res.__ui;
	} else {
		return res;
	}
};

function getTarget(targetElement) {
	var elm = null;
	if (typeof targetElement === 'string') {
		var p = null;
		if (parent) {
			p = parent.getDOM();
		} else {
			p = document.body;
		}
		elm = p.querySelector(targetElement);
	} else {
		elm = targetElement;	
	}
	return elm;
}

}(window));