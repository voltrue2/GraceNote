<!DOCTYPE html>
<html>
<head>
<style type="text/css">
<? Loader::template('template', 'common/generalCss.html.php'); ?>
</style>
<script type="text/javascript">
var columnTypes = {};
<? foreach($columnTypes as $key => $value): ?>
columnTypes['<?= $key; ?>'] = '<?= $value; ?>';
<? endforeach; ?>
</script>
</head>
<body>
<? Loader::template('template', 'common/header.html.php'); ?>
<div class="box">
<? Loader::template('template', 'dbmanager/menuList.html.php'); ?>
<div class="box">
<div class="head-line"><?= $text['createTable']; ?></div>
<div class="box">
<form>
<?= $text['tableName']; ?><input id="table" type="text" name="table" value="" />
<div id="createTableBtn" class="text-button"><?= $text['createTable']; ?></div>
<div id="columns" class="box"></div>
<div id="addColumnBtn" class="add-button"></div>
</form>
</div>
</div>
</div>
<? Loader::template('template', 'common/footer.html.php'); ?>
<script type="text/javascript">
<?= Asset::js('js', 'cms/dbManager.js'); ?>
</script>
</body>
</html>
