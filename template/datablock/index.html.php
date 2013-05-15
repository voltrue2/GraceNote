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
<? Loader::template('template', 'dbmanager/menuList.html.php'); ?>
<div class="head-line"><?= $text['manageDataBlock']; ?></div>
<? Loader::template('template', 'datablock/menu.html.php'); ?>
<!-- Data Block Source List -->
<? for ($i = 0, $count = count($datablockList); $i < $count; $i++): ?>
<div class="box">
<div class="title"><?= $datablockList[$i]['name']; ?></div>
<div class="area menu">
<? if ($cmsUser['permission'] == 1): ?>
<div class="edit-button menu-item" onmouseup="window.location.href='/datablock/editDataBlock/<?= $selectedDb; ?>/<?= $datablockList[$i]['id']; ?>/';"></div>
<? endif; ?>
<div class="search-button menu-item" onmouseup="window.location.href='/editdatablock/index/<?= $selectedDb; ?>/<?= $datablockList[$i]['id']; ?>/';"></div>
<? if ($cmsUser['permission'] == 1): ?>
<div class="delete-button menu-item" onmouseup="window.datablock.deleteDataBlockSource(<?= $datablockList[$i]['id']; ?>, '<?= $datablockList[$i]['name']; ?>');"></div>
<? endif; ?>
</div>
<div class="area"><?= $datablockList[$i]['description']; ?></div>
</div>
<? endfor; ?>
</div>
<? Loader::template('template', 'common/footer.html.php'); ?>
<script type="text/javascript">
<?= Asset::js('js', 'cms/datablock.js'); ?>
</script>
</body>
</html>
