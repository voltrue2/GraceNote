(function () {

var loader = new Loader();

var datablock = {};

window.blocker.setup(loader);

window.datablock = datablock;

function createWYSIWYG(name) {
	if (nicEditor) {
		return new nicEditor({fullPanel: true, onSave: doNothing}).panelInstance(name);
	}
	return null;
}

function doNothing() {
	
}

function deleteDataBlockSource(id, name) {
	var res = confirm(text.deleteMsg.replace('$1', name));
	if (res) {
		var uri = '/datablock/deleteDataBlockSource/' + selectedDb + '/';
		loader.ajax(uri, { id: id }, function (error, path, res) {
			if (error) {
				return alert(text.failedMsg);
			}
			window.location.href = res.redirectUri;
		});
	}
}

// create editor
var desc = document.getElementById('description');
if (desc) {
	var descEditor = createWYSIWYG('description');
}

// create page
var saveCreateBtn = Dom.getById('saveCreateBtn');
if (saveCreateBtn) {
	var name = Dom.getById('name');
	var mainTable = Dom.getById('mainTable');
	var mainColumn = Dom.getById('mainColumn');
	
	mainTable.allowEvents(['change']);
	mainTable.on('change', function () {
		var uri = '/datablock/getColumnList/' + selectedDb + '/' + mainTable.get('value') + '/';
		loader.ajax(uri, null, function (error, path, data) {
			if (error || !data) {
				return alert(error);
			}
			mainColumn.removeAllChildren();
			var option = mainColumn.createChild('option');
			option.set('value', '');
			option.text(text.select);
			var list = data.list || [];
			for (var i = 0, len = list.length; i < len; i++) {
				var option = mainColumn.createChild('option');
				option.set('value', list[i].field);
				option.text(list[i].field);
				
			}
		});
	});
	
	// save button
	saveCreateBtn.on('tapend', function () {
		// extract values
		var nameVal = name.get('value');
		var mainTableVal = mainTable.get('value');
		var mainColumnVal = mainColumn.get('value');
		var descVal = descEditor.instanceById('description').getContent(); 
		// check for required fileds
		if (!nameVal || !mainTableVal || !mainColumnVal) {
			return alert(text.missingRequiredMsg);
		}
		var uri = '/datablock/createNewDataBlockSource/' + selectedDb + '/';
		loader.ajax(uri, { name: nameVal, mainTable: mainTableVal, mainColumn: mainColumnVal, desc: descVal }, function (error, path, res) {
			if (error) {
				return alert(text.failedMsg);
			}
			window.location.href = res.redirectUri;
		});
	});
}

// edit page
var saveUpdateBtn = Dom.getById('saveUpdateBtn');
var dataBlockList = Dom.getById('dataBlockList');
var addDataBlockBtn = Dom.getById('addDataBlockBtn');
var saveDataBlockChangeBtn = Dom.getById('saveDataBlockChangeBtn');
if (saveUpdateBtn && dataBlockList && addDataBlockBtn && saveDataBlockChangeBtn) {
	// update button
	var name = Dom.getById('name');
	var mainTable = Dom.getById('mainTable');
	var mainColumn = Dom.getById('mainColumn');
	
	mainTable.allowEvents(['change']);
	mainTable.on('change', function () {
		var uri = '/datablock/getColumnList/' + selectedDb + '/' + mainTable.get('value') + '/';
		loader.ajax(uri, null, function (error, path, data) {
			if (error || !data) {
				return alert(error);
			}
			mainColumn.removeAllChildren();
			var option = mainColumn.createChild('option');
			option.set('value', '');
			option.text(text.select);
			var list = data.list || [];
			for (var i = 0, len = list.length; i < len; i++) {
				var option = mainColumn.createChild('option');
				if (window.dataBlockMainColumn === list[i].field) {
					option.set('selected', true);
				}
				option.set('value', list[i].field);
				option.text(list[i].field);
				
			}
		});
	});
	mainTable.emit('change');
	
	var descVal = descEditor.instanceById('description').getContent(); 
	saveUpdateBtn.on('tapend', function () {
		// extract values
		var nameVal = name.get('value');
		var mainTableVal = mainTable.get('value');
		var mainColumnVal = mainColumn.get('value');
		var descVal = descEditor.instanceById('description').getContent(); 
		// check for required fileds
		if (!nameVal || !mainTableVal || !mainColumnVal) {
			return alert(text.missingRequiredMsg);
		}
		var uri = '/datablock/updateDataBlockSource/' + selectedDb + '/' + dataBlockSourceId + '/';
		loader.ajax(uri, { name: nameVal, mainTable: mainTableVal, mainColumn: mainColumnVal, desc: descVal }, function (error, path, res) {
			if (error) {
				return alert(text.failedMsg);
			}
			window.location.href = res.redirectUri;
		});
	});
	
	// add data block button
	addDataBlockBtn.on('tapend', function () {
		addDataBlock();
	});

	function addDataBlock(id, nameValue, requiredValue, typeValue, limitValue, srcTableValue, srcRefColumnValue, srcColumnValue, refTableValue, refDisplayValue, refColumnValue, bg) {
		// box container
		var box = dataBlockList.createChild('div');
		box.setClassName('box');
		box.setStyle({ background: bg || '#efefef' });
		if (id) {
			box.id = id;
			box.setClassName('box dataBlockId');
		}
		// header
		var header = box.createChild('div');
		header.setClassName('title');
		header.text(text.dataBlock);
		// remove button
		var removeBtn = header.createChild('div');
		removeBtn.setClassName('delete-button');
		removeBtn.setStyle({ cssFloat: 'right' });
		removeBtn.width(20);
		removeBtn.height(20);
		removeBtn.on('tapend', function () {
			if (id) {
				// remove from database
				var res = confirm(text.deleteConfirmMsg.replace('$1', nameValue));
				if (res) {
					deleteDataBlock(id);
				}
			} else {
				box.remove();
			}
		});
		// data block name
		var nameArea = box.createChild('div');
		nameArea.setClassName('area');
		var name = nameArea.createChild('p');
		name.html(text.name + '<span class="red">' + text.required + '</span>');
		var nameInput = nameArea.createChild('input');
		nameInput.setClassName('dataBlockName');
		nameInput.setStyle({ width: '500px' });
		nameInput.set('type', 'text');
		nameInput.exec('focus');
		if (nameValue) {
			nameInput.set('value', nameValue);
		}
		// data block required filed
		var reqArea = box.createChild('div');
		reqArea.setClassName('area');
		var req = reqArea.createChild('p');
		req.html(text.requiredField + '<span class="red">' + text.required + '</span>');
		var reqInput = reqArea.createChild('input');
		reqInput.setClassName('dataBlockRequired');
		reqInput.set('type', 'checkbox');
		if (requiredValue) {
			requiredValue = Number(requiredValue);
			if (requiredValue) {
				reqInput.set('checked', true);
			}
		}
		// data block type
		var typeArea = box.createChild('div');
		typeArea.setClassName('area');
		var type = typeArea.createChild('p');
		type.html(text.dataType + '<span class="red">' + text.required + '</span>' + ' / ' + text.dataLengthLimit + '<span class="red">' + text.required + '</span>');
		var typeSelect = typeArea.createChild('select');
		typeSelect.setClassName('dataBlockType');
		for (var i = 0, len = dataBlockTypes.length; i < len; i++) {
			var option = typeSelect.createChild('option');
			if (typeValue === dataBlockTypes[i].id) {
				option.set('selected', true);
			}
			option.set('value', dataBlockTypes[i].id);
			option.text(dataBlockTypes[i].name);
		}
		// data length limit
		var limitInput = typeArea.createChild('input');
		limitInput.setClassName('dataBlockLimit');
		limitInput.set('type', 'text');
		limitInput.allowEvents(['keyup']);
		limitInput.on('keyup', function (event) {
			var val = limitInput.get('value');
			// only integer allowed 
			var allowed = '';
			for (var i = 0, len = val.length; i < len; i++) {
				if (val[i].match(/^[\d]+$/)) {
					allowed += val[i];
				}
			}
			limitInput.set('value', allowed);
		});
		if (limitValue) {
			limitInput.set('value', limitValue);
		}
		// data block source table name
		var srcTableArea = box.createChild('div');
		srcTableArea.setClassName('area');
		var srcTable = srcTableArea.createChild('p');
		srcTable.html(text.srcTable + '<span class="red">' + text.required + '</span>' + ' / ' + text.mainRefColumn +  '<span class="red">' + text.required + '</span>' + ' / ' + text.srcTableColumn + '<span class="red">' + text.required + '</span>');
		var srcTableSelect = srcTableArea.createChild('select');
		srcTableSelect.setClassName('dataBlockSrc');
		srcTableSelect.allowEvents(['change']);
		var srcTableOption = srcTableSelect.createChild('option');
		srcTableOption.set('value', '');
		srcTableOption.text(text.select);
		var selected = false; 
		for (var i = 0, len = tableList.length; i < len; i++) {
			var srcTableOption = srcTableSelect.createChild('option');
			if (!selected && srcTableValue === tableList[i]) {
				srcTableOption.set('selected', true);
				selected = true;
			} else if (!selected && window.dataBlockMainTable === tableList[i]) {
				srcTableOption.set('selected', true);
			}
			srcTableOption.set('value', tableList[i]);
			srcTableOption.text(tableList[i]); 
		}
		// data block source table reference column
		var srcTableRefColumnSelect = srcTableArea.createChild('select');
		srcTableRefColumnSelect.setClassName('dataBlockSrcRefColumn');
		// set up ajax call on change to populate column names of selected table
		srcTableSelect.on('change', function () {
			var uri = '/datablock/getColumnList/' + selectedDb + '/' + srcTableSelect.get('value') + '/';
			loader.ajax(uri, null, function (error, path, data) {
				if (error || !data) {
					return alert(error);
				}
				srcTableRefColumnSelect.removeAllChildren();
				var option = srcTableRefColumnSelect.createChild('option');
				option.set('value', '');
				option.text(text.select);
				var list = data.list || [];
				for (var i = 0, len = list.length; i < len; i++) {
					var option = srcTableRefColumnSelect.createChild('option');
					if (srcRefColumnValue === list[i].field) {
						option.set('selected', true);
					}
					option.set('value', list[i].field);
					option.text(list[i].field);
					
				}
			});
		});
		// data block source table column
		var srcTableColumnSelect = srcTableArea.createChild('select');
		srcTableColumnSelect.setClassName('dataBlockSrcColumn');
		// set up ajax call on change to populate column names of selected table
		srcTableSelect.on('change', function () {
			var uri = '/datablock/getColumnList/' + selectedDb + '/' + srcTableSelect.get('value') + '/';
			loader.ajax(uri, null, function (error, path, data) {
				if (error || !data) {
					return alert(error);
				}
				srcTableColumnSelect.removeAllChildren();
				var option = srcTableColumnSelect.createChild('option');
				option.set('value', '');
				option.text(text.select);
				var list = data.list || [];
				for (var i = 0, len = list.length; i < len; i++) {
					var option = srcTableColumnSelect.createChild('option');
					if (srcColumnValue === list[i].field) {
						option.set('selected', true);
					}
					option.set('value', list[i].field);
					option.text(list[i].field);
					
				}
			});
		});
		// emit change to update column
		srcTableSelect.emit('change');
		// data block reference table name
		var refTablePolulated = false;
		var refTableArea = box.createChild('div');
		refTableArea.setClassName('area');
		var refTable = refTableArea.createChild('p');
		refTable.html(text.refTable + '<span class="grey">' + text.optional + '</span>' + ' / ' + text.refTableColumn + ' / ' + text.refTableDisplayColumn);
		var refTableSelect = refTableArea.createChild('select');
		refTableSelect.setClassName('dataBlockRef');
		var refTableOption = refTableSelect.createChild('option');
		refTableOption.set('value', '');
		refTableOption.text(text.select); 
		for (var i = 0, len = tableList.length; i < len; i++) {
			var refTableOption = refTableSelect.createChild('option');
			if (refTableValue === tableList[i]) {
				refTableOption.set('selected', true);
				refTablePolulated = true;
			}
			refTableOption.set('value', tableList[i]);
			refTableOption.text(tableList[i]); 
		}
		// data block reference table column
		var refTableColumnSelect = refTableArea.createChild('select');
		refTableColumnSelect.setClassName('dataBlockRefColumn');
		// set up ajax call on change to populate column names of selected table
		refTableSelect.allowEvents(['change']);
		refTableSelect.on('change', function () {
			var uri = '/datablock/getColumnList/' + selectedDb + '/' + refTableSelect.get('value') + '/';
			loader.ajax(uri, null, function (error, path, data) {
				if (error || !data) {
					return alert(error);
				}
				// display column
				refTableDisplayColumnSelect.removeAllChildren();
				var option = refTableDisplayColumnSelect.createChild('option');
				option.set('value', '');
				option.text(text.select);
				var list = data.list || [];
				for (var i = 0, len = list.length; i < len; i++) {
					var option = refTableDisplayColumnSelect.createChild('option');
					if (refDisplayValue === list[i].field) {
						option.set('selected', list[i].field);
					}
					option.set('value', list[i].field);
					option.text(list[i].field);
					
				}
				// value column
				refTableColumnSelect.removeAllChildren();
				var option2 = refTableColumnSelect.createChild('option');
				option2.set('value', '');
				option2.text(text.select);
				var list = data.list || [];
				for (var i = 0, len = list.length; i < len; i++) {
					var option2 = refTableColumnSelect.createChild('option');
					if (refColumnValue === list[i].field) {
						option2.set('selected', true);
					}
					option2.set('value', list[i].field);
					option2.text(list[i].field);
					
				}
			});
		});
		// data block reference table column
		var refTableDisplayColumnSelect = refTableArea.createChild('select');
		refTableDisplayColumnSelect.setClassName('dataBlockRefDisplayColumn');
		if (refTablePolulated) {
			// emit change to update column and display column
			refTableSelect.emit('change');
		}
	}
	
	// save data block update button
	saveDataBlockChangeBtn.on('tapend', function () {
		// extract list of data block data
		var names = Dom.query('.dataBlockName');
		var requireds = Dom.query('.dataBlockRequired');
		var types = Dom.query('.dataBlockType');
		var limits = Dom.query('.dataBlockLimit');
		var srcTables = Dom.query('.dataBlockSrc');
		var srcColumns = Dom.query('.dataBlockSrcColumn');
		var srcRefColumns = Dom.query('.dataBlockSrcRefColumn');
		var refTables = Dom.query('.dataBlockRef');
		var refDisplayColumns = Dom.query('.dataBlockRefDisplayColumn');
		var refColumns = Dom.query('.dataBlockRefColumn');
		// id will be present for edit only
		var ids = Dom.query('.dataBlockId');
		// create list of bata block objects
		var dataBlockList = [];
		for (var i = 0, len = names.length; i < len; i++) {
			var dataBlock = {
				id: (ids[i] && ids[i].id) ? ids[i].id : null,
				name: names[i].get('value'),
				required: requireds[i].get('checked'),
				type: types[i].get('value'),
				limit: limits[i].get('value'),
				srcTable: srcTables[i].get('value'),
				srcRefColumn: srcRefColumns[i].get('value'),
				srcColumn: srcColumns[i].get('value'),
				refTable: refTables[i].get('value'),
				refDisplayColumn: refDisplayColumns[i].get('value'),
				refColumn: refColumns[i].get('value')
			};
			// check for missing required fields
			if (!dataBlock.name || !dataBlock.type || !dataBlock.srcTable || !dataBlock.srcRefColumn || !dataBlock.srcColumn) {
				return alert(text.missingRequiredMsg);
			}
			// if refTable is selected refDisplayColumn and refColumn will be required
			if (dataBlock.refTable) {
				if (!dataBlock.refDisplayColumn || !dataBlock.refColumn) {
					return alert(text.missingRequiredMsg);
				}
			}
			dataBlockList.push(dataBlock);
		}
		// send update request
		var uri = '/datablock/updateDataBlocks/' + selectedDb + '/' + dataBlockSourceId + '/';
		loader.ajax(uri, { dataBlocks: dataBlockList }, function (error, path, res) {
			if (error) {
				return alert(error);
			}
			window.location.href = res.redirectUri;
		});
	});

	function deleteDataBlock(id) {
		var uri = '/datablock/deleteDataBlock/' + selectedDb + '/' + dataBlockSourceId + '/';
		loader.ajax(uri, { id: id }, function (error, path, res) {
			if (error) {
				return alert(text.failedMsg);
			}
			window.location.href = res.redirectUri;
		});
	}
	
	datablock.addDataBlock = addDataBlock;
}

datablock.deleteDataBlockSource = deleteDataBlockSource;

}());
