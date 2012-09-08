<? Load::template('common/doc_type'); ?>
<html>
<head>
<? Load::template('common/meta'); ?>
</head>
<body>
<? Load::template('common/cms_top'); ?>
<div class="container">
<div class="inner">
<div class="section">
<h3><img src="/img/system/list.png" width="25" />&nbsp;<?= text($CONTENTS, 'list_of_cms_pages'); ?></h3>
<table style="width: 100%;">
<? foreach ($LIST as $i => $item): ?>
<tr class="cell">
<th style="width: 35px;" class="center th<? if ($i == 0): ?>-top<? endif; ?>"><img src="/img/system/edit.png" width="25" /></th>
<td class="td<? if ($i == 0): ?>-top<? endif; ?>"><a class="center" href="/cms/edit/<?= $item['id'][0]['cms_id']; ?>/"><?= $item['id'][0]['value']; ?></a></td>
<? if (isset($LOGGEDIN_USER['root_access']) && isset($LOGGEDIN_USER['root_access']['cms']) && $LOGGEDIN_USER['root_access']['cms']): ?>
<!-- Edit Option for Root access users -->
<td style="width: 70px;" class="center td<? if ($i == 0): ?>-top<? endif; ?>">
<a href="/cms/define/<?= $item['id'][0]['cms_id']; ?>/" class="btn"><?= text($CONTENTS, 'edit'); ?></a>
</td>
<!-- Delete Option for Root access users -->
<td style="width: 70px;" class="center td<? if ($i == 0): ?>-top<? endif; ?>">
<a onclick="confirmation('<?= text($CONTENTS, 'delete_confirmation') ?>', '/cms/delete/<?= $item['id'][0]['cms_id']; ?>/');" class="delete_btn"><?= text($CONTENTS, 'delete'); ?></a>
</td>
<? endif; ?>
</tr>
<? endforeach; ?>
</table>
<? Load::template('common/footer'); ?>
</body>
</html>
