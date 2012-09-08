<? Load::template('common/doc_type'); ?>
<html>
<head>
<? Load::template('common/meta'); ?>
<script type="text/javascript" src="/js/ImageBox.js<?= mtime($JS_PATH, 'ImageBox.js'); ?>"></script>
<script type="text/javascript">

if (agent.browser != 'Explorer'){
	var w = new Windows();
	
	var bstyle = {
		backgroundColor: '#FFFFFF',
		border: '3px solid #FFFFFF'
	};
	var cstyle = {
		//border: '1px solid #659EC7',
		backgroundImage: 'url("/img/system/top_menu_bk.png")',
		textAlign: 'right',
		padding: '2px'
	};
	var close = '<img src="/img/system/stop.png" width="20" />';
	var ib = new ImageBox('ib', {
		block_alpha: 60, 
		box_style: bstyle, 
		close_button_style: cstyle, 
		close_button: close, 
		preloader: '/img/preloaders/preloader.gif',
		max_width: 600,
		max_height: 600,
	});
	function image_box(path){
		if (agent.browser == 'Explorer' && agent.version == 6){
			// stupid ie 6
			window.open(path, 'blank');	
		}
		else{
			if (ib){
				if (ib.show){
					ib.show(path);
					// kill all the windows
					w.close();
				};
			};
		};
	};
	
	
	function open_resize(current_path, name, image){
		w.create(delegate(load_resize, {current_path:current_path, name:name, image:image}), 550, 300, '<?= text($CONTENTS, 'resize_title', 'Resize Image'); ?>');
	};
	
	function load_resize(holder, data){
		var html = '<form action="/cimgmanager/resize/" method="post">';
		html +=    '<input type="hidden" name="current_dir" value="'+data.current_path+'" />';
		html +=    '<input type="hidden" name="src_path" value="'+data.name+'" />';
		html +=    '<input type="hidden" name="output" value="save" />';
		html +=    '<table border="0" cellspacing="0" cellpadding="0" style="background-color: #FFFFFF; margin-left: auto; margin-right: auto; width: 100%;">';
		html +=    '<tr>';
		html +=    '<th class="th-top" style="border-right: 0;"><?= text($CONTENTS, 'file_name_tag', 'File Name'); ?></th>';
		html +=    '<th class="th-top" style="border-right: 0;"><?= text($CONTENTS, 'width_tag', 'Width'); ?></th>';
		html +=    '<th class="th-top" style="border-right: 0;"><?= text($CONTENTS, 'height_tag', 'Height'); ?></th>';
		html +=    '<th class="th-top"><?= text($CONTENTS, 'aspect_ratio_tag', 'Aspect Ratio'); ?></th>';
		html +=    '<th class="th-top"><?= text($CONTENTS, 'crop_image_tag', 'Crop Image'); ?></th>';
		html +=    '</tr>';
		html +=    '<tr>';
		html +=    '<td class="td" style="border-right: 0;"><input type="text" name="name" value="'+data.name+'" /></td>';
		html +=    '<td class="td" style="border-right: 0;"><input type="text" name="width" value="" size="4" onmouseup="number(this);" onkeyup="number(this);" />px</td>';
		html +=    '<td class="td" style="border-right: 0;"><input type="text" name="height" value="" size="4" onmouseup="number(this);" onkeyup="number(this);" />px</td>';
		html +=    '<td class="td">';
		html +=    '<select name="aspect_ratio">';
		html +=    '<option value="width"><?= text($CONTENTS, 'width_tag', 'Width'); ?></option>';
		html +=    '<option value="height"><?= text($CONTENTS, 'height_tag', 'Height'); ?></option>';
		html +=    '<option value="ignore"><?= text($CONTENTS, 'ignore_tag', 'Ignore'); ?></option>';
		html +=    '</select>';
		html +=    '</td>';
		html +=    '<td id="td" style="text-align: center;"><input type="checkbox" name="crop"></td>';
		html +=    '</tr>';
		html +=    '<tr><td colspan="5" class="td" style="text-align: right;"><input type="submit" value="<?= text($CONTENTS, 'create_btn', 'Create'); ?>" /></td></tr>';
		html +=    '<tr><td colspan="5" class="td" style="text-align: center;">';
		html +=    '<p><img src="'+data.image+'?'+epoch()+'" width="100" style="border: 1px solid #CCCCCC; padding: 1px;"/></p>';
		html +=    '<p><?= text($CONTENTS, 'width_tag', 'Width'); ?> : <strong>%w%</strong>&nbsp;px&nbsp;&nbsp;<strong style="color: #999999;">x</strong>&nbsp;&nbsp;<?= text($CONTENTS, 'height_tag', 'Height'); ?> : <strong>%h%</strong>&nbsp;px</p>';
		html +=    '</td></tr>';
		html +=    '</table></form>';
		
		var image_id = epoch();
		var container = holder.create('div');
		container.alpha(0);
		container.innerHTML = '<img id="'+image_id+'" src="'+data.image+'?'+epoch()+'"/>';
		var img = document.getElementById(image_id);
		eventlistener(img, 'load', function(event){
			var size = stats(img);
			html = html.replace('%w%', size.width);
			html = html.replace('%h%', size.height);
			container.innerHTML = html;
			container.alpha(100);
		});
	};
};

$('.ie_proof').ready(function(obj){
	if (agent.browser == 'Explorer'){
		obj.hide();
	};
});

$('.show_ie').ready(function(obj){
	if (agent.browser == 'Explorer'){
		obj.css({'display': 'block'});
	};
});
</script>
<!-- Alert Message for failed action such as delete -->
<script type="text/javascript">
var error = false;
<? if(isset($QUERIES['error'])): ?>
<? if($QUERIES['error']): ?>
<? if(isset($CONTENTS[$QUERIES['error']])): ?>;
error = '<?= $CONTENTS[$QUERIES['error']] ?>';
<? else: ?>
error = 'Action Failed';
<? endif; ?>
<? endif; ?>
<? endif; ?>
if (error){
	alert(error);
	<? if(isset($QUERIES['current_dir']) && isset($QUERIES['error'])): ?>
	var path = '<?= str_replace('&error='.$QUERIES['error'], '', str_replace('?error='.$QUERIES['error'], '', $FULL_URI)); ?>';
	window.location = path;
	<? endif; ?>
};
</script>
</head>
<body>
<? Load::template('common/cms_top'); ?>
<div class="container">
<div class="inner">
<div style="border-bottom: 1px dotted #CCCCCC; padding: 4px; margin-top: 10px;">
<!-- Back Button -->
<a href="/menu/"><img src="/img/system/go_back.png" width="20" />&nbsp;<?= text($CONTENTS, 'back_to_list', 'Back to table list'); ?></a>
</div>
<div style="border-bottom: 1px dotted #CCCCCC; padding: 4px; margin-top: 10px;">
<h3><img src="/img/system/tools.png" width="25" />&nbsp;<?= text($CONTENTS, 'cimg_title', 'Content Image Manager'); ?></h3>
<table border="0" cellspacing="0" cellpadding="4" width="100%">
<!-- Path Navigation -->
<tr><th class="th-top" colspan="3"><?= text($CONTENTS, 'current_path', 'Current Location'); ?></th></tr>
<tr>
<form method="method">
<td class="td" colspan="3">
<img src="/img/system/folders.png" width="25" />&nbsp;<input type="text" name="current_dir" value="<?= $CURRENT_PATH ?>" size="100" />
<a href="/cimgmanager/?current_dir=<?= $CURRENT_PATH ?>.."><img src="/img/system/back.png" width="25" /></a>
<input type="submit" value="<?= text($CONTENTS, 'change_dir_tag', 'Move Location'); ?>" />
</td>
</form>
</tr>
<? if(isset($LOGGEDIN_USER['root_access'][$QUERIES['TYPE']]) && $LOGGEDIN_USER['root_access'][$QUERIES['TYPE']]): ?>
<!-- Create Dir -->
<tr><th class="th" colspan="3"><?= text($CONTENTS, 'create_dir', 'Create Directory'); ?></th></tr>
<form action="/cimgmanager/mkdir/#list_area" method="post">
<tr>
<td class="td" colspan="3">
<img src="/img/system/folder.png" width="25" />&nbsp;<input type="text" name="dir" value="" />
<input type="hidden" name="current_dir" value="<?= $CURRENT_PATH; ?>" />
<input type="submit" name="submit" value="<?= text($CONTENTS, 'create_dir', 'Create Directory'); ?>" />
</td>
</tr>
</form>
<!-- Upload Image -->
<tr>
<th class="th" colspan="3"><?= text($CONTENTS, 'upload_tag', 'Upload Image'); ?></th>
</tr>
<form action="/cimgmanager/upload/#list_area" method="post" enctype="multipart/form-data">
<tr>
<td class="td" colspan="3">
<img src="/img/system/image.png" width="25" />&nbsp;<input type="file" name="image[]" multiple="" value="" />
<input type="hidden" name="current_dir" value="<?= $CURRENT_PATH; ?>" />
<input type="submit" name="submit" value="<?= text($CONTENTS, 'upload_tag', 'Upload Image'); ?>" />
</td>
</tr>
</form>
<? endif; ?>
<? if($LIST): ?>
<!-- Image/Dir List -->
<tr><th class="th" colspan="3"><?= text($CONTENTS, 'list_title', 'Directory List'); ?></th></tr>
<? foreach($LIST as $i => $item): ?>
<tr>
<? if($item['type'] == 'file'): ?>
<td class="td" style="font-size: 12px;">
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td><div style="padding: 2px; border: 1px solid #CCCCCC;">
<? if ($item['file_type'] == 'csv'): ?>
<a href="<?= $item['file_path']; ?>" target="_blank"><img src="/img/system/excel.png" width="50" /></a>
<? elseif ($item['file_type'] == 'doc'): ?>
<a href="<?= $item['file_path']; ?>" target="_blank"><img src="/img/system/doc.png" width="50" /></a>
<? elseif ($item['file_type'] == 'xls'): ?>
<a href="<?= $item['file_path']; ?>" target="_blank"><img src="/img/system/excel.png" width="50" /></a>
<? elseif ($item['file_type'] == 'pdf'): ?>
<a href="<?= $item['file_path']; ?>" target="_blank"><img src="/img/system/pdf.png" width="50" /></a>
<? elseif ($item['file_category'] == 'media'): ?>
<a href="<?= $item['file_path']; ?>" target="_blank"><img src="/img/system/media.png" width="50" /></a>
<? else: ?>
<a href="javascript:void(null);" onclick="image_box('<?= $item['file_path']; ?>');" style="text-decoration: none;"><img class="icon" src="/img/system/image.png" id="<?= $item['thumb']; ?>" width="50" /></a>
<? endif; ?>
</div>
</td>
<td>
<ul style="font-size: 12px; list-style-position: inside;">
<? if(isset($LOGGEDIN_USER['root_access'][$QUERIES['TYPE']]) && $LOGGEDIN_USER['root_access'][$QUERIES['TYPE']]): ?>
<!-- Edit Name -->
<li style="border-bottom: 1px dotted #CCCCCC; margin-bottom: 2px;">
<form action="/cimgmanager/rename/" method="post">
<input type="hidden" name="current_path" value="<?= $CURRENT_PATH; ?>" />
<input type="hidden" name="old_name" value="<?= $item['name']; ?>" />
<input style="font-size: 11px;  border: 0;" type="text" name="name" value="<?= $item['name']; ?>" size="50" />
<button><?= text($CONTENTS, 'edit_tag'); ?></button>
</form>
</li>
<? endif; ?>
<li style="border-bottom: 1px dotted #CCCCCC; margin-bottom: 2px;"><?= $item['file_path']; ?></li>
<li style="border-bottom: 1px dotted #CCCCCC; margin-bottom: 2px;"><?= number_format(text($item['stat'], 'size', 0)); ?><strong style="color: #333333;">&nbsp;bytes</strong></li>
<li style="border-bottom: 1px dotted #CCCCCC; margin-bottom: 2px;"><?= date('Y/m/d H:i:s', text($item['stat'], 'mtime', 0)); ?></li>
</ul>
</td>
</tr>
</table>
</td>
<td class="td" style="border-left: 0; text-align: center; font-size: 10px;">
<? if(isset($LOGGEDIN_USER['root_access'][$QUERIES['TYPE']]) && $LOGGEDIN_USER['root_access'][$QUERIES['TYPE']]): ?>
<!-- Image Resize -->
<? if ($item['file_category'] == 'image'): ?>
<button class="ie_proof" onclick="open_resize('<?= $CURRENT_PATH?>', '<?= $item['name']; ?>', '<?= $item['file_path']; ?>');"><?= text($CONTENTS, 'create_btn', 'Create'); ?></button>
<div class="show_ie" style="display: none;">
<form action="/cimgmanager/resize/" method="post">
<input type="hidden" name="current_dir" value="<?= $CURRENT_PATH; ?>" />
<input type="hidden" name="src_path" value="<?= $item['name']; ?>" />
<input type="hidden" name="output" value="save" />
<table border="0" cellspacing="0" cellpadding="0" style="margin-left: auto; margin-right: auto; width: 100%;">
<tr>
<th class="th-top" style="border-right: 0;"><?= text($CONTENTS, 'file_name_tag', 'File Name'); ?></th>
<th class="th-top" style="border-right: 0;"><?= text($CONTENTS, 'width_tag', 'Width'); ?></th>
<th class="th-top" style="border-right: 0;"><?= text($CONTENTS, 'height_tag', 'Height'); ?></th>
<th class="th-top"><?= text($CONTENTS, 'aspect_ratio_tag', 'Aspect Ratio'); ?></th>
<th class="th-top"><?= text($CONTENTS, 'crop_image_tag', 'Crop Image'); ?></th>
</tr>
<tr>
<td class="td" style="border-right: 0;"><input type="text" name="name" value="<?= $item['name']; ?>" /></td>
<td class="td" style="border-right: 0;"><input type="text" name="width" value="" size="4" onmouseup="number(this);" onkeyup="number(this);" />px</td>
<td class="td" style="border-right: 0;"><input type="text" name="height" value="" size="4" onmouseup="number(this);" onkeyup="number(this);" />px</td>
<td class="td" style="border-right: 0;">
<select name="aspect_ratio">
<option value="width"><?= text($CONTENTS, 'width_tag', 'Width'); ?></option>
<option value="height"><?= text($CONTENTS, 'height_tag', 'Height'); ?></option>
<option value="ignore"><?= text($CONTENTS, 'ignore_tag', 'Ignore'); ?></option>
</select>
</td>
<td class="td" style="text-align: center;"><input type="checkbox" name="crop"></td>
</tr>
<tr><td class="td" colspan="4" style="text-align: right;"><input type="submit" value="<?= text($CONTENTS, 'create_btn', 'Create'); ?>" /></td></tr>
</table>
</form>
</div>
<? endif; ?>
<? endif; ?>
</td>
<? if(isset($LOGGEDIN_USER['root_access'][$QUERIES['TYPE']]) && $LOGGEDIN_USER['root_access'][$QUERIES['TYPE']]): ?>
<!-- Remove -->
<td class="td" style="text-align: center; border-left: 0;">
<button class="delete_btn" onclick="confirmation('<?= text($CONTENTS, 'remove_conf', 'Would you like to remove'); ?> <?= $item['name']; ?>?', '/cimgmanager/rmfile/?current_dir=<?= $CURRENT_PATH; ?>&file=<?= $item['name']; ?>#list_area');">
<?= text($CONTENTS, 'remove_tag', 'Remove'); ?>
</button>
</td>
<? endif; ?>
<? elseif($item['type'] == 'prev'): ?>
<td class="td" colspan="3"><a href="/cimgmanager/menu/?current_dir=<?= '/'.$item['path']; ?>#list_area" style="text-decoration: none;">
<img src="/img/system/back.png" width="25" />&nbsp;<?= $item['name']; ?>
</a></td>
<? else: ?>
<td class="td">
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td>
<a href="/cimgmanager/menu/?current_dir=<?= '/'.$item['path']; ?>#list_area" style="text-decoration: none;"><img src="/img/system/folder.png" width="50" />&nbsp;</a>
</td>
<td>
<? if(isset($LOGGEDIN_USER['root_access'][$QUERIES['TYPE']]) && $LOGGEDIN_USER['root_access'][$QUERIES['TYPE']]): ?>
<!-- Edit Name (Folder) -->
<form action="/cimgmanager/rename/" method="post">
<input type="hidden" name="current_path" value="<?= $CURRENT_PATH; ?>" />
<input type="hidden" name="old_name" value="<?= $item['name']; ?>" />
<ul style="list-style-position: inside;">
<li><input style="font-size: 11px;  border: 0;" type="text" name="name" value="<?= $item['name']; ?>" size="50" />
<button><?= text($CONTENTS, 'edit_tag'); ?></button></li>
</ul>
</form>
<? else: ?>
<ul style="list-style-position: inside;">
<li>
<?= $item['name']; ?>
</li>
</ul>
<? endif; ?>
</td>
</tr>
</table>
</td>
<td class="td" style="border-left: 0;">&nbsp;</td>
<? if(isset($LOGGEDIN_USER['root_access'][$QUERIES['TYPE']]) && $LOGGEDIN_USER['root_access'][$QUERIES['TYPE']]): ?>
<!-- Remove (Folder) -->
<td class="td" style="text-align: center; border-left: 0;">
<button class="delete_btn" onclick="confirmation('<?= text($CONTENTS, 'remove_conf', 'Would you like to remove'); ?> <?= $item['name']; ?>?', '/cimgmanager/rmdir/?current_dir=<?= $CURRENT_PATH; ?>&dir=<?= $item['name']; ?>#list_area');">
<?= text($CONTENTS, 'remove_tag', 'Remove'); ?>
</button>
</td>
<? endif; ?>
<? endif; ?>
</tr>
<? endforeach; ?>
<? endif; ?>
</table>
</div>
</div>
</div>
</div>
</div>
</div>
<script type="text/javascript" src="/js/ImageReplace.src.js"></script>
<script type="text/javascript">
var replacer = new ImageReplace('icon', 'id');
</script>
<? Load::template('common/footer'); ?>
</body>
</html>
