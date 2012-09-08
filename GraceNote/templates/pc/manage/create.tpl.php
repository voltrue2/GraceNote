<? Load::template('common/doc_type'); ?>
<html>
<head>
<? Load::template('common/meta'); ?>
<script type="text/javascript">
form_auto_focus();
</script>
<? if(isset($TABLE_NAME)): ?>
<script type="text/javascript">
var count = 0;
var column_data = new Array();

function add_column(){
	var table = document.getElementById('create_table');
	var column     = '<div class="td"><span style="font-size: 14px;"><?= text($CONTENTS, 'column_name', 'Column'); ?></span>: <input type="text" name="column_name['+count+']" value="" />';
	column     += ' <span style="font-size: 14px;"><?= text($CONTENTS, 'column_type', 'Type'); ?></span>: <select name="column_type['+count+']">';
	<? foreach($DATA_TYPES as $key => $value): ?>
	column     += '<option value="<?= $value; ?>"><?= $key; ?></option>';
	<? endforeach; ?>
	column     += '</select>';
	column     += ' <span style="font-size: 14px;"><?= text($CONTENTS, 'column_default', 'Default'); ?></span>: <input type="text" name="column_default['+count+']" value="" />';
	var tr = document.createElement('div');
	tr.setAttribute('class', 'tr');
	tr.setAttribute('id', count);
	tr.innerHTML = column;
	table.appendChild(tr);
	count++;
};
</script>
<? endif; ?>

</head>
<body>
<? Load::template('common/cms_top'); ?>
<div class="container">
<div class="inner">
<div class="section">
<a href="/menu/"><img src="/img/system/go_back.png" width="20" />&nbsp;<?= text($CONTENTS, 'back_to_list', 'Back to table list'); ?></a>
</div>
<div class="section">
<? if($LOGGEDIN_USER && $LOGGEDIN_USER['permission'] == 0): ?>
<h3><img src="/img/system/tools.png" width="25" />&nbsp;<?= text($CONTENTS, 'create_new', 'Create A New Table'); ?></h3>
<form name="create_table" method="post">
<div class="tr">
<div class="th-top" colspan="3"><?= text($CONTENTS, 'table_name', 'Table Name'); ?></div>
</div>
<div class="tr">
<div class="td" colspan="3"><input type="text" name="table_name" value="<? if(isset($TABLE_NAME)): ?><?= $TABLE_NAME; ?><? endif; ?>" size="50"/></div>
</div>
<? if(isset($TABLE_NAME)): ?>
<div id="create_table"></div>
<!-- Add Column Button -->
<div class="tr">
<div class="th" colspan="3"><?= text($CONTENTS, 'add_column', 'Add Column'); ?></div>
</div>
<div class="tr">
<div class="td" style="text-align: right;" colspan="3"><a class="btn" href="javascript: void(null);" onclick="add_column();"><?= text($CONTENTS, 'add_column', 'Add Column'); ?></a></div>
</div>
<!-- Multi Lingual Enable/Disable Checkbox -->
<div class="tr">
<div class="th"><?= text($CONTENTS, 'lang_tag', 'Enable Multi Lingaual Contents'); ?></div>
<div class="td right">
<input type="checkbox" name="multi_lang" id="multi_lang" style="position: relative; top: 1px;" />
<span style="margin-left: 4px;"><label for="lang"><?= text($CONTENTS, 'lang_tag', 'Enable Multi Lingaual'); ?></label></span>
</div>
</div>
<!-- Table Category -->
<div class="tr">
<div class="th"><?= text($CONTENTS, 'category_tag', 'Table Category'); ?></div>
<div class="td right"><input type="text" name="table_category" value="" size="50" /></div>
</div>
<!-- Create Table Button -->
<div class="tr">
<div class="th"><?= text($CONTENTS, 'create_new', 'Create A New Table'); ?></div>
<div class="td" style="text-align: right;">
<input type="hidden" name="create_table" value="true" />
<input type="submit" name="submit" value="<?= text($CONTENTS, 'create_new', 'Create A New Table'); ?>" />
</div>
</div>
<? else: ?>
<div class="tr">
<div class="th" colspan="3"><?= text($CONTENTS, 'set_table_name', 'Set Table Name'); ?></div>
</div>
<div class="tr">
<div class="td" style="text-align: right;" colspan="3"><input type="submit" name="submit" value="<?= text($CONTENTS, 'set_table_name', 'Set Table Name'); ?>" /></div>
</div>
<? endif; ?>
</form>
<? else: ?>
<p style="color: #FF0000; font-weight: bold; "><?= text($CONTENTS, 'auth_warning', 'You have no permission for this action.'); ?></p>
<p><a href="/menu/"><?= text($CONTENTS, 'back_to_list', 'Back to table list'); ?></a></p>
<? endif; ?>
</div>
</div>
</div>
<? Load::template('common/footer'); ?>
</body>
</html>
