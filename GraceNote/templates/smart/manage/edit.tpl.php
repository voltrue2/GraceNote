<? include($TPL_PATH.'common/doc_type.tpl.php'); ?>
<html>
<head>
<? include($TPL_PATH.'common/meta.tpl.php'); ?>
<script type="text/javascript" src="/js/nicEdit.js?<?= filemtime($JS_PATH.'nicEdit.js'); ?>"></script>
<script type="text/javascript">
form_auto_focus();
</script>
</head>
<body>
<? include($TPL_PATH.'common/cms_top.tpl.php'); ?>
<div class="container">
<div class="inner">
<!-- Back Button -->
<div class="section"><a href="/menu/"><img src="/img/system/go_back.png" width="20" />&nbsp;<?= show($CONTENTS, 'back_to_list', 'Back to table list'); ?></a></div>
<!-- Table Structure -->
<? if(isset($LOGGEDIN_USER['permission'])): ?>
<? if($LOGGEDIN_USER['permission'] === 0): ?>
<div id="table_struc_area" class="section">
<h3><img src="/img/system/tools.png" width="25" />&nbsp;[<?= $TABLE; ?>] <?= show($CONTENTS, 'edit_struct', 'Edit Table Structure'); ?></h3>
<table border="0" cellpadding="4" cellspacing="0">
<? foreach($FIELDS as $i => $item): ?>
<tr>
<th class="<? if($i == 0): ?>th-top<? else: ?>th<? endif; ?>" style="border-right: 0;"><?= $item['field']; ?></th>
<td class="<? if($i == 0): ?>td-top<? else: ?>td<? endif; ?>" style="text-align: center; border-right: 0;">
<? if($item['field'] == 'id' || $item['field'] == 'modtime'): ?>
<span class="off"><?= show($CONTENTS, 'not_editable', 'Not Editable'); ?></span>
<? else: ?>
Edit(not functional yet)
<? endif; ?>
</td>
<td class="<? if($i == 0): ?>td-top<? else: ?>td<? endif; ?>" style="text-align: center;">
<? if($item['field'] == 'id' || $item['field'] == 'modtime'): ?>
<span class="off"><? if(isset($CONTENTS['not_editable'])): ?><?= show($CONTENTS, 'not_editable', 'Not Editable'); ?><? endif; ?></span>
<? else: ?>
<a href="javascript:void(null);" onclick="confirmation('<?= show($CONTENTS, 'remove_conf', 'Would you like to remove'); ?> : <?= $item['field']; ?>?', '/manage/table/<?= $TABLE ?>/?remove=true&column=<?= $item['field']; ?>#table_struc_area');">
<?= show($CONTENTS, 'remove_tag', 'Remove'); ?>
</a>
<? endif; ?>
</td>
</tr>
<? endforeach; ?>
</table>
<!-- Add a new Column -->
<table border="0" cellspacing="0" cellpadding="4" style="margin-top: 5px;">
<tr>
<th class="th-top" colspan="4" ><?= show($CONTENTS, 'add_column', 'Add Column'); ?></th>
</tr>
<tr>
<form method="post">
<input type="hidden" name="edit_struct" value="true" />
<td class="td" style="text-align: center; center; border-right: 0;"><?= show($CONTENTS, 'column_name', 'Column'); ?> : <input type="text" name="name" value="" /></td>
<td class="td" style="text-align: center; center; border-right: 0;">
<?= show($CONTENTS, 'column_type', 'Type'); ?> : 
<select name="type">
<? foreach($DATA_TYPES as $name => $item): ?>
<option value="<?= $item; ?>"><?= $name; ?></option>
<? endforeach; ?>
</select>
</td>
<td class="td" style="text-align: center; center; border-right: 0;"><?= show($CONTENTS, 'column_default', 'Default'); ?> : <input type="text" name="default" value="" /></td>
<td class="td" style="text-align: center;"><input type="submit" value="<?= show($CONTENTS, 'add_tag', 'Add'); ?>" /></td>
</form>
</tr>
</table>
</div>
<? endif; ?>
<? endif; ?>
<!-- Create New Entry / Edit Record -->
<div id="data_entry_area" class="section">
<? if(isset($DATA)): ?>
<h3>[<?= $TABLE; ?>] <?= show($CONTENTS, 'edit_title', 'Edit Data for ID '); ?>:<?= $DATA['id']; ?></h3>
<a href="/manage/table/<?= $TABLE; ?>/#data_entry_area"><?= show($CONTENTS, 'new_data_title', 'Create New Data'); ?></a>
<? else: ?>
<h3><img src="/img/system/tools.png" width="25" />&nbsp;[<?= $TABLE; ?>] <?= show($CONTENTS, 'new_data_title', 'Create New Data'); ?></h3>
<? endif; ?>
<form name="data_entry" action="/manage/table/<?= $TABLE; ?>/<? if(isset($DATA['id'])): ?><?= $DATA['id']; ?>/<? endif; ?>" method="post">
<input type="hidden" name="edit" value="true" />
<? foreach($FIELDS as $i => $item): ?>
<? if($item['field'] != 'modtime' && $item['field'] != 'id'): ?>
<div class="tr">
<div class="<? if($i == 0): ?>th-top<? else: ?>th<? endif; ?>"><?= $item['field']; ?></div>
<div class="td">
<? if(strpos($item['type'], 'int') !== false): ?>
<input type="text" name="<?= $item['field']; ?>" value="<? if(isset($DATA[$item['field']])): ?><?= $DATA[$item['field']]; ?><? endif; ?>" size="20" onmouseup="number(this);" onkeyup="number(this);" />
<?= show($CONTENTS, 'number_only', '(Number Only)'); ?>
<? elseif(strpos($item['type'], 'char') !== false): ?>
<input type="text" name="<?= $item['field']; ?>" value="<? if(isset($DATA[$item['field']]) && $item['field'] != 'password'): ?><?= $DATA[$item['field']]; ?><? endif; ?>" size="50" />
<?= show($CONTENTS, 'text', '(Text)'); ?>
<? elseif(strpos($item['type'], 'time') !== false): ?>
<input type="text" name="<?= $item['field']; ?>" value="<? if(isset($DATA[$item['field']])): ?><?= $DATA[$item['field']]; ?><? endif; ?>" size="30" onmouseup="time(this);" onkeyup="time(this);" />
(Time HH-MM-SS)
<? elseif(strpos($item['type'], 'date') !== false): ?>
<input type="text" name="<?= $item['field']; ?>" value="<? if(isset($DATA[$item['field']])): ?><?= $DATA[$item['field']]; ?><? endif; ?>" size="30" onmouseup="time(this);" onkeyup="time(this);" />
(Date YYYY-MM-DD)
<? elseif(strpos($item['type'], 'text') !== false): ?>
<textarea id="<?= $item['field']; ?>_editor" cols="95" rows="20"><? if(isset($DATA[$item['field']]) && $item['field'] != 'password'): ?><?= $DATA[$item['field']]; ?><? endif; ?></textarea>
<textarea style="display: none;" name="<?= $item['field']; ?>" id="<?= $item['field']; ?>" cols="95" rows="20"><? if(isset($DATA[$item['field']]) && $item['field'] != 'password'): ?><?= $DATA[$item['field']]; ?><? endif; ?></textarea>
<script type="text/javascript">
if (!window.save_data){
	function save_data(){
		var str = '';
		for (var i in window.editors){
			var desc = document.data_entry[i]; 
			desc.value = window.editors[i].instanceById(i+'_editor').getContent();
			str += desc.value;
		};
		document.data_entry.submit();
	};
};
if (!window.editors){
	window.editors = new Array();
};
function add(name) {
	window.editors[name] = new nicEditor({fullPanel: true, onSave: save_data}).panelInstance(name+'_editor');
};
add('<?= $item['field'] ?>');
</script>
<? else: ?>
<input type="text" name="<?= $item['field']; ?>" value="<? if(isset($DATA[$item['field']])): ?><?= $DATA[$item['field']]; ?><? endif; ?>" size="70" />
(Text)
<? endif; ?>
</div>
</div>
<? else: ?>
<div class="tr">
<div class="<? if($i == 0): ?>th-top<? else: ?>th<? endif; ?>"><?= $item['field']; ?></div>
<div class="td"><? if(isset($DATA[$item['field']])): ?><?= $DATA[$item['field']]; ?><? else: ?><span class="off"><?= show($CONTENTS, 'auto_value', 'Automatic Value'); ?></span><? endif; ?></div>
</div>
<? endif; ?>
<? endforeach; ?>
<div class="tr">
<div class="th"><?= show($CONTENTS, 'save_tag', 'Save'); ?></div>
<div class="td right"><button onclick="save_data();"><?= show($CONTENTS, 'save_tag', 'Save'); ?></button></div>
</div>
</form>
</div>
<!-- List of Existing Data -->
<div class="section">
<h3><img src="/img/system/tools.png" width="25" />&nbsp;[<?= $TABLE; ?>] <?= show($CONTENTS, 'list_title', 'Data List'); ?></h3>
<table border="0" cellpadding="4" cellspacing="0">
<? foreach($LIST as $i => $item): ?>
<tr>
<? if(!empty($item)): ?>
<? $c=0; ?>
<? foreach($item as $name => $value): ?>
<td style="background-color: #EFEFFF; <? if($i == 0): ?>border-top: 1px dotted #CCCCCC;<? endif; ?> border-bottom: 1px dotted #CCCCCC; <? if($c ==0): ?>border-left: 1px dotted #CCCCCC;<? endif; ?> border-right: 1px dotted #CCCCCC;">
<?= $value; ?>
</td>
<? if($c > 1): ?>
<? break; ?>
<? endif; ?> 
<? $c++; ?>
<? endforeach; ?>
</td>
<td nowrap style="<? if($i == 0): ?>border-top: 1px dotted #CCCCCC;<? endif; ?> border-bottom: 1px dotted #CCCCCC; border-right: 1px dotted #CCCCCC;"><a href="/manage/table/<?= $TABLE ?>/<?= $item['id']; ?>/#data_entry_area"><?= show($CONTENTS, 'edit_tag', 'Edit'); ?></a></td>
<td nowrap style="<? if($i == 0): ?>border-top: 1px dotted #CCCCCC;<? endif; ?> border-bottom: 1px dotted #CCCCCC; border-right: 1px dotted #CCCCCC;"><a href="javascript:void(null);" onclick="confirmation('<<?= show($CONTENTS, 'remove_conf', 'would you liek to remove '); ?>:<?= $item['id']; ?>?', '/manage/table/<?= $TABLE ?>/<?= $item['id']; ?>/?delete=true');"><?= show($CONTENTS, 'remove_tag', 'Delete'); ?></a></td>
</tr>
<? endif; ?>
<? endforeach; ?>
</table>
</div>
<!-- table description -->
<div class="section">
<h3><img src="/img/system/tools.png" width="25" />&nbsp;[<?= $TABLE; ?>] <?= show($CONTENTS, 'desc_title', 'Table description'); ?></h3>
<form name="table_desc" action="/manage/table/<?= $TABLE; ?>/<? if(isset($DATA['id'])): ?><?= $DATA['id']; ?>/<? endif; ?>" method="post">
<input type="hidden" onsubmit="save();" name="edit_desc" value="true" />
<div class="tr">
<div class="th-top"><?= show($CONTENTS, 'desc_title', 'Table description'); ?></div>
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
</script>
<textarea cols="95" rows="20" id="desc_editor"><? if(isset($DESC['description'])): ?><?= $DESC['description']; ?><? endif; ?></textarea>
<textarea style="display: none;" cols="95" rows="20" name="description" id="description"><? if(isset($DESC['description'])): ?><?= $DESC['description']; ?><? endif; ?></textarea>
</div>
</div>
<div class="tr">
<div class="th"><?= show($CONTENTS, 'category_tag', 'Table Category'); ?></div>
<div class="td"><input type="text" name="category" value="<? if(isset($DESC['category'])): ?><?= $DESC['category']; ?><? endif; ?>" size="70" /></div>
</div>
<div class="tr">
<div class="th"><?= show($CONTENTS, 'save_tag', 'Save'); ?></div>
<div class="td right"><button onclick="save();"><?= show($CONTENTS, 'save_tag', 'Save'); ?></button></div>
</div>
</form>
</div>
</div>
</div>
</body>
</html>
