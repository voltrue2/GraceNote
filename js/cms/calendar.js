(function () {
	
	var width = 300;
	var height = 430;
	var cell = { width: 20, height: 20 };

	function Calendar() {
		EventEmitter.call(this);
		var now = new Date();
		var year = now.getFullYear();
		var month = now.getMonth() + 1;
		this.current = { year: year, month: month, date: now.getDate(), hours: 0, minutes: 0 };
		this.selectedDate = null;
		this.display = null;
		this.timestamp = false;
		var that = this;
		var closeLightbox = null;
		var lightbox = window.lightbox.show(width, height, function (bar, box, close) {
			create(that, box);
			closeLightbox = close;
			var save = box.createChild('div', { margin: '10px auto' });
			save.setClassName('text-button');
			save.text(window.text.save || 'Save');
			save.on('tapend', function () {
				var data = {};
				if (!that.selectedDate) {
					data = {
						year: that.current.year,
						month: pad(that.current.month),
						date: pad(that.current.date),
						hours: pad(that.current.hours),
						minutes: pad(that.current.minutes),
					};
				} else {
					data = {
						year: that.selectedDate.year,
						month: pad(that.selectedDate.month),
						date: pad(that.selectedDate.date),
						hours: pad(that.current.hours),
						minutes: pad(that.current.minutes)
					};
				}
				// add timestamp
				data.timestamp = new Date(data.year + '/' + data.month + '/' + data.date + ' ' + data.hours + ':' + data.minutes + ':' + '00').getTime();
				closeLightbox();
				that.emit('close', data);
			});
		});
	}

	window.Calendar = Calendar;
	window.inherits(Calendar, EventEmitter);

	function displaySelected(that) {
		var val = that.display.date;
		var value = (pad(val.year) || '0000') + '-' + (pad(val.month) || '00') + '-' + (pad(val.date) || '00') + ' ' + (pad(val.hours) || '00') + ':' + (pad(val.minutes) || '00');
		if (that.timestamp) {
			value = new Date((val.year || '0000') + '/' + (val.month || '00') + '/' + (val.date || '00') + ' ' + (val.hours || '00') + ':' + (val.minutes || '00') + ':' + '00').getTime();
		}
		that.display.textArea.text(value);
	}

	function create (that, container) {
		var box = container.createChild('div');
		// selected date and time display
		that.display = box.createChild('div', { 
			border: '1px solid #ccc', 
			padding: '2px', 
			margin: '2px', 
			fontSize: '12px', 
			color: '#333',
			textAlign: 'center',
			height: '18px',
			lineHeight: '18px',
			clear: 'both'
		});
		that.display.textArea = that.display.createChild('div', { 
			cssFloat: 'left',
			width: '150px'
		});
		that.display.textArea.text('0000-00-00 00:00');
		that.display.toggleBtn = that.display.createChild('div', { 
			cssFloat: 'left', 
			fontSize: '12px', 
			height: '14px',
			lineHeight: '14px',
			margin: 0,
			padding: 0
		});
		that.display.toggleBtn.on('tapend', function () {
			if (that.timestamp) {
				that.display.toggleBtn.setStyle({ color: '#333' });
				that.timestamp = false;
			} else {
				that.display.toggleBtn.setStyle({ color: '#f00' });
				that.timestamp = true;
			}
			displaySelected(that);
		});
		that.display.toggleBtn.setClassName('text-button');
		that.display.toggleBtn.text('timestamp');
		that.on('date.select', function (selected) {
			that.display.date = selected;
			displaySelected(that);
		});
		that.on('hours.select', function (hours) {
			if (!that.display.date) {
				that.display.date = {};
			}
			that.display.date.hours = hours;
			displaySelected(that);
		});
		that.on('minutes.select', function (minutes) {
			if (!that.display.date) {
				that.display.date = {};
			}
			that.display.date.minutes = minutes;
			displaySelected(that);
		});
		// create time picker
		var time = box.createChild('div', { textAlign: 'center' });
		createTimePicker(that, time);
		// date picker
		var top = box.createChild('div', { borderTop: '1px solid #ccc', textAlign: 'center', fontWeight: 'bold', height: cell.height + 'px', padding: 0, margin: 0 });
		top.setClassName('title menu');	
		// create days table
		createDaysTable(that, box, cell, width, height);
		// calendar table
		var table = box.createChild('div');
		// back button
		var back = top.createChild('div', { lineHeight: cell.height + 'px', cursor: 'pointer', fontSize: '12px', padding: 0, margin: 0,  borderRight: '1px solid #ccc', width: cell.width + 'px', height: cell.height + 'px' });
		back.setClassName('title menu-item');
		back.text('<');
		back.allowEvents(['mouseover', 'mouseout']);
		back.on('mouseover', function () {
			this.setStyle({ color: '#f00' });
		});
		back.on('mouseout', function () {
			this.setStyle({ color: '#000' });
		});
		back.on('tapend', function () {
			that.current.year = (that.current.month - 1 === 0) ? that.current.year - 1 : that.current.year;
			that.current.month = (that.current.month - 1 === 0) ? 12 : that.current.month - 1;
			createTable(that, table, cell, width, height);
		});
		// center display
		var center = top.createChild('div', { lineHeight: cell.height + 'px', fontSize: '12px', width: (width - (cell.width * 2) - 2) + 'px', padding: 0, margin: 0, height: cell.height + 'px' });
		center.setClassName('title menu-item');
		// listener for center
		that.on('change', function () {
			center.text(that.current.month + ' / ' + that.current.year);
		});
		// forward button
		var forward = top.createChild('div', { lineHeight: cell.height + 'px', cursor: 'pointer', fontSize: '12px',  padding: 0, margin: 0, borderLeft: '1px solid #ccc', width: cell.width + 'px', height: cell.height + 'px' });
		forward.setClassName('title menu-item');
		forward.text('>');
		forward.allowEvents(['mouseover', 'mouseout']);
		forward.on('mouseover', function () {
			this.setStyle({ color: '#f00' });
		});
		forward.on('mouseout', function () {
			this.setStyle({ color: '#000' });
		});
		forward.on('tapend', function () {
			that.current.year = (that.current.month + 1 > 12) ? that.current.year + 1 : that.current.year;
			that.current.month = (that.current.month + 1 > 12) ? 1 : that.current.month + 1;
			createTable(that, table, cell, width, height);
		});
		// create calendar table
		createTable(that, table, cell, width, height);
	}

	function createDaysTable(that, box, cell, width, height) {
		var table = box.createChild('div', { height: cell.height + 'px' });
		table.setClassName('menu');
		var days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
		var len = days.length;
		var offset = 4;
		var w = ((width / len) - offset) + 'px';
		var h = cell.height + 'px';
		for (var i = 0; i < len; i++) {
			var day = days[i];
			var color = '#333';
			if (day === 'sat') {
				color = '#00c';
			} else if (day === 'sun') {
				color = '#c00';
			}
			var cell = table.createChild('div', { 
				height: h,
				width: w,
				fontSize: '10px',
				fontWeight: 'bold',
				color: color,
				textAlign: 'center',
				lineHeight: h,
				padding: 0,
				margin: '1px',
				border: '1px solid #ccc'
			 });
			cell.setClassName('title menu-item');
			cell.text(day);
		}
	}

	function createTable(that, box, cell, width, height) {
		box.removeAllChildren();
		var now = new Date(that.current.year, that.current.month);
		var prev = new Date(now.getFullYear(), now.getMonth() - 1, 0);
		var current = new Date(now.getFullYear(), now.getMonth(), 0);
		var startDay = prev.getDay();
		var numOfDay = current.getDate();
		// emit
		that.emit('change');
		// build table
		var pivot = 7;
		var counter = 0;
		var row = null;
		var offset = 4;
		var size = ((width / pivot) - offset) + 'px';
		for (var i = 0, len = numOfDay + (startDay + 1); i < len; i++) {
			if (counter === 0) {
				row = box.createChild('div', { padding: 0, margin: 0, height: size });
				row.setClassName('menu');	
			}
			var color = '#333';
			if (counter === 0) {
				color = '#c00';
			} else if (counter === 5) {
				color = '#00c';
			}
			var cell = row.createChild('div', {
				textAlign: 'center',
				width: size,
				height: size,
				padding: 0,
				margin: '1px',
				border: '1px solid #ccc',
				lineHeight: size,
				fontSize: '12px',
				fontWeight: 'bold',
				color: color,
				background: '#fff'
			});
			cell.setClassName('menu-item');
			counter += 1;
			if (counter === pivot) {
				counter = 0;
			}
			if (i > startDay) {
				cell.year = that.current.year;
				cell.month = that.current.month;
				cell.date = i + 1 - (1 + startDay);
				cell.text(cell.date);
				cell.setStyle({ cursor: 'pointer' });
				if (that.selectedDate && that.selectedDate.year === cell.year && that.selectedDate.month === cell.month && that.selectedDate.date === cell.date) {
					cell.setStyle({ background: '#fdc' });
					that.selectedDate = cell;
				}
				cell.allowEvents(['mouseover', 'mouseout']);
				cell.on('mouseover', function () {
					if (that.selectedDate && that.selectedDate.year === this.year && that.selectedDate.month === this.month && that.selectedDate.date === this.date) {
						return;
					}
					this.setStyle({ background: '#cdf' });
				});
				cell.on('mouseout', function () {
					if (that.selectedDate && that.selectedDate.year === this.year && that.selectedDate.month === this.month && that.selectedDate.date === this.date) {
						return;
					}
					this.setStyle({ background: '#fff' });
				});
				cell.on('tapend', function () {
					if (that.selectedDate) {
						that.selectedDate.setStyle({ background: '#fff' });
					}
					this.setStyle({ background: '#fdc' });
					that.current.date = this.date;
					that.selectedDate = this;
					that.emit('date.select', { year: that.current.year, month: that.current.month, date: this.date });
				});
			} else {
				cell.setStyle({ background: '#ddd' });
			}
		}
	}
	
	function createTimePicker(that, box) {
		var hours = box.createChild('select', { padding: 0, width: '70px' });
		for (var i = 0; i < 24; i++) {
			var option = hours.createChild('option');
			option.set('value', i);
			option.text(pad(i));
		}
		hours.allowEvents(['change']);
		hours.on('change', function () {
			that.current.hours = this.get('value');
			that.emit('hours.select', this.get('value'));
		});
		var sep = box.createChild('span', { fontWeight: 'bold' });
		sep.text(':');
		var mins = box.createChild('select', { padding: 0, width: '70px' });
		for (var i = 0; i < 60; i++) {
			var option = mins.createChild('option');
			option.set('value', i);
			option.text(pad(i));
		}
		mins.allowEvents(['change']);
		mins.on('change', function () {
			that.current.minutes = this.get('value');
			that.emit('minutes.select', this.get('value'));
		});
	}

	function pad(num) {
		if (num < 10) {
			return '0' + num;
		}
		if (num === undefined || num === null || num === false) {
			return num;
		}
		return num + '';
	}
}());
