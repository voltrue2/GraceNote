<? Load::template('common/doc_type'); ?>
<html>
<head>
<? Load::template('common/meta'); ?>
<? Load::template('manage/script'); ?>
</head>
<body>
<? Load::template('common/cms_top'); ?>
<div class="container">
<div class="inner">
<!-- Table Structure -->
<? Load::template('manage/struct'); ?>
<!-- Create New Entry / Edit Record -->
<? Load::template('manage/data_entry'); ?>
<!-- List of Existing Data -->
<? Load::template('manage/list'); ?>
<!-- Table Description -->
<? Load::template('manage/desc'); ?>
</div>
</div>
<? Load::template('common/footer'); ?>
</body>
</html>
