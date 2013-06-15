(function () {

	var loader = new Loader();

	function MediaBox(parent, width, height, startPath) {
		EventEmitter.call(this);
		this.currentPath = '';
		var container = parent.createChild('div', {
			width: width + 'px',
			height: height + 'px'
		});
		buildInitalBox(this, container, startPath || '/', height);
	}

	window.inherits(MediaBox, EventEmitter);
	window.MediaBox = MediaBox;

	function buildInitalBox(that, container, startPath, height) {
		var bar = container.createChild('div', { clear: 'both', border: '1px solid #ccc', padding: '2px', margin: '1px', height: '15px' });
		var back = bar.createChild('div', { cssFloat: 'left', margin: '2px', width: '10px', height: '10px' });
		back.setClassName('grey-box-button');
		that.on('updateList', function () {
			if (that.currentPath !== '/') {
				back.setClassName('back-button');
			} else {
				back.setClassName('grey-box-button');
			}
		});
		// item number
		var num = bar.createChild('div', { textIndent: '20px', cssFloat: 'left', fontSize: '12px' });
		that.on('_list', function (listItems) {
			num.html('<<span style="font-size: 10px;">' + that.currentPath + '</span>> #' + listItems.length);
		});
		// container box
		var box = container.createChild('div', {
			height: (height - 18) + 'px',
			overflow: 'scroll'
		});
		var footer = container.createChild('div', { border: '1px solid #ccc' });
		// back button event
		back.on('tapend', function () {
			if (that.currentPath !== '/') {
				// move back
				var currPath = that.currentPath.substring(1).substring(0, that.currentPath.length - 2);
				var pathItems = currPath.split('/');
				var prevPath = '/';
				for (var i = 0, len = pathItems.length; i < len; i++) {
					if (i < len - 1) {
						prevPath += pathItems[i] + '/';
					}
				}
				createList(that, box, footer, prevPath, 0);
			}
		});
		// build list
		createList(that, box, footer, startPath, 0);
	}

	function createList(that, box, footer, path, from) {
		box.removeAllChildren();
		footer.removeAllChildren();
		that.currentPath = path;
		if (path.substring(path.length - 1) !== '/') {
			that.currentPath += '/';
		}
		var uri = '/staticfile/getDirList/' + (from || 0) + '/';
		loader.ajax(uri, { path: that.currentPath }, function (error, reqPath, res) {
			if (error) {
				return alert(error);
			}
			var list = res.list;
			// create list
			buildList(that, box, footer, list, path, from);
			// check for more
			if (res.more) {
				var more = footer.createChild('div', { margin: '4px 30%'});
				more.setClassName('text-button');
				more.text('More');
				more.from = Number(res.from) + 50;
				more.on('tapend', function () {
					var thatMore = this;
					var uri = '/staticfile/getDirList/' + this.from + '/';
					loader.ajax(uri, { path: that.currentPath }, function (error, req, data) {
						if (error) {
							return alert(error);
						}
						if (!data.more) {
							thatMore.remove();
						}
						thatMore.from += 50;
						buildList(that, box, footer, data.list, path, thatMore.from);
					});	
				});
			}
			that.emit('updateList', list);
		});
	}

	function buildList(that, box, footer, list, currentPath, from) {
		for (var i = 0, len = list.length; i < len; i++) {
			var item = list[i];
			var cell = box.createChild('div');
			cell.setClassName('list');
			var icon = cell.createChild('div', { cursor: 'pointer', marginLeft: '40px', width: '50px', height: '50px' });
			var path = window.assets['folder'];
			if (!item.isDir) {
				path = window.assets['file'];
				if (item.name.toLowerCase().match(/(png|gif|jpg|jpeg)/)) {
					// image file
					path = item.uri;
					icon.set('isImage', true);
				}
			}
			icon.item = item;
			icon.drawImage(path);
			var menu = cell.createChild('div', { clear: 'both', height: '20px', marginLeft: '40px', textIndent: 0 });
			if (icon.get('isImage')) {
				var preview = menu.createChild('div', { width: '20px', height: '20px', cssFloat: 'left' });
				preview.setClassName('image-button');
				preview.set('path', path);
				preview.on('tapend', function () {
					var ib = new window.ImageBox(600, 500, this.get('path'));
				});
			}
			var name = menu.createChild('div', { lineHeight: '20px', padding: '4px',  cssFloat: 'left', height: '20px', whiteSpace: 'nowrap', fontSize: '12px' });
			var nameVal = item.name;
			if (nameVal.length > 50)  {
				nameVal = nameVal.substring(0, 50) + '...';
			}
			name.text(nameVal);
			// touch event
			icon.on('tapend', function () {
				if (this.item.isDir) {
					// move to a directory
					createList(that, box, footer, that.currentPath + this.item.name, 0);
				} else {
					// select a file
					that.emit('select', this.item);
				}
			});
		}
		that.emit('_list', box.query('.list'));
	}

}());
