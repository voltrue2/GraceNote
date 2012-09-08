<? Load::template('common/doc_type'); ?>
<html>
<head>
<? Load::template('common/meta'); ?>
</head>
<body>
<? Load::template('common/cms_top'); ?>
<div class="container">
<div class="inner">
<!-- Admin Menu -->
<? if(isset($LOGGEDIN_USER['permission'])): ?>
<? if($LOGGEDIN_USER['permission'] === 0): ?>
<div class="section">
<h3><img src="/img/system/tools.png" width="25" />&nbsp;<?= text($CONTENTS, 'create_table', 'Create Table'); ?></h3>
<a href="/manage/create/"><img src="/img/system/box.png" width="25" />&nbsp;<?= text($CONTENTS, 'create_table', 'Create Table'); ?></a>
</div>
<? endif; ?>
<? endif; ?>
<!-- List Area -->
<div class="section">
<h3><img src="/img/system/tools.png" width="25" />&nbsp;<?= text($CONTENTS, 'edit_tables', 'Edit Tables'); ?></h3>
<!-- Database List-->
<? if($CATEGORIES): ?>
<div>
<ul class="horizontal_menu">
<? if(!$CATEGORY): ?>
<li class="selected">
<? else: ?>
<li>
<? endif; ?>
<a href="/menu/?category=all"><img src="/img/system/post.png" width="20" >all</a>
</li>
<!-- Category List -->
<? $c = count($CATEGORIES) - 1; ?>
<? foreach($CATEGORIES as $i => $item): ?>
<? if($CATEGORY == $item['category']): ?>
<li class="selected">
<? else: ?>
<li>
<? endif; ?>
<a href="/menu/?category=<?= $item['category']; ?>"><img src="/img/system/post.png" width="20" ><?= $item['category']; ?></a>
</li>
<? endforeach; ?>
</ul>
</div>
<? endif; ?>
<!-- Table List -->
<? if (!empty($TABLES)): ?>
<table class="list">
<? foreach($TABLES as $i => $table): ?>
<tr class="cell">
<td class="metalic-label center"><?= $i + 1; ?></td>
<td nowrap class="td left">
<a href="/manage/table/<?= $table['table_name']; ?>/#record_list"><img src="/img/system/boxes.png" width="25"/>&nbsp;<?= $table['table_name']; ?></a></td>
<td><?= $table['description']; ?></td>
<!-- Edit -->
<td nowrap class="center"><a class="btn" href="/manage/table/<?= $table['table_name']; ?>/#record_list">
<?= text($CONTENTS, 'edit_tag', 'Edit'); ?>
</a></td>
<!-- Drop -->
<? if(isset($LOGGEDIN_USER['permission'])): ?>
<? if($LOGGEDIN_USER['permission'] === 0): ?>
<td nowrap class="center">
<a class="delete_btn" href="javascript:void(null);" onclick="confirmation('<?= text($CONTENTS, 'drop_msg', 'Would you like to drop the table?', array('name' => $table['table_name'])); ?>', '/manage/drop/<?= $table['table_name']; ?>/');">
<?= text($CONTENTS, 'drop_tag', 'Drop'); ?>
</a>
</td>
<? endif; ?>
<?endif; ?>
</tr>
<? endforeach; ?>
</table>
<!-- Paging -->
<div style="text-align: center;">
<? if($FROM - $ITEM_NUM >= 0): ?>
<a href="/menu/<?= $FROM - $ITEM_NUM; ?>/<? if($CATEGORY): ?>?category=<?= $CATEGORY; ?><? endif; ?>"><?= text($CONTENTS, 'back_btn', '< Back'); ?></a>
<? else: ?>
<span class="off"><?= text($CONTENTS, 'back_btn', '< Back'); ?></span>
<? endif; ?>
&nbsp;[<?= $PAGE; ?>]&nbsp;
<? if($FROM + $ITEM_NUM < $TABLE_TOTAL): ?>
<a href="/menu/<?= $FROM + $ITEM_NUM; ?>/<? if($CATEGORY): ?>?category=<?= $CATEGORY; ?><? endif; ?>"><?= text($CONTENTS, 'next_btn', 'Next >'); ?></a>
<? else: ?>
<span class="off"><?= text($CONTENTS, 'next_btn', 'Next >'); ?></span>
<? endif; ?>
</div>
<? endif; ?>
</div>
</div>
</div>
<? Load::template('common/footer'); ?>
</body>
</html>