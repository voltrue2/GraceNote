<? Load::template('common/doc_type'); ?>
<html>
<head>
<? Load::template('common/meta'); ?>
</head>
<body>
<? Load::template('common/cms_top'); ?>
<div class="container">
<div class="inner">
<!-- Back Button -->
<div class="section"><a href="/menu/"><img src="/img/system/go_back.png" width="20" />&nbsp;<?= text($CONTENTS, 'back_to_list', 'Back to table list'); ?></a></div>
<!-- Admin User Edit -->
<div class="section">
<h3><img src="/img/system/user.png" width="25" />&nbsp;<?= text($CONTENTS, 'admin_user_title_tag', 'Admin User Management'); ?></h3>
<? if($ADMIN_LIST): ?>
<table border="0" cellpadding="4" cellspacing="0" width="100%">
<tr style="border: 1px #CCCCCC dotted;">
<th class="top_title" style="background-color: #579FE9; color: #FFFFFF; text-align: center; border-left: 1px #CCCCCC dotted; border-right: 1px #CCCCCC dotted; border-top: 1px #CCCCCC dotted; border-bottom: 1px #CCCCCC dotted;"><?= text($CONTENTS, 'admin_tag', 'Admin User'); ?></th>
<th class="top_title" style="background-color: #579FE9; color: #FFFFFF; text-align: center; border-right: 1px #CCCCCC dotted; border-top: 1px #CCCCCC dotted; border-bottom: 1px #CCCCCC dotted;"><?= text($CONTENTS, 'admin_permission_tag', 'Admin User Permission'); ?></th>
<th class="top_title" style="background-color: #579FE9; color: #FFFFFF; text-align: center; border-right: 1px #CCCCCC dotted; border-top: 1px #CCCCCC dotted; border-bottom: 1px #CCCCCC dotted;"><?= text($CONTENTS, 'admin_delete_tag', 'Delete Admin User'); ?></th>
</tr>
<? $toggle = 0; ?>
<? foreach($ADMIN_LIST as $i => $admin): ?>
<tr>
<!-- Admin Edit -->
<td <? if($toggle === 0): ?>class="th-top"<? endif; ?> style="border-bottom: 1px #CCCCCC dotted; border-left: 1px #CCCCCC dotted;">
<form action="/permissionmanager/edit_admin/" method="post">
<input type="hidden" name="id" value="<?= $admin['id']; ?>" />
<label for="name" style="font-size: 11px;"><?= text($CONTENTS, 'admin_name_tag', 'User'); ?></label><input id="name" type="text" name="name" value="<?= $admin['name']; ?>" size="10" />
<label for="password" style="font-size: 11px;"><?= text($CONTENTS, 'admin_password_tag', 'Password'); ?></label><input id="password" type="password" name="password" value="" size="10" />
<label for="media_restriction" style="font-size: 11px;"><?= text($CONTENTS, 'admin_media_restriction_tag', 'Media Path'); ?></label><input id="media_restriction" type="text" name="media_restriction" value="<?= $admin['media_restriction']; ?>" size="15" />
<input type="submit" value="<?= text($CONTENTS, 'update_btn', 'Update'); ?>" />

</form>
</td>
<!-- Permission Edit -->
<td <? if($toggle === 0): ?>class="th-top"<? endif; ?> style="border-bottom: 1px #CCCCCC dotted; border-left: 1px #CCCCCC dotted;">
<form action="/permissionmanager/edit_permission/" method="post">
<select name="permission">
<option><?= text($CONTENTS, 'please_select', '-- Please Select --'); ?></option>
<? foreach($PERMISSION_NAME_LIST as $i => $item): ?>
<option value="<?= $item['permission_id']; ?>" <? if($item['permission_id'] == $admin['permission']): ?>selected="selected"<? endif; ?>><?= $item['name']; ?></option>
<? endforeach; ?>
</select>
<input type="hidden" name="id" value="<?= $admin['id']; ?>" />
<input type="submit" value="<?= text($CONTENTS, 'update_btn', 'Update'); ?>" />
</form>
</td>
<!-- Admin Delete -->
<td <? if($toggle === 0): ?>class="th-top"<? endif; ?> style="text-align: center; border-bottom: 1px #CCCCCC dotted; border-left: 1px #CCCCCC dotted; border-right: 1px #CCCCCC dotted;">
<a class="delete_btn" href="javascript:void(null);" onclick="confirmation('<?= text($CONTENTS, 'admin_delete_msg', 'Would you like to delete '); ?>:&nbsp;<?= $admin['name'].' ('.$item['name'].')'; ?> ?', '/permissionmanager/delete_admin/<?= $admin['id']; ?>/');"><?= text($CONTENTS, 'delete_btn', 'Delete'); ?></a>
</td>
</tr>
<? if($toggle === 0): ?><? $toggle = 1; ?><? else: ?><? $toggle = 0; ?><? endif; ?>
<? endforeach; ?>
</table>
<? endif; ?>
</div>
<!-- Create Admin User -->
<div class="section">
<table border="0" cellpadding="4" cellspacing="0" width="100%">
<tr style="border: 1px #CCCCCC dotted;">
<th class="top_title" style="background-color: #579FE9; color: #FFFFFF; text-align: center; border-left: 1px #CCCCCC dotted; border-right: 1px #CCCCCC dotted; border-top: 1px #CCCCCC dotted; border-bottom: 1px #CCCCCC dotted;"><?= text($CONTENTS, 'create_admin_tag', 'Create A New Admin User'); ?></th>
</tr>
<tr>
<form action="/permissionmanager/create_admin/" method="post">
<td style="text-align: center; border-left: 1px #CCCCCC dotted; border-right: 1px #CCCCCC dotted; border-bottom: 1px #CCCCCC dotted;">
<?= text($CONTENTS, 'admin_name_tag', 'User'); ?><input type="text" name="name" value="" size="10" />&nbsp;
<?= text($CONTENTS, 'admin_password_tag', 'Password'); ?><input type="password" name="password" value="" size="10" />
<?= text($CONTENTS, 'admin_media_restriction_tag', 'Media Path'); ?><input type="text" name="media_restriction" value="" size="20" />
<select name="permission">
<option><?= text($CONTENTS, 'please_select', '-- Please Select --'); ?></option>
<? foreach($PERMISSION_NAME_LIST as $i => $item): ?>
<option value="<?= $item['permission_id']; ?>"><?= $item['name']; ?></option>
<? if($toggle === 0): ?><? $toggle = 1; ?><? else: ?><? $toggle = 0; ?><? endif; ?>
<? endforeach; ?>
</select>
<input type="submit" value="<?= text($CONTENTS, 'create_btn', 'Create'); ?>" />
</td>
</form>
</tr>
</table>
</div>
<!-- Create Admin User -->
<div class="section">
<table border="0" cellpadding="4" cellspacing="0" width="100%">
<tr style="border: 1px #CCCCCC dotted;">
<th class="top_title" style="background-color: #579FE9; color: #FFFFFF; text-align: center;  border-left: 1px #CCCCCC dotted;  border-right: 1px #CCCCCC dotted;  border-top: 1px #CCCCCC dotted; border-bottom: 1px #CCCCCC dotted;"><?= text($CONTENTS, 'create_permission_tag', 'Create A New Admin Permission'); ?></th>
</tr>
<tr>
<form action="/permissionmanager/create_permission/" method="post">
<td style="text-align: center; border-left: 1px #CCCCCC dotted; border-right: 1px #CCCCCC dotted; border-bottom: 1px #CCCCCC dotted;">
<?= text($CONTENTS, 'permission_name_tag', 'Name'); ?>&nbsp;:&nbsp;<input type="text" name="name" value="" />&nbsp;
<input type="submit" value="<?= text($CONTENTS, 'create_btn', 'Create'); ?>" />
</td>
</form>
</tr>
</table>
</div>
<!-- Admin User Permissions -->
<div class="section">
<h3><img src="/img/system/lock.png" width="25" />&nbsp;<?= text($CONTENTS, 'admin_permission_title_tag', 'Admin User Permission Management'); ?></h3>
<table border="0" cellpadding="4" cellspacing="0" width="100%">
<tr>
<th colspan="2" class="th-top" style="text-align: center; border: 1px solid #CCCCCC;"><?= text($CONTENTS, 'admin_permission_tag', 'Admin User Permission'); ?></th>
</tr>
<tr>
<td class="td" style="border-left: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; border-bottom: 1px solid #CCCCCC;">
<form action="/permissionmanager/edit_access/" method="get">
<select name="permission">
<option><?= text($CONTENTS, 'please_select', '-- Please Select --'); ?></option>
<? foreach($PERMISSION_NAME_LIST as $i => $item): ?>
<option value="<?= $item['permission_id']; ?>" <? if(isset($PERMISSION)): ?><? if($PERMISSION == $item['permission_id']): ?>selected="selected"<? endif; ?><? endif; ?>><?= $item['name']; ?></option>
<? endforeach; ?>
</select>
<input type="submit" value="<?= text($CONTENTS, 'select_btn', 'Select'); ?>" />
</form>
</td>
<td style=" text-align: center; border-right: 1px solid #CCCCCC; border-bottom: 1px solid #CCCCCC;">
<? if(isset($PERMISSION)): ?>
<a class="delete_btn" href="javascript: void(null)" onclick="confirmation('<?= text($CONTENTS, 'admin_delete_msg', 'Would you like to delete '); ?>:&nbsp;<?= $item['name']; ?> ?', '/permissionmanager/delete_permission/?permission=<?= $PERMISSION; ?>');"><?= text($CONTENTS, 'delete_btn', 'Delete'); ?></a>
<? else: ?>
<span style="color: #CCCCCC; "><?= text($CONTENTS, 'void_tag', 'None'); ?></span>
<? endif; ?>
</td>
</tr>
</table>
</div>
<!-- Access List -->
<? if(isset($EXT_LIST) && isset($TABLE_LIST)): ?>
<div class="section">
<table class="list">
<tr>
<th class="th-top center"><?= text($CONTENTS, 'access_name_tag', 'Access Name'); ?></th>
<th class="th-top center"><?= text($CONTENTS, 'access_flag_tag', 'Allowed'); ?></th>
<th class="th-top center"><?= text($CONTENTS, 'root_flag_tag', 'Root Access'); ?></th>
</tr>
<form action="/permissionmanager/edit_access_list/" method="post">
<input type="hidden" name="permission" value="<?= $PERMISSION; ?>" />
<? $c = '#FFFFFF'; ?>
<? foreach($EXT_LIST as $i => $item): ?>
<tr>
<td style="background-color: <?= $c; ?>; <? if(isset($ACCESS_LIST)): ?><? if(isset($ACCESS_LIST[str_replace('/', '', $item['path'])])): ?>color: #009900;<? else: ?>color: #999999;<? endif; ?><? endif; ?>">
<? if(isset($ACCESS_LIST)): ?>
<? $key = str_replace('/', '', $item['path']); ?>
<? if(isset($ACCESS_LIST[$key])): ?>
<? if($ACCESS_LIST[$key]['root']): ?>
<img src="/img/system/check.png" width="20px;" style="padding-right: 6px;">
<? else: ?>
<img src="/img/system/grey_check.png" width="20px;" style="padding-right: 6px;">
<? endif; ?>
<? else: ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<? endif; ?>
<? endif; ?>
<?= str_replace('/', '', $item['path']); ?>
</td>
<td class="center" style="background-color: <?= $c; ?>;">
<input type="checkbox" name="access_list[<?= str_replace('/', '', $item['path']); ?>]" <? if(isset($ACCESS_LIST)): ?><? if(isset($ACCESS_LIST[str_replace('/', '', $item['path'])])): ?>checked="checked"<? endif; ?><? endif; ?> />
</td>
<td class="center" style="background-color: <?= $c; ?>;">
<input type="checkbox" name="root_list[<?= str_replace('/', '', $item['path']); ?>]" <? if(isset($ROOT_LIST)): ?><? if(isset($ROOT_LIST[str_replace('/', '', $item['path'])]) && $ROOT_LIST[str_replace('/', '', $item['path'])]): ?>checked="checked"<? endif; ?><? endif; ?> />
</td>
</tr>
<? if($c == '#EFEFFF'): ?><? $c = '#FFFFFF'; ?><? else: ?><? $c = '#EFEFFF'; ?><? endif; ?>
<? endforeach; ?>
<? $c = '#FFFFFF'; ?>
<tr>
<th class="th-top center"><?= text($CONTENTS, 'access_table_tag'); ?></th>
<th class="th-top center"><?= text($CONTENTS, 'access_flag_tag', 'Allowed'); ?></th>
<th class="th-top center"><?= text($CONTENTS, 'root_flag_tag', 'Root Access'); ?></th>
</tr>
<? foreach($TABLE_LIST as $i => $item): ?>
<tr>
<td style="background-color: <?= $c; ?>; <? if(isset($ACCESS_LIST)): ?><? if(isset($ACCESS_LIST[$item['table_name']])): ?>color: #009900;<? else: ?>color: #999999;<? endif; ?><? endif; ?>">
<? if(isset($ACCESS_LIST)): ?>
<? if(isset($ACCESS_LIST[$item['table_name']])): ?>
<? if($ACCESS_LIST[$item['table_name']]['root']): ?>
<img src="/img/system/check.png" width="20px;" style="padding-right: 6px;">
<? else: ?>
<img src="/img/system/grey_check.png" width="20px;" style="padding-right: 6px;">
<? endif; ?>
<? else: ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<? endif; ?>
<? endif; ?>
<?= $item['table_name']; ?>
</td>
<td class="center" style="background-color: <?= $c; ?>;">
<input type="checkbox" name="access_list[<?= $item['table_name']; ?>]" <? if(isset($ACCESS_LIST)): ?><? if(isset($ACCESS_LIST[$item['table_name']])): ?>checked="checked"<? endif; ?><? endif; ?> />
</td>
<td class="center" style="background-color: <?= $c; ?>;">
<input type="checkbox" name="root_list[<?= $item['table_name']; ?>]" <? if(isset($ROOT_LIST)): ?><? if(isset($ROOT_LIST[$item['table_name']]) && $ROOT_LIST[$item['table_name']]): ?>checked="checked"<? endif; ?><? endif; ?> />
</td>
</tr>
<? if($c == '#EFEFFF'): ?><? $c = '#FFFFFF'; ?><? else: ?><? $c = '#EFEFFF'; ?><? endif; ?>
<? endforeach; ?>
<tr>
<td class="right" colspan="3"><input type="submit" value="<?= text($CONTENTS, 'update_btn', 'Update'); ?>" /></td>
</tr>
</form>
</table>
</div>
<? endif; ?>
</div>
</div>
<? Load::template('common/footer'); ?>
</body>
</html>
