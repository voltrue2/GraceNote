<script type="text/javascript">
var selectedDb = '<?= $selectedDb; ?>';
</script>
<div class="box">
<div class="db-name"><?= ($selectedDb) ? $selectedDb : $text['selectDb']; ?></div>
</div>
<div class="tab">
<div class="tab-item"><a href="/dbmanager/"><?= $text['selectDb']; ?></a></div>
<? if($cmsUser['permission'] === 1): ?>
<div class="tab-item<? if($currentPage === 'createTable'): ?>-active<? endif; ?>"><a href="/dbmanager/createTable/<?= $selectedDb; ?>/"><?= $text['createTable']; ?></a></div>
<? endif; ?>
<div class="tab-item<? if($currentPage === 'tableList'): ?>-active<? endif; ?>"><a href="/dbmanager/tableList/<?= $selectedDb; ?>/"><?= $text['tableList']; ?></a></div>
<div class="tab-item<? if($currentPage === 'manageDataBlock'): ?>-active<? endif; ?>"><a href="/datablock/index/<?= $selectedDb; ?>/"><?= $text['manageDataBlock']; ?></a></div>
</div>
