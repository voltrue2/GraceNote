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
<div class="head-line"><?= $text['createDataBlock']; ?></div>
<? Loader::template('template', 'datablock/menu.html.php'); ?>
</div>
<div class="box">
<div class="box">
<!-- Name -->
<div class="title"><?= $text['name']; ?><span style="font-size: 12px" class="red"><?= $text['required']; ?></span></div>
<div class="area"><input type="text" id="name" style="width: 400px;" value="" /></div>
<!-- Main Table -->
<div class="title"><?= $text['mainTable']; ?><span style="font-size: 12px" class="red"><?= $text['required']; ?></span></div>
<div class="area">
<select id="mainTable">
<option value=""><?= $text['select']; ?></option>
<? for ($i = 0, $len = count($tableList); $i < $len; $i++): ?>
<option value="<?= $tableList[$i]; ?>"><?= $tableList[$i]; ?></option>
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
<div class="area" style="padding-top: 10px;"><textarea id="description" style="width: 500px" cols="95" rows="10"></textarea></div>

<div id="saveCreateBtn" class="text-button"><?= $text['save']; ?></div>

</box>
</div>
<? Loader::template('template', 'common/footer.html.php'); ?>
<script type="text/javascript">
var selectedDb = '<?= $selectedDb; ?>';
<?= Asset::js('js', 'cms/datablock.js'); ?>
</script>
</body>
</html>
