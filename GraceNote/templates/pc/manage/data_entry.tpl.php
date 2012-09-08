<div id="data_entry_area" class="section">
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
<? if(isset($DATA)): ?>
<h3><img src="/img/system/edit.png" width="25" />&nbsp;[<?= $TABLE; ?>] <?= text($CONTENTS, 'edit_title', 'Edit Data for ID '); ?>:<?= $DATA['id']; ?></h3>
<p><a href="/manage/table/<?= $TABLE; ?>/#data_entry_area"><img src="/img/system/file.png" width="25" />&nbsp;<?= text($CONTENTS, 'new_data_title', 'Create New Data'); ?></a></p>
<? else: ?>
<h3><img src="/img/system/file.png" width="25" />&nbsp;[<?= $TABLE; ?>] <?= text($CONTENTS, 'new_data_title', 'Create New Data'); ?></h3>
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
<div class="th"><img src="/img/system/orange_lock.png" width="20">&nbsp;<?= 'modtime'; ?></div>
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
<option value=""><?= text($CONTENTS, 'please_select'); ?></option>
<? foreach ($COLUMN_REFS[$item['field']] as $ref): ?>
<option value="<?= $ref['column']; ?>" <? if (isset($DATA[$item['field']]) && $DATA[$item['field']] == $ref['column']): ?>selected="selected"<? endif; ?>><?= $ref['label']; ?></option>
<? endforeach; ?>
</select>
<? else: ?>
<? if(strpos($item['type'], 'int') !== false): ?>
<input type="number" id="<?= $item['field']; ?>" name="<?= $item['field']; ?>" value="<? if(isset($DATA[$item['field']])): ?><?= escape($DATA[$item['field']]); ?><? endif; ?>" size="20" onkeypress="filter(event, this, 'integer');" />
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
	$media = '/img/system/excel.png';
}
else if ($ext == 'doc'){
	$media = '/img/system/doc.png';
}
else if ($ext == 'pdf'){
	$media = '/img/system/pdf.png';
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
<input type="text" id="<?= $item['field']; ?>" name="<?= $item['field']; ?>" <? if(!isset($META[$item['field']]['attribute']) || $META[$item['field']]['attribute'] != 'html'): ?>onkeypress="filter(event, this, 'text');"<? endif; ?> value="<? if(isset($DATA[$item['field']])): ?><?= escape($DATA[$item['field']]); ?><? endif; ?>" size="100" />
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