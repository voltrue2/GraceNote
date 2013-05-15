(function () {

	function TextEditor(parent, width, height, maxLen, html) {
		EventEmitter.call(this);
		var that = this;
		var htmlEditor = null;
		var box = parent.createChild('div', { textAlign: 'center', padding: '4px' });
		var counter = box.createChild('div', { margin: '4px', padding: '5px', border: '1px solid #ccc' });
		this._input = box.createChild('textarea', { color: '#333', fontFamily: 'Verdana', width: width + 'px', height: height + 'px' });
		this._id = Date.now();
		this._input.set('id', this._id);
		this._input.allowEvents(['change']);
		this._input.on('change', function () {
			that.emit('change', that.get());
		});
		if (maxLen) {
			this.on('set', function () {
				var len = (this.get('value')) ? this.get('value').length : 0;
				counter.text(len + '/' + maxLen);
			});
			this._input.allowEvents(['keydown']);
			this._input.on('keydown', function () {
				var len = (this.get('value')) ? this.get('value').length : 0;
				if (len > maxLen) {
					// exceeds the max length allowed
					this.set('value', this.get('value').substring(0, maxLen));
					len = (this.get('value')) ? this.get('value').length : 0;
				}
				counter.text(len + '/' + maxLen);
			});
		} else {
			this.on('set', function () {
				var len = (this.get('value')) ? this.get('value').length : 0;
				counter.text(len);
			});
			this._input.allowEvents(['keydown']);
			this._input.on('keydown', function () {
				var len = (this.get('value')) ? this.get('value').length : 0;
				counter.text(len);
			});
		}
		// check if the editor is in html mode
		if (html && window.nicEditor) {
			var options = { fullPanel: true };
			var elm = this._input._src;
			this._htmlEditor = new window.nicEditor(options).panelInstance(elm, this._id);
		}
		// save
		var save = box.createChild('div', { margin: '4px auto' });
		save.setClassName('text-button');
		save.text(window.text.save || 'Save');
		save.on('tapend', function () {
			if (that._htmlEditor) {
				// html editor
				return that.emit('save', that._htmlEditor.instance(0).getContent());
			}
			// regular text editor
			that.emit('save', that.get());
		});
	}
	
	window.inherits(TextEditor, EventEmitter);
	window.TextEditor = TextEditor;
	
	TextEditor.prototype.get = function () {
		return this._input.get('value');
	};
	
	TextEditor.prototype.set = function (value) {
		this._input.set('value', value);
		if (this._htmlEditor) {
			this._htmlEditor.instance(0).setContent(value);
		}
		this.emit('set', value);
	};

}());
