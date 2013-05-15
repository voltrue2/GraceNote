<!DOCTYPE html>
<html>
<head>
<style type="text/css">
<? Loader::template('template', 'common/generalCss.html.php'); ?>
</style>
</head>
<body>
<? Loader::template('template', 'common/header.html.php'); ?>
<div class="box">
<div class="head-line"><?= $text['menu']; ?></div>
<div class="box">
<div class="list"><a href="/staticfile/"><?= $text['staticFile']; ?></a></div>
<div class="list"><a href="/dbmanager/"><?= $text['dbManager']; ?></a></div>
<div class="list"><a href="/cachemanager/"><?= $text['cacheManager']; ?></a></div>
</div>
</div>
<? Loader::template('template', 'common/footer.html.php'); ?>
</body>
</html>
