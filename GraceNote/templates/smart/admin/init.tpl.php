<? include($TPL_PATH.'common/doc_type.tpl.php'); ?>
<html>
<head>
<? include($TPL_PATH.'common/meta.tpl.php'); ?>
</head>
<body>
<? include($TPL_PATH.'common/cms_top.tpl.php'); ?>
<div class="container">
<div class="inner">
<!-- Check for the initial state -->
<div style="border-bottom: 1px dotted #CCCCCC; padding: 4px; margin-top: 10px;">
<? if($INIT): ?>
<form method="post">
<?= show($CONTENTS, 'create_root', 'Please create a root user to start.'); ?><br /><br />
<?= show($CONTENTS, 'pass_tag', 'Password'); ?> : <input type="password" name="password" value="" size="30" maxlength="12" />
<input type="hidden" name="action" value="initial" />
<input type="submit" name="submit" value="Create" />
</form>
<? else: ?>
<!-- -->
<? if($LOGGEDIN): ?>
<p><strong>
<img src="/img/system/music_note.png" width="15" />
<?= show($CONTENTS, 'welcome', 'Welcome to ConnecTree CMS.'); ?>
</strong></p>
<a href="/menu/">
<img src="/img/system/check.png" width="15" />
<?= show($CONTENTS, 'start_from_here', 'Please start from here.'); ?>
</a>
<? else: ?>
<p><strong>
<img src="/img/system/music_note.png" width="15" />
<?= show($CONTENTS, 'welcome', 'Welcome to ConnecTree CMS.'); ?>
</strong></p>
<a href="/login/">
<img src="/img/system/keys.png" width="15" />
<?= show($CONTENTS, 'login_from_here', 'Please login from here.'); ?>
</a>
<? endif; ?>
<? endif; ?>
</div>
</div>
</div>
</body>
</html>
