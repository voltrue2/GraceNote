// Loader.src.js is required
(function () {

var loader = new Loader();

window.blocker.setup(loader);

window.staticFile = {};

window.staticFile.currentList = null;
window.staticFile.from = 0;
window.staticFile.more = false;

window.staticFile.getDirList = function (path, from, cb) {
	var uri = '/staticfile/getDirList/' + (from || 0) + '/';
	loader.ajax(uri, { path: path || '' }, function (error, requestPath, data) {
		if (error) {
			return cb(error);
		}
		window.staticFile.currentList = data;
		window.staticFile.from = data.from;
		window.staticFile.more = data.more;
		cb(null, data);
	});
};	

window.staticFile.rename = function (oldName, filePath, newName, cb) {
	var uri = '/staticfile/rename/';
	var newPath = filePath.replace(oldName, newName);
	loader.ajax(uri, { oldPath: filePath, newPath: newPath }, function (error) {
		if (error) {
			return cb(error);
		}
		cb();
	});
};

window.staticFile.createDir = function (path, name, cb) {
	var uri = '/staticfile/createDir/';
	loader.ajax(uri, { path: path + '/' + name }, function (error) {
		cb(error);
	});
};

window.staticFile.download = function (path, cb) {
	var uri = '/staticfile/download/?path=' + path;
	window.location.href = uri;
};

window.staticFile.upload = function (form, cb) {
	if (!document.getElementById('uploader')) {
		window.blocker.show();
		var uri = '/staticfile/upload/';
		var target =  'uploader';
		var iframe = document.createElement('iframe');
		iframe.style.width = 0;
		iframe.style.height = 0;
		iframe.style.border = 0;
		iframe.style.display = 'none';
		iframe.setAttribute('id', target);
		iframe.setAttribute('name', target);
		form.appendChild(iframe);
		form.target = target;
		form.action = uri;
		iframe.onload = function (event) {
			window.blocker.hide();
			cb();
		};
	}
};

window.staticFile.deleteFile = function (path, cb) {
	var uri = '/staticfile/deleteFile/';
	loader.ajax(uri, { path: path }, function (error, requestPath, res) {
		cb(error, res.success);
	});
};

window.staticFile.deleteFolder = function (path, cb) {
	var uri = '/staticfile/deleteFolder/';
	loader.ajax(uri, { path: path }, function (error, requestPath, res) {
		cb(error, res.success);
	});
};

window.staticFile.massDelete = function (path, list, cb) {
	var deleter = function (index, list) {
		if (list[index]) {
			var item = path + '/' + list[index].path;
			var isDir = list[index].isDir;
			if (isDir) {
				// folder
				window.staticFile.deleteFolder(item, function (error) {
					if (error) {
						return cb(error);
					}
					index += 1;
					deleter(index, list);
				});
			} else {
				// file
				window.staticFile.deleteFile(item, function (error) {
					if (error) {
						return cb(error);
					}
					index += 1;
					deleter(index, list);
				});
			}
		} else {
			cb();
		}
	};
	deleter(0, list);
};

window.staticFile.getFileData = function (path, cb) {
	var uri = '/staticfile/getFileData/';
	loader.ajax(uri, { path: path }, function (error, requestPath, data) {
		if (error) {
			return cb(error);
		}
		cb(null, data);
	});
};

}());
