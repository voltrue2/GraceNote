// Loader.src.js is required
(function () {

var loader = new Loader();

window.blocker.setup(loader);

var table = new Dom(document.getElementById('table'));
var createTableBtn = new Dom(document.getElementById('createTableBtn'));
var addColumnBtn = new Dom(document.getElementById('addColumnBtn'));
var columns = new Dom(document.getElementById('columns'));
var columnList = [];

addColumnBtn.on('tapend', function () {
	addColumn();
});

createTableBtn.on('tapend', function () {
	createTable();
});

function addColumn() {
	var box = columns.createChild('div', { clear: 'both' });
	// remove button
	var removeBtn = box.createChild('div', { cssFloat: 'left' });
	removeBtn.setClassName('delete-button');
	removeBtn.on('tapend', function () {
		var index = 0;
		for (var i = 0, len = columnList.length; i < len; i++) {
			if (columnList[i].name === nameInput) {
				index = i;
				break;
			}
		}
		columnList.splice(index, 1);
		box.remove();
		console.log(columnList);
	});
	var name = box.createChild('span');
	name.text(text.columnName);
	// column name input
	var nameInput = box.createChild('input');
	nameInput.setAttribute({ type: 'text' });
	nameInput.exec('focus');
	// column type select list
	var type = box.createChild('span');
	type.text(text.columnType);
	var typeInput = box.createChild('select');
	for (var key in columnTypes) {
		var option = typeInput.createChild('option');
		option.setAttribute({ value: columnTypes[key]});
		option.text(key);
	}
	// add colunm name and column type to the list
	columnList.push({name: nameInput, type: typeInput});
}

function createTable() {
	var tableName = table.get('value');
	var columns = [];
	for (var i = 0, len = columnList.length; i < len; i++) {
		columns.push({ name: columnList[i].name.get('value'), type: columnList[i].type.get('value') });
	}
	var uri = '/dbmanager/createNewTable/';
	loader.ajax(uri, { selectedDb: window.selectedDb, table: tableName, columns: columns }, function (error, requestPath, res) {
		if (error) {
			return alert(text.createNewTableFailed.replace('$1', tableName));
		}
		window.location.href = res.redirectUri;
	});
}

}());
