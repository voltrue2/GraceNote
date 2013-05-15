(function () {
	var updateList = [];
	var frameUpdate = null;
	var selected = [];
	var list = [];
	var currentFile = null;
	var prevPath = '';
	var currentPath = '';
	var header = document.getElementById('header');
	var fileList = document.getElementById('dirListBox');	
	var backBtn = document.getElementById('backBtn');
	var folderBtn = document.getElementById('folderBtn');
	var uploadBtn = document.getElementById('uploadBtn');
	var fileUploadPath = document.getElementById('fileUploadPath');
	var fileUploadImages = document.getElementById('fileUploadImages');
	var fileUploadForm = document.getElementById('fileUploadForm');
	var currentPos = document.getElementById('currentPosition');
	var massSelectBtn = document.getElementById('massSelectBtn');
	var massDeleteBtn = document.getElementById('massDeleteBtn');
	
	setupTweener();
	buildFileList(prevPath);
	setupNavigator();

	function buildFileList(path) {
		if (!path) {
			backBtn.disabled = true;
			backBtn.className = 'grey-box-button';
		} else {
			backBtn.disabled = false;
			backBtn.className = 'back-button';
		}
		// reset mass selecter
		if (massSelectBtn.checked) {
			massSelectBtn.onmouseup();
		}
		selected = [];
		list = [];
		currentPath = path;
		// get file list
		window.staticFile.getDirList(path || '/', 0, function (error, data) {
			if (error) {
				return alert(error);
			}
			fileList.innerHTML = '';
			var list = data.list;
			list.sort();
			for (var i = 0, len = list.length; i < len; i++) {
				var filePath = list[i];
				createDirFile(filePath);
			}
			// clean up previous more button
			var more = Dom.getById('moreBtn');
			if (more) {
				more.remove();
			}
			// display current postition
			currentPos.textContent = '<' + currentPath + '> ' + list.length + ' Items';
			// create more button
			if (data.more) {
				var more = new Dom(document.createElement('div'));
				more.set('id', 'moreBtn');
				more.from = (Number(data.from) || 0) + 50;
				more.length = list.length;
				more.appendTo(fileList.parentNode);
				more.setClassName('text-button');
				more.setStyle({ margin: '4px 43%' });
				more.text(window.text.more || 'More');
				more.on('tapend', function () {
					window.staticFile.getDirList(path || '/', more.from, function (error, data) {
						var list = data.list;
						more.from = (Number(data.from) || 0) + 50;
						more.length += data.list.length;
						list.sort();
						for (var i = 0, len = list.length; i < len; i++) {
							var filePath = list[i];
							createDirFile(filePath);
						}
						if (!data.more) {
							more.remove();
						}
						currentPos.textContent = '<' + currentPath + '> ' + more.length + ' Items';
					});
				});
			}
		});
	}

	function createDirFile(item) {
		var elm = document.createElement('div');
		var icon = document.createElement('img');
		var path = item.name;
		var isDir = item.isDir;
		elm.path = ((currentPath) ? currentPath + '/' : '') + path;
		elm.name = path;
		if (isDir) {
			icon.src = '/img/system/folder.png';
			icon.onmouseup = function () {
				icon.onmouseout();
				if (currentFile) {
					return;
				}
				prevPath = elm.path.substring(0, elm.path.lastIndexOf('/'));
				buildFileList(elm.path);
			};
			var timer = null;
			icon.onmousedown = function () {
				timer = window.setTimeout(function () {
					timer = null;
					if (currentFile) {
						return;
					}
					currentFile = elm;
					openEditBox(elm, 'folder');
					elm.style.background = '#acf';
				}, 200);
			};
			icon.onmouseout = function () {
				if (timer) {
					window.clearTimeout(timer);
					timer = null;
				}
			};
		} else {
			// check for image file type
			if (path.toLowerCase().match(/(png|jpg|jpeg|gif)/)) {
				icon.src = item.uri;
			} else {
				icon.src = '/img/system/file.png';
			}
			icon.onmouseup = function (event) {
				if (currentFile) {
					return;
				}
				elm.style.background = '#acf';
				currentFile = elm;
				openEditBox(elm, 'file');
			};
		}
		icon.height = 25;
		icon.style.padding = '4px';
		icon.style.cursor = 'pointer';
		elm.appendChild(icon);
		var checkbox = document.createElement('span');
		checkbox.style.border = '1px solid #999';
		checkbox.style.width = '15px';
		checkbox.style.height = '15px';
		checkbox.style.lineHeight = '15px';
		checkbox.style.display = 'block';
		checkbox.style.background = 'url(/img/system/grey_check.png)';
		checkbox.style.backgroundSize = '100%';
		checkbox.style.cursor = 'pointer';
		checkbox.path = path;
		elm.appendChild(checkbox);
		checkbox.onmouseup = function () {
			if (checkbox.check) {
				checkbox.check = false;
				var index = null;
				for (var i = 0, len = selected.length; i < len; i++) {
					if (selected[i].path === path) {
						index = i;
						break;
					}
				}
				if (index === null) {
					return;
				}
				selected.splice(index, 1);
				checkbox.style.background = 'url(/img/system/grey_check.png)';
				checkbox.style.border = '1px solid #999';
			} else {
				checkbox.check = true;
				selected.push({ path: path, isDir: isDir });
				checkbox.style.background = 'url(/img/system/green_check.png)';
				checkbox.style.border = '1px solid #6c0';
			}
			checkbox.style.backgroundSize = '100%';
		};
		list.push(checkbox);
		var name = document.createElement('div');
		name.textContent = path;
		fileList.appendChild(elm);
		elm.style.margin = '4px';
		elm.style.padding = '4px';
		elm.style.cssFloat = 'left';
		elm.style.cursor = 'pointer';
		elm.style.border = '1px solid #ddd';
		elm.style.width = '100px';
		elm.style.height = '100px';
		elm.style.overflow = 'hidden';
		elm.style.textAlign = 'center';
		elm.style.cursor = 'default';
		elm.appendChild(name);
	}
	
	function openEditBox(file, type, createFolder) {
		var box = document.createElement('div');
		box.className = 'box';
		box.style.width = '350px';
		box.style.height = '200px';
		box.style.marginLeft = '-175px';
		box.style.position = 'fixed';
		box.style.top = '200px';
		box.style.left = '50%';
		box.style.background = '#fff';
		box.style.border = '8px solid rgba(200, 200, 200, 0.5)';
		currentFile.box = box;
		document.body.appendChild(box);
		// close button
		var close = document.createElement('div');
		close.className = 'cancel-button';
		box.appendChild(close);
		close.onmouseup = closeEditBox;
		// title
		var name = document.createElement('div');
		name.textContent = (createFolder) ? text.newFolder : text.edit;
		name.style.width = '98%';
		name.style.fontSize = '16px';
		name.style.color = '#666';
		name.style.textAlign = 'center';
		name.style.borderBottom = '1px dotted #ccc';
		box.appendChild(name);
		// rename file/folder
		var rename = document.createElement('input');
		rename.style.marginTop = '10px';
		rename.type = 'text';
		rename.value = file.name || '';
		rename.style.width = '250px';
		// rename save button
		var edit = document.createElement('div');
		edit.className = 'edit-button';
		edit.style.cssFloat = 'left';
		edit.style.marginTop = '10px';
		if (!createFolder) {
			box.appendChild(edit);
		} else {
			rename.style.marginLeft = '45px';
		}
		box.appendChild(rename);
		edit.onmouseup = function () {
			tryToRename(rename, file);
		};
		// give focus
		rename.focus();
		// edit menu
		var menu = document.createElement('table');
		menu.style.margin = '40px auto';
		box.appendChild(menu);
		var tr = document.createElement('tr');
		menu.appendChild(tr);
		if (type === 'file') {
			// check for image file type
			// file preview
			var td = document.createElement('td');
			tr.appendChild(td);
			var preview = document.createElement('div');
			preview.className = 'image-button';
			td.appendChild(preview);
			preview.onmouseup = function () {
				if (file.path.toLowerCase().match(/(png|jpg|jpeg|gif)/)) {
					var ib = new window.ImageBox(600, 500, '/' + file.path);
				} else {
					win = window.open('http://' + window.location.host + '/' + file.path, '_blank');
					win.focus();
				}
			};
			// download button
			var td = document.createElement('td');
			tr.appendChild(td);
			var download = document.createElement('div');
			download.className = 'download-button';
			td.appendChild(download);
			download.onmouseup = function () {
				downloadFile(file.path);
			};
		}
		if (!createFolder) {
			// delete button
			var td = document.createElement('td');
			tr.appendChild(td);
			var deleteBtn = document.createElement('div');
			deleteBtn.className = 'delete-button';
			td.appendChild(deleteBtn);
			deleteBtn.onmouseup = function () {
				var res = confirm(text.confirmDelete.replace('$1', file.name));
				if (res) {
					if (type === 'file') {
						deleteFile(file.path);
					} else if (type === 'folder') {
						deleteFolder(file.path);
					}
				}
			};
		} else {
			// create dir button
			var td = document.createElement('td');
			tr.appendChild(td);
			var createBtn = document.createElement('div');
			createBtn.className = 'upload-button';
			td.appendChild(createBtn);
			createBtn.onmouseup = function () {
				createDir(rename);
			};
		}
		// animation
		var scale = new Tween(Tween.StrongOut, {a: 0, s: 0.8}, {a: 1, s: 1}, 0, 0.3);
		scale.on('change', function (v) {
			box.style.webkitTransform = 'scale(' + v.s + ', ' + v.s + ')';
			box.style.mozTransform = 'scale(' + v.s + ', ' + v.s + ')';
			box.style.oTransform = 'scale(' + v.s + ', ' + v.s + ')';
			box.style.transform = 'scale(' + v.s + ', ' + v.s + ')';
			box.style.opacity = v.a;
		});
		animate(scale);
	}
	
	function closeEditBox(cb) {
		if (!currentFile || !currentFile.box) {
			if (typeof cb === 'function') {
				cb();
			}
			return;
		}
		var scale = new Tween(Tween.StrongOut, {s: 1, a: 1}, {s: 1.2, a: 0}, 0, 0.3);
		scale.on('change', function (v) {
			currentFile.box.style.webkitTransform = 'scale(' + v.s + ', ' + v.s + ')';
			currentFile.box.style.mozTransform = 'scale(' + v.s + ', ' + v.s + ')';
			currentFile.box.style.oTransform = 'scale(' + v.s + ', ' + v.s + ')';
			currentFile.box.style.transform = 'scale(' + v.s + ', ' + v.s + ')';
			currentFile.box.style.opacity = v.a;
		});
		scale.on('finish', function () {
			document.body.removeChild(currentFile.box);
			currentFile.style.background = '';
			currentFile = null;
			if (typeof cb === 'function') {
				cb();
			}
		});
		animate(scale);
	}

	function tryToRename(name, file) {
		var nameValue = name.value;
		if (nameValue === file.name) {
			return alert(text.noChangeMsg);
		}
		// only alpha numeric characters allowed
		var regex = /^[a-z0-9\.\-\_]+$/i;
		if (!regex.test(nameValue)) {
			// not allowed
			return alert(text.alphaNumericOnlyMsg);
		}
		staticFile.rename(file.name, file.path, nameValue, function (error) {
			if (error) {
				return alert(error);
			}
			// close edit box and reload the file list
			closeEditBox(function () {
				buildFileList(currentPath);
			});	
		});
	}

	function createDir(name) {
		var nameValue = name.value;
		// only alpha numeric characters allowed
		var regex = /^[a-z0-9\.\-\_]+$/i;
		if (!regex.test(nameValue)) {
			// not allowed
			return alert(text.alphaNumericOnlyMsg);
		}
		staticFile.createDir(currentPath, nameValue, function (error) {
			if (error) {
				return alert(error);
			}
			// close edit box and reload the file list
			closeEditBox(function () {
				buildFileList(currentPath);
			});	
		});
	}

	function setupNavigator() {
		header.style.height = '40px';
		// back button
		backBtn.onmouseup = function () {
			if (!backBtn.disabled && !currentFile) {
				buildFileList(prevPath);
				prevPath = currentPath.substring(0, currentPath.lastIndexOf('/'));
			}
		};
		// folder button
		folderBtn.onmouseup = function () {
			if (currentFile) {
				return;
			}
			var createDir = true;
			currentFile = folderBtn;
			openEditBox({ name: '' }, 'folder', createDir);
		};
		// file upload
		uploadBtn.style.border = 0;
		fileUploadForm.onsubmit = function () {
			fileUploadPath.value = currentPath;
			staticFile.upload(fileUploadForm, function (error) {
				if (error) {
					return alert(error);
				}
				fileUploadPath.value = '';
				buildFileList(currentPath);
			});
		};
		// mass select button
		massSelectBtn.style.width = '32px';
		massSelectBtn.style.height = '32px';
		massSelectBtn.style.border = '1px solid #999';
		massSelectBtn.style.margin = '4px';
		massSelectBtn.style.background = 'url(/img/system/grey_check.png)';
		massSelectBtn.style.backgroundSize = '100%';
		massSelectBtn.style.cursor = 'pointer';		
		massSelectBtn.onmouseup = function () {
			if (massSelectBtn.checked) {
				massSelectBtn.checked = false;
				massSelectBtn.style.background = 'url(/img/system/grey_check.png)';	
				massSelectBtn.style.border = '1px solid #999';
			} else {
				massSelectBtn.checked = true;
				massSelectBtn.style.background = 'url(/img/system/green_check.png)';
				massSelectBtn.style.border = '1px solid #6c0';
			}
			for (var i = 0, len = list.length; i < len; i++) {
				list[i].onmouseup();
			}
			massSelectBtn.style.backgroundSize = '100%';
		};
		// mass delete button
		massDeleteBtn.onmouseup = function () {
			if (selected.length) {
				var selectedStr = '';
				for (var i = 0, len = selected.length; i < len; i++) {
					selectedStr += selected[i].path + ', ';
				}
				var res = confirm(text.confirmDelete.replace('$1', selectedStr));
				if (res) {
					closeEditBox(function () {
						staticFile.massDelete(currentPath, selected, function (error) {
							if (error) {
								return alert(error);
							}
							buildFileList(currentPath);
							// reset mass selecter
							if (massSelectBtn.checked) {
								massSelectBtn.onmouseup();
							}
						});
					});
				}
			} else {
				alert(text.nothingToDeleteMsg);
			}
		};
	}

	function downloadFile(fileName) {
		staticFile.download(fileName);
	}
	
	function deleteFile(fileName) {
		closeEditBox(function () {
			staticFile.deleteFile(fileName, function (error, success) {
				if (error) {
					return alert(error);
				}
				if (!success) {
					return alert(text.deleteFailed + '"' + fileName + '"');
				}
				buildFileList(currentPath);
			});
		});
	}
	
	function deleteFolder(fileName) {
		closeEditBox(function () {
			staticFile.deleteFolder(fileName, function (error, success) {
				if (error) {
					return alert(error);
				}
				if (!success) {
					return alert(text.deleteFailed + '"' + fileName + '"');
				}
				buildFileList(currentPath);
			});
		});
	}
	
	function animate(tween) {
		if (updateList.indexOf(tween) === -1) {
			updateList.push(tween);
		}
		tween.start();
	}

	function setupTweener() {
		frameUpdate = new FrameUpdate();
		frameUpdate.on('update', function () {
			var list = updateList.slice();
			for (var i = 0, len = updateList.length; i < len; i++) {
				updateList[i].update();
			}
		});
		frameUpdate.start();
	}
}());
