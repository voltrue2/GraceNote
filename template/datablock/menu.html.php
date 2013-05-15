<div class="box menu">
<? if ($cmsUser['permission'] == 1): ?>
<div class="tool-button menu-item" onmouseup="window.location.href='/datablock/create/<?= $selectedDb; ?>/'; "></div>
<? endif; ?>
<div class="search-button menu-item" onmouseup="window.location.href='/datablock/index/<?= $selectedDb; ?>/'; "></div>
</div>
<script type="text/javascript">
var dataBlockTypes = [];
<? for ($i = 0, $len = count($dataBlockTypes); $i < $len; $i++): ?>
dataBlockTypes[<?= $i ?>] = { id: '<?= $dataBlockTypes[$i]['id']; ?>', name: '<?= $dataBlockTypes[$i]['name']; ?>' };
<? endfor; ?>
</script>
