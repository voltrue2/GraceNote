(function () {

	function DataTree(obj) {
		
	}

	window.inherits(DataTree, EventEmitter);
	window.dataTree = new DataTree();

	DataTree.prototype.create = function (obj) {
		return new Node(obj, null, null);
	};
	
	function Node(nodeValue, parentNode, nodeKey) {
		EventEmitter.call(this);
		this.name = nodeKey;
		this._parent = parentNode;
		this._value = nodeValue;
		this._children = [];
		this._map = {};
		if (typeof nodeValue === 'object') {
			for (var key in nodeValue) {
				if (!isNaN(key)) {
					key = Number(key);
				}
				this.addChild(key, nodeValue[key]);
			}
		}
	}
	
	window.inherits(Node, EventEmitter);

	Node.prototype.get = function () {
		return copyObj(this._value);
	};

	// value can be either a single value or an object to update child nodes
	Node.prototype.set = function (value) {
		this._update(value);		
	};

	Node.prototype.getChildNode = function (key) {
		var index = this._map[key];
		if (index === undefined) {
			return null;
		}
		return this._children[index] || null;		
	};

	Node.prototype.getChildNodes = function () {
		return this._children; 
	};

	Node.prototype.getChildNodeNames = function () {
		var names = [];
		for (var key in this._map) {
			names.push(key);
		}
		return names;
	};

	Node.prototype.addChild = function (key, value) {
		if (this._map[key] !== undefined) {
			this.removeChild(key);
		}
		this._map[key] = this._children.length;
		var child = new Node(value, this, key);
		var that = this;
		child.on('_set', function (value) {
			that._value[this.name] = value;
		});
		child.on('change', function () {
			that.emit('change', that.get());
		});
		this._value[key] = value;
		this._children.push(child);	
		this.emit('change', this.get());
		return true;
	};

	Node.prototype.removeChild = function (key) {
		var index = (this._map[key] !== undefined) ? this._map[key] : null;
		if (index === null) {
			return false;
		}
		this._children.splice(index);
		this._map = {};
		for (var i = 0, len = this._children.length; i < len; i++) {
			if (this._children[i] !== undefined) {
				this._map[this._children[i].name] = i;
			}
		}
		delete this._value[key];
		this._value = copyObj(this._value);
		this.emit('change', this.get());
	};

	Node.prototype._update = function (newValue) {
		if (typeof newValue === 'object') {
			for (var key in newValue) {
				var node = this.getChildNode(key);
				if (node) {
					if (typeof newValue[key] !== typeof node.get()) {
						// different data type conversion not allowed
						console.warn('[Node] _update: data type nodes not much', newValue[key], node.get());
						continue;
					}
					if (typeof newValue[key] === 'object') {
						node._update(newValue[key]);
					} else {
						node.set(newValue[key]);
					}
				} else {
					// add new node
					this.addChild(key, newValue[key]);
				}
			}
		}
		if (typeof this._value !== typeof newValue) {
			// different data type conversion not allowed
			console.warn('[Node] _update: data type nodes not much', this._value, newValue);
			return;
		}
		this._value = newValue;
		this.emit('_set', this._value);
		this.emit('change', this.get());
	};

	function copyObj(obj) {
		if (typeof obj !== 'object') {
			return obj;
		}
		var copy;
		if (Array.isArray(obj)) {
			copy = [];
			for (var i = 0, len = obj.length; i < len; i++) {
				if (typeof obj[i] === 'object') {
					copy[i] = copyObj(obj[i]);
				} else if (obj[i] !== undefined) {
					copy[i] = obj[i];
				}
			}
		} else {
			copy = {};
			for (var key in obj) {
				if (typeof obj[key] === 'object') {
					copy[key] = copyObj(obj[key]);
				} else if (obj[key] !== undefined) {
					copy[key] = obj[key];
				}
			}

		}
		return copy;
	}

}());
