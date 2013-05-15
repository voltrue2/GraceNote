(function () {

var loader = new Loader();

window.blocker.setup(loader);

window.editDataBlock = {};

editDataBlock.createSpreadSheet = createSpreadSheet;

var table = null;
var currentPath = null;
var mediaBox = null;

var newRecordBtn = Dom.getById('newRecordBtn');
if (newRecordBtn) {
	newRecordBtn.on('tapend', function () {
		var newData = [];
		var res = createLightbox();
		var box = res.box;
		var container = box.createChild('div');
		container.setStyle({ overflowX: 'hidden', overflowY: 'scroll', height: '550px' });
		// unique ID input
		var cell = container.createChild('div');
		var label = cell.createChild('div');
		label.setClassName('title');
		label.html(window.src.main_column + '<span style="color: #f00;">' + text.required + '</span>');
		var input = cell.createChild('div', { paddingTop: '4px' });
		input.setClassName('area');
		var field = input.createChild('input');
		field.allowEvents(['change']);
		field.on('change', function () {
			this.value = this.get('value');
		});
		newData.push({
			table: window.src.main_table,
			column: window.src.main_column,
			valueObj: field
		});
		// data block fields
		for (var i = 0, len = blocks.length; i < len; i++) {
			var block = blocks[i];
			var cell = container.createChild('div', { clear: 'both' });
			var label = cell.createChild('div', { clear: 'both' });
			label.setClassName('title');
			var req = (block.required) ? '<span style="color: #f00;">' + text.required + '</span>' : '<span style="color: #ccc;">' + text.optional + '</span>';
			label.html(block.name  + req);
			var input = cell.createChild('div', { height: '50px', lineHeight: '50px', paddingTop: '4px' });
			input.setClassName('area');
			var field = createNewInput(input, block);	
			newData.push({
				dataBlock: block,
				valueObj: field
			});
		}
		// save button
		var saveBtn = container.createChild('div');
		saveBtn.setClassName('text-button');
		saveBtn.text(text.save);
		saveBtn.on('tapend', function () {
			var error = null;
			var dataList = [];
			for (var i = 0, len = newData.length; i < len; i++) {
				var data = newData[i];
				dataList[i] = {};
				if (data.table) {
					// unique identifier is always required
					if (!data.valueObj.value) {
						alert(JSON.stringify(data));
						error = 'missingRequiredMsg';
					}
					dataList[i].mainTable = true;
					dataList[i].table = data.table;
					dataList[i].column = data.column;
				}
				if (data.dataBlock) {
					if (data.dataBlock.required && data.valueObj.value === undefined) {
						alert(JSON.stringify(data));
						error = 'missingRequiredMsg';
					}
					dataList[i].table = data.dataBlock.source_table;
					dataList[i].column = data.dataBlock.source_column;
					dataList[i].refColumn = data.dataBlock.source_ref_column;
				}
				dataList[i].value = newData[i].valueObj.value;
			}
			if (error) {
				return alert(text[error]);
			}
			console.log(dataList);
			var uri = '/editdatablock/createNew/' + window.selectedDb + '/' + window.srcId + '/';
			loader.ajax(uri, { newData: dataList }, function (error, path, res) {
				if (error) {
					return alert(error);
				}
				window.location.reload();
			});
		});	
	});
}

// search
var searchColumn = Dom.getById('searchColumn');
var searchText = Dom.getById('searchText');
var searchBtn = Dom.getById('searchButton');
searchBtn.on('tapend', function () {
	var searchThis = (searchText.get('value')) ? searchColumn.get('value') + '/' + encodeURI(searchText.get('value')) + '/' : '';
	window.location.href = '/editdatablock/search/' + window.selectedDb + '/' + window.srcId + '/' + searchThis;
});
searchText.allowEvents(['keydown']);
searchText.on('keydown', function (event) {
	if (event.keyCode === 13 || event.keyIdentifier === 'Enter') {
		var searchThis = (searchText.get('value')) ? searchColumn.get('value') + '/' + encodeURI(searchText.get('value')) + '/' : '';
		window.location.href = '/editdatablock/search/' + window.selectedDb + '/' + window.srcId + '/' + searchThis;
	}
});

var spreadSheet = Dom.getById('spreadSheet');
var scroll = Dom.getById('topScroll');
scroll.setStyle({ overflow: 'scroll' });
// create the spread sheet
createSpreadSheet();

window.createSpreadSheet = createSpreadSheet;

function createSpreadSheet() {
	window.blocker.show();
	if (table) {
		table.remove();
		table = null;
		scroll.removeAllChildren();
	}
	// create the container
	table = spreadSheet.createChild('table');
	var columnTr = table.createChild('tr');
	// create delete columns
	var fixed = [text['delete']];
	var fixedColumns = [];
	if (window.cmsUser.permission == 1 || window.cmsUser.permission == 2) {
		for (var i = 0, len = fixed.length; i < len; i++) {
			var c = createColumn(columnTr, '', null, null, '#ccc');
			c.setStyle({ color: '#ccc', textShadow: '0 0 0', background: '#ccc' });
			fixedColumns.push(c);
		}
	}
	// create datablock columns
	var columns = {};
	for (var i = 0, len = blocks.length; i < len; i++) {
		var block = blocks[i];
		var c = createColumn(columnTr, block.name, block.required, null, '#ccc');
		c.setStyle({ color: '#ccc', textShadow: '0 0 0', background: '#ccc' });
		if (window.searchColumn === block.source_column) {
			c.setStyle({ border: '1px solid #fc0' });
		}
		block._column = c;
		columns[block.source_column] = block;
	}
	// create rows
	for (var i = 0, len = list.length; i < len; i++) {
		var item = list[i];
		var tr = table.createChild('tr');
		// populate each column
		createRow(tr, columns, item);
	}
	// set up top horizontal scrollbar
	scroll.allowEvents(['scroll']);
	scroll.on('scroll', function () {
		spreadSheet.set('scrollLeft', scroll.get('scrollLeft'));
	});
	spreadSheet.allowEvents(['scroll']);
	spreadSheet.on('scroll', function () {
		scroll.set('scrollLeft', spreadSheet.get('scrollLeft'));
	});
	var dummy = scroll.createChild('div');
	var columns = scroll.createChild('div');
	columns.setStyle({ width: (spreadSheet.get('scrollWidth') + 14) + 'px', clear: 'both' });
	var tr = columns.createChild('div');
	var padding = 10;
	// re-create fixed columns with horizontal scrollbar
	for (var i = 0, len = fixed.length; i < len; i++) {
		if (fixedColumns[i]) {
			var width = fixedColumns[i].get('scrollWidth') + 'px';
			var c = createColumn(tr, '', null, 'div');
			c.setStyle({ width: width, cssFloat: 'left', padding: 0, margin: 0, textAlign: 'center', borderLeft: '1px solid #fff', borderRight: '1px solid #fff' });
		}
	}
	// re-create columns with the horizontal scrollbar
	for (var i = 0, len = blocks.length; i < len; i++) {
		var block = blocks[i];
		var width = (block._column.get('scrollWidth')) + 'px';
		var c = createColumn(tr, block.name, block.required, 'div');
		c.setStyle({ width: width, cssFloat: 'left', padding: 0, margin: 0, textAlign: 'center', borderLeft: '1px solid #fff', borderRight: '1px solid #fff' });
		if (window.searchColumn === block.source_column) {
			c.setStyle({ border: '1px solid #fc0' });
		}
	}
	window.blocker.hide();
}

function createColumn(parent, str, req, altDiv, altReq) {
	if (req) {
		req = '<span style="color: ' + (altReq || '#f00' ) + '; font-size: 12px;">&nbsp;' + window.text.required + '</span>';
	} else {
		req = '';
	}
	var td = parent.createChild(altDiv || 'td');
	td.setClassName('title');
	td.setStyle({ whiteSpace: 'nowrap', padding: '0 10px' });
	td.html(str + req);
	return td;
}

function createRow(parent, columns, item) {
	// create fixed row delete
	if (window.cmsUser.permission == 1 || window.cmsUser.permission == 2) {
		var del = parent.createChild('td');
		del.setClassName('area');
		del.setStyle({ borderRight: '1px solid #ddd', textAlign: 'center', padding: '0 5px' });
		var btn = del.createChild('div');
		btn.setClassName('delete-button');
		btn.on('tapstart', function () {
			var unique = item[window.src.main_column]
			var res = confirm(text.deleteConfirmMsg.replace('$1', unique));
			if (res) {
				console.log(unique);
				var uri = '/editdatablock/deleteData/' + window.selectedDb + '/' + window.srcId + '/' + unique + '/';
				loader.ajax(uri, null, function (error, path, res) {
					if (error) {
						return alert(text.failedMsg);	
					}
					window.location.reload();
				});
			}
		});
	}
	// create date row
	for (var key in columns) {
		var cell = columns[key];
		var val = '';
		if (item[key] !== undefined) {
			val = item[key];
			// inflated data
			if (Array.isArray(val)) {
				val = val[0][(cell.reference_column || cell.source_column)];
			}
		}
		var width = (cell._column.get('scrollWidth')) + 'px';
		var td = parent.createChild('td');
		td.setClassName('area');
		td.setStyle({ borderRight: '1px solid #ddd', width: width, textAlign: 'center', padding: '0 5px' });
		if (window.searchColumn === key) {
			td.setStyle({ border: '1px solid #fc0' });
		}
		if (cell.required && val === null) {
			// missing value
			td.setStyle({ background: '#fee' });
		}
		// check column type
		switch (cell.datablock_type) {
			case 'selectList':
				createSelectList(td, cell, val, item);
				break;
			case 'list':
				createList(td, cell, val, item);
				break;
			case 'number':
				createInput(td, cell, val, item);
				break;
			case 'datetime':
				createDateTime(td, cell, val, item);
				break;
			case 'shortText':
				createInput(td, cell, val, item);
				break;
			case 'longText':
				createInput(td, cell, val, item);
				break;
			case 'htmlText':
				createInput(td, cell, val, item);
				break;
			case 'media':
				createMedia(td, cell, val, item); 
				break;
			default:
				break;
			
		}
	}
}

function createSelectList(parent, cell, selected, data) {
	var listName = (cell.reference_table) ? cell.reference_table : cell.source_table;
	var listId = (cell.reference_column) ? cell.reference_column : cell.source_ref_column;
	var listDisplay = (cell.reference_column_display) ? cell.reference_column_display : cell.source_column;
	var selectList = window.selectLists[listName];
	var select = parent.createChild('select');
	select.setClassName('nostyle');
	select.setStyle({ padding: 0, margin: '1px', fontSize: '12px' });
	for (var i = 0, len = selectList.length; i < len; i++) {
		var option = select.createChild('option');
		option.set('value', selectList[i][listId]);
		option.text(selectList[i][listDisplay]);
		if (selectList[i][listId] === selected) {
			option.set('selected', true);
		}
	}
	// set up save
	select.allowEvents(['change']);
	select.on('change', function () {
		var send = {};
		send.value = select.get('value');
		send.column = cell.source_column;
		send.table = cell.source_table;
		send.refColumn = cell.source_ref_column;
		send.id = data[window.src.main_column];
		send.rules = { type: cell.datablock_type, limit: cell.data_length_limit, required: cell.required };
		// save
		var uri = '/editdatablock/updateData/' + window.selectedDb + '/' + window.srcId + '/';
		loader.ajax(uri, send, function (error, path, res) {
			if (error) {
				return alert(window.text.failedMsg);
			}
		});
	});
}

function createList(parent, dataMeta, val, data) {
	var btn = parent.createChild('div');
	btn.setClassName('list-button');
	btn.setStyle({ margin: '0 50%', width: '25px', height: '25px' });
	btn.on('tapend', function () {
		openList(dataMeta, val, data);
	});
}

function openList(dataMeta, val, data, selectCallback) {
	window.lightbox.show(500, 600, function (bar, box, close) {
		// tool area
		var tools = box.createChild('div');
		tools.setClassName('menu');
		// add new button
		var add = tools.createChild('div', { width: '15px', height: '15px' });
		add.setClassName('add-button menu-item');
		if (dataMeta.reference_table) {
			// add select list
			var select = tools.createChild('select', { height: '18px', padding: 0, fontSize: '12px' });
			select.setClassName('menu-item');
			var selectList = null;
			var send = { 
				table: dataMeta.reference_table, 
				refColumn: dataMeta.reference_column, 
				displayColumn: dataMeta.reference_column_display, 
				whereColumn: dataMeta.source_ref_column,
				whereValue: (data && data[window.src.main_column]) ? data[window.src.main_column] : null
			};
			loader.ajax('/editdatablock/getRefList/' + window.selectedDb + '/' + window.srcId + '/', send, function (error, path, res) {
				if (error) {
					return alert(error);
				}
				var list = res.list;
				selectList = list;
				for (var i = 0, len = list.length; i < len; i++) {
					var option = select.createChild('option');
					option.set('value', list[i].ident);
					option.text(list[i].name);
				}
			});
			// add button 
			add.on('tapend', function () {
				if (data) {
					if (data[dataMeta.source_column] === dataMeta.data_length_limit) {
						return alert(text.failedMsg);
					}
					// save
					var send = {};
					send.val = select.get('value');
					send.table = dataMeta.source_table;
					send.refColumn = dataMeta.source_ref_column;
					send.valColumn = dataMeta.source_column;
					send.ref = data[window.src.main_column];
					send.rules = { type: dataMeta.datablock_type, limit: dataMeta.data_length_limit, required: dataMeta.required };
					var uri = '/editdatablock/addItemToList/' + window.selectedDb + '/' + window.srcId + '/' + (window.from || 0) + '/';
					loader.ajax(uri, send, function (error, path, res) {
						if (error) {
							return alert(text.failedMsg);
						}
						window.list = res.list;
						createSpreadSheet();
						close();
					});
				} else {
					// select
					var selected = select.get('value');
					var ident = null;
					var name = null;	
					for (var i = 0, len = selectList.length; i < len; i++) {
						if (selectList[i].ident === selected) {
							ident = selectList[i].ident;
							name = selectList[i].name;
							break;
						}
					}
					// remember selected
					if (!add.selectedItems) {
						add.selectedItems = [];
						add.selectedNames = [];
					}
					// check redundancy
					if (add.selectedItems.indexOf(ident) !== -1) {
						return;
					}
					add.selectedItems.push(ident); 
					add.selectedNames.push(name);				
					var listItem = container.createChild('div', { borderBottom: '1px dotted #ccc', background: (len === dataMeta.data_length_limit) ? '#fee' : '#fff' });
					listItem.setClassName('menu');
					var delBtn = listItem.createChild('div', { width: '15px', height: '15px' });
					delBtn.setClassName('delete-button menu-item');
					delBtn.on('tapend', function () {
						var res = confirm(text.deleteConfirmMsg.replace('$1', name));
						if (res) {
							var index = add.selectedItems.indexOf(ident);
							add.selectedItems.splice(index, 1);
							add.selectedNames.splice(index, 1);
							listItem.remove();
						}
					});
					var display = listItem.createChild('div', { border: 0 });
					display.setClassName('list menu-item');
					display.text(name);
					// select callback
					if (typeof selectCallback === 'function') {
						selectCallback(add.selectedItems);
					}
				}
			});
		} else {
			add.on('tapend', function () {
				var uri = '/tabledata/getData/' + window.selectedDb + '/' + dataMeta.source_table + '/';
				loader.ajax(uri, null, function (error, path, res) {
					if (error) {
						return alert(error);
					}
					// create table data creator
					createDataCreator((data[window.src.main_column] || null), dataMeta.source_table, dataMeta.source_ref_column, res.desc);
				});
			});
		}
		// create list
		var list = (data && data[dataMeta.source_column]) ? data[dataMeta.source_column] : [];
		// max number of item allowed
		var max = tools.createChild('div', { padding: '5px' });
		max.setClassName('menu-item');
		max.text((list.length || 0) + ' / ' + dataMeta.data_length_limit);
		// list container area
		var container = box.createChild('div', {
			height: '538px',
			overflowY: 'scroll'
		});
		box.container = container;
		// populate list
		if (!Array.isArray(list)) {
			return;
		}
		for (var i = 0, len = list.length; i < len; i++) {
			var item = list[i];
			if (Array.isArray(list[i][dataMeta.source_column])) {
				item = list[i][dataMeta.source_column][0];
			}
			var ident = item[(dataMeta.reference_column || dataMeta.source_ref_column)];
			var name = item[(dataMeta.reference_column_display || dataMeta.source_column)];
			var listItem = container.createChild('div', { borderBottom: '1px dotted #ccc', background: (len === dataMeta.data_length_limit) ? '#fee' : '#fff' });
			listItem.setClassName('menu');
			var delBtn = listItem.createChild('div', { width: '15px', height: '15px' });
			delBtn.setClassName('delete-button menu-item');
			delBtn.ident = ident;
			delBtn.name = name;
			delBtn.item = item;
			delBtn.on('tapend', function () {
				console.log(dataMeta, delBtn.item);
				var res = confirm(text.deleteConfirmMsg.replace('$1', this.name));
				if (res) {
					var send = {};
					send.table = dataMeta.source_table;
					if (dataMeta.reference_table) {
						send.refColumn = dataMeta.source_column;
						send.ref = this.ident;
						send.assocColumn = dataMeta.source_ref_column;
						send.assoc = data[window.src.main_column];
					} else {
						send.refColumn = dataMeta.source_column;
						send.ref = this.item[send.refColumn];
						send.assocColumn = dataMeta.source_ref_column;
						send.assoc = this.item[send.assocColumn];
					}
					send.rules = { type: dataMeta.datablock_type, limit: dataMeta.data_length_limit, required: dataMeta.required };
					var uri = '/editdatablock/removeItem/' + window.selectedDb + '/' + window.srcId + '/' + (window.from || 0) + '/';
					loader.ajax(uri, send, function (error, path, res) {
						if (error) {
							return alert(text.failedMsg);
						}
						window.list = res.list;
						createSpreadSheet();
						close();
					});
				}
			});
			var editBtn = listItem.createChild('div', { width: '20px', height: '20px'});
			editBtn.setClassName('edit-button menu-item');
			editBtn.item = list[i];
			editBtn.on('tapend', function () {
				var uri = '/tabledata/getData/' + window.selectedDb + '/' + dataMeta.source_table + '/';
				var send = this.item;
				loader.ajax(uri, { columns: send }, function (error, path, res) {
					if (error) {
						return alert(error);
					}
					// create table data editor
					createDataEditor(dataMeta.source_table, res.desc, res.data);
				});
			});
			var display = listItem.createChild('div', { border: 0 });
			display.setClassName('list menu-item');
			display.text(name);
		}
	});
}

function createInput(parent, dataMeta, val, data) {
	var type = dataMeta.datablock_type;
	var limit = dataMeta.data_length_limit;
	var input = parent.createChild('input');
	input.set('type', 'text');
	input.setStyle({ padding: 0, margin: '1px', fontSize: '12px', width: '100%' });
	input.setClassName('nostyle');
	input.set('value', val);
	if (type === 'number') {
		input.setStyle({ textAlign: 'right' });
		input.allowEvents(['keyup']);
		input.on('keyup', function (event) {
			// number only allowed
			var val = input.get('value');
			var allowed = '';
			for (var i = 0, len = val.length; i < len; i++) {
				if (val[i].match(/^[\d]+$/)) {
					allowed += val[i];
				}
			}
			// apply length limit
			if (allowed.length > limit) {
				allowed = allowed.substring(0, limit);
			}
			// warn empty cell
			if (!allowed) {
				parent.setStyle({ background: '#fee' });	
			} else {
				parent.setStyle({ background: '#fff' });
			}
			input.set('value', allowed);
		});
	} else if (type === 'shortText') {
		input.setStyle({ width: '250px', cursor: 'pointer' });
		input.set('readOnly', true);
		input.on('tapend', function () {
			window.lightbox.show(500, 135, function (bar, box, close) {
				var te = new window.TextEditor(box, 480, 20, dataMeta.data_length_limit);
				te.set(input.get('value'));
				te.on('save', function (value) {
					input.set('value', value);
					input.emit('change');
					close();
				});
			});
		}); 
	} else if (type === 'longText') {
		input.setStyle({ width: '450px', cursor: 'pointer' });
		input.set('readOnly', true);
		input.on('tapend', function () {
			window.lightbox.show(500, 420, function (bar, box, close) {
				var te = new window.TextEditor(box, 480, 300, dataMeta.data_length_limit);
				te.set(input.get('value'));
				te.on('save', function (value) {
					input.set('value', value);
					input.emit('change');
					close();
				});
			});
		});
	} else if (type === 'htmlText') {
		input.setStyle({ width: '450px', cursor: 'pointer' });
		input.set('readOnly', true);
		input.on('tapend', function () {
			window.lightbox.show(490, 460, function (bar, box, close) {
				var te = new window.TextEditor(box, 480, 300, dataMeta.data_length_limit, (type === 'htmlText') ? true : false);
				te.set(input.get('value'));
				te.on('save', function (value) {
					input.set('value', value);
					input.emit('change');
					close();
				});
			});
		});
	} else if (type === 'password') {
		input.set('type', 'password');
	}
	// set up save
	input.allowEvents(['change']);
	input.on('change', function () {
		var send = {};
		send.table = dataMeta.source_table;
		send.refColumn = dataMeta.source_ref_column;
		send.id = data[window.src.main_column] || null;
		if (send.id === null) {
			// fatal
			console.error('missing source_ref_column in data', val, dataMeta, data);
			return alert(window.text.failedMsg);
		}
		send.column = dataMeta.source_column;
		send.value = input.get('value');
		send.rules = { type: dataMeta.datablock_type, limit: dataMeta.data_length_limit, required: dataMeta.required };
		if (dataMeta.required && (send.value === '' || send.value === null)) {
			return alert(window.text.missingRequiredMsg);
		}
		// save
		var uri = '/editdatablock/updateData/' + window.selectedDb + '/' + window.srcId + '/';
		loader.ajax(uri, send, function (error, path, res) {
			if (error) {
				return alert(window.text.failedMsg);
			}
		});
	});
}

function createDateTime(parent, dataMeta, val, data) {
	var box = parent.createChild('div');
	box.setStyle({ clear: 'both', width: '240px' });
	var input = box.createChild('input', { cursor: 'pointer', cssFloat: 'left', width: '200px' });
	input.set('readOnly', true);
	input.set('value', val);
	input.on('tapend', function () {
		var calendar = new window.Calendar();
		calendar.on('close', function (selected) {
			var dateTime = selected.year + '-' + selected.month + '-' + selected.date + ' ' + selected.hours + ':' + selected.minutes + ':' + '00';
			if (this.timestamp) {
				dateTime = selected.timestamp;
			}
			input.set('value', dateTime);	
			// save
			save();
		});
	});

	var delBtn = box.createChild('div', { cssFloat: 'left', width: '17px', height: '17px' });
	delBtn.setClassName('delete-button');
	delBtn.on('tapend', function () {
		input.set('value', '');
		// save
		save();
	});

	// save function
	function save() {
		// prepare save
		var send = {};
		send.table = dataMeta.source_table;
		send.refColumn = dataMeta.source_ref_column;
		send.id = data[window.src.main_column] || null;
		if (send.id === null) {
			// fatal
			console.error('missing source_ref_column in data', val, dataMeta, data);
			return alert(window.text.failedMsg);
		}
		send.column = dataMeta.source_column;
		send.value = input.get('value');
		send.rules = { type: dataMeta.datablock_type, limit: dataMeta.data_length_limit, required: dataMeta.required };
		if (dataMeta.required && (send.value === '' || send.value === null)) {
			return alert(window.text.missingRequiredMsg);
		}
		// save
		var uri = '/editdatablock/updateData/' + window.selectedDb + '/' + window.srcId + '/';
		loader.ajax(uri, send, function (error, path, res) {
			if (error) {
				return alert(window.text.failedMsg);
			}
		});
	}
}

function createMedia(parent, dataMeta, val, data) {
	var box = parent.createChild('div', { width: '80px', clear: 'both' });
	var img = box.createChild('img', { cssFloat: 'left', cursor: 'pointer' });
	if (val.toLowerCase().match(/(png|gif|jpg|jpeg)/g)) {
		img.set('src', val);
	} else {
		img.set('src', '/img/system/file.png');
	}
	img.set('height', 25);
	img.on('tapend', function () {
		window.lightbox.show(450, 500, function (bar, box, close) {
			var mediaBox = new window.MediaBox(box, 450, 430);
			mediaBox.on('select', function (selected) {
				var send = {};
				send.table = dataMeta.source_table;
				send.refColumn = dataMeta.source_ref_column;
				send.id = data[send.refColumn] || null;
				if (send.id === null) {
					// fatal
					console.error('missing source_ref_column in data', val, dataMeta, data);
					return alert(window.text.failedMsg);
				}
				var dirPath = selected.directoryPath;
				if (dirPath.substring(dirPath.length - 1) !== '/') {
					dirPath += '/';
				}
				send.column = dataMeta.source_column;
				send.value = selected.uri;
				send.rules = { type: dataMeta.datablock_type, limit: dataMeta.data_length_limit, required: dataMeta.required };
				if (dataMeta.required && (send.value === '' || send.value === null)) {
					return alert(window.text.missingRequiredMsg);
				}
				// save
				img.setAttribute({ src: send.value });
				var uri = '/editdatablock/updateData/' + window.selectedDb + '/' + window.srcId + '/';
				loader.ajax(uri, send, function (error, path, res) {
					if (error) {
						return alert(window.text.failedMsg);
					}
					close();
				});
			});
		});
	});
	// preview
	var preview = box.createChild('div', { cssFloat: 'left', width: '20px', height: '20px' });
	preview.setClassName('image-button');
	preview.on('tapend', function () {
		var ib = new window.ImageBox(400, 400, val);
	});
}

function createNewInput(parent, dataMeta) {
	var input = null;
	switch (dataMeta.datablock_type) {
		case 'shortText': 
			input = createNewText(parent, dataMeta);
			break;
		case 'longText': 
			input = createNewText(parent, dataMeta);
			break;
		case 'password': 
			input = createNewText(parent, dataMeta);
			break;
		case 'number': 
			input = createNewText(parent, dataMeta);
			break;
		case 'datetime': 
			input = createNewDateTime(parent, dataMeta);
			break;
		case 'selectList': 
			input = createNewSelectList(parent, dataMeta);
			break;
		case 'list':
			input = createNewList(parent, dataMeta);
			break;
		case 'media':
			input = createNewMedia(parent, dataMeta);
			break;
		default:
			break;
	}
	return input;
}

function createNewSelectList(parent, dataMeta) {
	var listName = (dataMeta.reference_table) ? dataMeta.reference_table : dataMeta.source_table;
	var listId = (dataMeta.reference_column) ? dataMeta.reference_column : dataMeta.source_ref_column;
	var listDisplay = (dataMeta.reference_column_display) ? dataMeta.reference_column_display : dataMeta.source_column;
	var selectList = window.selectLists[listName];
	var select = parent.createChild('select');
	select.setStyle({ background: '#fff', padding: 0, margin: '1px', fontSize: '12px' });
	var option = select.createChild('option');
	option.text(text.select);
	for (var i = 0, len = selectList.length; i < len; i++) {
		var option = select.createChild('option');
		option.set('value', selectList[i][listId]);
		option.text(selectList[i][listDisplay]);
	}
	select.allowEvents(['change']);
	select.on('change', function () {
		select.value = select.get('value');
	});
	return select;
}

function createNewList(parent, dataMeta) {
	var btn = parent.createChild('div');
	btn.setClassName('list-button');
	btn.setStyle({ margin: 0, width: '25px', height: '25px' });
	btn.on('tapend', function () {
		openList(dataMeta, null, null, function (selectedValue) {
			btn.value = selectedValue;
		});
	});
	return btn;
}

function createNewText(parent, dataMeta) {
	var input = parent.createChild('input', { background: '#fff', fontSize: '12px' });
	var type = dataMeta.datablock_type;
	if (type === 'number') {
		var limit = dataMeta.data_length_limit;
		input.setStyle({ textAlign: 'right' });
		input.allowEvents(['keyup']);
		input.on('keyup', function (event) {
			// number only allowed
			var val = input.get('value');
			var allowed = '';
			for (var i = 0, len = val.length; i < len; i++) {
				if (val[i].match(/^[\d]+$/)) {
					allowed += val[i];
				}
			}
			// apply length limit
			if (allowed.length > limit) {
				allowed = allowed.substring(0, limit);
			}
			input.set('value', allowed);
		});
	} else if (type === 'shortText') {
		input.setStyle({ cursor: 'pointer' });
		input.set('readOnly', true);
		input.on('tapend', function () {
			window.lightbox.show(500, 135, function (bar, box, close) {
				var te = new window.TextEditor(box, 480, 20, dataMeta.data_length_limit);
				te.set(input.get('value'));
				te.on('save', function (value) {
					input.set('value', value);
					input.emit('change');
					close();
				});
			});
		}); 
	} else if (type === 'longText') {
		input.setStyle({ width: '450px', cursor: 'pointer' });
		input.set('readOnly', true);
		input.on('tapend', function () {
			window.lightbox.show(500, 415, function (bar, box, close) {
				var te = new window.TextEditor(box, 480, 300, dataMeta.data_length_limit);
				te.set(input.get('value'));
				te.on('save', function (value) {
					input.set('value', value);
					input.emit('change');
					close();
				});
			});
		});
	} else if (type === 'password') {
		input.set('type', 'password');
	}
	input.allowEvents(['change']);
	input.on('change', function () {
		input.value = input.get('value');
	});
	return input;
}

function createNewDateTime(parent, dataMeta) {
	var box = parent.createChild('div');
	box.setStyle({ clear: 'both', width: '240px' });
	var input = box.createChild('input', {cursor: 'pointer', cssFloat: 'left', width: '200px' });
	input.set('readOnly', true);
	input.on('tapend', function () {
		var calendar = new window.Calendar();
		calendar.on('close', function (selected) {
			var dateTime = selected.year + '-' + selected.month + '-' + selected.date + ' ' + selected.hours + ':' + selected.minutes + ':' + '00';
			if (this.timestamp) {
				dateTime = selected.timestamp;
			}
			input.set('value', dateTime);	
		});
	});

	var delBtn = box.createChild('div', { cssFloat: 'left', width: '17px', height: '17px' });
	delBtn.setClassName('delete-button');
	delBtn.on('tapend', function () {
		input.set('value', '');
	});
	return input;
}

function createNewMedia(parent, dataMeta) {
	var img = parent.createChild('div');
	img.setStyle({
		width: '100px',
		height: '50px',
		cursor: 'pointer'
	});
	img.drawImage('/img/system/no_image.png', { positionX: 0, size: '50% 100%' });
	img.on('tapend', function () {
		window.lightbox.show(450, 500, function (bar, box, close) {
			var mediaBox = new window.MediaBox(box, 450, 430);
			mediaBox.on('select', function (selected) {
				var dirPath = selected.directoryPath;
				if (dirPath.substring(dirPath.length - 1) !== '/') {
					dirPath += '/';
				}
				var src;
				img.value = selected.uri;
				if (img.value.toLowerCase().match(/(png|gif|jpg|jpeg)/g)) {
					src = img.value;
				} else {
					src = '/img/system/file.png';
				}
				img.drawImage(src, { positionX: 0, size: '50% 100%' });
				close();
			});
		});
	});
	return img;
}

function createLightbox() {
	// transparent background
	var bg = Dom.create('div');
	bg.appendTo(document.body);
	bg.setStyle({
		position: 'fixed',
		top: 0,
		left: 0,
		width: '100%',
		height: '100%',
		background: 'rgba(0, 0, 0, 0.4)'
	});
	// list box
	var box = bg.createChild('div');
	box.setStyle({
		zIndex: 900,
		margin: '-300px -250px',
		position: 'fixed',
		top: '50%',
		left: '50%',
		width: '500px',
		height: '600px',
		background: '#fff',
		border: '4px solid #666'
	});
	// status bar
	var bar = box.createChild('div', { height: '20px' });
	bar.setClassName('title');
	// close button
	var close = bar.createChild('div', { width: '15px', height: '15px' });
	close.setClassName('cancel-button');
	close.on('tapend', function () {
		bg.remove();
		bg.emit('close');
	});
	return { bg: bg, box: box };
}

function createDataEditor(table, desc, data) {
	window.lightbox.show(600, 500, function (bar, box, onClose) {
		var container = box.createChild('div', {
			width: '600px',
			height: '478px',
			overflowY: 'scroll'
		});
		window.buildTableDataEditor(container, table, desc, data);
	});
}

function createDataCreator(mainId, srcTable, srcRefColumn, desc) {
	window.lightbox.show(600, 500, function (bar, box, onClose) {
		var container = box.createChild('div', {
			width: '600px',
			height: '478px',
			overflowY: 'scroll'
		});
		window.buildTableDataCreator(container, mainId, srcTable, srcRefColumn, desc);
	});
	console.log(arguments);
}

}());
