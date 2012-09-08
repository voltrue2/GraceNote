<? if(isset($LOGGEDIN_USER['root_access'])): ?>
<? if(isset($LOGGEDIN_USER['root_access'][$TABLE]) && $LOGGEDIN_USER['root_access'][$TABLE]): ?>
<div id="table_struc_area" class="section">
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
<h3><img src="/img/system/tools.png" width="25" />&nbsp;[<?= $TABLE; ?>] <?= text($CONTENTS, 'edit_struct', 'Edit Table Structure'); ?></h3>
<table class="list">
<tr>
<th class="th-top center"><?= text($CONTENTS, 'column_name_tag', 'Column Name'); ?></th>
<th class="th-top center"><?= text($CONTENTS, 'column_type_tag', 'Column Type'); ?></th>
<th class="th-top center"><?= text($CONTENTS, 'column_comment_tag', 'Column Detail'); ?></th>
<th class="th-top center"><?= text($CONTENTS, 'remove_tag', 'Remove'); ?></th>
</tr>
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
<? endif; ?>
</div>
<? endif; ?>
<? endif; ?>