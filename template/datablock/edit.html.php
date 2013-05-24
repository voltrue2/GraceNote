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
</div>
<div class="box">
<div class="head-line"><?= $srcData['name']; ?></div>
<? Loader::template('template', 'datablock/menu.html.php'); ?>
</div>

<!-- Data Block Source Edit -->
<div class="box">
<!-- Name -->
<div class="title"><?= $text['name']; ?><span style="font-size: 12px" class="red"><?= $text['required']; ?></span></div>
<div class="area"><input type="text" id="name" style="width: 400px;" value="<?= $srcData['name']; ?>" /></div>
<!-- Main Table -->
<div class="title"><?= $text['mainTable']; ?><span style="font-size: 12px" class="red"><?= $text['required']; ?></span></div>
<div class="area">
<select id="mainTable">
<option value=""><?= $text['select']; ?></option>
<? for ($i = 0, $len = count($tableList); $i < $len; $i++): ?>
<option value="<?= $tableList[$i]; ?>" <? if ($tableList[$i] === $srcData['main_table']): ?>selected="selected"<? endif; ?>><?= $tableList[$i]; ?></option>
<? endfor; ?>
</select>
</div>
<!-- Main Column -->
<div class="title"><?= $text['mainColumn']; ?><span style="font-size: 12px" class="red"><?= $text['required']; ?></span></div>
<div class="area">
<select id="mainColumn"></select>
</div>
<!-- Description -->
<div class="title"><?= $text['description']; ?><span style="font-size: 12px" class="grey"><?= $text['optional']; ?></span></div>
<div class="area" style="padding-top: 10px;"><textarea id="description" style="width: 500px;" cols="95" rows="10"><?= $srcData['description']; ?></textarea></div>

<div id="saveUpdateBtn" class="text-button"><?= $text['save']; ?></div>

</div>

<!-- Data Blocks Edit -->
<div class="box">
<div class="head-line"><?= $text['editDataBlock']; ?></div>
<div id="dataBlockList" class="box"></div>
<div class="box menu" style="height: 42px;">
<div id="addDataBlockBtn" class="add-button menu-item"></div>
<div id="saveDataBlockChangeBtn" class="text-button menu-item"><?= $text['save']; ?></div>
</div>
</div>
</div>
<? Loader::template('template', 'common/footer.html.php'); ?>
<script type="text/javascript">
var selectedDb = '<?= $selectedDb; ?>';
var dataBlockSourceId = '<?= $srcData['id']; ?>';
var dataBlockMainTable = '<?= $srcData['main_table']; ?>';
var dataBlockMainColumn = '<?= $srcData['main_column']; ?>';
var tableList = [];
<? for ($i = 0, $len = count($tableList); $i < $len; $i++): ?>
tableList[<?= $i; ?>] = '<?= $tableList[$i]; ?>';
<? endfor; ?>
<?= Asset::js('js', 'cms/datablock.js'); ?>
if (window.datablock.addDataBlock) {
<? for ($i = 0, $len = count($dataBlockList); $i < $len; $i++): ?>
	window.datablock.addDataBlock(
		'<?= $dataBlockList[$i]['id']; ?>', 
		'<?= $dataBlockList[$i]['name']; ?>', 
		'<?= $dataBlockList[$i]['required']; ?>', 
		'<?= $dataBlockList[$i]['type']; ?>',
		'<?= $dataBlockList[$i]['data_limit']?>',
		'<?= $dataBlockList[$i]['srctable']; ?>',
		'<?= $dataBlockList[$i]['srcrefcolumn']; ?>',
		'<?= $dataBlockList[$i]['srccolumn']; ?>',
		'<?= $dataBlockList[$i]['reftable']; ?>',
		'<?= $dataBlockList[$i]['refdisplay']; ?>',
		'<?= $dataBlockList[$i]['refcolumn']; ?>',
		'#fff'
	);
<? endfor; ?>
}
</script>
</body>
</html>
