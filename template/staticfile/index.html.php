<!DOCTYPE html>
<html>
<head>
<style type="text/css">
<? Loader::template('template', 'common/generalCss.html.php'); ?>
<?= Asset::css('css', 'staticfile/index.css'); ?>
</style>
<script type="text/javascript">
</script>
</head>
<body>
<? Loader::template('template', 'common/header.html.php'); ?>
<div class="box top-box">
<div class="head-line"><?= $text['staticFileHeader']; ?></div>
<div id="header" class="box">
<table>
<tr>
<td><div id="backBtn" class="back-button"></div></td>
<td><div id="folderBtn" class="folder-button"></div></td>
<td><div id="currentPosition"></div></td>
<td>
<div>
<form id="fileUploadForm" method="post" enctype="multipart/form-data">
<input id="fileUploadPath" type="hidden" name="path" value="" />
<input id="fileUploadImages" type="file" name="images[]" multiple="" value="" />
<input id="uploadBtn" class="upload-button" type="submit" value="" />
</form>
</div>
</td>
<td><div id="massSelectBtn"></div></td>
<td><div id="massDeleteBtn" class="delete-button"></div></td>
</tr>
</table>
</div>
<div id="dirListBox" class="box"></div>
</div>
</div>
<? Loader::template('template', 'common/footer.html.php'); ?>
<script type="text/javascript">
<?= Asset::js('js', 'cms/staticfile.js'); ?>
<?= Asset::js('js', 'cms/staticfileEdit.js'); ?>
</script>
</body>
</html>
