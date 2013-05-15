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
<!-- tab menu -->
<? Loader::template('template', 'dbmanager/menuList.html.php'); ?>
<!-- menu -->
<div class="box menu">
<!-- back button -->
<? if ($from - $to < 0): ?>
<div class="grey-box-button menu-item"></div>
<? else: ?>
<div class="back-button menu-item" onmouseup="window.location.href = '/tabledata/dataList/<?= $selectedDb; ?>/<?= $tableName; ?>/<?= $from - $to; ?>/<?= ($searchCol && $search) ? $searchCol . '/' . $search . '/' : ''; ?>';"></div>
<? endif; ?>
<!-- forward button -->
<div class="forward-button menu-item" onmouseup="window.location.href = '/tabledata/dataList/<?= $selectedDb; ?>/<?= $tableName; ?>/<?= $from + $to; ?>/<?= ($searchCol && $search) ? $searchCol . '/' . $search . '/': ''; ?>';"></div>
<!-- position display -->
<div class="menu-item" style="padding-left: 30px; font-size: 15px; line-height: 40px;">(<?= $from; ?> - <?= $from + count($list); ?>)</div>
<!-- new data button -->
<? if ($cmsUser['permission'] == 1 || $cmsUser['permission'] == 2): ?>
<div class="new-button menu-item" style="margin-left: 30px;" onmouseup="window.getDataCreator();"></div>
<? endif; ?>
<!-- refresh button -->
<div class="refresh-button menu-item" style="margin-left: 30px;" onmouseup="window.location.href='/tabledata/dataList/<?= $selectedDb; ?>/<?= $tableName; ?>/<?= ($from) ? $from : 0; ?>/';"></div>
<!-- search -->
<div class="search-button menu-item" style="margin-left: 30px;" id="searchBtn"></div>
<select class="menu-item" id="searchCol">
<? for ($i = 0, $len = count($desc); $i < $len; $i++): ?>
<option <? if ($searchCol === $desc[$i]['field']): ?>selected="selected"<? endif; ?> value="<?= $desc[$i]['field']; ?>"><?= $desc[$i]['field']; ?></option>
<? endfor; ?>
</select>
<input type="text" id="searchText" value="<?= $search; ?>" />
</div>
<!-- title -->
<div class="box">
<div class="head-line"><?= $tableName; ?></div>
<!-- list -->
<div class="box" style="overflow: scroll;">
<table>
<!-- columns -->
<tr>
<? if ($cmsUser['permission'] == 1 || $cmsUser['permission'] == 2): ?>
<td class="title" style="padding: 0 10px;"></td>
<td class="title" style="padding: 0 10px;"></td>
<? endif; ?>
<? for ($i = 0, $len = count($desc); $i < $len; $i++): ?>
<td class="title" style="<? if ($searchCol === $desc[$i]['field']): ?>border: 1px solid #fc0; <? endif; ?>padding: 0 10px; text-align: center;"><??><?= $desc[$i]['field']; ?></td>
<? endfor; ?>
</tr>
<!-- data list -->
<? for ($i = 0, $len = count($list); $i < $len; $i++): ?>
<tr>
<? if ($cmsUser['permission'] == 1 || $cmsUser['permission'] == 2): ?>
<!-- edit button -->
<td class="area" style="border-right: 1px solid #ccc;" onmouseup="window.getDataEditor(<?= $i; ?>);">
<div class="edit-button"></div>
</td>
<!-- delete button -->
<td class="area" style="border-right: 1px solid #ccc;" onmouseup="window.deleteData(<?= $i; ?>);">
<div class="delete-button"></div>
</td>
<? endif; ?>
<? for ($j = 0, $jlen = count($desc); $j < $jlen; $j++): ?>
<td class="area" style="white-space: nowrap; <? if ($searchCol === $desc[$j]['field']): ?>border: 1px solid #fc0;<? else: ?>border-right: 1px solid #ccc;<? endif; ?>"><?= $list[$i][$desc[$j]['field']]; ?></td>
<? endfor; ?>
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
