<!-- Language Menu -->
<div style="text-align: right; font-size: 12px; margin-bottom: 2px;">
<? foreach($LANGS as $i => $lang): ?>
<? if($lang['id'] == $CURRENT_LANG): ?>
<a style="color: #FFFFFF; border: 1px solid #FFFFFF; background-color: #659EC7; padding: 0 2px; text-decoration: none;">
<? else: ?>
<a style="color: #FFFFFF; border: 1px solid #FFFFFF; background-color: #357EC7; padding: 0 2px;" href="<?= $LANG_PATH.'lang='.$lang['lang_id']; ?>">
<? endif; ?>
<?= $lang['name']; ?>
</a>
<? endforeach; ?>
</div>
<!-- Top Title Bar -->
<div style="color: #FFFFFF; background-color: #357EC7; padding: 4px;">
&nbsp;<img src="/img/system/logo.png" width="20" />&nbsp;
<strong>
<a style="color: #FFFFFF; text-decoration: none;" href="/menu/">
<?= show($HEADER, 'title', 'ConnecTree CMS'); ?>
</a>
</strong>
<!-- Login Status -->
<? if(isset($LOGGEDIN) && $LOGGEDIN): ?>
<div style="float: right; font-size: 12px; padding: 4px;">&nbsp;
<!-- User Name -->
<? if($LOGGEDIN_USER): ?>
<span style="color: #CCCCCC;"><?= show($HEADER, 'user_tag', 'User'); ?>
:&nbsp;</span><span style="color: #FFFFFF; font-size: 15px;"><?= $LOGGEDIN_USER['name']; ?></span>&nbsp;
<!-- User Permission -->
<span style="color: #CCCCCC;"><?= show($HEADER, 'permission_tag', 'Permission'); ?>
:&nbsp;</span><span style="color: #FFFFFF; font-size: 15px;"><?= $LOGGEDIN_USER['permission']; ?></span>&nbsp;
<!-- Logout Button -->
<a class="log" href="/login/out/"><?= show($HEADER, 'logout_tag', 'Logout'); ?></a>
<? endif; ?>
</div>
<? endif; ?>
</div>
<!-- Extended CMS Menu -->
<? if(isset($LOGGEDIN) && $LOGGEDIN): ?>
<? if(isset($EXTENDED_MENU)): ?>
<? if(!empty($EXTENDED_MENU)): ?>
<div style="clear: both; font-size: 14px; text-align: center; padding-top: 10px;">
<div style="text-align: left; background-image: url('/img/system/top_menu_bk.png'); border: 1px solid #659EC7;">
<? foreach($EXTENDED_MENU as $i => $item): ?>
<div style="display: table-cell; padding: 4px 8px 4px 4px; border-left: 4px solid #659EC7; border-right: 1px solid #659EC7; <? if('/'.$QUERIES['TYPE'].'/' == $item['path']): ?>background-color: #FFFFFF;<? endif; ?>">
<a href="<?= $item['path']; ?>" <? if($item['target']): ?>target="<?= $item['target']; ?>"<? endif; ?> >
<img src="/img/system/blue_tools.png" width="15" />
<?= $item['name']; ?>
</a>
</div>
<? endforeach; ?>
</div>
</div>
<? endif; ?>
<? endif; ?>
<? endif; ?>