<? Load::template('common/doc_type'); ?>
<html>
<head>
<? Load::template('common/meta'); ?>
<script type="text/javascript">
form_auto_focus();
</script>
</head>
<body>
<? Load::template('common/cms_top'); ?>
<div class="container">
<div class="inner">
<h2 class="center"><img src="/img/system/keys.png" width="35" />&nbsp;<?= text($CONTENTS, 'login_title', 'CMS Login'); ?></h2>

<table class="list login">
<form action="/login/check/" method="post">
<input type="hidden" name="RETURN" value="<?= $QUERIES['RETURN']; ?>" />
<tr>
<th nowrap><?= text($CONTENTS, 'user_tag', 'User'); ?></th>
<td><input type="text" name="name" value="" size="30" /></td>
<tr>
<tr>
<th nowrap><?= text($CONTENTS, 'pass_tag', 'Password'); ?></th>
<td><input type="password" name="password" value="" size="30" /></td>
</tr>
<? if(!$LOGIN): ?>
<tr>
<th><?= text($CONTENTS, 'msg', 'Message'); ?></th>
<td style="color: #FF0000;"><img src="/img/system/alert.png" width="25" style="vertical-align: bottom;" />&nbsp;&nbsp;&nbsp;<?= text($CONTENTS, 'error_message', 'Login failed'); ?></td>
</tr>
<? endif; ?>
<tr>
<td colspan="2" class="center"><input type="submit" name="submit" value="<?= text($CONTENTS, 'login_tag', 'Login'); ?>" /></td>
</tr>
</form>
</table>
</div>
</div>
<? Load::template('common/footer'); ?>
</body>
</html>
