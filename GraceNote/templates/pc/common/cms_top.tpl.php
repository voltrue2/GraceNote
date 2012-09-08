<!-- Language Menu -->
<div style="text-align: right; font-size: 12px; margin-bottom: 2px;">
<? if(isset($LANGS)): ?>
<? foreach($LANGS as $i => $lang): ?>
<? if($lang['lang_id'] == $CURRENT_LANG): ?>
<a class="btn_on" style="color: #FFFFFF; border: 1px solid #659EC7; background-color: #659EC7; padding: 0 2px; text-decoration: none;">
<? else: ?>
<a class="btn_off" style="color: #FFFFFF; border: 1px solid #659EC7; background-color: #357EC7; padding: 0 2px;" href="<?= $LANG_PATH.'lang='.$lang['lang_id']; ?>">
<? endif; ?>
<?= $lang['name']; ?>
</a>
<? endforeach; ?>
<? endif; ?>
</div>
<!-- Top Title Bar -->
<div class="top_title" style="color: #FFFFFF; background-color: #357EC7; padding: 4px;">
&nbsp;<img src="/img/system/home.png" width="25" />&nbsp;
<strong>
<a style="color: #FFFFFF; text-decoration: none;" href="/menu/">
<?= text($HEADER, 'title', 'PHP GraceNote Framework CMS'); ?>
</a>
</strong>
<!-- Login Status -->
<? if(isset($LOGGEDIN) && $LOGGEDIN): ?>
<div style="padding: 2px; float: right; font-size: 12px;">&nbsp;
<!-- User Name -->
<? if($LOGGEDIN_USER): ?>
<span style="color: #CCCCCC;"><?= text($HEADER, 'user_tag', 'User'); ?>
:&nbsp;</span><span style="color: #FFFFFF; font-size: 15px;"><?= $LOGGEDIN_USER['name']; ?></span>&nbsp;
<!-- User Permission -->
<span style="color: #CCCCCC;"><?= text($HEADER, 'permission_tag', 'Permission'); ?>
:&nbsp;</span><span style="color: #FFFFFF; font-size: 15px;"><?= $LOGGEDIN_USER['permission_name']; ?></span>&nbsp;
<!-- Logout Button -->
<a id="logout_btn" class="log" href="/login/out/"><?= text($HEADER, 'logout_tag', 'Logout'); ?></a>
<? endif; ?>
</div>
<? endif; ?>
</div>
<!-- Extended CMS Menu -->
<? if(isset($LOGGEDIN) && $LOGGEDIN): ?>
<? if(isset($EXTENDED_MENU)): ?>
<? if(!empty($EXTENDED_MENU)): ?>
<div style="clear: both; font-size: 12px;">
<? foreach($EXTENDED_MENU as $i => $item): ?>
<p <? if(isset($QUERIES['TYPE'])): ?><? if('/'.$QUERIES['TYPE'].'/' == $item['path']): ?>class="selected_menu"<? else: ?>class="menu"<? endif; ?><? else: ?>class="menu"<? endif; ?> style="background-color: #E3E4FA; list-style-type: none; border: 1px solid #659EC7; border-left: 1px solid #659EC7; padding: 3px; float: left; margin: 0;">
<a href="<?= $item['path']; ?>" <? if($item['target']): ?>target="<?= $item['target']; ?>"<? endif; ?> >
<? if(isset($QUERIES['TYPE'])): ?>
<? if('/'.$QUERIES['TYPE'].'/' == $item['path']): ?>
<img src="/img/system/yellow_tools.png" width="15" style="padding: 2px;" />
<? else: ?>
<img src="/img/system/blue_tools.png" width="15" style="padding: 2px;" />
<? endif; ?>
<? else: ?>
<img src="/img/system/blue_tools.png" width="15" style="padding: 2px;" />
<? endif; ?>
<?= $item['name']; ?>
</a>
</p>
<? endforeach; ?>
</div>
<? endif; ?>
<? endif; ?>
<? endif; ?>

