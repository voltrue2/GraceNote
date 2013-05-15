(function () {

	function Sprite() {

		window.EventEmitter.call(this);

		this.x = 0;
		this.y = 0;
		this.pivotX = 0;
		this.pivotY = 0;
		this.scaleX = 1;
		this.scaleY = 1;
		this.width = 0;
		this.height = 0;
		this.alpha = 1;
		this.rotation = 0; // radius NOT degree
		this.visible = true;
		this.context = window.context || null;

		this._parent = null;
		this._children = [];
		this._draw = null; // record a function to draw

		this._tweens = {};
	}


	window.inherits(Sprite, window.EventEmitter);

	window.Sprite = Sprite;

	Sprite.prototype.getParent = function () {
		return this._parent;
	};

	Sprite.prototype.getChildByIndex = function (index) {
		if (index !== undefined && this._children[index]) {
			return this._children[index];
		} else {
			return null;
		}
	};

	Sprite.prototype.getIndex = function () {
		if (this._parent) {
			return this._parent._children.indexOf(this);
		} else {
			// no parent
			return null;
		}
	};

	// move itself to the bottom of index
	Sprite.prototype.moveToBottomIndex = function () {
		if (this._parent) {
			var siblings = this._parent._children;
			var myIndex = siblings.indexOf(this);
			siblings.splice(0, 0, siblings.splice(myIndex, 1)[0]);
		}
	};

	// move itself to the top of index
	Sprite.prototype.moveToTopIndex = function () {
		if (this._parent) {
			var siblings = this._parent._children;
			var myIndex = siblings.indexOf(this);
			var me = siblings.splice(myIndex, 1)[0];
			siblings.push(me);
		}
	};

	Sprite.prototype.createChild = function () {
			var sprite = new Sprite();
			this.appendChild(sprite);
		return sprite;
	};

	Sprite.prototype.appendChild = function (sprite) {

		if (sprite._parent && this !== sprite._parent) {
			console.warn('Sprite.appendChild: given sprite is a child of another parent: ', JSON.stringify(sprite._parent));
			sprite._parent.removeChild(sprite);
		}

		if (this === sprite._parent) {
			return;
		}
		// inherit canvas context from parent sprite
		sprite.context = this.context;
		this._children.push(sprite);
		sprite._parent = this;

	};

	Sprite.prototype.removeChild = function (sprite) {
		var index = this._children.indexOf(sprite);
		if (index === -1) {
			return console.warn('Sprite.removeChild: Given sprite is not a child.');
		}
		this._children.splice(index, 1);
		sprite.context = null;
		sprite._parent = null;
	};

	Sprite.prototype.destroyChildren = function () {
		var children = this._children.slice();

		while (this._children.length > 0) {
			var child = this._children.pop();
			child.destroy();
		}
	};

	// remove itself from its parent
	Sprite.prototype.destroy = function () {
		this.emit('destroy');
		if (this._parent) {
			this._parent.removeChild(this);
		}
		this.context = null;
		this.destroyChildren();
		//this.removeAllListeners();
	};

	Sprite.prototype.detach = function () {
		this._parent.removeChild(this);
	};

	// this function allows you to copy Sprites into a different canvas
	// returns a copy of this Sprite
	// copies children, but does NOT copy parent
	Sprite.prototype.copy = function (newContext) {
		var copy = new Sprite();
		copy.x = this.x;
		copy.y = this.y;
		copy.pivotX = this.pivotX;
		copy.pivotY = this.pivotY;
		copy.scaleX = this.scaleX;
		copy.scaleY = this.scaleY;
		copy.alpha = this.alpha;
		copy.visible = this.visible;
		copy.rotation = this.rotation;
		copy.width = this.width;
		copy.height = this.height;
		copy._draw = this._draw;

		// move all copied sprites to a new context if provided
		copy.context = newContext || this.context;

		// copy all children
		for (var i = 0, len = this._children.length; i < len; i++) {
			var child = this._children[i];
			var copiedChild = child.copy(newContext);
			copy.appendChild(copiedChild);
		}

		return copy;
	};

	Sprite.prototype.setRenderMethod = function (drawingFunc) {
		if (typeof drawingFunc !== 'function') {
			return console.warn('Sprite.setRenderMethod: Expecting the argument to be a function.');
		}
		this._draw = drawingFunc;
	};
	
	Sprite.prototype.render = function () {
		this.x = Math.round(this.x);
		this.y = Math.round(this.y);
		this._render();
	};

	Sprite.prototype.addTween = function (name, tween) {
		if (this._tweens[name]) {
			return console.warn('Sprite.addTween: cannot add the same tween more than once', name);
		}

		this._tweens[name] = tween;

	};


	Sprite.prototype.getTween = function (name) {
		if (this._tweens[name]) {
			return this._tweens[name];
		}
		return false;
	}

	Sprite.prototype.removeTween = function (name) {
		this._tweens[name] = null;
	};

	Sprite.prototype.removeAllTweens = function () {
		this._tweens = {};
	};
	
	
	Sprite.prototype.getParentAlpha = function () {
		return this._parent ? this._parent.alpha : 1;
	};

	Sprite.prototype.isVisible = function () {
		var sprite = this;
		var visible = sprite.visible;
		while (visible && sprite) {
			visible = sprite.visible;
			sprite = sprite._parent;
		}
		return visible;
	};

	Sprite.prototype._updateTween = function () {
		for (var tweenName in this._tweens) {
			this._tweens[tweenName].update();
		}
	};

	Sprite.prototype._render = function () {

		// There is no rendering method set
		if (!this._draw) {
			return;
		}

		if (this.visible && this.context) {
			// run tween if there is any
			this._updateTween();

			// render myself

			this.context.save();
			this._transform();
			this._draw(this.context);

			// render children
			for (var i = 0, len = this._children.length; i < len; i++) {
				var child = this._children[i];
				child._render();
			}

			this.context.restore();
		}

	};

	Sprite.prototype._transform = function () {
		var context = this.context;
		context.globalAlpha = context.globalAlpha * this.alpha;
		context.translate(this.x + this.pivotX, this.y + this.pivotY);
		context.rotate(this.rotation);
		context.scale(this.scaleX, this.scaleY);
		// offset the center of the Sprite object after scale and rotation
		context.translate(-this.pivotX, -this.pivotY);
	};

	Sprite.prototype._getLocalCoordinate = function (x, y) {
		var result = {x : x, y : y};

		// revert the parent transformation :
		if (this._parent) {
			result = this._parent._getLocalCoordinate(result.x,result.y);
		}

		// revert first translation :
		result.x = result.x - this.x - this.pivotX;
		result.y = result.y - this.y - this.pivotY;

		// revert rotation :
		var cosR = Math.cos(this.rotation);
		var sinR = Math.sin(this.rotation);
		var rotX =  (cosR * result.x) + (sinR * result.y);
		result.y = (-sinR * result.x) + (cosR * result.y);
		result.x = rotX;

		// revert scale :
		result.x = result.x / this.scaleX;
		result.y = result.y / this.scaleY;

		// revert pivot translation :
		result.x = result.x + this.pivotX;
		result.y = result.y + this.pivotY;

		return result;
	};

}());