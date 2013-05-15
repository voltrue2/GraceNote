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
<? Loader::template('template', 'dbmanager/menuList.html.php'); ?>
<div class="box">
<div class="head-line"><?= $text['tableList']; ?>(<?= count($tableList); ?>)</div>
</div>
<div class="box">
<table>
<? $count = count($tableList); for ($i = 0; $i < $count; $i++): ?>
<tr>
<? if ($cmsUser['permission'] == 1): ?>
<td style="border-bottom: 1px dotted #ccc;">
<div class="delete-button" onmouseup="window.table.deleteTable('<?= $tableList[$i]['name']; ?>');"></div>
</td>
<? endif; ?>
<td style="width: 100%; border-bottom: 1px dotted #ccc;">
<div id="<?= $tableList[$i]['name']; ?>" style="border: 0;" class="list<? if ($tableList[$i]['new']): ?>-selected highlighted<? endif; ?>">
<a href="/tabledata/index/<?= $selectedDb; ?>/<?= $tableList[$i]['name']; ?>/"><?= $tableList[$i]['name']; ?></a>
</div>
</td>
</tr>
<? endfor; ?>
</table>
</div>
</div>
<? Loader::template('template', 'common/footer.html.php'); ?>
<script type="text/javascript">
<?= Asset::js('js', 'cms/table.js'); ?>
</script>
</body>
</html>
