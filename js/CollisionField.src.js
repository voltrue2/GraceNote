(function (window) {

function CollisionField(options) {
	this.positionMap = {};
	this.cells = {};
	this.defaults = {
		gridSize: 200
	};
	for (var key in this.defaults) {
		if (options && options[key] !== undefined) {
			this.defaults[key] = options[key];
		}
	}
}

CollisionField.prototype = new EventEmitter();
window.CollisionField = CollisionField;

/***
* Add an object to the collision filed
* @param (Object) collisionObject
* @return (Void)
*/
CollisionField.prototype.add = function (collisionObject) {
	this.positionMap[collisionObject] = createPosition(this, collisionObject);
	this.update(collisionObject);
	this.emit('add', collisionObject);
};

/***
* Remove an object from the collision field
* @param (Object) collisionObjectToRemove
* @return (Void)
*/
CollisionField.prototype.remove = function (collisionObjectToRemove) {
	delete this.positionMap[collisionObjectToRemove];
	this.emit('remove', collisionObjectToRemove);
};

/***
* Override to provide the logic to extract x and y coordinate of an object
* x1/y1 ____ x2/y1
       |    |
       |____|
   x1/y2     x2/y2
*
* @param (Object) collisionObject
* @return (Object) { x1: Number, y1: Number, x2: Number, y2: Number }
**/
CollisionField.prototype.getObjectPos = function (collisionObject) {

};

/***
* Update one collision object's position in the collision field
* @param (Object) collisionObject
* @return (Void)
*/
CollisionField.prototype.update = function (collisionObject) {
	// remove the collision object from the grids
	var prevPos = getPosition(this, collisionObject);
	var px = prevPos.x;
	var py = prevPos.y;
	if (this.cells[px] && this.cells[px][py]) {
		var index = this.cells[px][py].indexOf(collisionObject);
		if (index > -1) {
			this.cells[px][py].splice(index, 1);
		}
	}
	// update
	this.positionMap[collisionObject] = createPosition(this, collisionObject);
	var pos = getPosition(this, collisionObject);
	var x = pos.x;
	var y = pos.y;
	// update cells
	if (!this.cells[x]) {
		this.cells[x] = {};
	}
	if (!this.cells[x][y]) {
		this.cells[x][y] = [collisionObject];
	} else {
		this.cells[x][y].push(collisionObject);
	}
	this.emit('update', this.positionMap[collisionObject]);
};

/***
* Rectangle based collision test
* Call this function in sequence to test more complex shapes
* Returns an array of collision objects that are touching the given collision object
*
* Visialized grid cells
* [][][]
* [][][]
* [][][]
*
* @param (Object) collisionObject
* @return (Array) list of collision objects
*/
CollisionField.prototype.test = function (collisionObject) {
	var pos = this.getObjectPos(collisionObject);
	var results = [];
	var list = getNearestObjects(this, collisionObject);
	for (var i = 0, len = list.length; i < len; i++) {
		if (list[i] === collisionObject) {
			// no test against yourself
			continue;
		}
		var testee = list[i];
		var testPos = this.getObjectPos(testee);
		if (pos.x1 >= testPos.x1 && pos.x1 <= testPos.x2) {
			if (pos.y1 >= testPos.y1 && pos.y1 <= testPos.y2) {
				if (results.indexOf(testee) === -1) {
					results.push(testee);
				}
			} else if (pos.y1 <= testPos.y1 && pos.y2 >= testPos.y1) {
				if (results.indexOf(testee) === -1) {
					results.push(testee);
				}
			}
		} else if (pos.x1 <= testPos.x1 && pos.x2 >= testPos.x1) {
			if (pos.y1 >= testPos.y1 && pos.y1 <= testPos.y2) {
				if (results.indexOf(testee) === -1) {
					results.push(testee);
				}
			} else if (pos.y1 <= testPos.y1 && pos.y2 >= testPos.y1) {
				if (results.indexOf(testee) === -1) {
					results.push(testee);
				}
			}
		}
	}
	this.emit('test', results);
	return results;
};

/***
* Return x and y of the collision object from cached list
* @param (Object) collisionObject
* @return (Object) { x: Number, y: Number, index: Number }
*/
function getPosition(that, collisionObject) {
	return that.positionMap[collisionObject] || null;
}

/**
* Calculate x and y of a collision object
* @param (Object) collisionObject
* @return (Object) { x: Number, y: Number }
*/
function createPosition(that, collisionObject) {
	var pos = that.getObjectPos(collisionObject);
	var centerX = (pos.x1 + (pos.x2 - pos.x1));
	var centerY = (pos.y1 + (pos.y2 - pos.y1));
	var x = Math.ceil(centerX / that.defaults.gridSize);
	var y = Math.ceil(centerY / that.defaults.gridSize);
	return { x: x, y: y };
};

/***
* Return a list of collistion objects that are closest to the given collision object
* @param (Object) collisionObject
* @return (Array) an array of collision object
*/
function getNearestObjects(that, collisionObject) {
	var found = [];
	var pos = getPosition(that, collisionObject);
	var center = pos.x;
	var middle = pos.y;
	var left = center - 1;
	var right = center + 1;
	var top = middle - 1;
	var bottom = middle + 1;
	var cells = that.cells;
	// left
	if (cells[left] && that.cells[left][top]) {
		found = addToFound(found, cells[left][top]);
	}
	if (cells[left] && cells[left][middle]) {
		found = addToFound(found, cells[left][middle]);
	}
	if (cells[left] && cells[left][bottom]) {
		found = addToFound(found, cells[left][bottom]);
	}
	// center
	if (cells[center] && cells[center][top]) {
		found = addToFound(found, cells[center][top]);
	}
	if (cells[center] && cells[center][middle]) {
		found = addToFound(found, cells[center][middle]);
	}
	if (cells[center] && cells[center][bottom]) {
		found = addToFound(found, cells[center][bottom]);
	}
	// right
	if (cells[right] && cells[right][top]) {
		found = addToFound(found, cells[right][top]);
	}
	if (cells[right] && cells[right][middle]) {
		found = addToFound(found, cells[right][middle]);
	}
	if (cells[right] && cells[right][bottom]) {
		found = addToFound(found, cells[right][bottom]);
	}
	return found;
}

function addToFound(found, list) {
	return found = found.concat(list);
}

}(window));
