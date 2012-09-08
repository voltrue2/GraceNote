<? Load::template('common/doc_type'); ?>
<html>
<head>
<? Load::template('common/meta'); ?>
<script type="text/javascript" src="/js/nicEdit.js?<?= filemtime($JS_PATH.'nicEdit.js'); ?>"></script>
<script type="text/javascript">
// bulk delete
function check_all(){
	$('.bulk_delete').ready(function(obj){
		if (!obj.checked){
			obj.checked = true;
		}
		else {
			obj.checked = false;
		};
	});
};

function bulk_delete(msg){
	var answer = confirm(msg);
	if (answer) {
		var ids = '';
		$('.bulk_delete').ready(function(obj){
			if (obj.checked){
				ids += obj.id+'-';
			};
		});
		if (ids){
			window.location.href = '/manage/bulk_delete/<?= $TABLE; ?>/'+ids;
		};
	};
};

// column reference
function get_table_columns(target_id, select_id, column_type){
	var list = select_id;
	if (list){
		var ref_table = false;
		for (var i = 0; i < list.options.length; i++){
			if (list.options[i].selected){
				ref_table = list.options[i].value;
				break;
			};
		};
		if (ref_table){
			json('/manage/ref_table_column/<?= $TABLE; ?>/'+ref_table+'/', delegate(create_column_list, {target_id:target_id, column_type:column_type}));
		};
	};
};

function create_column_list(data, params){
	var target_id = params.target_id;
	$('#'+target_id).ready(function(obj){
		if (obj.column_labels){
			obj.column_labels.remove();
			obj.column_labels = false;
		};
		obj.column_labels = obj.create('div');
		var sel = '<?= text($CONTENTS, 'column_reference_label_tag'); ?>&nbsp;:&nbsp;<select name="reference[label]">';
		sel += '<option><?= text($CONTENTS, 'please_select'); ?></option>';
		if (data){
			for (var i = 0; i < data.length; i++){
				sel += '<option value="'+data[i]['field']+'">'+data[i]['field']+'</option>';
			};
		};
		sel += '</select>';
		obj.column_labels.innerHTML = sel;
		if (obj.column_list){
			obj.column_list.remove();
			obj.column_list = false;
		};
		obj.column_list = obj.create('div');
		var sel = '<?= text($CONTENTS, 'column_reference_column_tag'); ?>&nbsp;:&nbsp;<select name="reference[column]">';
		sel += '<option><?= text($CONTENTS, 'please_select'); ?></option>';
		if (data){
			for (var i = 0; i < data.length; i++){
				sel += '<option value="'+data[i]['field']+'">'+data[i]['field']+'</option>';
			};
		};
		sel += '</select>';
		obj.column_list.innerHTML = sel;
	});
};
// set up table meta data
var meta_data = new Array();
<? if($META): ?>
<? foreach($META as $column => $att): ?>
meta_data['<?= $column; ?>'] = new Array();
<? foreach($att as $name => $value): ?>
meta_data['<?= $column; ?>']['<?= $name; ?>'] = '<?= $value; ?>';
<? endforeach; ?>
<? endforeach; ?>
<? endif; ?>
/*
// set up sections for edit display
var sections = new Array();
sections.table_struc_area = true;
sections.data_entry_area = true;
sections.record_list = true;
sections.table_desc_area = true;

change_display();

function change_display(show_this){
	if (show_this){
		var section = show_this;
	}
	else {
		var sep = explode('#', window.location.href);
		if (sep[1]){
			var section = sep[1];
			window.location.href = '#'+section;
		}
		else {
			var section = 'data_entry_area';
		};
	};
	for (var i in sections){
		if (section == i){
			$('#'+section).ready(function (obj){
				obj.show();
			});
		}
		else {
			$('#'+i).ready(function (obj){
				if (!obj.null_object){
					obj.hide();
				};
			});
		};
	};
};
*/

function change_display(show_this){
	/*
	var el = $('#'+show_this.replace('_contents', ''));
	if (el.toggle){
		el.toggle();
	};
	*/
};


// set up image window
var w = new Windows();
w.single();
//********** image list 
var box = false;
var current_path = '/cimgmanager/image_list/';
var media_path = '/cimgmanager/media_list/';
var selected = false;
var path_name = false;
var gap = 30;
var x = gap;
var y = gap;

function open_image_list(id){
	if (agent.browser == 'Explorer'){
		//window.open('/cimgmanager/', '_blank');
		var obj = $('#'+id+'_label');
		obj.target_id = id;
		json(media_path+'?data_type=json', delegate(open_ie_image_picker, obj));
	}
	else {
		json(media_path+'?data_type=json', delegate(open_image_picker, id));
	};
};

function open_image_picker(data, id){
	if (data && !box){
		w.create(delegate(construct_list, {data:data, id:id, media:media_path}), 350, 400, '<?= text($CONTENTS, 'image_list_title', 'Image Picker List'); ?>');
	};
};

function open_ie_image_picker(data, obj){
	window.__nicEditor_inject_image_path = false;
	if (data){
		if (box){
			box.remove();
		};
		box = obj.create('div');
		box.css({
			'height': '150px',
			'overflow': 'scroll',
			'background-color': '#FFFFFF'
		});
		var list = data.list;
		var path = data.current_path;
		// image list
		for (var i = 0; i < list.length; i++){
			var item = list[i];
			var cell = box.create('div');
			cell.setAttribute('id', 'id_'+i);
			cell.css({
				'background-gradient': 'linear 90 #FFFFFF #FFFFFF',
				'border-bottom': '1px dotted #CCCCCC',
				'padding': '3px',
				'font-size': '12px'
			});
			if (i == 0){
				cell.css({'border-top': '1px dotted #CCCCCC'});
			};
			cell.mouseover(delegate(mouse_action, {cell:cell, color: '#FFCCCC #FFFFFF'}));
			cell.mouseout(delegate(mouse_action, {cell:cell, color: '#FFFFFF #FFFFFF'}));
			var thumb = cell.create('img');
			thumb.setAttribute('width', 20);
			thumb.setAttribute('height', 20);
			thumb.css({
				'border': '1px solid #CCCCCC',
				'padding': '1px'
			});
			if (item.type == 'file'){
				thumb.setAttribute('width', 40);
				thumb.setAttribute('height', 40);
				var path = item.thumb;
				if (item.file_type == 'csv'){
					path = '/img/system/excel.png';
				}
				else if (item.file_type == 'xls'){
					path = '/img/system/excel.png';
				}
				else if (item.file_type == 'doc'){
					path = '/img/system/doc.png';
				}
				else if (item.file_type == 'pdf'){
					path = '/img/system/pdf.png';
				}
				else if (item.file_category == 'media'){
					path = '/img/system/media.png';
				};
				thumb.setAttribute('src', path);
				cell.click(delegate(set_image_field, {item:item, id:obj.target_id}));
			}
			else {
				thumb.css({'border': '0'});
				if (item.name == '..'){
					thumb.setAttribute('width', 20);
					thumb.setAttribute('height', 20);
					thumb.setAttribute('src', '/img/system/go_back.png');
				}
				else {
					thumb.setAttribute('width', 30);
					thumb.setAttribute('height', 30);
					thumb.setAttribute('src', '/img/system/folder.png');
				};
				cell.click(delegate(function(event, target){
					json(media_path+'?current_dir=/'+target.path+'&data_type=json', delegate(read_ie_image_picker_list, obj));
				}, item));
			};
			var name = cell.create('span');
			name.css({'padding-left': '5px'});
			name.innerHTML = item.name;
		};
	};
};

function read_ie_image_picker_list(data, obj){
	window.__nicEditor_inject_image_path = false;
	if (data){
		if (box){
			box.remove();
		};
		box = obj.create('div');
		box.css({
			'height': '150px',
			'overflow': 'scroll',
			'background-color': '#FFFFFF'
		});
		var list = data.list;
		var path = data.current_path;
		// image list
		for (var i = 0; i < list.length; i++){
			var item = list[i];
			var cell = box.create('div');
			cell.setAttribute('id', 'id_'+i);
			cell.css({
				'background-gradient': 'linear 90 #FFFFFF #FFFFFF',
				'border-bottom': '1px dotted #CCCCCC',
				'padding': '3px',
				'font-size': '12px'
			});
			if (i == 0){
				cell.css({'border-top': '1px dotted #CCCCCC'});
			};
			cell.mouseover(delegate(mouse_action, {cell:cell, color: '#FFCCCC #FFFFFF'}));
			cell.mouseout(delegate(mouse_action, {cell:cell, color: '#FFFFFF #FFFFFF'}));
			var thumb = cell.create('img');
			thumb.css({
				'border': '1px solid #CCCCCC',
				'padding': '1px'
			});
			if (item.type == 'file'){
				thumb.setAttribute('width', 40);
				thumb.setAttribute('height', 40);
				var path = item.thumb;
				if (item.file_type == 'csv'){
					path = '/img/system/excel.png';
				}
				else if (item.file_type == 'doc'){
					path = '/img/system/doc.png';
				}
				else if (item.file_type == 'xls'){
					path = '/img/system/excel.png';
				}
				else if (item.file_type == 'pdf'){
					path = '/img/system/pdf.png';
				}
				else if (item.file_category == 'media'){
					path = '/img/system/media.png';
				};
				thumb.setAttribute('src', path);
				cell.click(delegate(set_image_field, {item:item, id:obj.target_id}));
			}
			else {
				thumb.css({'border': '0'});
				if (item.name == '..'){
					thumb.setAttribute('src', '/img/system/go_back.png');
					thumb.setAttribute('width', 20);
					thumb.setAttribute('height', 20);
				}
				else {
					thumb.setAttribute('src', '/img/system/folder.png');
					thumb.setAttribute('width', 30);
					thumb.setAttribute('height', 30);
				};
				cell.click(delegate(function(event, target){
					json(media_path+'?current_dir=/'+target.path+'&data_type=json', delegate(read_ie_image_picker_list, obj));
				}, item));
			};
			var name = cell.create('span');
			name.css({'padding-left': '5px'});
			name.innerHTML = item.name;
		};
	};
};

function read_image_list(data){
	if (data && !box){
		w.create(delegate(construct_list, {data:data}), 350, 400, '<?= text($CONTENTS, 'image_list_title', 'Image Picker List'); ?>');
	};
};

function read_ie_image_list(data, obj){
	window.__nicEditor_inject_image_path = false;
	if (data){
		if (box){
			box.remove();
		};
		box = obj.create('div');
		box.css({
			'height': '150px',
			'overflow': 'scroll',
			'background-color': '#FFFFFF'
		});
		var list = data.list;
		var path = data.current_path;
		// image list
		for (var i = 0; i < list.length; i++){
			var item = list[i];
			var cell = box.create('div');
			cell.setAttribute('id', 'id_'+i);
			cell.css({
				'background-gradient': 'linear 90 #FFFFFF #FFFFFF',
				'border-bottom': '1px dotted #CCCCCC',
				'padding': '3px',
				'font-size': '12px'
			});
			if (i == 0){
				cell.css({'border-top': '1px dotted #CCCCCC'});
			};
			cell.mouseover(delegate(mouse_action, {cell:cell, color: '#FFCCCC #FFFFFF'}));
			cell.mouseout(delegate(mouse_action, {cell:cell, color: '#FFFFFF #FFFFFF'}));
			var thumb = cell.create('img');
			thumb.css({
				'border': '1px solid #CCCCCC',
				'padding': '1px'
			});
			if (item.type == 'file'){
				thumb.setAttribute('width', 40);
				thumb.setAttribute('height', 40);
				var path = item.thumb;
				if (item.file_type == 'csv'){
					path = '/img/system/excel.png';
				}
				else if (item.file_type == 'pdf'){
					path = '/img/system/pdf.png';
				}
				else if (item.file_type == 'doc'){
					path = '/img/system/doc.png';
				}
				else if (item.file_type == 'xls'){
					path = '/img/system/excel.png';
				}
				else if (item.file_category == 'media'){
					path = '/img/system/media.png';
				};
				thumb.setAttribute('src', path);
				cell.click(delegate(select_image, {path:item.file_path, cell:cell, item:item}));
			}
			else {
				thumb.css({'border': '0'});
				if (item.name == '..'){
					thumb.setAttribute('src', '/img/system/go_back.png');
					thumb.setAttribute('width', 20);
					thumb.setAttribute('height', 20);
				}
				else {
					thumb.setAttribute('src', '/img/system/folder.png');
					thumb.setAttribute('width', 30);
					thumb.setAttribute('height', 30);
				};
				cell.click(delegate(function(event, target){
					json(current_path+'?current_dir=/'+target.path+'&data_type=json', delegate(read_ie_image_list, obj));
				}, item));
			};
			var name = cell.create('span');
			name.css({'padding-left': '5px'});
			name.innerHTML = item.name;
		};
	};
};

function construct_list(holder, params){
	var data = params.data;
	var list = data.list;
	var path = data.current_path;
	var container = holder.create('div');
	// image list
	for (var i = 0; i < list.length; i++){
		var item = list[i];
		var cell = container.create('div');
		cell.setAttribute('id', 'id_'+i);
		cell.css({
			'background-gradient': 'linear 90 #FFFFFF #FFFFFF',
			'border-bottom': '1px dotted #CCCCCC',
			'padding': '3px',
			'font-size': '12px'
		});
		if (i == 0){
			cell.css({'border-top': '1px dotted #CCCCCC'});
		};
		cell.mouseover(delegate(mouse_action, {cell:cell, color: '#FFCCCC #FFFFFF'}));
		cell.mouseout(delegate(mouse_action, {cell:cell, color: '#FFFFFF #FFFFFF'}));
		var thumb = cell.create('img');
		thumb.css({
			'border': '1px solid #CCCCCC',
			'padding': '1px'
		});
		if (item.type == 'file'){
			thumb.setAttribute('width', 40);
			thumb.setAttribute('height', 40);
			var path = item.thumb;
			if (item.file_type == 'csv'){
				path = '/img/system/excel.png';
			}
			else if (item.file_type == 'doc'){
				path = '/img/system/doc.png';
			}
			else if (item.file_type == 'xls'){
				path = '/img/system/excel.png';
			}
			else if (item.file_type == 'pdf'){
				path = '/img/system/pdf.png';
			}
			else if (item.file_category == 'media'){
				path = '/img/system/media.png';
			};
			thumb.setAttribute('src', path);
			if (params.id){
				// for image field
				cell.click(delegate(set_image_field, {item:item, id:params.id}));
			}
			else {
				// for WYSIWYG
				cell.click(delegate(select_image, {path:item.file_path, cell:cell}));
			};
		}
		else {
			thumb.css({'border': '0'});
			if (item.name == '..'){
				thumb.setAttribute('src', '/img/system/go_back.png');
				thumb.setAttribute('width', 20);
				thumb.setAttribute('height', 20);
			}
			else {
				thumb.setAttribute('src', '/img/system/folder.png');
				thumb.setAttribute('width', 30);
				thumb.setAttribute('height', 30);
			};
			cell.click(delegate(open_dir, {path:item.path, name:item.name, id:params.id, media:params.media}));
		};
		var name = cell.create('span');
		name.css({'padding-left': '5px'});
		name.innerHTML = item.name;
		// tween
	};
	if (path_name == '..'){
		container.tween('x', container.easeOut.strong, container.width() / 2, 0, 0.3);
	}
	else {
		container.tween('x', container.easeOut.strong, (-1 * container.width()) / 2, 0, 0.3);
	};
};

function mouse_action(event, data){
	if (data.cell != selected){
		data.cell.css({'background-gradient': 'linear 90 '+data.color});
	};
};

function select_image(event, data){
	window.__nicEditor_inject_image_path = data.path;
	if (selected){
		selected.css({
			'background-gradient': 'linear 90 #FFFFFF #FFFFFF',
			'background-color': '#FFFFFF'
		});
	};
	data.cell.css({
		'background-gradient': 'linear 90 #FF9999 #FFFFFF',
		'background-color': '#FF9999'
	});
	selected = data.cell;
};

function set_image_field(event, params){
	var item = params.item;
	var input = document.getElementById(params.id);
	if (input){
		input.value = item.file_path;
	};
	var image = document.getElementById(params.id+'_thumb');
	if (image){
		var path = item.file_path;
		if (params.item.file_type == 'csv'){
			path = '/img/system/excel.png';
		}
		else if (params.item.file_type == 'xls'){
			path = '/img/system/excel.png';
		}
		else if (params.item.file_type == 'doc'){
			path = '/img/system/doc.png';
		}
		else if (params.item.file_type == 'pdf'){
			path = '/img/system/pdf.png';
		}
		if (params.item.file_category == 'media'){
			path = '/img/system/media.png';
		}
		image.setAttribute('src', path);
	};
};

function open_dir(event, data){
	var path = current_path;
	if (data.media){
		path = data.media;
	};
	reload(path+'?current_dir=/'+data.path+'&data_type=json', data);
	selected = false;
	path_name = data.name;
};

function reload(path, params){
	json(path, delegate(reload_list, params));	
};

function reload_list(data, params){
	if (data){
		if (box.holder){
			box.holder.remove();
		};
		selected = false;
		window.__nicEditor_inject_image_path = false;
		w.get(0).reload(function(holder){
			construct_list(holder, {data:data, id:params.id, media:params.media});
		});
	};
};

function deconstruct(){
	if (box){
		box.remove();
		box = false;
	};
	window.__nicEditor_inject_image_path = false;
	selected = false;
};
function save_data(){
	var error = false;
	for (var i in window.editors){
		var desc = document.data_entry[i]; 
		desc.value = window.editors[i].instanceById(i+'_editor').getContent();
		/*
		var req = meta_data[i]['required'];
		var min = meta_data[i]['min'];
		var max = meta_data[i]['max'];
		// check for required
		if (req){
			if (!desc.value || desc.value == '<br>'){
			
				alert(desc.value);
			
				error = true;
				document.getElementById(i+'_container').className = 'error';
			};
		};
		*/
	};
	// check for the fields 
	for (var column in meta_data){
		var req = meta_data[column]['required'];
		var min = meta_data[column]['min'];
		var max = meta_data[column]['max'];
		var field = document.getElementById(column);
		if (field){
			var v = field.value;
			// check for "required"
			if (req){
				if (!v){
					error = true;
					field.className = 'error';
				};	
			};
			// check for min length
			if (min){
				if (Number(min) > v.length){
					error = true;
					field.className = 'error';
				};
			};
			// check for max length
			if (max){
				if (Number(max) < v.length){
					error = true;
					field.className = 'error';
				};
			};
		};
	};
	if (!error){
		document.data_entry.submit();
	};
};

function accordion(obj, direction, duration){
	if (!duration){
		duration = 0.5;
	};
	// direction: width, height
	obj.state = 1;
	obj.changing = false;
	obj.max = obj[direction]();
	obj.css({direction: obj[direction]() + 'px', overflow: 'hidden'});
	obj.open = function (){
		if (!obj.changing){
			if (obj.ot){
				obj.ot.stop();
				obj.ot.start();	
			}
			else {
				obj.ot = obj.tween(direction, obj.easeOut.strong, obj[direction](), obj.max, duration);
				obj.ot.onMotionChanged = function (){
					obj.changing = true;
				};
				obj.ot.onMotionFinished = function (){
					obj.state = 1;
					obj.changing = false;
				};
			};
		};
	};
	obj.close = function(){
		if (!obj.changing){
			if (obj.ct){
				obj.ct.stop();
				obj.ct.start();	
			}
			else {
				obj.ct = obj.tween(direction, obj.easeOut.strong, obj[direction](), 0, duration);
				obj.ct.onMotionChanged = function (){
					obj.changing = true;
				};
				obj.ct.onMotionFinished = function (){
					obj.state = 0;
					obj.changing = false;
				};
			};
		};
	};
	obj.toggle = function (){
		if (obj.state){
			obj.close();
		}
		else {
			obj.open();
		};
	};
	obj.instant_open = function (){
		obj[direction](obj.max);
		obj.state = 1;
	};
	obj.instant_close = function (){
		obj[direction](0);
		obj.state = 0;
	};
	obj.instant_toggle = function(){
		if (obj.state){
			obj.instant_close();
		}
		else {
			obj.instant_open();
		};
	};
};

var dir = 'height';
var dur = 0.5;
var i = 0;
var sections = new Array();
$('.accordion').ready(function(obj){
	accordion(obj, dir, dur);
	sections[obj.id] = obj;
	var loc = obj.id.replace('_content', '_title');
	var h = $('#' + loc);
	h.click(function(){
		for (var id in sections){
			if (h.id.replace('_title', '_content') == id){
				sections[id].open();
			}
			else {
				sections[id].close();
			};
		};
	});
	obj.instant_close();
});

</script>
</head>
<body>
<? Load::template('common/cms_top'); ?>
<div class="container">
<div class="inner">
<!-- Table Structure -->
<? if(isset($LOGGEDIN_USER['root_access'])): ?>
<? if(isset($LOGGEDIN_USER['root_access'][$TABLE]) && $LOGGEDIN_USER['root_access'][$TABLE]): ?>
<div id="table_struc_area" class="section">
<? /*
<div class="section">
<a href="/menu/"><img src="/img/system/go_back.png" width="20" />&nbsp;<?= text($CONTENTS, 'back_to_list', 'Back to table list'); ?></a>
&nbsp;
&nbsp;
<a href="/manage/table/<?= $TABLE; ?>/"><img src="/img/system/file.png" width="25" />&nbsp;<?= text($CONTENTS, 'new_data_title', 'Create New Data'); ?></a>
&nbsp;
&nbsp;
<a onclick="change_display('record_list');" style="margin-left: 15px;"><img src="/img/system/arrow_down.png" width="25" />&nbsp;<?= text($CONTENTS, 'back_to_data_list', 'Back to the list'); ?></a>
&nbsp;
&nbsp;
<a onclick="change_display('table_desc_area');" style="margin-left: 15px;"><img src="/img/system/brick_edit.png" width="25" />&nbsp;<?= text($CONTENTS, 'edit_table_desc_tag', 'Edit Table Description'); ?></a>
</div>
 */ ?>
<h3 id="table_struc_area_title"><img src="/img/system/tools.png" width="25" />&nbsp;[<?= $TABLE; ?>] <?= text($CONTENTS, 'edit_struct', 'Edit Table Structure'); ?></h3>
<div id="table_struc_area_content" class="accordion">
<table class="list">
<tr>
<th class="th-top center"><?= text($CONTENTS, 'column_name_tag', 'Column Name'); ?></th>
<th class="th-top center"><?= text($CONTENTS, 'column_type_tag', 'Column Type'); ?></th>
<th class="th-top center"><?= text($CONTENTS, 'column_comment_tag', 'Column Detail'); ?></th>
<th class="th-top center"><?= text($CONTENTS, 'remove_tag', 'Remove'); ?></th>
</tr>
<!-- Table Name -->
<? /*
<tr>
<th class="th-top" colspan="3" style="border-bottom: 0; text-align: center;">
<form action="/manage/rename/" method="post">
<input type="hidden" name="table" value="<?= $TABLE; ?>" />
<input type="text" name="name" value="<?= $TABLE; ?>" size="40" />
<input type="submit" value="<?= text($CONTENTS, 'edit_tag', 'Edit'); ?>" />
</form>
</th>
</tr>
*/ ?>
<!-- Table Structure -->
<? foreach($FIELDS as $i => $item): ?>
<tr>
<th class="th" style="border-right: 0;">
<? if($item['field'] != 'id' && $item['field'] != 'modtime'): ?>
<? if($LOGGEDIN_USER['permission'] == 0): ?>
<form action="/manage/rename_column/" method="post">
<input type="hidden" name="table" value="<?= $TABLE; ?>" />
<input type="hidden" name="column_from" value="<?= $item['field']; ?>" />
<input type="text" name="column_to" value="<?= $item['field']; ?>" size="20" />
<input type="submit" value="<?= text($CONTENTS, 'edit_tag', 'Edit'); ?>" />
</form>
<? else: ?>
<?= $item['field']; ?>
<? endif; ?>
<? else: ?>
<?= $item['field']; ?>
<? endif; ?>
</th>
<td class="td center">
<? if($item['field'] == 'id' || $item['field'] == 'modtime'): ?>
<span class="off"><?= text($CONTENTS, 'not_editable', 'Not Editable'); ?></span>
<? else: ?>
<? if($LOGGEDIN_USER['permission'] == 0): ?>
<form action="/manage/change_data_type/" method="post">
<input type="hidden" name="table" value="<?= $TABLE; ?>" />
<input type="hidden" name="column" value="<?= $item['field']; ?>" />
<select name="type">
<option><?= text($CONTENTS, 'please_select', '-- Please Select --'); ?></option>
<? foreach($DATA_TYPES as $name => $type): ?>
<option value="<?= $name; ?>" <? if($item['type'] == $name): ?>selected="selected"<? endif; ?>><?= $name; ?></option>
<? endforeach; ?>
</select>
<input type="submit" value="<?= text($CONTENTS, 'edit_tag', 'Edit'); ?>" />
</form>
<? else: ?>
<span class="off"><?= text($CONTENTS, 'not_editable', 'Not Editable'); ?></span>
<? endif; ?>
<? endif; ?>
</td>
<td class="td center">
<? if($item['field'] == 'id' || $item['field'] == 'modtime'): ?>
<span class="off"><?= text($CONTENTS, 'not_editable', 'Not Editable'); ?></span>
<? else: ?>
<div class="tr">
<form action="/manage/meta/" method="post">
<input type="hidden" name="table" value="<?= $TABLE; ?>" />
<input type="hidden" name="column" value="<?= $item['field']; ?>" />
<div class="td-top center" style="font-size: 11px;">
<?= text($CONTENTS, 'column_desc_tag'); ?>&nbsp;:&nbsp;
<input type="text" name="desc" onkeypress="filter(event, this, 'text');" onblur="filter(event, this, 'text');" value="<? if(isset($META[$item['field']]['desc'])): ?><?= $META[$item['field']]['desc']; ?><? endif; ?>" size="35" />
</div>
<div class="td center" style="font-size: 11px;">
<?= text($CONTENTS, 'required_tag'); ?>&nbsp;:&nbsp;
<input type="checkbox" name="required" <? if(isset($META[$item['field']]) && isset($META[$item['field']]['required']) && $META[$item['field']]['required'] == 'on'): ?>checked="checked"<? endif; ?> />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?= text($CONTENTS, 'min_length_tag'); ?>&nbsp;:&nbsp;
<input type="text" name="min" onkeypress="filter(event, this, 'integer');" value="<? if(isset($META[$item['field']]['min'])): ?><?= $META[$item['field']]['min']; ?><? endif; ?>" size="3" />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= text($CONTENTS, 'max_length_tag'); ?>&nbsp;:&nbsp;
<input type="text" name="max" onkeypress="filter(event, this, 'integer');" value="<? if(isset($META[$item['field']]['max'])): ?><?= $META[$item['field']]['max']; ?><? endif; ?>" size="3" />
</div>
<? if(strpos($item['type'], 'text') !== false || strpos($item['type'], 'var') !== false): ?>
<div class="td center" style="font-size: 11px;">
<?= text($CONTENTS, 'password_tag'); ?>&nbsp;:&nbsp;
<input type="radio" name="attribute" value="password" <? if(isset($META[$item['field']]) && isset($META[$item['field']]['attribute']) && $META[$item['field']]['attribute'] == 'password'): ?>checked="checked"<? endif; ?> />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= text($CONTENTS, 'allow_html'); ?>&nbsp;:&nbsp;
<input type="radio" name="attribute" value="html" <? if(isset($META[$item['field']]) && isset($META[$item['field']]['attribute']) && $META[$item['field']]['attribute'] == 'html'): ?>checked="checked"<? endif; ?> />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= text($CONTENTS, 'image_field_tag'); ?>&nbsp;:&nbsp;
<input type="radio" name="attribute" value="image" <? if(isset($META[$item['field']]) && isset($META[$item['field']]['attribute']) && $META[$item['field']]['attribute'] == 'image'): ?>checked="checked"<? endif; ?> />
</div>
<? endif; ?>
<div class="td right" style="font-size: 11px;" id="<?= $item['field']; ?>_ref">
<?= text($CONTENTS, 'column_reference_tag'); ?>&nbsp;:&nbsp;
<? if($TABLE_LIST): ?>
<select id="<?= $item['field']; ?>_ref_table" name="reference[table]" 
<? foreach ($TABLE_LIST as $op): ?>
<? if (isset($META[$item['field']]['attribute']['table']) && $op['table_name'] == $META[$item['field']]['attribute']['table']): ?>
style="background-color: #EEFFEE; border: 1px solid #66CC66; color: #006600;"
<? break; ?>
<? endif; ?>
<? endforeach; ?>
 onchange="get_table_columns('<?= $item['field']; ?>_ref', <?= $item['field']; ?>_ref_table, '<?= $item['type']; ?>');">
<option><?= text($CONTENTS, 'please_select'); ?></option>
<? foreach ($TABLE_LIST as $option): ?>
<option value="<?=$option['table_name'];?>" <? if (isset($META[$item['field']]['attribute']['table']) && $option['table_name'] == $META[$item['field']]['attribute']['table']): ?>selected="selected"<? endif; ?>><?= $option['table_name']; ?></option>
<? endforeach; ?>
</select>
<? endif; ?>
</div>
<div class="td right">
<?= text($CONTENTS, 'reset_attribute_tag'); ?>&nbsp;:&nbsp;
<input type="radio" name="attribute" value="reset" <? if(!isset($META[$item['field']]) || !isset($META[$item['field']]['attribute']) || $META[$item['field']]['attribute'] == 'reset'): ?>checked="checked"<? endif; ?> />
<input type="submit" value="<?= text($CONTENTS, 'edit_tag', 'Edit'); ?>" />
</div>
</form>
</div>
<? endif; ?>
</td>
<td class="td center">
<? if($item['field'] == 'id' || $item['field'] == 'modtime'): ?>
<span class="off"><? if(isset($CONTENTS['not_editable'])): ?><?= text($CONTENTS, 'not_editable', 'Not Editable'); ?><? endif; ?></span>
<? else: ?>
<? if($LOGGEDIN_USER['permission'] == 0): ?>
<a class="delete_btn" href="javascript:void(null);" onclick="confirmation('<?= text($CONTENTS, 'remove_conf', 'Would you like to remove'); ?> : <?= $item['field']; ?>?', '/manage/table/<?= $TABLE ?>/?remove=true&column=<?= $item['field']; ?>#table_struc_area');">
<?= text($CONTENTS, 'remove_tag', 'Remove'); ?>
</a>
<? else: ?>
<span class="off"><? if(isset($CONTENTS['not_editable'])): ?><?= text($CONTENTS, 'not_editable', 'Not Editable'); ?><? endif; ?></span>
<? endif; ?>
<? endif; ?>
</td>
</tr>
<? endforeach; ?>
</table>
<!-- Add a new Column -->
<? if($LOGGEDIN_USER['permission'] == 0): ?>
<table clas="list" style="width: 100%; margin-top: 5px;">
<tr>
<th class="th-top center" colspan="4" ><?= text($CONTENTS, 'add_column', 'Add Column'); ?></th>
</tr>
<tr>
<form method="post">
<input type="hidden" name="edit_struct" value="true" />
<td class="td" style="text-align: center; center; border-right: 0;"><?= text($CONTENTS, 'column_name', 'Column'); ?> : <input type="text" name="name" value="" size="30" /></td>
<td class="td" style="text-align: center; center; border-right: 0;">
<?= text($CONTENTS, 'column_type', 'Type'); ?> : 
<select name="type">
<? foreach($DATA_TYPES as $name => $item): ?>
<option value="<?= $item; ?>"><?= $name; ?></option>
<? endforeach; ?>
</select>
</td>
<td class="td" style="text-align: center; center; border-right: 0;"><?= text($CONTENTS, 'column_default', 'Default'); ?> : <input type="text" name="default" value="" size="30" /></td>
<td class="td" style="text-align: center;"><input type="submit" value="<?= text($CONTENTS, 'add_column', 'Add'); ?>" /></td>
</form>
</tr>
</table>
</div>
<? endif; ?>
</div>
<? endif; ?>
<? endif; ?>
<!-- Create New Entry / Edit Record -->
<div id="data_entry_area" class="section">
<? /*
<!-- Back Button -->
<div class="section">
<a href="/menu/"><img src="/img/system/go_back.png" width="20" />&nbsp;<?= text($CONTENTS, 'back_to_list', 'Back to table list'); ?></a>
&nbsp;
&nbsp;
<a onclick="change_display('record_list');" style="margin-left: 15px;"><img src="/img/system/arrow_down.png" width="25" />&nbsp;<?= text($CONTENTS, 'back_to_data_list', 'Back to the list'); ?></a>
<? if(isset($LOGGEDIN_USER['root_access'])): ?>
<? if(isset($LOGGEDIN_USER['root_access'][$TABLE]) && $LOGGEDIN_USER['root_access'][$TABLE]): ?>
&nbsp;
&nbsp;
<a onclick="change_display('table_struc_area');"><img src="/img/system/tool_edit.png" width="25" />&nbsp;<?= text($CONTENTS, 'edit_structure_tag', 'Edit Table Structure'); ?></a>
&nbsp;
&nbsp;
<a onclick="change_display('table_desc_area');" style="margin-left: 15px;"><img src="/img/system/brick_edit.png" width="25" />&nbsp;<?= text($CONTENTS, 'edit_table_desc_tag', 'Edit Table Description'); ?></a>
<? endif; ?>
<? endif; ?>
</div>
*/ ?>
<? if(isset($DATA)): ?>
<h3 id="data_entry_area_title"><img src="/img/system/edit.png" width="25" />&nbsp;[<?= $TABLE; ?>] <?= text($CONTENTS, 'edit_title', 'Edit Data for ID '); ?>:<?= $DATA['id']; ?></h3>
<p><a href="/manage/table/<?= $TABLE; ?>/"><img src="/img/system/file.png" width="25" />&nbsp;<?= text($CONTENTS, 'new_data_title', 'Create New Data'); ?></a></p>
<? else: ?>
<h3 id="data_entry_area_title"><img src="/img/system/file.png" width="25" />&nbsp;[<?= $TABLE; ?>] <?= text($CONTENTS, 'new_data_title', 'Create New Data'); ?></h3>
<!-- CSV Upload -->
<div class="section" style="margin-bottom: 4px; text-align: center;">
<form action="/manage/csv_upload/" method="post" enctype="multipart/form-data">
<img src="/img/system/excel.png" width="25" />
&nbsp;
<input type="hidden" name="table" value="<?= $TABLE; ?>" />
<input type="file" name="csv" />
<button><?= text($CONTENTS, 'data_entry_from_csv'); ?></button>
</form>
</div>
<? endif; ?>
<div id="data_entry_area_content" class="accordion">
<!-- Data Block -->
<form name="data_entry" action="/manage/table/<?= $TABLE; ?>/<? if(isset($DATA['id'])): ?><?= $DATA['id']; ?>/<? endif; ?>" method="post">
<input type="hidden" name="edit" value="true" />
<div class="tr">
<div class="th-top"><?= text($CONTENTS, 'save_tag', 'Save'); ?></div>
<div class="td right"><a href="javascript: void(null);" class="btn" onclick="save_data();"><?= text($CONTENTS, 'save_tag', 'Save'); ?></a></div>
</div>
<!-- Auto Values -->
<div class="tr">
<div class="th"><img src="/img/system/orange_lock.png" width="20">&nbsp;<?= 'id'; ?></div>
<div class="td"><? if(isset($DATA['id'])): ?><?= $DATA['id']; ?><? else: ?><span class="off"><?= text($CONTENTS, 'auto_value', 'Automatic Value'); ?></span><? endif; ?></div>
</div>
<div class="tr">
<div class="<? if($i == 1): ?>th-top<? else: ?>th<? endif; ?>"><img src="/img/system/orange_lock.png" width="20">&nbsp;<?= 'modtime'; ?></div>
<div class="td"><? if(isset($DATA['modtime'])): ?><?= $DATA['modtime']; ?><? else: ?><span class="off"><?= text($CONTENTS, 'auto_value', 'Automatic Value'); ?></span><? endif; ?></div>
</div>
<!-- Language -->
<? foreach($FIELDS as $i => $item): ?>
<? if($item['field'] == 'lang_id' && $TABLE != 'languages'): ?>
<div class="tr">
<div class="th"><img src="/img/system/lang.png" width="20" />
&nbsp;<?= $item['field']; ?>&nbsp;&nbsp;
<? if(isset($META[$item['field']])): ?>
<? if($META[$item['field']]['desc']): ?><span class="meta"><?= $META[$item['field']]['desc'] ?></span><? endif; ?>
<? if($META[$item['field']]['min']): ?><span class="meta"><?= text($CONTENTS, 'min_length_tag', 'Min Length'); ?>:<span style="color: #009900;"><?= $META[$item['field']]['min'] ?></span></span><? endif; ?>
<? if($META[$item['field']]['max']): ?><span class="meta"><?= text($CONTENTS, 'max_length_tag', 'Max Length'); ?>:<span style="color: #009900;"><?= $META[$item['field']]['max'] ?></span></span><? endif; ?>
<? if($META[$item['field']]['required']): ?><span class="meta" style="color: #CC0000;"><?= text($CONTENTS, 'required_tag', 'Requried'); ?></span><? endif; ?>
<? endif; ?>
</div>
<div class="td">
<strong><?= text($CONTENTS, 'content_lang_tag', 'Content Language'); ?></strong>
&nbsp;:&nbsp;
<select name="<?= $item['field']; ?>" id="<?= $item['field']; ?>">
<option value=""><?= text($CONTENTS, 'please_select', '-- Please Select --'); ?></option>
<? foreach($LANGS as $lang_item): ?>
<option value="<?= $lang_item['lang_id']; ?>" <? if(isset($DATA[$item['field']])): ?><? if($lang_item['lang_id'] == $DATA[$item['field']]): ?>selected="selected"<? endif; ?><? endif; ?>><?= $lang_item['name']; ?>&nbsp;(<?= $lang_item['lang_id']?>)</option>
<? endforeach; ?>
</select>
</div>
</div>
<? break; ?>
<? endif; ?>
<? endforeach; ?>
<!-- Language -->
<? foreach($FIELDS as $i => $item): ?>
<? if($item['field'] != 'modtime' && $item['field'] != 'id' && ($item['field'] != 'lang_id' || $TABLE == 'languages')): ?>
<div class="tr">
<div class="th" id="<?= $item['field'].'_label'; ?>"><img src="/img/system/data_edit.png" width="20">
&nbsp;<?= $item['field']; ?>&nbsp;&nbsp;
<? if(isset($META[$item['field']])): ?>
<? if(isset($META[$item['field']]['desc']) && $META[$item['field']]['desc']): ?><span class="meta"><?= $META[$item['field']]['desc'] ?></span><? endif; ?>
<? if(isset($META[$item['field']]['attribute']) && $META[$item['field']]['attribute'] == 'html'): ?><span class="meta"><?= text($CONTENTS, 'allow_html'); ?></span><? endif; ?>
<? if(isset($META[$item['field']]['attribute']) && $META[$item['field']]['attribute'] == 'password'): ?><span class="meta"><?= text($CONTENTS, 'password_tag'); ?></span><? endif; ?>
<? if(isset($META[$item['field']]['attribute']) && $META[$item['field']]['attribute'] == 'image'): ?><span class="meta"><?= text($CONTENTS, 'image_field_tag'); ?></span><? endif; ?>
<? if(isset($META[$item['field']]['min']) && $META[$item['field']]['min']): ?><span class="meta"><?= text($CONTENTS, 'min_length_tag', 'Min Length'); ?>:<span style="color: #009900;"><?= $META[$item['field']]['min'] ?></span></span><? endif; ?>
<? if(isset($META[$item['field']]['max']) && $META[$item['field']]['max']): ?><span class="meta"><?= text($CONTENTS, 'max_length_tag', 'Max Length'); ?>:<span style="color: #009900;"><?= $META[$item['field']]['max'] ?></span></span><? endif; ?>
<? if(isset($META[$item['field']]['required']) && $META[$item['field']]['required']): ?><span class="meta" style="color: #CC0000;"><?= text($CONTENTS, 'required_tag', 'Requried'); ?></span><? endif; ?>
<? endif; ?>
</div>
<div class="td" id="<?= $item['field'].'_container'; ?>">
<? if(isset($META[$item['field']]['attribute']) && is_array($META[$item['field']]['attribute']) && isset($COLUMN_REFS[$item['field']])): ?>
<select name="<?= $item['field']; ?>" id="<?= $item['field']; ?>">
<option><?= text($CONTENTS, 'please_select'); ?></option>
<? foreach ($COLUMN_REFS[$item['field']] as $ref): ?>
<option value="<?= $ref['column']; ?>" <? if (isset($DATA[$item['field']]) && $DATA[$item['field']] == $ref['column']): ?>selected="selected"<? endif; ?>><?= $ref['label']; ?></option>
<? endforeach; ?>
</select>
<? else: ?>
<? if(strpos($item['type'], 'int') !== false): ?>
<input type="text" id="<?= $item['field']; ?>" name="<?= $item['field']; ?>" value="<? if(isset($DATA[$item['field']])): ?><?= escape($DATA[$item['field']]); ?><? endif; ?>" size="20" onkeypress="filter(event, this, 'integer');" />
<?= text($CONTENTS, 'number_only', '(Number Only)'); ?>
<? elseif(strpos($item['type'], 'char') !== false): ?>
<? if(isset($META[$item['field']]) && isset($META[$item['field']]['attribute']) && $META[$item['field']]['attribute'] == 'password'): ?>
<input type="text" id="<?= $item['field']; ?>" name="<?= $item['field']; ?>" onkeypress="filter(event, this, 'password');" value="" size="100" />
<? elseif(isset($META[$item['field']]) && isset($META[$item['field']]['attribute']) && $META[$item['field']]['attribute'] == 'image'): ?>
<div>
<? if(isset($DATA[$item['field']])): ?>
<?php 
$filename = $DATA[$item['field']];
$index = strrpos($filename, '.') + 1;
$ext = strtolower(substr($filename, $index, strlen($filename) - $index));
if ($ext == 'csv'){
	$media = '/img/system/excel.png';
}
else if ($ext == 'xls'){
	$media = '/img/system/excel.pdf';
}
else if ($ext == 'doc'){
	$media = '/img/system/doc.pdf';
}
else if ($ext == 'pdf'){
	$media = '/img/system/excel.pdf';
}
else if ($ext == 'mp4' || $ext == 'mp3' || $ext == 'mov' || $ext == 'avi'){
	$media = '/img/system/media.png';
}
else {
	$media = escape($filename);
}
?>
<?else: ?>
<? $media = '/img/system/image.png'; ?>
<? endif; ?>
<img style="margin: 3px; padding: 2px; border: 1px solid #CCCCCC;" id="<?= $item['field']; ?>_thumb" src="<?= $media; ?>" alt="<?= $media; ?>" height="130" />
<input readonly type="text" id="<?= $item['field']; ?>" name="<?= $item['field']; ?>" value="<? if(isset($DATA[$item['field']])): ?><?= escape($DATA[$item['field']]); ?><? endif; ?>" size="100" />
<div style="width: 130px;" id="<?= $item['field']; ?>_btn" class="btn" onclick="open_image_list('<?= $item['field']; ?>');"><?= text($CONTENTS, 'image_popup_tag'); ?></div>
</div>
<? else: ?>
<input type="text" id="<?= $item['field']; ?>" name="<?= $item['field']; ?>" <? if(!isset($META[$item['field']]['html']) || !$META[$item['field']]['html']): ?>onkeypress="filter(event, this, 'text');"<? endif; ?> value="<? if(isset($DATA[$item['field']])): ?><?= escape($DATA[$item['field']]); ?><? endif; ?>" size="100" />
<? endif; ?>
<? if(isset($META[$item['field']]) && isset($META[$item['field']]['attribute']) && $META[$item['field']]['attribute'] == 'password'): ?><?= text($CONTENTS, 'password_tag', '(Password)'); ?>
<? else: ?>
<? if(isset($META[$item['field']]) && isset($META[$item['field']]['attribute']) && $META[$item['field']]['attribute'] != 'image'): ?>
<?= text($CONTENTS, 'text', '(Text)'); ?>
<? endif; ?>
<? endif; ?>
<? elseif(strpos($item['type'], 'timestamp') !== false): ?>
<input type="text" id="<?= $item['field']; ?>" name="<?= $item['field']; ?>" value="<? if(isset($DATA[$item['field']])): ?><?= escape($DATA[$item['field']]); ?><? endif; ?>" size="30" onkeypress="filter(event, this, 'datetime');" />
(Date Time YYYY-MM-DD HH:MM:SS)
<? elseif(strpos($item['type'], 'time') !== false): ?>
<input type="text" id="<?= $item['field']; ?>" name="<?= $item['field']; ?>" value="<? if(isset($DATA[$item['field']])): ?><?= escape($DATA[$item['field']]); ?><? endif; ?>" size="30" onkeypress="filter(event, this, 'time');" />
(Time HH:MM:SS)
<? elseif(strpos($item['type'], 'date') !== false): ?>
<input type="text" id="<?= $item['field']; ?>" name="<?= $item['field']; ?>" value="<? if(isset($DATA[$item['field']])): ?><?= escape($DATA[$item['field']]); ?><? endif; ?>" size="30" onkeypress="filter(event, this, 'date');" />
(Date YYYY-MM-DD)
<? elseif(strpos($item['type'], 'text') !== false): ?>
<? if(isset($META[$item['field']]['attribute']) && $META[$item['field']]['attribute'] == 'html'): ?>
<textarea id="<?= $item['field']; ?>_editor" style="width: 880px;" cols="95" rows="20"><? if(isset($DATA[$item['field']])): ?><?= $DATA[$item['field']]; ?><? endif; ?></textarea>
<textarea style="display: none;" name="<?= $item['field']; ?>" id="<?= $item['field']; ?>" cols="95" rows="20"><? if(isset($DATA[$item['field']])): ?><?= $DATA[$item['field']]; ?><? endif; ?></textarea>
<script type="text/javascript">
if (!window.editors){
	window.editors = new Array();
};
function add(name) {
	window.editors[name] = new nicEditor({fullPanel: true, onSave: save_data}).panelInstance(name+'_editor');
};
add('<?= $item['field'] ?>');
// add image popup button
<? if(strpos($item['type'], 'text') !== false): ?>
$('#<?= $item['field']; ?>_label').ready(function(obj){
	var btn = obj.create('a');
	btn.setAttribute('class', 'btn');
	btn.innerHTML = '<?= text($CONTENTS, 'image_popup_tag', 'Show Image List'); ?>';
	btn.click(function(){
		if (agent.browser == 'Explorer'){
			//window.open('/cimgmanager/', '_blank');
			json(current_path+'?data_type=json', delegate(read_ie_image_list, obj));
		}
		else {
			json(current_path+'?data_type=json', read_image_list);
		};
	});
});
<? endif; ?>
</script>
<? else: ?>
<textarea name="<?= $item['field']; ?>" id="<?= $item['field']; ?>" cols="107" rows="20" onkeypress="filter(event, this, 'no_html', true);"><? if(isset($DATA[$item['field']]) && $item['field'] != 'password'): ?><?= $DATA[$item['field']]; ?><? endif; ?></textarea>
<? endif; ?>
<? else: ?>
<input type="text" id="<?= $item['field']; ?>" name="<?= $item['field']; ?>" value="<? if(isset($DATA[$item['field']])): ?><?= escape($DATA[$item['field']]); ?><? endif; ?>" size="70" />
<? if(isset($META[$item['field']]) && isset($META[$item['field']]['attribute']) && $META[$item['field']]['attribute'] != 'image'): ?>
<?= text($CONTENTS, 'text', '(Text)'); ?>
<? endif; ?>
<? endif; ?>
<? endif; ?>
</div>
</div>
<? endif; ?>
<? endforeach; ?>
<div class="tr">
<div class="th"><?= text($CONTENTS, 'save_tag', 'Save'); ?></div>
<div class="td right"><a href="javascript: void(null);" class="btn" onclick="save_data();"><?= text($CONTENTS, 'save_tag', 'Save'); ?></a></div>
</div>
</form>
</div>
</div>
<? /*
<!-- SQL Exec -->
<div class="section" id="exec_sql">
<h3><img src="/img/system/search.png" width="25" />&nbsp;[<?= $TABLE; ?>]&nbsp;<?= text($CONTENTS, 'exec_sql_tag', 'SQL Select'); ?></h3>
<form action="/manage/table/<?= $TABLE; ?>/#exec_sql" method="post">
<input type="hidden" name="exec_sql" value="true" />
<p style="text-align: center;"><textarea name="sql" cols="100" rows="5"><?= $QUERY; ?></textarea></p>
<p style="text-align: right;"><input type="submit" value="<?= text($CONTENTS, 'exec_sql_btn', 'Execute Select SQL'); ?>" /></p>
</form>
<p style="text-align: right;"><a href="/manage/table/<?= $TABLE; ?>/#record_list"><img src="/img/system/list.png" width="25" />&nbsp;<?= text($CONTENTS, 'show_all_tag', 'Show All'); ?></a></p>
</div>
*/ ?>
<!-- List of Existing Data -->
<div class="section" id="record_list">
<? /*
<!-- Back Button -->
<div class="section">
<a href="/menu/"><img src="/img/system/go_back.png" width="20" />&nbsp;<?= text($CONTENTS, 'back_to_list', 'Back to table list'); ?></a>
&nbsp;
&nbsp;
<a href="/manage/table/<?= $TABLE; ?>/"><img src="/img/system/file.png" width="25" />&nbsp;<?= text($CONTENTS, 'new_data_title', 'Create New Data'); ?></a>
<? if(isset($LOGGEDIN_USER['root_access'])): ?>
<? if(isset($LOGGEDIN_USER['root_access'][$TABLE]) && $LOGGEDIN_USER['root_access'][$TABLE]): ?>
&nbsp;
&nbsp;
<a onclick="change_display('table_struc_area');"><img src="/img/system/tool_edit.png" width="25" />&nbsp;<?= text($CONTENTS, 'edit_structure_tag', 'Edit Table Structure'); ?></a>
&nbsp;
&nbsp;
<a onclick="change_display('table_desc_area');" style="margin-left: 15px;"><img src="/img/system/brick_edit.png" width="25" />&nbsp;<?= text($CONTENTS, 'edit_table_desc_tag', 'Edit Table Description'); ?></a>
<? endif; ?>
<? endif; ?>
</div>
*/ ?>
<!-- List Area -->
<h3 id="record_list_title"><img src="/img/system/list.png" width="25" />&nbsp;[<?= $TABLE; ?>]&nbsp;<?= text($CONTENTS, 'list_title', 'Data List'); ?></h3>
<div id="record_list_content" class="accordion">
<!-- CSV Upload -->
<div class="section" style="margin-bottom: 4px; text-align: center;">
<form action="/manage/csv_upload/" method="post" enctype="multipart/form-data">
<img src="/img/system/excel.png" width="25" />
&nbsp;
<input type="hidden" name="table" value="<?= $TABLE; ?>" />
<input type="file" name="csv" />
<button><?= text($CONTENTS, 'data_entry_from_csv'); ?></button>
</form>
</div>
<!-- Search -->
<div class="section" style="margin: 3px; text-align: center;">
<form action="#record_list" method="get">
<a href="/manage/table/<?= $TABLE; ?>/<? if(isset($DATA['id'])): ?><?= $DATA['id']; ?>/<? endif; ?>#record_list"><img src="/img/system/refresh.png" width="25" />&nbsp;<?= text($CONTENTS, 'show_all_tag', 'Show All'); ?></a>
<? if($FIELDS): ?>
&nbsp;
&nbsp;
<img src="/img/system/search.png" width="25" />
&nbsp;
<select name="column">
<option value="all" <? if(isset($QUERIES['column']) && $QUERIES['column'] == $COLUMN): ?>selected="selected"<? endif; ?>><?= text($CONTENTS, 'search_all'); ?></option>
<? foreach($FIELDS as $i => $item): ?>
<? if(strpos($item['type'], 'char') !== false || strpos($item['type'], 'text') !== false || strpos($item['type'], 'int') !== false): ?>
<option value="<?= $item['field'] ?>" <? if($item['field'] == $COLUMN): ?>selected="selected"<? endif; ?>><?= $item['field']; ?></option>
<? endif; ?>
<? endforeach; ?>
</select>
&nbsp;
<input type="text" name="search" value="<?= $SEARCH; ?>" />
&nbsp;
<input type="submit" value="<?= text($CONTENTS, 'search_btn', 'Search'); ?>" />
<? endif; ?>
&nbsp;
<?= $FROM + 1; ?> - <?= $FROM + count($LIST); ?>&nbsp;:&nbsp;[<strong style="color: #009900;"><?= $TOTAL; ?></strong>]&nbsp;<?= text($CONTENTS, 'found_tag', 'Found'); ?>
</form>
<!-- Paging -->
<div style="text-align: center; margin-bottom: 3px;">
<? if($FROM - $ITEM_NUM >= 0): ?>
<a href="/manage/table/<?= $TABLE; ?>/<? if(isset($QUERIES['EXTRA'])): ?><?= $QUERIES['EXTRA']; ?>/<? endif; ?>?from=<?= $FROM - $ITEM_NUM; ?><? if($COLUMN): ?>&column=<?= $COLUMN; ?><? endif; ?><? if($SEARCH): ?>&search=<?= $SEARCH; ?><? endif; ?>#record_list"><?= text($CONTENTS, 'back_btn', '< Back'); ?></a>
<? else: ?>
<span style="color: #CCCCCC"><?= text($CONTENTS, 'back_btn', '< Back'); ?></span>
<? endif; ?>
&nbsp;[<?= $PAGE; ?>]&nbsp;
<? if($FROM + $ITEM_NUM <= $TOTAL): ?>
<a href="/manage/table/<?= $TABLE; ?>/<? if(isset($QUERIES['EXTRA'])): ?><?= $QUERIES['EXTRA']; ?>/<? endif; ?>?from=<?= $FROM + $ITEM_NUM; ?><? if($COLUMN): ?>&column=<?= $COLUMN; ?><? endif; ?><? if($SEARCH): ?>&search=<?= $SEARCH; ?><? endif; ?>#record_list"><?= text($CONTENTS, 'next_btn', 'Next >'); ?></a>
<? else: ?>
<span style="color: #CCCCCC"><?= text($CONTENTS, 'next_btn', 'Next >'); ?></span>
<? endif; ?>
</div>
<!-- List -->
<table class="list long">
<? $toggle = 0; ?>
<? $limit = 6; ?>
<? $j = 0; ?>
<tr>
<th class="th center"><input id="bulk_delete_all" type="checkbox" name="all" onclick="check_all();" /></th>
<? foreach($FIELDS as $k => $v): ?>
<? if($v['field'] == 'lang_id'): ?>
<th class="th"><?= text($CONTENTS, 'content_lang_tag', 'Content Language'); ?></th>
<? break; ?>
<? endif; ?>
<? endforeach; ?>
<? foreach($FIELDS as $n => $value): ?>
<td class="center th"><?= $value['field']; ?></td>
<? $j++; ?>
<? if($j >= $limit): ?>
<? break; ?>
<? endif; ?>
<? endforeach; ?>
<td class="center th"><?= text($CONTENTS, 'edit_tag'); ?></td>
<td class="center th"><?= text($CONTENTS, 'remove_tag'); ?></td>
</tr>
<? foreach($LIST as $i => $item): ?>
<tr class="cell">
<? if($toggle == 0): ?><? $toggle = 1; ?><? else: ?><? $toggle = 0; ?><? endif; ?>
<? if(!empty($item)): ?>
<td class="th center"><input class="bulk_delete" type="checkbox" id="<?= $item['id']; ?>" /></td>
<!-- Language ID (if any) -->
<? if(isset($item['lang_id'])): ?>
<td class="center th" onclick="window.location = '/manage/table/<?= $TABLE ?>/<?= $item['id']; ?>/'; return;">
<? foreach($LANGS as $lang): ?>
<? if($lang['lang_id'] == $item['lang_id']): ?>
<?= $lang['name']; ?>
<? break; ?>
<? endif; ?>
<? endforeach; ?>
</td>
<? endif; ?>
<? $c = 0; ?>
<? foreach($item as $name => $value): ?>
<td onclick="window.location = '/manage/table/<?= $TABLE ?>/<?= $item['id']; ?>/'; return;">
<a href="/manage/table/<?= $TABLE ?>/<?= $item['id']; ?>/#data_entry_area"><?= truncate(remove_html($value), 30); ?></a>
</td>
<? $c++; ?>
<? if($c >= $limit): ?>
<? break; ?>
<? endif; ?>
<? endforeach; ?>
</td>
<td nowrap class="center"><a class="btn" href="/manage/table/<?= $TABLE ?>/<?= $item['id']; ?>/"><?= text($CONTENTS, 'edit_tag', 'Edit'); ?></a></td>
<td nowrap class="center"><a class="delete_btn" href="javascript:void(null);" onclick="confirmation('<?= text($CONTENTS, 'remove_conf', 'would you liek to remove '); ?> : [<?= $item['id']; ?>] ?', '/manage/table/<?= $TABLE ?>/<?= $item['id']; ?>/?delete=true');"><?= text($CONTENTS, 'remove_tag', 'Delete'); ?></a></td>
</tr>
<? endif; ?>
<? endforeach; ?>
</table>
<!-- Bulk Delete -->
<div class="section right">
<button class="delete_btn" onclick="bulk_delete('<?= text($CONTENTS, 'remove_checked_conf'); ?>');"><?= text($CONTENTS, 'remove_checked_tag'); ?></button>
</div>
<!-- Paging -->
<div style="text-align: center;">
<? if($FROM - $ITEM_NUM >= 0): ?>
<a href="/manage/table/<?= $TABLE; ?>/<? if(isset($QUERIES['EXTRA'])): ?><?= $QUERIES['EXTRA']; ?>/<? endif; ?>?from=<?= $FROM - $ITEM_NUM; ?><? if($COLUMN): ?>&column=<?= $COLUMN; ?><? endif; ?><? if($SEARCH): ?>&search=<?= $SEARCH; ?><? endif; ?>#record_list"><?= text($CONTENTS, 'back_btn', '< Back'); ?></a>
<? else: ?>
<span style="color: #CCCCCC"><?= text($CONTENTS, 'back_btn', '< Back'); ?></span>
<? endif; ?>
&nbsp;[<?= $PAGE; ?>]&nbsp;
<? if($FROM + $ITEM_NUM <= $TOTAL): ?>
<a href="/manage/table/<?= $TABLE; ?>/<? if(isset($QUERIES['EXTRA'])): ?><?= $QUERIES['EXTRA']; ?>/<? endif; ?>?from=<?= $FROM + $ITEM_NUM; ?><? if($COLUMN): ?>&column=<?= $COLUMN; ?><? endif; ?><? if($SEARCH): ?>&search=<?= $SEARCH; ?><? endif; ?>#record_list"><?= text($CONTENTS, 'next_btn', 'Next >'); ?></a>
<? else: ?>
<span style="color: #CCCCCC"><?= text($CONTENTS, 'next_btn', 'Next >'); ?></span>
<? endif; ?>
</div>
</div>
</div>
<!-- table description -->
<? if(isset($LOGGEDIN_USER['root_access'])): ?>
<? if(isset($LOGGEDIN_USER['root_access'][$TABLE]) && $LOGGEDIN_USER['root_access'][$TABLE]): ?>
<div id="table_desc_area" class="section">
<? /*
<div class="section">
<a href="/menu/"><img src="/img/system/go_back.png" width="20" />&nbsp;<?= text($CONTENTS, 'back_to_list', 'Back to table list'); ?></a>
&nbsp;
&nbsp;
<a href="/manage/table/<?= $TABLE; ?>/"><img src="/img/system/file.png" width="25" />&nbsp;<?= text($CONTENTS, 'new_data_title', 'Create New Data'); ?></a>
&nbsp;
&nbsp;
<a onclick="change_display('record_list');" style="margin-left: 15px;"><img src="/img/system/arrow_up.png" width="25" />&nbsp;<?= text($CONTENTS, 'back_to_data_list', 'Back to the list'); ?></a>
<? if(isset($LOGGEDIN_USER['root_access'])): ?>
<? if(isset($LOGGEDIN_USER['root_access'][$TABLE]) && $LOGGEDIN_USER['root_access'][$TABLE]): ?>
&nbsp;
&nbsp;
<a onclick="change_display('table_struc_area');"><img src="/img/system/tool_edit.png" width="25" />&nbsp;<?= text($CONTENTS, 'edit_structure_tag', 'Edit Table Structure'); ?></a>
<? endif; ?>
<? endif; ?>
</div>
*/ ?>
<h3 id="table_desc_area_title"><img src="/img/system/info.png" width="25" />&nbsp;[<?= $TABLE; ?>] <?= text($CONTENTS, 'desc_title', 'Table description'); ?></h3>
<div id="table_desc_area_content" class="accordion">
<form name="table_desc" action="/manage/table/<?= $TABLE; ?>/<? if(isset($DATA['id'])): ?><?= $DATA['id']; ?>/<? endif; ?>" method="post">
<input type="hidden" onsubmit="save();" name="edit_desc" value="true" />
<div class="tr">
<div id="table_desc" class="th-top"><?= text($CONTENTS, 'desc_title', 'Table description'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
<div class="td">
<script type="text/javascript">
var editor;
function add_editor() {
	editor = new nicEditor({fullPanel: true, onSave: save}).panelInstance('desc_editor');
};
function save(){
	var desc = document.getElementById('description');
	desc.value = editor.instanceById('desc_editor').getContent();
	document.table_desc.submit();
};
bkLib.onDomLoaded(editor_ready);
function editor_ready(){
	add_editor();
};
// add image popup button
$('#table_desc').ready(function(obj){
	var btn = obj.create('a');
	btn.setAttribute('class', 'btn');
	btn.innerHTML = '<?= text($CONTENTS, 'image_popup_tag', 'Show Image List'); ?>';
	btn.click(function(){
		if (agent.browser == 'Explorer'){
			//window.open('/cimgmanager/', '_blank');
			json(current_path+'?data_type=json', delegate(read_ie_image_list, obj));
		}
		else {
			json(current_path+'?data_type=json', read_image_list);
		};
	});
});
</script>
<textarea style="width: 880px;" cols="95" rows="20" id="desc_editor"><? if(isset($DESC['description'])): ?><?= $DESC['description']; ?><? endif; ?></textarea>
<textarea style="display: none;" cols="95" rows="20" name="description" id="description"><? if(isset($DESC['description'])): ?><?= $DESC['description']; ?><? endif; ?></textarea>
</div>
</div>
<div class="tr">
<div class="th"><?= text($CONTENTS, 'category_tag', 'Table Category'); ?></div>
<div class="td"><input type="text" name="category" value="<? if(isset($DESC['category'])): ?><?= $DESC['category']; ?><? endif; ?>" size="70" /></div>
</div>
<div class="tr">
<div class="th"><?= text($CONTENTS, 'save_tag', 'Save'); ?></div>
<div class="td right"><button onclick="save();"><?= text($CONTENTS, 'save_tag', 'Save'); ?></button></div>
</div>
</form>
</div>
</div>
<? endif; ?>
<? endif; ?>
</div>
</div>
<? Load::template('common/footer'); ?>
</body>
</html>
