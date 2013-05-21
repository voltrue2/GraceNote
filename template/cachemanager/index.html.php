<!DOCTYPE html>
<html>
<head>
<style type="text/css">
<? Loader::template('template', 'common/generalCss.html.php'); ?>
</style>
</head>
<body>
<!-- header -->
<? Loader::template('template', 'common/header.html.php'); ?>
<!-- title -->
<div class="box">
<div class="head-line"><?= $text['cacheManager']; ?></div>
</div>
<!-- paging menu -->
<div class="box menu">
<!-- back button -->
<? if ($from - $num < 0): ?>
<div class="grey-box-button menu-item"></div>
<? else: ?>
<div class="back-button menu-item" onmouseup="window.location.href='/cachemanager/index/<?= $from - $num; ?>/<?= ($search) ? $search . '/' : ''; ?>';"></div>
<? endif; ?>
<!-- forward button -->
<div class="forward-button menu-item" onmouseup="window.location.href='/cachemanager/index/<?= $from + $num; ?>/<?= ($search) ? $search . '/' : ''; ?>';"></div>
<!-- displaying number -->
<div class="menu-item" style="font-size: 20px; margin-left: 30px; line-height: 32px;" id="numDisplay">(<?= $from; ?> - <?= $to; ?>)</div>
<!-- reset search button -->
<div class="refresh-button menu-item" style="margin-left: 30px;" onmouseup="window.location.href='/cachemanager/index/<?= ($from) ? $from : 0; ?>/';"></div>
<!-- search -->
<div class="search-button menu-item" id="searchBtn" style="margin-left: 30px;"></div>
<input type="text" value="<?= $search; ?>" id="searchField" style="width: 500px;" />
</div>
<!-- list of memcahe keys -->
<div class="box">
<? for ($i = 0, $len = count($list); $i < $len; $i++): ?>
<div id="<?= $i; ?>" class="menu" style="border-bottom: 1px solid #ccc;">
<!--<div class="menu-item checkbox"></div>-->
<div class="menu-item image-button" onmouseup="window.getPreview(<?= $i; ?>)"></div>
<div class="menu-item delete-button" onmouseup="window.deleteCache(<?= $i; ?>)"></div>
<div class="menu-item" style="text-indent: 20px; font-size: 15px; line-height: 30px;">
<?= (strlen($list[$i]['key']) >= 100) ? substr($list[$i]['key'], 0, 100) . '...' : $list[$i]['key']; ?>
</div>
</div>
<? endfor; ?>
</div>
<!-- footer -->
<? Loader::template('template', 'common/footer.html.php'); ?>
<script type="text/javascript">
<?= Asset::js('js', 'cms/cachemanager.js'); ?>
</script>
</body>
</html>
