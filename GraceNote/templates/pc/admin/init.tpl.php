<? Load::template('common/doc_type'); ?>
<html>
<head>
<? Load::template('common/meta'); ?>
</head>
<body>
<? Load::template('common/cms_top'); ?>
<div class="container">
<div class="inner">
<!-- Check for the initial state -->
<div>
<? if($INIT): ?>
<form method="post">
<?= text($CONTENTS, 'create_root', 'Please create a root user to start.'); ?><br /><br />
<?= text($CONTENTS, 'pass_tag', 'Password'); ?> : <input type="password" name="password" value="" size="30" maxlength="12" />
<input type="hidden" name="action" value="initial" />
<input type="submit" name="submit" value="Create" />
</form>
<? else: ?>
<!-- -->
<? if($LOGGEDIN): ?>
<p class="center">
<img src="/img/system/music_note.png" width="30" style="vertical-align: bottom;" />
<strong><?= text($CONTENTS, 'welcome', 'Welcome to PHP GraceNote Framework CMS.'); ?></strong>
<a href="/menu/">
<br /><br />
<img src="/img/system/lightbulb.png" width="30" style="vertical-align: bottom;" />
<?= text($CONTENTS, 'start_from_here', 'Please start from here.'); ?>
</a>
</p>
<? else: ?>
<p class="center">
<img src="/img/system/music_note.png" width="30" style="vertical-align: bottom;" />
<strong><?= text($CONTENTS, 'welcome', 'Welcome to PHP GraceNote Framework CMS.'); ?></strong>
<a href="/login/">
<br /><br />
<img src="/img/system/keys.png" width="30"  style="vertical-align: bottom;" />
<?= text($CONTENTS, 'login_from_here', 'Please login from here.'); ?>
</a>
</p>
<? endif; ?>
<? endif; ?>
</div>
</div>
</div>
<? Load::template('common/footer'); ?>
</body>
</html>
