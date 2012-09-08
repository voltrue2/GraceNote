<? include($TPL_PATH.'common/doc_type.tpl.php'); ?>
<html>
<head>
<? include($TPL_PATH.'common/meta.tpl.php'); ?>
<script type="text/javascript">
form_auto_focus();
</script>
</head>
<body>
<? include($TPL_PATH.'common/cms_top.tpl.php'); ?>
<div class="container">
<div class="inner">
<h2><img src="/img/system/keys.png" width="35" />&nbsp;<?= show($CONTENTS, 'login_title', 'CMS Login'); ?></h2>
<table border="0" cellspacing="0" cellpadding="4">
<form action="/login/check/" method="post">
<input type="hidden" name="RETURN" value="<?= $QUERIES['RETURN']; ?>" />
<tr>
<td style="border: 1px dotted #CCCCCC; background-color: #EFEFFF;"><?= show($CONTENTS, 'user_tag', 'User'); ?></td>
<td style="border-right: 1px dotted #CCCCCC; border-top: 1px dotted #CCCCCC; border-bottom: 1px dotted #CCCCCC;"><input type="text" name="name" value="" size="30" /></td>
<tr>
<tr>
<td style="border-right: 1px dotted #CCCCCC; border-left: 1px dotted #CCCCCC; border-bottom: 1px dotted #CCCCCC; background-color: #EFEFFF;"><?= show($CONTENTS, 'pass_tag', 'Password'); ?></td>
<td style="border-right: 1px dotted #CCCCCC; border-bottom: 1px dotted #CCCCCC;"><input type="password" name="password" value="" size="30" /></td>
</tr>
<? if(!$LOGIN): ?>
<tr>
<td style="border-right: 1px dotted #CCCCCC; border-left: 1px dotted #CCCCCC; border-bottom: 1px dotted #CCCCCC; background-color: #EFEFFF;"><?= show($CONTENTS, 'msg', 'Message'); ?></td>
<td style="border-right: 1px dotted #CCCCCC; dotted #CCCCCC; border-bottom: 1px dotted #CCCCCC; color: #FF0000;"><img src="/img/system/stop.png" width="15" />&nbsp;<?= show($CONTENTS, 'error_message', 'Login failed'); ?></td>
</tr>
<? endif; ?>
<tr>
<td style="border-right: 1px dotted #CCCCCC; border-left: 1px dotted #CCCCCC; border-bottom: 1px dotted #CCCCCC; background-color: #EFEFFF;"><?= show($CONTENTS, 'login_tag', 'Login'); ?></td>
<td style="border-right: 1px dotted #CCCCCC; dotted #CCCCCC; border-bottom: 1px dotted #CCCCCC; text-align: right;"><input type="submit" name="submit" value="<?= show($CONTENTS, 'login_tag', 'Login'); ?>" /></td>
</tr>
</form>
</table>
</div>
</div>
</body>
</html>
