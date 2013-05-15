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
<div class="box">
<? $count = count($dbList); for ($i = 0; $i < $count; $i++): ?>
<div class="list"><a href="/dbmanager/menu/<?= $dbList[$i]; ?>/"><?= $dbList[$i]; ?></a></div>
<? endfor; ?>
</div>
</div>
<? Loader::template('template', 'common/footer.html.php'); ?>
</body>
</html>
