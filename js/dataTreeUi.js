(function () {

	function DataTreeUi(parent, dataObj, options) {
		EventEmitter.call(this);
		this._parent = parent;
		this._dataTree = window.dataTree.create(dataObj);
		this._options = options || {};
		// generate data tree UI
		this._createUi();
	}
	
	window.inherits(DataTreeUi, EventEmitter);
	window.DataTreeUi = DataTreeUi;

	DataTreeUi.prototype._createUi = function () {
		var box = this._parent.createChild('div');
		var names = this._dataTree.getChildNodeNames();
		for (var i = 0, len = names.length; i < len; i++) {
			this._createNode(box, names[i], this._dataTree.getChildNode(names[i]));
		}
	};

	DataTreeUi.prototype._createNode = function (box, name, node) {
		var that = this;
		var label = '';
		var value = '';
		var me = box.createChild('div', { background: '#fff', border: '1px solid #ccc', padding: '2px', margin: '2px', paddingLeft: '6px' });
		var btn = me.createChild('span', { cursor: 'pointer' });
		// check if me has more child nodes below
		var children = node.getChildNodes();
		if (children.length) {
			// there is more
			label = '[+]  ';
			btn.on('tapend', function () {
				if (this.open) {
					label = '[+]  ';
					var list = me.list;
					for (var i = 0, len = list.length; i < len; i++) {
						list[i].remove();
					}
					this.text(label);
					this.open = false;
					me.list = [];
					me.setStyle({ background: '#fff' });
				} else {
					label = '[-]  ';
					this.text(label);
					// open child node
					var names = node.getChildNodeNames();
					for (var i = 0, len = names.length; i < len; i++) {
						that._createNode(me, names[i], node.getChildNode(names[i]));
					}
					this.open = true;
					me.setStyle({ background: '#efefe7' });
				}
			});
		} else {
			value = node.get();
		}
		me.setClassName(name);
		if (!box.list) {
			box.list = [];
		}
		box.list.push(me);
		btn.text(label);
		var display = me.createChild('span');
		var displayValue = value;
		if (typeof value === 'number') {
			displayValue = '<span style="color: #00f;">' + value + '</span>';
		}else if (typeof value === 'boolean') {
			displayValue = '<span style="color: #f33;">' + value + '</span>';
		} else if (typeof value === 'string' && !children.length) {
			displayValue = '<span style="color: #090;">"' + value + '"</span>';
		}
		display.html('<span style="font-weight: bold; color: #555;">' + name + '</span>:  ' + displayValue);
		if (children.length && this._options.expand) {
			window.setTimeout(function () {
				btn.emit('tapend');
			}, 0);
		}
	};

}());
