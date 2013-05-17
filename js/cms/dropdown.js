(function () {
	
	// list: [{ id: 'selectId', name: 'displayName' }, { id: , name: }...]
	function Dropdown(parent, list, selected) {
		EventEmitter.call(this);
		this.list = list;
		create(this, parent, selected);
	}

	window.inherits(Dropdown, EventEmitter);
	window.Dropdown = Dropdown;

	Dropdown.prototype.get = function (key) {
		return this.key || null;
	};

	Dropdown.prototype.set = function (key, value) {
		this.key = value;
		this.emit('change', value);
	};

	Dropdown.prototype.add = function (id, name) {
		this.list.push({ id: id, name: name });
		this.emit('add', { id: id, name: name });
	};

	function create(that, parent, selected) {
		var box = parent.createChild('div');
		box.setStyle({
			margin: '4px',
			width: '200px',
			height: '30px',
			border: '1px solid #ddd',
			clear: 'clear',
			background: '#fff',
			cursor: 'pointer'
		});
		var select = box.createChild('div');
		select.setStyle({
			width: '166px',
			height: '30px',
			lineHeight: '25px',
			padding: '2px',
			color: '#666',
			fontSize: '15px',
			cssFloat: 'left',
			overflow: 'hidden',
			textAlign: 'center'
		});
		var text = window.text.select || 'Please Select';
		if (selected !== null && selected !== undefined) {
			for (var i = 0, len = that.list.length; i < len; i++) {
				if (that.list[i].id == selected) {
					text = that.list[i].name;
					that.set('value', selected);
					break;
				}
			}
		}
		select.html(text);
		var arrow = box.createChild('div');
		arrow.setStyle({
			height: '30px',
			width: '30px',
			cssFloat: 'right'
		});
		arrow.drawImage(window.assets['arrow-down']);
		// list
		box.on('tapend', function () {
			createList(that, select);
		});
	}

	function createList(that, select) {
		var height = (30 * that.list.length) + 20;
		if (height > window.innerHeight / 2) {
			height = window.innerHeight / 2;
		}
		window.lightbox.show(200, height, function (bar, box, close) {
			var container = box.createChild('div');
			container.setStyle({
				height: (height - 20) + 'px',
				overflow: 'scroll'
			});
			for (var i = 0, len = that.list.length; i < len; i++) {
				var item = container.createChild('div');
				item.setStyle({
					color: '#666',
					fontSize: '15px',
					textAlign: 'center',
					height: '30px',
					lineHeight: '25px',
					borderBottom: '1px solid #eee',
					background: '#fff',
					cursor: 'pointer'
				});
				if (that.list[i].id == that.get('value')) {
					item.setStyle({
						background: '#e7e7ef'
					});
				}
				item.data = that.list[i];
				item.html(that.list[i].name);
				item.on('tapend', function () {
					that.set('value', this.data.id);
					select.html(this.data.name);
					close();
				});
			}
			that.once('add', function () {
				close();
				createList(that, select);
			});
		});
	}

}());
