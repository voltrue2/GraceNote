(function () {

	function DataTreeUi(parent, dataObj) {
		EventEmitter.call(this);
		this._parent = parent;
		this._dataTree = window.dataTree.create(dataObj);
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
		var me = box.createChild('div', { border: '1px solid #ccc', padding: '2px', margin: '2px', paddingLeft: '6px' });
		var btn = me.createChild('span', { cursor: 'pointer' });
		// check if me has more child nodes below
		var children = node.getChildNodes();
		if (children.length) {
			// there is more
			label = '[>]  ';
			btn.on('tapend', function () {
				if (this.open) {
					label = '[>]  ';
					var list = me.list;
					for (var i = 0, len = list.length; i < len; i++) {
						list[i].remove();
					}
					this.text(label);
					this.open = false;
					me.list = [];
				} else {
					label = '[v]  ';
					this.text(label);
					// open child node
					var names = node.getChildNodeNames();
					for (var i = 0, len = names.length; i < len; i++) {
						that._createNode(me, names[i], node.getChildNode(names[i]));
					}
					this.open = true;
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
		display.text(name + ':  ' + value);
	};

}());
