<!DOCTYPE html>
<html>
<head>
<style type="text/css">
<? Loader::template('template', 'common/generalCss.html.php'); ?>
<?= Asset::css('css', 'auth/signin.css'); ?>
</style>
</head>
<body>
<? Loader::template('template', 'common/header.html.php'); ?>
<div class="top-box signin-box center">
<form action="/auth/authenticate/" method="post">
<div class="title text-center"><?= $text['signin']; ?></div>
<div class="name"><span><?= $text['accountName']; ?></span><input id="nameInput" type="text" name="user" /></div>
<div class="pass"><span><?= $text['pass']; ?></span><input type="password" name="pass" /></div>
<div class="signin"><input type="submit" value="<?= $text['signin']; ?>" /></div>
</form>
</div>
<? Loader::template('template', 'common/footer.html.php'); ?>
<script type="text/javascript">
<?= Asset::js('js', 'cms/auth.js'); ?>
</script>
</body>
</html>
