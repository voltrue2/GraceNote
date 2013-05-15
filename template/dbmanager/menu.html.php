<!DOCTYPE html>
<html>
<head>
<style type="text/css">
<? Loader::template('template', 'common/generalCss.html.php'); ?>
</style>
<script type="text/javascript">
</script>
</head>
<body>
<? Loader::template('template', 'common/header.html.php'); ?>
<div class="box">
<div class="head-line"><?= $text['menu']; ?></div>
<? Loader::template('template', 'dbmanager/menuList.html.php'); ?>
</div>
<? Loader::template('template', 'common/footer.html.php'); ?>
</body>
</html>
