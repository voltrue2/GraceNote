<? if(isset($LOGGEDIN_USER['root_access'])): ?>
<? if(isset($LOGGEDIN_USER['root_access'][$TABLE]) && $LOGGEDIN_USER['root_access'][$TABLE]): ?>
<div id="table_desc_area" class="section">
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
<h3><img src="/img/system/info.png" width="25" />&nbsp;[<?= $TABLE; ?>] <?= text($CONTENTS, 'desc_title', 'Table description'); ?></h3>
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
<? endif; ?>
<? endif; ?>