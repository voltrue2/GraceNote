// Loader.src.js is required
(function () {

var loader = new Loader();

var table = {};

window.blocker.setup(loader);

window.table = table;

table.deleteTable = deleteTable;
table.removeColumn = removeColumn;

function deleteTable(table) {
	var res = confirm(text.deleteConfirmMsg.replace('$1', table));
	if (res) {
		var uri = '/dbmanager/deleteTable/' + window.selectedDb + '/' + table + '/';
		loader.ajax(uri, null, function (error, path, res) {
			if (error) {
				return alert(text.failedToDeleteTableMsg.replace('$1', table));
			}
			window.location.href = res.redirectUri;
		});
	}
}

// index
var saveBtn = Dom.getById('saveBtn');
if (saveBtn && tableName) {
	saveBtn.on('tapend', function () {
		var columnNames = Dom.query('.columnName');
		var columnTypes = Dom.query('.columnType');
		// column names
		var columns = [];
		for (var i = 0, len = columnNames.length; i < len; i++) {
			var name = columnNames[i].get('value');
			var type = columnTypes[i].get('value');
			columns.push({ name: name, type: type });
		}
		var uri = '/tabledata/editTableStructure/' + window.selectedDb + '/' + window.tableName + '/';
		loader.ajax(uri, { columns: columns }, function (error, path, res) {
			if (error) {
				return alert(text.failedToEditTable.replace('$1', window.tableName));
			}
			window.location.href = res.redirectUri;
		});
	});	
}

var addColumnBtn = Dom.getById('addColumnBtn');
var columnList = Dom.getById('columnList');
if (addColumnBtn && columnList) {
	addColumnBtn.on('tapend', function () {
		// box
		var box = columnList.createChild('div');
		box.setClassName('box');
		box.setStyle({ background: '#ddd' });
		// name
		var name = box.createChild('input');
		name.setClassName('columnName');
		name.setAttribute({ type: 'text' });
		// type list
		var type = box.createChild('select');
		type.setClassName('columnType');
		for (var key in window.columnTypes) {
			var option = type.createChild('option');
			option.set('value', key);
			option.text(window.columnTypes[key]);
		}
		// remove
		var remove = box.createChild('div');
		remove.setClassName('delete-button');
		remove.setStyle({ cssFloat: 'left' });
		remove.on('tapend', function () {
			window.table.removeColumn(this);
		});
	});
	
	function removeColumn(columnName) {
		if (typeof columnName === 'string') {
			var res = confirm(text.removeColumnMsg.replace('$1', columnName));
			if (res) {
				var uri = '/tabledata/removeColumn/' + window.selectedDb + '/' + window.tableName + '/';
				loader.ajax(uri, { columnName: columnName }, function (error, path, res) {
					if (error) {
						return alert(text.failedToRemoveColumn.replace('$1', columnName));
					}
					window.location.href = res.redirectUri;
				});
			}	
		} else if (typeof columnName === 'object') {
			var box = columnName.getParent();
			box.remove();
		}
	}
}

// dataList
var searchBtn = Dom.getById('searchBtn');
var searchCol = Dom.getById('searchCol');
var searchText = Dom.getById('searchText');
if (searchBtn && searchCol && searchBtn) {
	function search () {
		var col = searchCol.get('value');
		var text = searchText.get('value');
		var uri = '/tabledata/dataList/' + window.selectedDb + '/' + window.tableName + '/' + (window.from || 0) + '/' + col + '/' + text + '/';
		window.location.href = uri;
	}

	searchBtn.on('tapend', search);
	searchText.allowEvents(['change']);
	searchText.on('change', function () {
		search();
	});
}

window.getDataEditor = function (index) {
	var uri = '/tabledata/getData/' + window.selectedDb + '/' + window.tableName + '/';
	loader.ajax(uri, { columns: window.list[index] }, function (error, req, res) {
		if (error) {
			return alert(error);
		}
		var w = 750;
		var h = (window.innerHeight / 1.5 < 500) ? 500 : window.innerHeight / 1.5;
		window.lightbox.show(w, h, function (bar, box, close) {
			var data = res.data;
			var dataList = {};
			var container = box.createChild('div', { width: w + 'px', height: (h - 60) + 'px', overflow: 'scroll' });
			// create data type map
			var dataTypeMap = {};
			for (var i = 0, len = window.desc.length; i < len; i++) {
				dataTypeMap[window.desc[i].field] = window.desc[i].type;
			}
			// create data editor per column
			for (var key in data) {
				var title = container.createChild('div');
				title.setClassName('title');
				title.text(key);
				var area = container.createChild('div', { marginTop: '10px' });
				area.setClassName('area');
				var type = dataTypeMap[key];
				var input = createEditField(area, type, data[key], w);
				input.set('value', data[key]);
				dataList[key] = input;
			}
			var save = box.createChild('div', { margin: '2px 38%' });
			save.setClassName('text-button');
			save.text(window.text.save || 'Save');
			save.on('tapend', function () {
				var uri = '/tabledata/updateData/' + window.selectedDb + '/' + window.tableName + '/';
				var newData = {};
				for (var key in dataList) {
					newData[key] = dataList[key].get('value');
				}
				loader.ajax(uri, { prevData: data, data: newData }, function (error) {
					if (error) {
						return alert(error);
					}
					window.location.reload();
				});
			});
		});
	});
};

function createEditField(parent, type, data, width) {
	var inputType = 'input';
	var style = { width: (width * 0.9) + 'px' };
	if (type === 'text' || type === 'mediumtext') {
		inputType = 'textarea';
		style.margin = '5px';
		style.height = '100px';
	}
	var input = parent.createChild(inputType, style);
	input.set('type', 'text');
	var options = parent.createChild('div', { clear: 'both', height: '30px' });
	if (type === 'int') {
		input.allowEvents(['keyup', 'change']);
		input.on('keyup', function () {
			// integer only
			var val = input.get('value');
			var allowed = '';
			for (var i = 0, len = val.length; i < len; i++) {
				if (val[i].match(/^[\d]+$/)) {
					allowed += val[i];
				}
			}
			input.set('value', allowed);
		});
	} else {
		// optional insert button media file path
		var imgBtn = options.createChild('div', { cssFloat: 'left', width: '20px', height: '20px' });
		imgBtn.setClassName('image-button');
		imgBtn.on('tapend', function () {
			window.lightbox.show(500, 500, function (bar, box, close) {
				var mb = new window.MediaBox(box, 500, 430);
				mb.on('select', function (selected) {
					input.set('value', selected.uri);
					close();
				});
			});
		});
		// optional insert button calendar
		var calBtn = options.createChild('div', { cssFloat: 'left', width: '20px', height: '20px' });
		calBtn.setClassName('calendar-button');
		calBtn.on('tapend', function () {
			var calendar = new window.Calendar();
		calendar.on('close', function (selected) {
			var dateTime = selected.year + '-' + selected.month + '-' + selected.date + ' ' + selected.hours + ':' + selected.minutes + ':' + '00';
				if (this.timestamp) {
					dateTime = selected.timestamp;
				}
				input.set('value', dateTime);	
			});
		});
	}
	// table reference insert
	var table = options.createChild('select', { cssFloat: 'left', padding: 0, height: '20px', fontSize: '10px' });
	var option = table.createChild('option');
	option.set('value', null);
	option.text(window.text.select);
	for (var i = 0, len = window.tableList.length; i < len; i++) {
		var option = table.createChild('option');
		option.set('value', window.tableList[i]);
		option.text(window.tableList[i]);
	}
	var column = options.createChild('select', { cssFloat: 'left', padding: 0, height: '20px', fontSize: '10px' });
	var option = column.createChild('option');
	option.set('value', null);
	option.text(window.text.select);
	// table event to get columns of the selected table
	table.allowEvents(['change']);
	table.on('change', function () {
		var uri = '/datablock/getColumnList/' + window.selectedDb + '/' + table.get('value') + '/';
		loader.ajax(uri, null, function (error, path, resData) {
			if (error) {
				alert(error);
				return window.location.href = '/';
			}
			column.removeAllChildren();
			var option = column.createChild('option');
			option.set('value', null);
			option.text(window.text.select);
			var list = resData.list;
			for (var i = 0, len = list.length; i < len; i++) {
				var option = column.createChild('option');
				option.set('value', list[i].field);
				option.text(list[i].field);
				
			}
		});
	});
	var refValue = options.createChild('select', { cssFloat: 'left', padding: 0, height: '20px', fontSize: '10px' });
	var option = refValue.createChild('option');
	option.set('value', null);
	option.text(window.text.select);
	// reference value event to get insert the data from another table
	refValue.allowEvents(['change']);
	refValue.on('change', function () {
		input.set('value', refValue.get('value'));
		input.emit('keyup');
	});
	// column event to populate the list of reference table
	column.allowEvents(['change']);
	column.on('change', function () {
		var uri = '/tabledata/getDataList/' + window.selectedDb + '/' + table.get('value') + '/';
		var send = { columns: [column.get('value')] };
		loader.ajax(uri, send, function (error, path, val) {
			if (error) {
				alert(error);
				return window.location.href = '/';
			}
			refValue.removeAllChildren();
			var option = refValue.createChild('option');
			option.set('value', null);
			option.text(window.text.select);
			var dataList = val.dataList;
			for (var i = 0, len = dataList.length; i < len; i++) {
				var option = refValue.createChild('option');
				option.set('value', dataList[i][column.get('value')]);
				option.text(dataList[i][column.get('value')]);
				
			}
		});
	});
	return input;
}

window.getDataCreator = function () {
	var w = 750;
	var h = 500;
	window.lightbox.show(w, h, function (bar, box, close) {
		// create data type map
		var dataTypeMap = {};
		for (var i = 0, len = window.desc.length; i < len; i++) {
			dataTypeMap[window.desc[i].field] = window.desc[i].type;
		}
		// create edit field per column
		var dataList = {};
		var container = box.createChild('div', { width: w + 'px', height: (h - 60) + 'px', overflow: 'scroll' });
		for (var i = 0, len = window.desc.length; i < len; i++) {
			var key = window.desc[i].field;
			var title = container.createChild('div');
			title.setClassName('title');
			title.text(key);
			var area = container.createChild('div');
			area.setClassName('area');
			var type = dataTypeMap[key];
			var input = createEditField(area, type, '', w);
			dataList[key] = input;
		}
		var save = box.createChild('div', { margin: '2px 38%' });
		save.setClassName('text-button');
		save.text(window.text.save || 'Save');
		save.on('tapend', function () {
			var uri = '/tabledata/createData/' + window.selectedDb + '/' + window.tableName + '/';
			var newData = {};
			for (var key in dataList) {
				if (dataList[key].get('value') !== '') {
					newData[key] = dataList[key].get('value');
				}
			}
			loader.ajax(uri, { data: newData }, function (error) {
				if (error) {
					return alert(error);
				}
				window.location.reload();
			});
		});
	});
};

window.deleteData = function (index) {
	var res = confirm(window.text.deleteRowMsg);
	if (res) {
		var uri = '/tabledata/deleteData/' + window.selectedDb + '/' + window.tableName + '/';
		loader.ajax(uri, { data: window.list[index] }, function (error) {
			if (error) {
				return alert(error);
			}
			window.location.reload();
		});
	}
};

}());
