(function () {

	var loader = new Loader();

	window.buildTableDataEditor = buildTableDataEditor;
	window.buildTableDataCreator = buildTableDataCreator;

	function buildTableDataEditor(box, table, desc, data) {
		var rows = {};
		for (var i = 0, len = desc.length; i < len; i++) {
			var item = desc[i];
			if (data[item.field] !== undefined) {
				rows[item.field] = createRow(box, item, data[item.field]);		
			}
		}
		// save button
		var saveBtn = box.createChild('div');
		saveBtn.setClassName('text-button');
		saveBtn.text(window.text.save);
		saveBtn.on('tapend', function () {
			var send = {};
			for (var col in rows) {
				send[col] = rows[col].get('value');
			}
			var uri = '/tabledata/updateData/' + window.selectedDb + '/' + table + '/';
			loader.ajax(uri, { data: send, prevData: data }, function (error, path, res) {
				if (error) {
					return alert(error);
				}
				window.location.reload();
			} );
		});	
	}

	function buildTableDataCreator(box, mainId, table, column, desc) {
		var rows = {};
		for (var i = 0, len = desc.length; i < len; i++) {
			var item = desc[i];
			var value = '';
			var lock = false;
			if (item.field === column) {
				value = mainId;
				lock = true;
			}
			rows[item.field] = createRow(box, item, value, lock);		
		}
		// save button
		var saveBtn = box.createChild('div');
		saveBtn.setClassName('text-button');
		saveBtn.text(window.text.save);
		saveBtn.on('tapend', function () {
			var uri = '/tabledata/createData/' + window.selectedDb + '/' + table + '/' + column + '/';
			var send = {};
			for (var col in rows) {
				var val = rows[col].get('value');
				if (val !== '') {
					send[col] = val;
				}
			}
			console.log(uri, send);	
			loader.ajax(uri, { data: send }, function (error, path, res) {
				if (error) {
					return alert(error);
				}
				window.location.reload();
			});
		});
	}

	function createRow(box, def, data, lock) {
		var row = box.createChild('div');
		var label = row.createChild('div');
		label.setClassName('title');
		label.text(def.field);
		var area = row.createChild('div', { padding: '10px' });
		area.setClassName('area');
		var input;
		if (data && data.length > 50) {
			input = area.createChild('textarea', { width: '300px', height: '100px' });
		} else {
			input = area.createChild('input', { width: '300px' });
		}
		if (lock) {
			input.set('readOnly', true);
		}
		input.set('value', data);
		if (def.type === 'int') {
			input.allowEvents(['keyup']);
			input.on('keyup', function () {
				var val = input.get('value');
				// only integer allowed 
				var allowed = '';
				for (var i = 0, len = val.length; i < len; i++) {
					if (val[i].match(/^[\d]+$/)) {
						allowed += val[i];
					}
				}
				input.set('value', allowed);				
			});
		}
		return input;
	}

}());
