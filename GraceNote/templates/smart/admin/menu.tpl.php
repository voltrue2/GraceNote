<? include($TPL_PATH.'common/doc_type.tpl.php'); ?>
<html>
<head>
<? include($TPL_PATH.'common/meta.tpl.php'); ?>
</head>
<body>
<? include($TPL_PATH.'common/cms_top.tpl.php'); ?>
<div class="container">
<div class="inner">
<!-- Admin Menu -->
<? if(isset($LOGGEDIN_USER['permission'])): ?>
<? if($LOGGEDIN_USER['permission'] === 0): ?>
<div class="section">
<h3><img src="/img/system/tools.png" width="25" />&nbsp;<?= show($CONTENTS, 'create_table', 'Create Table'); ?></h3>
<a href="/manage/create/"><img src="/img/system/box.png" width="25" />&nbsp;<?= show($CONTENTS, 'create_table', 'Create Table'); ?></a>
</div>
<? endif; ?>
<? endif; ?>
<!-- List Area -->
<div class="section">
<h3><img src="/img/system/tools.png" width="25" />&nbsp;<?= show($CONTENTS, 'edit_tables', 'Edit Tables'); ?></h3>
<!-- Database List-->
<? if($CATEGORIES): ?>
<div style="margin-bottom: 4px;">
<div style="background-image: url('/img/system/top_menu_bk.png'); padding-top: 3px; border-bottom: 1px solid #659EC7;">
<? if(!$CATEGORY): ?>
<div style="display: table-cell; padding-left: 10px; padding-right: 10px; border-right: 1px solid #659EC7; background-color: #FFFFFF;">
<? else: ?>
<div style="display: table-cell; padding-left: 10px; padding-right: 10px; border-right: 1px solid #659EC7;">
<? endif; ?>
<a href="/menu/"><img src="/img/system/post.png" width="20" >all</a>
</div>
<!-- Category List -->
<? $c = count($CATEGORIES) - 1; ?>
<? foreach($CATEGORIES as $i => $item): ?>
<? if($CATEGORY == $item['category']): ?>
<div style="display: table-cell; padding-left: 10px; padding-right: 10px; border-right: 1px solid #659EC7; background-color: #FFFFFF;">
<? else: ?>
<div style="display: table-cell; padding-left: 10px; padding-right: 10px; padding-bottom: 2px; border-right: 1px solid #659EC7;">
<? endif; ?>
<a href="/menu/?category=<?= $item['category']; ?>"><img src="/img/system/post.png" width="20" ><?= $item['category']; ?></a>
</div>
<? endforeach; ?>
</div>
</div>
<? endif; ?>
<!-- Table List -->
<? if (!empty($TABLES)): ?>
<table border="0" cellpadding="4" cellspacing="0">
<? foreach($TABLES as $i => $table): ?>
<tr>
<td nowrap style="<? if($i == 0): ?>border-top: 1px dotted #CCCCCC;<? endif; ?> background-color: #EFEFFF; border-bottom: 1px dotted #CCCCCC; border-left: 1px dotted #CCCCCC; border-right: 1px dotted #CCCCCC;">
<a href="/manage/table/<?= $table['table_name']; ?>/"><img src="/img/system/boxes.png" width="25" />&nbsp;<?= $table['table_name']; ?></a></td>
<td nowrap style="<? if($i == 0): ?>border-top: 1px dotted #CCCCCC;<? endif; ?> background-color: #EFEFFF; border-bottom: 1px dotted #CCCCCC; border-right: 1px dotted #CCCCCC;"><img src="/img/system/post.png" width="25" ><?= $table['category']; ?></td>
<td style="<? if($i == 0): ?>border-top: 1px dotted #CCCCCC;<? endif; ?> border-bottom: 1px dotted #CCCCCC; border-right: 1px dotted #CCCCCC; font-size: 12px;"><?= $table['description']; ?></td>
<td nowrap style="<? if($i == 0): ?>border-top: 1px dotted #CCCCCC;<? endif; ?> border-bottom: 1px dotted #CCCCCC; border-right: 1px dotted #CCCCCC;"><a href="/manage/table/<?= $table['table_name']; ?>/">
<?= show($CONTENTS, 'edit_tag', 'Edit'); ?>
</a></td>
<? if(isset($LOGGEDIN_USER['permission'])): ?>
<? if($LOGGEDIN_USER['permission'] === 0): ?>
<td nowrap style="<? if($i == 0): ?>border-top: 1px dotted #CCCCCC;<? endif; ?> border-bottom: 1px dotted #CCCCCC; border-right: 1px dotted #CCCCCC;">
<a href="javascript:void(null);" onclick="confirmation('[<?= $table['table_name']; ?>] : <?= show($CONTENTS, 'drop_msg', 'Would you like to drop the table?'); ?>', '/manage/drop/<?= $table['table_name']; ?>/');">
<?= show($CONTENTS, 'drop_tag', 'Drop'); ?>
</a>
</td>
<? endif; ?>
<?endif; ?>
</tr>
<? endforeach; ?>
</table>
<? endif; ?>
</div>
</div>
</div>
</body>
</html>