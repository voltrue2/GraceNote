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
// set up sections for edit display
var sections = new Array();
sections.table_struc_area = true;
sections.data_entry_area = true;
sections.record_list = true;
sections.table_desc_area = true;
var cached_sec = {};
var current_show = null;

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
	// use cached
	if (current_show && cached_sec[show_this]){
		current_show.hide();
		cached_sec[show_this].show();
		current_show = cached_sec[show_this];
	}
	// we don't have cached yet
	for (var i in sections){
		if (section == i){
			$('#'+section).ready(function (obj){		
				obj.show();
				cached_sec[section] = obj;
				current_show = obj;
			});
		}
		else {
			$('#'+i).ready(function (obj){
				if (!obj.null_object){
					obj.hide();
					cached_sec[i] = obj;
				};
			});
		};
	};
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
	}
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
				if (!v.length){
					error = true;
					field.className = 'error';
				}
			}
			// check for min length
			if (min){
				if (Number(min) > v.length){
					error = true;
					field.className = 'error';
				}
			}
			// check for max length
			if (max){
				if (Number(max) < v.length){
					error = true;
					field.className = 'error';
				}
			}
		}
	}
	if (!error){
		document.data_entry.submit();
	};
};
</script>
