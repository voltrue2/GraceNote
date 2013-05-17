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
<div class="box menu">
<!-- new data button -->
<? if ($cmsUser['permission'] == 1 || $cmsUser['permission'] == 2): ?>
<div id="newRecordBtn" class="new-button menu-item"></div>
<? endif; ?>
<!-- back button -->
<? if ($from === 0 || $from - $num < 0): ?>
<div class="grey-box-button menu-item"></div>
<? else: ?>
<? if (isset($searchMode) && $searchMode): ?>
<div class="back-button menu-item" onmouseup="window.location.href = '/editdatablock/search/<?= $selectedDb; ?>/<?= $srcId; ?>/<?= $searchColumn; ?>/<?= $searchThis; ?>/<?= ($from - $num >= 0) ? ($from - $num) : 0; ?>/';"></div>
<? else: ?>
<div class="back-button menu-item" onmouseup="window.location.href = '/editdatablock/index/<?= $selectedDb; ?>/<?= $srcId; ?>/<?= ($from - $num >= 0) ? ($from - $num) : 0; ?>/';"></div>
<? endif; ?>
<? endif; ?>
<!-- forward button -->
<? if (isset($searchMode) && $searchMode): ?>
<div class="forward-button menu-item" onmouseup="window.location.href = '/editdatablock/search/<?= $selectedDb; ?>/<?= $srcId; ?>/<?= $searchColumn; ?>/<?= $searchThis; ?>/<?= $to; ?>/';"></div>
<? else: ?>
<div class="forward-button menu-item" onmouseup="window.location.href = '/editdatablock/index/<?= $selectedDb; ?>/<?= $srcId; ?>/<?= $to; ?>/';"></div>
<? endif; ?>
<!-- search -->
<div class="menu-item" style="line-height: 34px; font-size: 15px; color: #333; margin: 0 20px;"><?= '(' . $from . ' - ' . ($from + count($list)) . ')'; ?></div>
<div class="refresh-button menu-item" onmouseup="window.location.href='/editdatablock/index/<?= $selectedDb; ?>/<?= $srcId; ?>/';"></div>
<div class="search-button menu-item" id="searchButton"></div>
<div class="menu-item" id="searchColumn"></div>
<input class="menu-item" type="text" id="searchText" value="<?= (isset($searchThis)) ? urldecode($searchThis) : ''; ?>" />
</div>
<div class="head-line"><?= $src['name']; ?></div>
<!-- List of Data Block Source Data -->
<div class="box" id="topScroll"></div>
<div class="box" style="overflow: scroll; height: 750px;" id="spreadSheet"></div>
</div>
</div>
<? Loader::template('template', 'common/footer.html.php'); ?>
<script type="text/javascript">
<?= Asset::js('js', 'cms/staticfile.js'); ?>
<?= Asset::js('js', 'cms/editdatablock.js'); ?>
</script>
</body>
</html>
