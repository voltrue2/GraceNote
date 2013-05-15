/* dependencies: EventEmitter */
(function () {

var eventNameAlias = {};
var allowedEvents = null;
var buttonEvents = {
	'mousedown': 'tapstart',
	'touchstart': 'tapstart',
	'mouseup': 'tapend',
	'touchend': 'tapend',
	'mousemove': 'tapmove',
	'touchmove': 'tapmove',
	'mouseover': 'over',
	'mouseuout': 'out'
};

function Dom(srcElm) {
	if (!srcElm) {
		return null;
	}
	EventEmitter.call(this);
	this._src = srcElm;
	this._src.__domObject = this;
	// event name alias
	this._eventNameAlias = eventNameAlias;
	// allowed events
	if (allowedEvents) {
		this.allowEvents(allowedEvents);
	}
}

// static methods

Dom.query = query;
Dom.getById = getById;
Dom.setEventNameAlias = setEventNameAlias;
Dom.allowEvents = allowEvents;
Dom.create = create;
Dom.button = button;

function create(tagName) {
	var elm = document.createElement(tagName);
	return new Dom(elm);
}

function query(q, me) {
	var dom = me || document;
	var list = dom.querySelectorAll(q);
	var res = [];
	for (var i = 0, len = list.length; i < len; i++) {
		var item = null;
		if (list[i].__domObject) {
			item = list[i].__domObject;
		} else {
			item = new Dom(list[i]);
		}
		res.push(item);
	}
	return res;
}

function getById(id) {
	var elm = document.getElementById(id);
	if (elm) {
		if (elm.__domObject) {
			return elm.__domObject;
		}
		return new Dom(elm);
	}
	return null;
}

function button(dom) {
	dom.allowEvents(buttonEvents);
}

/*
* alias { eventName: aliasForEvent } 
* Example: { mousedown: 'tapstart', touchstart: 'tapstart' } 
* dom.on('tapstart', function) will listen to both mousedown and touchstart
*/
function setEventNameAlias(alias) {
	eventNameAlias = alias;
}

function allowEvents(eventList) {
	allowedEvents = eventList;
}

window.inherits(Dom, EventEmitter);
window.Dom = Dom;

// public methods 

Dom.prototype.appendTo = function (parent) {
	var prevParent = this.getParent();
	if (prevParent) {
		this.remove();
	}
	if (parent instanceof Dom) {
		parent = parent._src;
	}
	parent.appendChild(this._src);	
};

Dom.prototype.query = function (queryStr) {
	return query(queryStr, this._src);
};

Dom.prototype.setStyle = function (styles) {
	for (var key in styles) {
		this._src.style[key] = styles[key];
	}
};

Dom.prototype.get = function (key) {
	return this._src[key];
};

Dom.prototype.set = function (key, value) {
	this._src[key] = value;
};

Dom.prototype.exec = function (funcName, callback) {
	var res = null;
	if (typeof this._src[funcName] === 'function') {
		res = this._src[funcName]();
	}
	if (typeof callback === 'function') {
		cb(null, res);
	}
};

Dom.prototype.setAttribute = function (attributes) {
	for (var key in attributes) {
		this._src.setAttribute(key, attributes[key]);
	}
};

Dom.prototype.getAttribute = function (keyList) {
	var list = {};
	for (var i = 0, len = keyList; i < len; i++) {
		list[keyList[i]] = this._src.getAttribute(keyList[i]);
	}
	return list;
};

Dom.prototype.getParent = function () {
	var parent = this._src.parentNode;
	if (!parent) {
		return null;
	}
	return parent.__domObject || new Dom(parent);
};

Dom.prototype.appendChild = function (childDom) {
	try {
		this._src.appendChild(childDom._src);
		this.emit('appendChild');
	} catch (exception) {
		console.error('Dom.appendChild', exception);
		console.trace();
		childDom = null;
	}
	return childDom;
};

Dom.prototype.createChild = function (domType, styles) {
	var src = document.createElement(domType);
	var childDom = new Dom(src);
	if (styles) {
		childDom.setStyle(styles);
	}
	this.appendChild(childDom);
	this.emit('createChild');
	return childDom;
};

Dom.prototype.removeChild = function (childDom) {
	try {
		this._src.removeChild(childDom._src);
		this.emit('removeChild');
	} catch (exception) {
		console.error('Dom.removeChild', exception);
		console.trace();
	}
};

Dom.prototype.removeAllChildren = function () {
	this._src.innerHTML = '';
};

Dom.prototype.remove = function () {
	try {
		this.getParent()._src.removeChild(this._src);
		this.emit('remove');	
	} catch (exception) {
		console.error('Dom.remove', exception);
		console.trace();
	}
};

/*
* eventList { eventName: aliasForEvent } 
* Example: { mousedown: 'tapstart', touchstart: 'tapstart' } 
* dom.on('tapstart', function) will listen to both mousedown and touchstart
*/
Dom.prototype.allowEvents = function (eventList) {
	var that = this;
	var callback = function (event) {
		that.emit(that._eventNameAlias[event.type] || event.type, event);
	};

	for (var i = 0, len = eventList.length; i < len; i++) {
		var event = eventList[i];
		this._src.addEventListener(event, callback, false);
	}
};

Dom.prototype.setClassName = function (clsName) {
	this._src.className = clsName;
};

Dom.prototype.addClassName = function (clsName) {
	this._src.className = (this._src.className || '') + ' ' + clsName;
};

Dom.prototype.removeClassName = function (clsName) {
	var clsList = this._src.className.split(' ');
	var index = clsList.indexOf(clsName);
	if (index !== -1) {
		clsList.splice(index, 1);
	}
	this.setClassName(clsList.join(' '));
};

Dom.prototype.text = function (text) {
	this._src.textContent = text;
	this.emit('text', text);
};

Dom.prototype.html = function (html) {
	this._src.innerHTML = html;
	this.emit('html', html);
};

Dom.prototype.numberInput = function () {
	this.allowEvents(['keyup']);
	this.on('keyup', function () {
		// number only allowed
		var val = this.get('value');
		var allowed = '';
		for (var i = 0, len = val.length; i < len; i++) {
			if (val[i].match(/^[\d]+$/)) {
				allowed += val[i];
			}
		}
		this.set('value', allowed);
	});
};

Dom.prototype.drawImage = function (imagePath, options) {
	if (!options) {
		options = {};
	}
	this.setStyle({
		backgroundImage: 'url(' + imagePath + ')',
		backgroundSize: (options.size || '100%'),
		backgroundRepeat: 'no-repeat',
		backgroundPositionX: ((options.positionX !== undefined) ? options.positionX : 50) + '%',
		backgroundPositionY: ((options.positionY !== undefined) ? options.positionY: 50) + '%'
	});
	this.emit('drawImage', imagePath, options);
};

// value: Number, measure: String > %, px
Dom.prototype.width = function (value, measure) {
	setNumberStyle(this, 'width', value, measure);
};

Dom.prototype.height = function (value, measure) {
	setNumberStyle(this, 'height', value, measure);
};


Dom.prototype.x = function (value, measure) {
	setNumberStyle(this, 'left', value, measure);
};

Dom.prototype.y = function (value, measure) {
	setNumberStyle(this, 'top', value, measure);
};

Dom.prototype.show = function () {
	this._src.style.display = '';
};

Dom.prototype.hide = function () {
	this._src.style.display = 'none';
};

function setNumberStyle(dom, style, value, measure) {
	if (!measure) {
		measure = 'px';
	}
	var styles = {};
	styles[style] = value + measure;
	dom.setStyle(styles);
	dom.emit(style, value, measure);
}

}());