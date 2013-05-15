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
<!-- menu -->
<div class="box menu">
<!-- back button -->
<div class="back-button menu-item" onmouseup="window.location.href = '/dbmanager/tableList/<?= $selectedDb; ?>/';"></div>
<!-- edit button -->
<div class="edit-button menu-item" onmouseup="window.location.href = '/tabledata/dataList/<?= $selectedDb; ?>/<?= $tableName; ?>/';"></div>
</div>
<div class="box">
<div class="db-name"><?= $selectedDb; ?></div>
</div>
<div class="box">
<div class="head-line"><?= $tableName; ?></div>
<div id="columnList" class="box">
<? foreach ($tableDesc as $item): ?>
<div class="box">
<!-- column name -->
<input type="text" style="width: 400px;" class="columnName" value="<?= $item['field']; ?>" />
<!-- column type -->
<select class="columnType">
<? foreach ($columnTypes as $key => $type): ?>
<option<? if ($key === $item['type']): ?> selected="selected"<? endif; ?>><?= $type; ?></option>
<? endforeach; ?>
</select>
<!-- remove column button -->
<div style="float: left;" class="delete-button" onmouseup="window.table.removeColumn('<?= $item['field']; ?>');"></div>
</div>
<? endforeach; ?>
</div>
<table>
<tr>
<!-- add column button -->
<td><div id="addColumnBtn" class="add-button"></div></td>
<!-- save button -->
<td><div id="saveBtn" class="text-button"><?= $text['save']; ?></div></tc>
</tr>
</table>
</div>
</div>
<? Loader::template('template', 'common/footer.html.php'); ?>
<script type="text/javascript">
var selectedDb = '<?= $selectedDb; ?>';
var tableName = '<?= $tableName; ?>';
var columnTypes = {};
<? foreach ($columnTypes as $key => $type): ?>
columnTypes['<?= $key; ?>'] = '<?= $type; ?>';
<? endforeach; ?>
<?= Asset::js('js', 'cms/table.js'); ?>
</script>
</body>
</html>
