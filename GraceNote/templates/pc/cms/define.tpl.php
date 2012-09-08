<? Load::template('common/doc_type'); ?>
<html>
<head>
<? Load::template('common/meta'); ?>
<script type="text/javascript">
form_auto_focus();

var id = 0;
var column_data = false;
var style = 'style="margin-left: 10px; width: 50px; cursor: pointer;"';
var style2 = 'style="margin: 5px; position: relative; top: -2px; width: 50px; cursor: pointer;"';
var table_data = {};

// for edit mode
<? if ($DATA && $DATA['tables']): ?>
$('#definition_area').ready(function () {
<? foreach ($DATA['tables'] as $table_name => $table): ?>
table_data['<?= $table_name; ?>'] = {language_common_column: '<?= $table['value']; ?>'};
table_data['<?= $table_name; ?>']['columns'] = {};
<? foreach ($table['cms_id'] as $item): ?> 
if (!table_data['<?= $table_name; ?>']['columns']['<?= $item['column_name']; ?>']){
	table_data['<?= $table_name; ?>']['columns']['<?= $item['column_name']; ?>'] = [];
}
table_data['<?= $table_name; ?>']['columns']['<?= $item['column_name']; ?>']['<?= $item['lang_id']; ?>'] = {label: '<?= $item['label']; ?>', column_name: '<?= $item['column_name']; ?>'};
<? endforeach; ?>
add_definition('<?= $table_name; ?>');
<? endforeach; ?>
});
<? endif; ?>

function add_definition (selected) {
	var def = document.getElementById('definition_area');
	if (def){
		id++;
		var def_html = '<div class="th blue-label" style="text-shadow: 0 0 0 #fff; border-left: 1px dotted #ccc; border-right: 1px dotted #ccc;">';
		def_html += '<a onclick="remove_definition(' + id + ');" ' + style2 + ' class="delete_btn"><?= text($CONTENTS, 'delete'); ?></a>';
		def_html += '<?= text($CONTENTS, 'definition'); ?></div>';
		def_html += '<div class="td"><?= text($CONTENTS, 'table_name'); ?> : <select id="table_select_' + id + '" name="table" onchange="get_columns(this, ' + id + ');">';
		<? foreach($TABLE_LIST as $table): ?>
		var s = '';
		if (selected == '<?= $table['table_name']; ?>'){
			s = 'selected="selected"';
		}
		def_html += '<option value="<?= $table['table_name']; ?>" ' + s + '><?= $table['table_name']; ?></option>';
		<? endforeach; ?>
		def_html += '</select>';
		def_html += '<a onclick="insert_column(' + id + ');" ' + style + ' class="btn"><?= text($CONTENTS, 'add_column'); ?></a>';
		def_html += '<div id="unique_column_' + id + '" style="float: right;"></div>';
		def_html += '</div>';
		def_html += '<div class="td" style="clear: both;" id="container_' + id + '"></div>';
		var section = document.createElement('div');
		section.setAttribute('id', 'definition_' + id);
		section.innerHTML = def_html;
		def.appendChild(section);
		// for edit mode 
		
		console.log('table_data', table_data);
		
		if (selected){
			var selector = document.getElementById('table_select_' + id);
			if (selector){
				get_columns(selector, id, table_data[selected]);
			}
		}
		return id;
	}
}

function remove_definition (id) {
	$('#definition_' + id).ready(function (obj) {
		obj.remove();
	});
}

function get_columns (select, id, table) {
	if (select && select.options){
		var options = select.options;
		var len = options.length;
		for (var i = 0; i < len; i++){
			if (options[i].selected){
				// change the name
				select.setAttribute('name', 'table[' + options[i].value + '][table_name]');
				// get columns of the selected table
				if (table){
					var columns = table['columns'];
					for (var column_name in columns){
						var params = { table_name: options[i].value, 
								id: id, 
								column_name: columns[column_name], 
								language_common_column: table['language_common_column']};
						window.setTimeout(function () {
							json('/cms/table_columns/' + options[i].value, delegate(change_table, params));
						}, 100);	
					}
				}
				else {
					var params = {table_name: options[i].value, id: id, column_data: false, language_common_column: false};
					json('/cms/table_columns/' + options[i].value, delegate(change_table, params));
				}
				break;
			}
		}
	}
}

function change_table (data, params) {
	var table_name = params.table_name;
	var id = params.id;
	// add unique column selector for multi-lingual table
	var multilanguage = false;
	for (var i = 0; i < data.length; i++){
		if (data[i].field == 'lang_id' && table_name != 'languages'){
			$('#unique_column_' + id).ready(function (obj) {
				obj.innerHTML = '';
				unique_column(table_name, obj, data, params.language_common_column);
			});
			multilanguage = true;
			break;
		}
	}
	if (!multilanguage){
		$('#unique_column_' + id).ready(function (obj) {
			obj.innerHTML = '';
		});	
	}
	// add column selector
	
	console.log(params);
	
	show_columns(data, table_name, id, params);
}

function unique_column (table_name, obj, data, language_common_column) {
	var label = '<?= text($CONTENTS, 'lang_common_column'); ?>';
	var html = label + ' : <select name="table[' + table_name + '][lang_common_column]">';
	for (var i = 0; i < data.length; i++){
		var s = '';
		if (language_common_column == data[i].field){
			s = 'selected="selected"';
		}
		html += '<option ' + s + ' value="' + data[i].field + '">' + data[i].field + '</option>';
	}
	html += '</select>';
	var select = document.createElement('div');
	select.style.margin = '1px 0';
	select.style.padding = '0'; 
	select.innerHTML = html;
	obj.appendChild(select);
}

function show_columns (data, table_name, id, params) {
	$('#container_' + id).ready(function (obj) {
		if (data && obj){
			column_data = data;
			obj.innerHTML = '';
			obj.counter = null;
			add_column(table_name, obj, data, params);
		}
	});
	
}

function insert_column (id) {
	$('#container_' + id).ready(function (obj) {
		if (obj && obj.table_name && column_data){
			add_column(obj.table_name, obj, column_data);
		}	
	});
}

function add_column (table_name, obj, data, params) {
	if (!obj.counter){
		obj.counter = 0;
		obj.table_name = table_name;
	}
	// check for edit mode
	var column_name = false;
	if (params && params.column_data){
		for (var i = 1; i < params.column_data.length; i++){
			var cdata = params.column_data[i];
			if (cdata && cdata.column_name){
				column_name = cdata.column_name;
			}
		}
	}
	var key = table_name + obj.counter;
	var span = 'style="margin-left: 5px; font-size: 12px;"';
	var cb = 'style="vertical-align: middle;"';
	var attr_html = '<p style="border-top: 1px dotted #999; margin: 0; margin-top: 4px; padding: 4px 0;">';
	<? foreach($LANGUAGES as $lang): ?>
	attr_html += '<p style="margin: 0; padding: 0;"><span '+ span + '><?= text($CONTENTS, 'label'); ?>(<?= $lang['name']; ?>):</span>';
	// edit mode
	var val = '';
	if (params && params.column_data){
		for (var lang_id = 1; lang_id < params.column_data.length; lang_id++){
			if (lang_id == <?= $lang['lang_id']; ?>){
				val = params.column_data[lang_id].label;
				break;
			}	
		}
	}	
	attr_html += '<input ' + cb + ' type="text" size="40" name="table[' + table_name + '][columns][' + obj.counter + '][label][<?= $lang['lang_id']; ?>]" value="' + val + '" /></p>';
	<? endforeach; ?>
	attr_html += '</p><p style="border-top: 1px dotted #999; margin: 0; padding: 4px;">';
	attr_html += '<span '+ span + '><?= text($CONTENTS, 'required'); ?>:</span>';
	attr_html += '<input ' + cb + ' type="checkbox" name="table[' + table_name + '][columns][' + obj.counter + '][attributes][required]" />';
	attr_html += '<span '+ span + '><?= text($CONTENTS, 'allow_html'); ?>:</span>';
	attr_html += '<input ' + cb + ' type="checkbox" name="table[' + table_name + '][columns][' + obj.counter + '][attributes][allow_html]" />';
	attr_html += '<span '+ span + '><?= text($CONTENTS, 'min_length'); ?>:</span>';
	attr_html += '<input ' + cb + ' type="number" onkeypress="filter(event, this, \'integer\');" size="10" name="table[' + table_name + '][columns][' + obj.counter + '][attributes][min_length]" />';
	attr_html += '<span '+ span + '><?= text($CONTENTS, 'max_length'); ?>:</span>';
	attr_html += '<input ' + cb + ' type="number" onkeypress="filter(event, this, \'integer\');" size="10" name="table[' + table_name + '][columns][' + obj.counter + '][attributes][max_length]" />';
	attr_html += '</p><p style="margin: 0; padding: 4px;"><span '+ span + '><?= text($CONTENTS, 'normal'); ?>:</span>';
	attr_html += '<input ' + cb + ' type="radio" name="table[' + table_name + '][columns][' + obj.counter + '][attributes][type]" value="normal" />';
	attr_html += '<span '+ span + '><?= text($CONTENTS, 'password'); ?>:</span>';
	attr_html += '<input ' + cb + ' type="radio" name="table[' + table_name + '][columns][' + obj.counter + '][attributes][type]" value="password" />';
	attr_html += '<span '+ span + '><?= text($CONTENTS, 'midea'); ?>:</span>';
	attr_html += '<input ' + cb + ' type="radio" name="table[' + table_name + '][columns][' + obj.counter + '][attributes][type]" value="media" />';
	attr_html += '<span '+ span + '><?= text($CONTENTS, 'reference'); ?>:</span>';
	attr_html += '<input ' + cb + ' type="radio" name="table[' + table_name + '][columns][' + obj.counter + '][attributes][type]" value="reference" />';
	attr_html += '<select style="vertical-align: middle;" name="table[' + table_name + '][columns][' + obj.counter + '][attributes][reference][table_name]" onchange="get_columns(this, ' + key + ');">';
	<? foreach($TABLE_LIST as $table): ?>
	attr_html += '<option value="<?= $table['table_name']; ?>"><?= $table['table_name']; ?></option>';
	<? endforeach; ?>
	attr_html += '</select>';
	attr_html += '<span id="container_' + key + '"></span><span></span>';
	attr_html += '</p>';
	var label = '&nbsp;<?= text($CONTENTS, 'column_name'); ?>';
	var html = label + ' : <select name="table[' + table_name + '][columns][' + obj.counter + '][column_name]">';
	for (var i = 0; i < data.length; i++){
		var s = '';
		if (column_name && column_name == data[i].field){
			s = 'selected="selected"';
		}
		html += '<option ' + s + ' value="' + data[i].field + '">' + data[i].field + '</option>';
	}
	html += '</select>';
	html += '<a onclick="remove_column(' + obj.counter + '); " ' + style + ' class="delete_btn"><?= text($CONTENTS, 'delete'); ?></a>';
	html += attr_html;
	var select = document.createElement('div');
	select.setAttribute('id', 'select_' + obj.counter);
	select.className = 'metalic-label';
	select.style.margin = '1px 0';
	select.style.padding = '2px'; 
	select.style.borderBottom = '1px dotted #666';
	select.innerHTML = html;
	obj.appendChild(select);
	obj.counter++;
}

function remove_column (id) {
	$('#select_' + id).ready(function (obj) {
		obj.remove();
	});
}
</script>
</head>
<body>
<? Load::template('common/cms_top'); ?>
<form action="/cms/save_define/" method="post">
<div class="container">
<div class="inner">
<div class="section">
<h3><img src="/img/system/book.png" width="25" />&nbsp;<?= text($CONTENTS, 'define_title'); ?></h3>
<div class="tr">
<!-- ID for edit mode -->
<? if ($ID): ?>
<input type="hidden" name="id" value="<?= $ID; ?>" />
<? endif; ?>
<!-- Title -->
<div class="th-top"><?= text($CONTENTS, 'content_title'); ?></div>
<? foreach($LANGUAGES as $lang): ?>
<div class="td">
<? if ($DATA): ?>
<!-- Edit -->
<? foreach ($DATA['id'] as $title): ?>
<? if ($lang['lang_id'] == $title['lang_id'] && $title['property'] == 'title'): ?>
<?= $lang['name']; ?> : <input type="text" name="title[<?= $lang['lang_id']; ?>]" value="<?= $title['value']; ?>" size="50" />
<? endif; ?>
<? endforeach; ?>
<? else: ?>
<!-- New -->
<?= $lang['name']; ?> : <input type="text" name="title[<?= $lang['lang_id']; ?>]" value="" size="50" />
<? endif; ?>
<input type="hidden" name="lang" value="<?= $lang['lang_id']; ?>" />
</div>
<? endforeach; ?>
</div>
<!-- Add Field -->
<div class="th"><?= text($CONTENTS, 'add_definition'); ?></div>
<div class="td"><div class="btn" style="width: 80px;" onclick="add_definition();"><?= text($CONTENTS, 'add_definition'); ?></div></div>
<!-- Definitioon Area -->
<div id="definition_area"></div>
<!-- Save Button -->
<div class="td right"><input type="submit" value="<?= text($CONTENTS, 'save'); ?>" /></div>
</div>
</div>
</div>
</form>
<? Load::template('common/footer'); ?>
</body>
</html>
