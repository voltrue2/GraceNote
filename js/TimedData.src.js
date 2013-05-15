/* dependencies: EventEmitter */
(function (window) {

/*
* structure of data
* data = { value: Number, min: Number, max: Number, stepValue: Number, lastUpdate: timestamp, interval: milliseconds };
*/
var data = {};
var self = null;

function TimedNumber(srcData) {
	data = srcData;
	self = this;
}

window.TimedNumber = TimedNumber;
TimedNumber.prototype = new EventEmitter();

TimedNumber.prototype.getMaxValue = function () {
	return data.max;
};

TimedNumber.prototype.getMinValue = function () {
	return data.min;
};

TimedNumber.prototype.getValue = function () {
	return calcCurrentState().currentValue;
};

TimedNumber.prototype.getLastUpdate = function () {
	return calcCurrentState().lastUpdate;
};

TimedNumber.prototype.getInterval = function () {
	return data.interval;
};

TimedNumber.prototype.getTime = function () {
	var now = Date.now();
	var lastUpdate = this.getLastUpdate();
	var diff = now - lastUpdate;
	var d = new Date(diff);
	var total = new Date(this.getInterval());
	var time = total.getTime() - d.getTime();
	var anchorSec = Math.floor(time / 1000);
	if (time >= 0 && this.getValue() < this.getMaxValue()) { 
		var min = 0;
		if (anchorSec > 60) {
			min = Math.floor(anchorSec / 60);
		}
		var sec = anchorSec - (min * 60);
		if (sec === 60) {
			min = 1;
			sec = 0;	
		}
		return { minutes: min, seconds: sec };
	} else {
		return false; // value at max no clock counter needed
	}
};

TimedNumber.prototype.update = function (srcData) {
	for (var key in data) {
		if (srcData[key] !== undefined) {
			data[key] = srcData[key];
		}
	}
	calcCurrentState();
	// emit event
	this.emit('update', data);
}; 

function calcCurrentState() {
	// calculate current value
	var now = Date.now();
	var timeElapsed = now - data.lastUpdate;
	var numOfSteps = Math.floor(timeElapsed / data.interval);
	var valueOffset = numOfSteps * data.stepValue;
	var newValue = valueOffset + data.value;
	if (newValue > data.max) {
		newValue = data.max;
	} else if (newValue < data.min) {
		newValue = data.min;
	}
	// calculcate lastUpdate
	var lastUpdate = data.lastUpdate + (numOfSteps * data.interval);
	return { currentValue: newValue, lastUpdate: lastUpdate };
}

}(window));

(function (window) {

var conf = {
	min: 0,
	max: 0,
	step: 1,
	interval: 0
};

var compactConf = {
	m: 'min',
	M: 'max',
	s: 'step',
	i: 'interval'
};

var counterItem = {
	value: 0,
	lastUpdate: 0
};

var compactCounterItem = {
	v: 'value',
	l: 'lastUpdate'
};

/***
* @src (Object) { cfg: { m: Number, M: Number, s: Number, i: Number}, cnt: { itemId: { v: Number, l: Number}... } }
* itemId: unique ID for each counter item
*/
function TimedCounter(src) {
	this.conf = decode(compactConf, src.cfg);
	this.counterItems = decodeEach(compactCounterItem, src.cnt);
}

TimedCounter.prototype = new EventEmitter();
window.TimedCounter = TimedCounter;

TimedCounter.prototype.getCurrentCounter = function (itemId) {
	if (this.counterItems[itemId]) {
		var now = Date.now();
		var item = this.counterItems[itemId];
		var diff = now - item.lastUpdate;
		if (diff >= this.conf.interval) {
			// reset > purge the item
			delete this.counterItems[itemId];
			return this.conf.min;
		} else {
			return item.value;
		}
	} else {
		return null;
	}
};

function decode(base, src) {
	var decoded = {};
	for (var key in src) {
		var prop = base[key];
		var value = src[key];
		if (typeof value == 'object') {
			value = decode(base, value);
		}
		decoded[prop] = value;
	}
	return decoded;
}

function decodeEach(base, srcList) {
	var decodedList = {};
	for (var index in srcList) {
		decodedList[index] = decode(base, srcList[index]);
	}
	return decodedList;
}

}(window));
