<div class="section" id="record_list">
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
<!-- List Area -->
<h3><img src="/img/system/list.png" width="25" />&nbsp;[<?= $TABLE; ?>]&nbsp;<?= text($CONTENTS, 'list_title', 'Data List'); ?></h3>
<!-- CSV Upload -->
<div class="section" style="margin-bottom: 4px; text-align: center;">
<form action="/manage/csv_upload/" method="post" enctype="multipart/form-data">
<img src="/img/system/excel.png" width="25" />
&nbsp;
<input type="hidden" name="table" value="<?= $TABLE; ?>" />
<input type="file" name="csv" />
<button><?= text($CONTENTS, 'data_entry_from_csv'); ?></button>
<!--
<? if (count($LIST) > 0): ?>
&nbsp;
&nbsp;
<a class="btn" href="/manage/csv_export/<?= $TABLE; ?>/" target="_blank"><?= text($CONTENTS, 'export_csv'); ?></a>
<? endif; ?>
-->
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
</div>
<!-- Paging -->
<? Load::template('manage/page'); ?>
<!-- List -->
<table class="list long">
<? $toggle = 0; ?>
<? $limit = 6; ?>
<? $j = 0; ?>
<tr>
<th class="th center"><input id="bulk_delete_all" type="checkbox" name="all" onclick="check_all();" /></th>
<? $multilingual = false; ?>
<? if ($FIELDS && !empty($FIELDS)):  ?>
<? foreach($FIELDS as $k => $v): ?>
<? if($v['field'] == 'lang_id'): ?>
<? $multilingual = true; ?>
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
<? endif; ?>
<td class="center th"><?= text($CONTENTS, 'edit_tag'); ?></td>
<td class="center th"><?= text($CONTENTS, 'remove_tag'); ?></td>
</tr>
<? foreach($LIST as $i => $item): ?>
<tr class="cell">
<? if($toggle == 0): ?><? $toggle = 1; ?><? else: ?><? $toggle = 0; ?><? endif; ?>
<? if(!empty($item)): ?>
<td class="th center"><input class="bulk_delete" type="checkbox" id="<?= $item['id']; ?>" /></td>
<!-- Language ID (if any) -->
<? if($multilingual): ?>
<td class="center th" onclick="window.location = '/manage/table/<?= $TABLE ?>/<?= $item['id']; ?>/'; return;">
<? foreach($LANGS as $lang): ?>
<? if(isset($item['lang_id']) && $lang['lang_id'] == $item['lang_id']): ?>
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
<? Load::template('manage/page'); ?>
</div>