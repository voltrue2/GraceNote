/** Global Extensions **/

function hideAddressBar() {
	setTimeout(function () {
		window.scrollTo(0, 0);
	}, 0);
}

var __onPageReadyCallbacks__ = [];
var __pageReady__ = false;
window.addEventListener('DOMContentLoaded', __onPageReady__);

function onPageReady(callback) {
	if (typeof callback == 'function'){
		if (!__pageReady__){
			__onPageReadyCallbacks__.push(callback);
		} else {
			callback();
		}
	} else {
		console.error('onPageReady > the argument needs to be a function "' + callback + '" given');
	}
}
function __onPageReady__() {
	__pageReady__ = true;
	var len = __onPageReadyCallbacks__.length;
	for (var i = 0; i < len; i++){
		__onPageReadyCallbacks__[i]();
	}
	__onPageReadyCallbacks__ = [];
}

function pad(n, digit, padWith) {
	if (!digit){
		digit = 2;
	}
	var z = padWith || "0";
	var anchor = 1;
	for (var i = 0; i < digit - 1; i++){
		anchor = anchor * 10;
	}
	if (n < anchor){
		var temp = n+"";
		n = "";
		for (var i = 0; i < digit - temp.length; i++){
			n += z;
		}
		n += temp;
		temp = null;
	}
	return n;
}

function isArray(value) {
	return value instanceof Array;
}

function comma(n, d) {
	n = n+"";
	if (!d){
		d = 3;
	}
	if (n.length > d){
		var t = Math.round(n.length/d);
		var s;
		for (var i = 0; i < t; i++){
			var v = n.substring(n.length-d, n.length);
			n = n.substring(0, n.length-d);
			if (i == 0){
				s = v;
			} else {
				s = v+","+s;
			}
		}
		if (n){
			s = n+","+s;
		}
		return s;
	} else {
		return n;
	}
}

function rand(min, max){
	var r = Math.floor(Math.random() * (max + 1));
	if (r < min){
		r = min;
	}
	return r;
}

function epoch(){
	var d = new Date();
	return d.getTime();
}

function delegate() {
	if (typeof arguments[0] == 'object' && typeof arguments[1] == 'function'){
		var owner_obj = arguments[0];
		var method = arguments[1];
		var args = [];
		var len = arguments.length;
		for (var i = 2; i < len; i++){
			args.push(arguments[i]);
		}
		var func = function () {
			method.apply(owner_obj, args);
		};
		return func;
	} else {
		console.error('delegate > invalid object and/or method given');
		console.error(arguments);
		return false;
	}
}

function scrollpos() {
	var x = 0;
	var y = 0;
	if(typeof(window.pageYOffset) == 'number') {
		y = window.pageYOffset;
		x = window.pageXOffset;
	} else if(document.body && (document.body.scrollLeft || document.body.scrollTop)) {
		y = document.body.scrollTop;
		x = document.body.scrollLeft;
	} else if(document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
		y = document.documentElement.scrollTop;
		x = document.documentElement.scrollLeft;
	}
	return {x:x, y:y};
}

var device = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string.toLowerCase();
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1) {
					return data[i].identity;
				}
			}
			else if (dataProp) {
				return data[i].identity;
			}
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{
			string: navigator.userAgent,
			subString: "chrome",
			identity: "Chrome"
		},
		{ 	string: navigator.userAgent,
			subString: "omniweb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "apple",
			identity: "Safari",
			versionSearch: "Version"
		},
		{
			prop: window.opera,
			identity: "opera"
		},
		{
			string: navigator.vendor,
			subString: "icab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "kde",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "camino",
			identity: "Camino"
		},
		{	
			string: navigator.userAgent,
			subString: "netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "msie",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "geko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{ 	
			string: navigator.userAgent,
			subString: "mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			   string: navigator.userAgent,
			   subString: "iphone",
			   identity: "iOS"
	    	},
		{
			   string: navigator.userAgent,
			   subString: "ipad",
			   identity: "iOS"
	    	},
	    	{
			   string: navigator.userAgent,
			   subString: "android",
			   identity: "Andorid"
	    	},
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};
device.init();

/** DOM Extensions **/
document.find = function (query) {
	return __find('single', document, query);
};

document.findAll = function (query) {
	return __find('all', document, query);
};

function __find(type, parent, query) {
	if (type == 'all'){
		var me = parent.querySelectorAll(query);
	}
	else { 
		var me = parent.querySelector(query);
	}
	if (!me){
		console.error('window.__find: query not found > "', query, parent);
		return null;
	}
	if (type == 'single'){
		me.find = function (query) {
			return __find('single', me, query);
		};
		me.findAll = function (query){
			return __find('all', me, query);
		};
		extend(me);
	} else {
		for (var i = 0; i < me.length; i++){
			me[i].find = function (query) {
				return __find('single', me, query);
			};
			me[i].findAll = function (query) {
				return __find('all', me, query);
			};
			extend(me[i]);
		}
	}
	return me;
}

function getElement(element, tag_name, cls_name, att){
	var elements = (tag_name == '*' && element.all)? element.all : element.getElementsByTagName(tag_name);
	var res = new Array();
	cls_name = cls_name.replace(/\-/g, '\\-');
	var regex = new RegExp('(^|\\s)' + cls_name + '(\\s|$)');
	for(var i = 0; i < elements.length; i++){
		var el = elements[i];
		if(regex.test(el[att])){
			res[res.length] = el;
		}
	}
	if (res.length > 0){
		return res;
	} else {
		return false;
	}
}

function extend (e) {

	setup_dom(e);
	
	function setup_dom (element) {
		element.eventEmitter = new EventEmitter();
		element.css = function (styles) {
			setCss(element, styles);
		};
		element.create = function (tag) {
			return create(element, tag);
		};
		element.remove = function () {
			remove(element);
		};
		element.removeChildren = function () {
			removeChildren(element);
		};
		element.addClass = function (cls) {
			addClassName(element, cls);
		};
		element.replaceClass = function (cls, rep) {
			replaceClassName(element, cls, rep);
		};
		element.removeClass = function (cls) {
			removeClassName(element, cls);
		};
		element.stats = function () {
			return stats(element);
		};
		element.find = function (query) {
			return __find('single', element, query);
		};
		element.findAll = function (query) {
			return __find('all', element, query);
		};
		var event = new EventHandler(element);
		element.event = event;
		var button = new Button(element);
		element.button = button;
		return element;
	}
	
	function setCss(elm, css) {
		for (var key in css) {
			elm.style[key] = css[key];
		}
	}
	
	function Button(obj) {
		var touch = false;
		var callback = null;
		var initCallback = null;
		var cancelCallback = null;
		
		this.execute = function (cb) {
			callback = cb;
			obj.event.add('touchstart', '_btnD', down);
			obj.event.add('touchend', '_btnU', up);
			obj.event.add('touchmove', '_btnM', move);
			obj.event.add('mousedown', '_btnMD', down);
			obj.event.add('mouseup', '_btnMU', up);
			obj.event.add('mousemove', '_btnMM', move);
		};
		
		this.cancel = function (cb) {
			cancelCallback = cb;
		};
		
		this.init = function (cb) {
			initCallback = cb;
		};
		
		this.disable = function () {
			obj.event.deactivate('touchstart', '_btnD');
			obj.event.deactivate('touchend', '_btnU');
			obj.event.deactivate('touchmove', '_btnM');
			obj.event.deactivate('mousedown', '_btnMD');
			obj.event.deactivate('mouseup', '_btnMU');
			obj.event.deactivate('mousemove', '_btnMM');
		};
		
		this.enable = function () {
			obj.event.activate('touchstart', '_btnD');
			obj.event.activate('touchend', '_btnU');
			obj.event.activate('touchmove', '_btnM');
			obj.event.activate('mousedown', '_btnMD');
			obj.event.activate('mouseup', '_btnMU');
			obj.event.activate('mousemove', '_btnMM');
		};
		
		this.remove = function () {
			callback = null;
			initCallback = null;
			cancelCallback = null;
			obj.event.remove('touchstart', '_btnD');
			obj.event.remove('touchend', '_btnU');
			obj.event.remove('touchmove', '_btnM');
			obj.event.remove('mousedown', '_btnMD');
			obj.event.remove('mouseup', '_btnMU');
			obj.event.remove('mousemove', '_btnMM');
		};
		
		function down() {
			touch = true;
			if (initCallback) {
				initCallback();
			}
		}
		
		function up() {
			if (touch) {
				touch = false;
				callback();
			}
			else {
				if (cancelCallback) {
					cancelCallback();
				}
			}
		}
		
		function move() {
			touch = false;
			if (cancelCallback) {
				cancelCallback();
			}
		}	
		
	}
	
	function stats(el){
		for(var res = {x:el.offsetLeft, y:el.offsetTop, height:el.offsetHeight, width:el.offsetWidth}; el = el.offsetParent; res.x += el.offsetLeft, res.y += el.offsetTop);
		return res;
	}
	
	function addClassName (element, cls) {
		if (element.className){
			if (element.className.indexOf(cls) == -1){
				element.className += ' ' + cls;
			}
		} else {
			element.className = cls;
		}
	}
	
	function replaceClassName (element, cls, rep) {
		element.className = element.className.replace(cls, rep);
	}
	
	function removeClassName (element, cls) {
		replaceClassName(element, ' ' + cls + ' ', ' ');
		replaceClassName(element, ' ' + cls, '');
		replaceClassName(element, cls, '');
	}
	
	function create (parent, element_type) {
		var child = document.createElement(element_type);
		parent.appendChild(child);
		return setup_dom(child);
	}
	
	function remove (me) {
		removeChildren(me);
		if (me.parentNode) {
			me.parentNode.removeChild(me);
			me = null;
		}
	}
	
	function removeChildren (me) {
		if (me && me.childNodes && me.childNodes.length) {
			var len = me.childNodes.length;
			for (var i = 0; i < len; i++) {
				var child = me.childNodes[i];
				if (child) {
					if (child.event && child.event.removeAll) {
						child.event.removeAll();
					}
					me.removeChild(child);
					removeChildren(child);
					child = null;
				}
			}
		}
	}
	
	function event(obj, e, func){
		if (obj.addEventListener){ 
			obj.addEventListener(e, func, false); 
			return true; 
		} else if (obj.attachEvent){ 
			var r = obj.attachEvent('on'+e, func); 
			return r; 
		} else { 
			return false; 
		}
	}
	
	function remove_event(obj, e, func){
		if (obj.removeEventListener){ 
			obj.removeEventListener(e, func, false); 
			return true; 
		} 
		else if (obj.detachEvent){ 
			var r = obj.detachEvent('on'+e, func); 
			return r; 
		} else { 
			return false; 
		}
	}
	
	/****
	* usage: 
	* var eh = new EventHandler(domOjb);
	*  > to assign a callback to an event: eh.add('click', 'clickhandler', clickhandler);
	*  > to deactivate(the callback will be ignored): eh.click.deactivate('clickhandler'); or eh.deactivate('click', 'clickhadler');
	*  > to activate(the callback will be called): eh.click.activate('clickhandler'); or eh.activate('click', 'clickhadler');
	*  > to remove an event: eh.click.remove(); or eh.remove('click');
	*  > to check callback's active status: eh.click.active('clickhandler'); or eh.active('click', 'clickhandler');
	*/
	function EventHandler (obj) {
	
		var callbacks = {};
		var counter = 0;
		var self = this;
	
		this.add = function (event_type, func_name, func) {
			if (typeof func !== 'function'){
				console.error('EventHandler.add: Invalid callback type');
				return;
			}
			if (func_name === false || func_name === null){
				func_name = counter;
				counter++;
			}
			if (!callbacks[event_type]){
				callbacks[event_type] = [];
				self[event_type] = {};
				self[event_type].deactivate = function (cb_name) {
					self.deactivate(event_type, cb_name);
				};
				self[event_type].activate = function (cb_name) {
					self.activate(event_type, cb_name);
				};
				self[event_type].active = function (cb_name) {
					return self.active(event_type, cb_name);
				};
				self[event_type].remove = function () {
					self.remove(event_type);
				};
				event(obj, event_type, delegate(self, call, event_type));
			}
			callbacks[event_type][callbacks[event_type].length] = {name: func_name, func: func, active: true};
		};
		
		// deactivate individual callback
		this.deactivate = function (event_name, func_name) {
			if (callbacks[event_name]){
				var len = callbacks[event_name].length;
				for (var i = 0; i < len; i++){
					var cb = callbacks[event_name][i];
					if (func_name == cb.name){
						cb.active = false;
						break;
					}
				}
			}
		};
		
		// activate individual callback
		this.activate = function (event_name, func_name) {
			if (callbacks[event_name]){
				var len = callbacks[event_name].length;
				for (var i = 0; i < len; i++){
					var cb = callbacks[event_name][i];
					if (func_name == cb.name){
						cb.active = true;
						break;
					}
				}
			}
		};
		
		this.active = function (event_name, func_name) {
			if (callbacks[event_name]){
				var len = callbacks[event_name].length;
				for (var i = 0; i < len; i++){
					var cb = callbacks[event_name][i];
					if (func_name == cb.name){
						return cb.active;
					}
				}
				return false;
			} else {
				return false;
			}
		};
		
		// remove all callbacks for an event
		this.remove = function (event_name, callback_name) {
			if (callbacks[event_name] && !callback_name){
				// remove the entire event
				remove_event(obj, event_name, delegate(self, call, event_name));
				callbacks[event_name] = null;
			}
			else if (callbacks[event_name] && callback_name) {
				// remove a callback from an event
				var len = callbacks[event_name].length;
				for (var i = 0; i < len; i++){
					var cb = callbacks[event_name][i];
					if (func_name == cb.name){
						callbacks[event_name].splice(i, 1);
						break;
					}
				}
			}
		};
		
		this.removeAll = function () {
			for (var event_name in callbacks) {
				this.remove(event_name);
			}
		};

		function call (event_type) {
			if (callbacks[event_type]){
				var funcs = callbacks[event_type];
				var len = funcs.length;
				for (var i = 0; i < len; i++){
					if (funcs[i].active){
						funcs[i].func();
					}
				}
			}
		};
	}
}

/** String Extensions **/
String.prototype.explode  = function (delimiter) {
	var str = this;
	var index = str.indexOf(delimiter);
	var res = new Array();
	while (index > -1){
		var val = str.substring(0, index);
		if (val != ""){
			res[res.length] = val;
		}
		str = str.substring(index + delimiter.length, str.length);
		index = str.indexOf(delimiter);
	}
	if (str != ""){
		res[res.length] = str;
	}
	return res;
};

String.prototype.base64 = new Base64();

function Base64 (){
	var key = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	var self = this;
	self.encode = encode;
	self.decode = decode;
	self.utf8_encode = utf8_encode;
	self.utf8_decode = utf8_decode;
 
	function encode (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;
 
		input = utf8_encode(input);
 
		while (i < input.length) {
 
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);
 
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
 
			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}
 
			output = output + key.charAt(enc1) + key.charAt(enc2) + key.charAt(enc3) + key.charAt(enc4);
 
		};
 
		return output;
	};
 
	function decode (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;
		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
		while (i < input.length) {
			enc1 = key.indexOf(input.charAt(i++));
			enc2 = key.indexOf(input.charAt(i++));
			enc3 = key.indexOf(input.charAt(i++));
			enc4 = key.indexOf(input.charAt(i++));
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
			output = output + String.fromCharCode(chr1);
			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			};
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			};
		};
		return utf8_decode(output);
	};
 
	function utf8_encode (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
 
		for (var n = 0; n < string.length; n++) {
 
			var c = string.charCodeAt(n);
 
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			};
 
		};
 
		return utftext;
	};

	function utf8_decode (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
 
		while ( i < utftext.length ) {
 
			c = utftext.charCodeAt(i);
 
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			};
 
		};
 
		return string;
	};
}

/** Array Extensions **/
Array.prototype.implode = function (glue){
	var str = '';
	var init = true;
	var len = this.length;
	if (!glue){
		glue = '';
	}
	for (var i = 0; i < len; i++){
		if (init){
			str += String(this[i]);
			init = false;
		}
		else {
			str += glue + String(this[i]);
		}
	}
	return str;	
};

Array.empty = function () {
	if (this.length > 0){
		return false;
	}
	else {
		return true;
	}
};

// executes an array of functions one after another
function sequence (funcs) {
    var len = funcs.length;
    var counter = 1;
    var callback = function () {
        var cb = funcs[counter];
        if (typeof cb == 'function'){
            counter++;
            cb(callback);
        }
    };
    // execute the first function
    funcs[0](callback);
}

// pads number >> this function will turn the number into string
function padNumber (n, digit, padWith, direction) {
    if (!digit){
        // integer
        digit = 2;
    }
    if (!padWith){
        // string
        padWith = '0';
    }
    if (!direction){
        // string flag: left of right
        direction = 'left';
    }
    n = n.toString();
    if (n.length < digit){
        var gap = digit - n.length;
        var p = '';
        for (var i = 0; i < gap; i++){
            p += padWith;
        }
        if (direction == 'left'){
            n = p + n;
        }
        else if (direction == 'right'){
            n = n + p;
        }
    }
    return n;
}

// Extension/Overwriting localStorage.get/set
Storage.prototype.__getItem = Storage.prototype.getItem;
Storage.prototype.__setItem = Storage.prototype.setItem;
Storage.prototype.__removeItem = Storage.prototype.removeItem;
Storage.prototype.keyPrefix = null;
Storage.prototype.createKey = function (keySrc) {
    if (this.keyPrefix) {
        return this.keyPrefix + keySrc;
    }
    else {
        return keySrc;
    }
};

Storage.prototype.getItem = function (keySrc) {
    return this.__getItem( this.createKey(keySrc) );
};

Storage.prototype.setItem = function (keySrc, value) {
    this.__setItem( this.createKey(keySrc), value);
};

Storage.prototype.removeItem = function (keySrc) {
    this.__removeItem( this.createKey(keySrc) );
};




// ---------------------------------------------------
// button response handling and management
/*  requires EventHandler and EventController
	e.g.
	var button = new ButtonHandler();
	button.onStart = sharedGenericFunctionForStart
	button.onEnd = sharedGenericFunctionForEnd
	button.onCancel = sharedGenericFunctionForCancel
	button.onDisable = sharedGenericFunctionForDisable
	button.onEnable = sharedGenericFunctionForEnable
	button.onCreate = sharedGenericFunctionForCreate
	button.onDestroy = sharedGenericFunctionForDestroy
	button.create(targetElement, callbackFunction, optionalCallbackFunctions); // register tap response function: onStart > onEnd > callbackFuntion
	button.overrideResponse(targetElement, overrideFunction); // temporarily assign a response function and suppress the original response function
	button.resetOverrideResponse(targetElement); // reset temporary response function set by overrideResponse
	button.disableDefault(targetLElement); // suppress global default response callbacks such as button.onStart, .onEnd, onCancel, onDisable, onEnable, onCreate, onDestroy
	button.enableDefault(targetElement); // re-enable global default response callbacks suppressed by disableDefault
	button.disable(targetElement); // onDisable
	button.enable(targetElement); // onEnable
	button.destroy(targetElement); // unregister tap response and all listeners
*/

window.button = new ButtonHandler();

function ButtonHandler() {
	var self = this;
	var elementCounter = 0;
	var events = [];
	this.onStart = function () {}; // this will be assigned to every button response
	this.onEnd = function () {}; // this will be assigned to every button response
	this.onCancel = function () {}; // this will be assigned to every button response
	this.onDisable = function () {};
	this.onEnable = function () {};
	this.onCreate = function () {};
	this.onDestory = function () {};

	// public
	// options.onStart: Function > called on mousedown/touchstart
	// options.onCancel: Function > called on button response cancel/touchcancel
	this.create = function (target, callback, options) {
		if (getCounter(target) >= 0) {
			options = applyDefaults({ onStart: null, onEnd: null, onCancel: null, onDisable: null, onEnable: null }, options);
			// set up and prepare the target element
			setCounter(target, elementCounter);
			// assign events
			var event = new EventHandler(target);
			var ecStartClick = event.add('mousedown', bind(null, onStart, options.onStart));
			var ecEndClick = event.add('mouseup', bind(null, onEnd, options.onEnd, callback));
			var ecMoveClick = event.add('mousemove', bind(null, onMove, options.onCancel));
			var ecStart = event.add('touchstart', bind(null, onStart, options.onStart));
			var ecEnd = event.add('touchend', bind(null, onEnd, options.onEnd, callback));
			var ecMove = event.add('touchmove', bind(null, onMove, options.onCancel));
			var ecCancel = event.add('touchcancel', bind(null, onMove, options.onCancel));
			var eventControllers = [ ecStartClick, ecEndClick, ecMoveClick, ecStart, ecEnd, ecMove, ecCancel ];
			events[elementCounter] = { event: event, eventControllers: eventControllers, override: null, overrideOptions: null };
			// callback on create
			if (!prevent(target)) {
				self.onCreate(target);
			}
			// done
			elementCounter += 1;
		}
	};

	// suppress global default response functions such as onStart, onEnd, onCancel, onDisable, onEnable, onCreate, onDestroy
	this.disableDefault = function (target) {
		target.setAttribute('ui-button-no-default', 'true');
	};

	// re-enable global default response functions such as onStart, onEnd, onCancel, onDisable, onEnable, onCreate, onDestroy
	this.enableDefault = function (target) {
		target.setAttribute('ui-button-no-default', '');
	};

	// public
	// tmpOptions.onStart: Function > called on mousedown/touchstart
	// tmpOptions.onCancel: Function > called on button response cancel/touchcancel
	// tmpOptions.onDisable: Function > called on button disable
	// tmpOptions.onEnable: Function > called on button enable
	this.overrideResponse = function (target, tmpCallback, tmpOptions) {
		var counter = getCounter(target);
		if (events[counter]) {
			var tmpOptions = applyDefaults({ onStart: null, onCancel: null, onDisable: null, onEnable: null }, tmpOptions);
			events[counter].override = tmpCallback;
			events[counter].overrideOptions = tmpOptions;
		}
	};

	// public
	this.resetOverrideResponse = function (target) {
		var counter = getCounter(target);
		if (events[counter]) {
			events[counter].override = null;
			events[counter].overrideOptions = null;
		}
	};

	// public
	this.destroy = function (target) {
		var counter = getCounter(target);
		if (events[counter]) {
			var event = events[counter].event;
			event.remove('mousedown');
			event.remove('mouseup');
			event.remove('mousemove');
			event.remove('touchstart');
			event.remove('touchend');
			event.remove('touchmove');
			event.remove('touchcancel');
			setCounter(target, '');
			target.setAttribute('ui-button-state', '');
			events[counter] = null;
			// callback
			if (!prevent(target)) {
				self.onDestory(target);
			}
		}
	};

	// public
	this.disable = function (target) {
		var counter = getCounter(target);
		if (events[counter]) {
			var eventCnt = events[counter].eventControllers;
			var len = eventCnt.length;
			for (var i = 0; i < len; i++) {
				eventCnt[i].disable();
			}
			target.setAttribute('ui-button-status', 'disabled');
			if (!prevent(target)) {
				self.onDisable(target);
			}
			// check for override
			var ovr = checkForOverride(target, 'onDisable');
			if (ovr) {
				ovr(target);
			}
		}
	};

	// public
	this.enable = function (target) {
		var counter = getCounter(target);
		if (events[counter] && target.getAttribute('ui-button-status') === 'disabled') {
			var eventCnt = events[counter].eventControllers;
			var len = eventCnt.length;
			for (var i = 0; i < len; i++) {
				eventCnt[i].enable();
			}
			target.setAttribute('ui-button-status', '')
			if (!prevent(target)) {
				self.onEnable(target);
			}
			// check for override
			var ovr = checkForOverride(target, 'onEnable');
			if (ovr) {
				ovr(target);
			}
		}
	};

	// private
	function onStart(event, cb) {
		event.srcElement.setAttribute('ui-button-state', 'start');
		if (!prevent(event.srcElement)) {
			self.onStart(event);
		}
		// check for override
		var ovr = checkForOverride(event.srcElement, 'onStart');
		if (ovr) {
			ovr(event);
		}
		else {
			if (typeof cb === 'function') {
				cb(event);
			}
		}
	}

	// private
	function onEnd(event, evenCallback, callback) {
		var state = event.srcElement.getAttribute('ui-button-state');
		if (state === 'start') {
			event.srcElement.setAttribute('ui-button-state', '');
			if (!prevent(event.srcElement)) {
				self.onEnd(event);
			}
			// check for override
			var ovr = checkForOverride(event.srcElement, 'onEnd');
			if (ovr) {
				ovr(event);
			}
			else {
				if (typeof evenCallback === 'function') {
					evenCallback(event);
				}
			}
			// check for override
			var counter = getCounter(event.srcElement);
			if (events[counter] && typeof events[counter].override === 'function') {
				events[counter].override(event);
			}
			else {
				callback(event);
			}
		}
	}

	// private > cancel button response
	// we are canceling button response on move > maybe too sensitive
	function onMove(event, cb) {
		if (event.srcElement.getAttribute('ui-button-state') === 'start') {
			event.srcElement.setAttribute('ui-button-state', '');
			if (!prevent(event.srcElement)) {
				self.onCancel(event);
			}
			// check for override
			var ovr = checkForOverride(event.srcElement, 'onCancel');
			if (ovr) {
				ovr(event);
			}
			else {
				if (typeof cb === 'function') {
					cb(event);
				}
			}
		}
	}

	// private
	function prevent(element) {
		if (element.getAttribute('ui-button-no-default') === 'true') {
			return true;
		}
		else {
			return false;
		}
	}

	// private
	function applyDefaults(defaults, incomingValues) {
		for (var key in defaults) {
			if (incomingValues && incomingValues[key]) {
				defaults[key] = incomingValues[key];
			}
		}
		return defaults;
	}

	// private
	function getCounter(element) {
		return element.getAttribute('ui-button');
	}

	// private
	function setCounter(element, counter) {
		element.setAttribute('ui-button', counter);
	}

	// private
	function checkForOverride(element, overrideName) {
		var counter = getCounter(element);
		if (events[counter] && events[counter].overrideOptions && typeof events[counter].overrideOptions[overrideName] === 'function') {
			return events[counter].overrideOptions[overrideName];
		}
		else {
			return null;
		}
	}
}

// event listener handler
/*
Usage eg var eventhandler = new EventHandler(myButton);
var eventcontroller = eventhandler.add('touchstart', myStartFunction);
// to disable > eventcontroller.disable();
// to enable > eventcontroller.enable();
eventhandler.remove('touchstart'); // remove all event listeners
eventhandler.remove('touchstart', eventcontroller); // remove an event listener for a specific eventcontroller
*/
function EventHandler(targetElementIn) {
	this.targetElement = targetElementIn; // private
	this.callbacks = {}; // private > but EventController uses this
}

// public > assign a callback to an event
EventHandler.prototype.add = function (eventName, callback) {
	if (!this.callbacks[eventName]) {
		this.callbacks[eventName] = [];
	}
	var index = this.callbacks[eventName].length;
	this.callbacks[eventName][index] = { cb: callback, active: true };
	this.targetElement.addEventListener(eventName, bind(this, this.eventCallback, eventName, index), false);
	// return object for more control
	return new EventController(this, eventName, index);
};

// public > remove an event and callbacks assigned
// make sure to delete EventController object(s) for this event as well
// second argument is optional for removing a specific event callback
EventHandler.prototype.remove = function (eventName, eventController) {
	if (this.callbacks[eventName]) {
		if (eventController && this.callbacks[eventName][eventController.eventIndex]) {
			this.targetElement.removeEventListener(eventName, bind(this, this.eventCallback, eventName, eventController.eventIndex));
			this.callbacks[eventName][eventController.eventIndex] = null;
		}
		else {
			var len = this.callbacks[eventName].length;
			for (var i = 0; i < len; i++) {
				this.targetElement.removeEventListener(eventName, bind(this, this.eventCallback, eventName, i));
			}
			this.callbacks[eventName] = null;
		}
	}
};

// private
EventHandler.prototype.eventCallback = function (event, eventName, index) {
	if (!this.callbacks[eventName]) {
		return;
	}
	var cbObj = this.callbacks[eventName][index];
	if (cbObj && cbObj.active) {
		cbObj.cb(event);
	}
};

// child object of EventHandler
function EventController(eh, name, index) {
	this.eventHandler = eh;
	this.eventName = name;
	this.eventIndex = index;
}

// disable a specific callback to an event
EventController.prototype.disable = function () {
	if (this.eventHandler.callbacks[this.eventName][this.eventIndex]) {
		this.eventHandler.callbacks[this.eventName][this.eventIndex].active = false;
	}
};

// enable a specific callback to an event
EventController.prototype.enable = function () {
	if (this.eventHandler.callbacks[this.eventName][this.eventIndex]) {
		this.eventHandler.callbacks[this.eventName][this.eventIndex].active = true;
	}
};

// delegate function
function bind(obj, method) {
	var args = [];
	var len = arguments.length;
	if (len > 2) {
		for (var i = 2; i < len; i++) {
			args[i - 1] = arguments[i];
		}
	}
	var cb = function (event) {
		args[0] = event;
		method.apply(obj, args);
	};
	return cb;
}

// ---------------------------------------------------

window.logger = new Logger();

// console debugger class
function Logger() {
	this.suppressDebug = false; // private
	this.suppressWarn = false; // private
	this.suppressError = false; // private
	this.filterRegex = null; // private
	this.filterOut = false; // private > if true filter will suppress output matched, if false filter will output ONLY matched
	var self = this;
	this.suppress =  {
		debug: function (flag) {
			if (flag === false) {
				self.suppressDebug = false;
			}
			else {
				self.suppressDebug = true;
			}
			console.warn('Logger.suppress.debug: ', self.suppressDebug);
		},
		warn: function (flag) {
			if (flag === false) {
				self.suppressWarn = false;
			}
			else {
				self.suppressWarn = true;
			}
			console.warn('Logger.suppress.warn: ', self.suppressWarn);
		},
		error: function (flag) {
			if (flag === false) {
				self.suppressError = false;
			}
			else {
				self.suppressError = true;
			}
			console.warn('Logger.suppress.error: ', self.suppressError);
		}
	};
}

// filters debug/warn/error logs and suppresses logs that match
Logger.prototype.filter = function (regex) {
	var prev = this.filterRegex;
	this.filterRegex = regex;
	console.warn('Logger.filter: ' + prev + ' -> ' + regex);
	console.warn((this.filterOut) ? 'Logger.filter: black list mode' : 'Logger.filter: white list mode' );
};

// public > output not matched logs
Logger.prototype.blackList = function () {
	this.filterOut = true;
	console.warn('Logger.blackList: black list mode');
};

// public > output matched logs
Logger.prototype.whiteList = function () {
	this.filterOut = false;
	console.warn('Logger.whiteList: white list mode');
};

// public
Logger.prototype.clearFilter = function () {
	this.filterRegex = null;
	console.warn('Logger.clearFilter');
};

// public console.log
Logger.prototype.debug = function () {
	if (!this.suppressDebug) {
		this.log('log', arguments);
	}
};

// public console.warn
Logger.prototype.warn = function () {
	if (!this.suppressWarn) {
		this.log('warn', arguments);
	}
};

// public function console.error
Logger.prototype.error = function () {
	if (!this.suppressError) {
		this.log('error', arguments);
		console.trace();
	}
};

// public function console.log
Logger.prototype.snapshot = function (list, ignoreFunction) {
        if (!this.suppressDebug) {
                if (ignoreFunction === undefined) {
                        ignoreFunction = true; // default
                }
                this._parseSnapshot('', '', list, ignoreFunction);
        }
};

// private
Logger.prototype._parseSnapshot = function (indent, parentKey, list, ignoreFunction) {
        if (typeof list !== 'object') {
                return this.log('log', [ list + '[' + (typeof list).toUpperCase() + ']'  ]);
        }
        for (var i in list) {
                var val = list[i];
                var more = false;
                if (typeof val === 'function' && ignoreFunction) {
                        continue;
                }
                if (typeof val === 'object') {
                        var type = '[OBJECT]';
                        if (isArray(val)) {
                                type = '[ARRAY]';
                        }
                        this.log('log', [ indent + parentKey + i + ' = ' + type ]);
                        this._parseSnapshot(indent + ' ', parentKey + i + '.', val, ignoreFunction);
                }
                else {
                        this.log('log', [ indent + parentKey + i + ' = ' + val + '[' + (typeof val).toUpperCase() + ']' ]);
                }
        }
};

// private
Logger.prototype.log = function (type, args) {
	var len = args.length;
	// check for filter
	if (this.filterRegex) {
		var argStr = JSON.stringify(args);
		if (this.filterOut) {
			// black list mode
			if (argStr.match(this.filterRegex)) {
				// filter applied and there is a match > we do not output log
				return false;
			}
		}
		else {
			// white list mode
			if (!argStr.match(this.filterRegex)) {
				// filter applied and there is no match > we do not output log
				return false;
			}
		}
	}
	console[type].apply(console, args);
};




