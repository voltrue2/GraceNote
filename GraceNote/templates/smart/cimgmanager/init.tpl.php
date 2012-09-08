<? include($TPL_PATH.'common/doc_type.tpl.php'); ?>
<html>
<head>
<? include($TPL_PATH.'common/meta.tpl.php'); ?>
<script type="text/javascript" src="/js/ImageBox.js<?= mtime($JS_PATH, 'ImageBox.js'); ?>"></script>
<script type="text/javascript">
var bstyle = {
	backgroundColor: '#FFFFFF',
	border: '4px solid #CCCCCC'
};
var cstyle = {
	//border: '1px solid #659EC7',
	backgroundImage: 'url("/img/system/top_menu_bk.png")',
	textAlign: 'right',
	padding: '2px'
};
var close = '<img src="/img/system/stop.png" width="20" />';
var ib = new ImageBox('ib', {box_style: bstyle, close_button_style: cstyle, close_button: close, preloader: '/img/preloaders/preloader.gif'});
function image_box(path){
	ib.show(path);
};

form_auto_focus();
</script>
<!-- Alert Message for failed action such as delete -->
<script type="text/javascript">
var error = false;
<? if(isset($QUERIES['error'])): ?>
<? if($QUERIES['error']): ?>
<? if(isset($CONTENTS[$QUERIES['error']])): ?>;
error = '<?= $CONTENTS[$QUERIES['error']] ?>';
<? else: ?>
error = 'Action Failed';
<? endif; ?>
<? endif; ?>
<? endif; ?>
if (error){
	alert(error);
	<? if(isset($QUERIES['current_dir']) && isset($QUERIES['error'])): ?>
	var path = '<?= str_replace('&error='.$QUERIES['error'], '', str_replace('?error='.$QUERIES['error'], '', $FULL_URI)); ?>';
	window.location = path;
	<? endif; ?>
};
</script>
</head>
<body>
<? include($TPL_PATH.'common/cms_top.tpl.php'); ?>
<div class="container">
<div class="inner">
<div style="border-bottom: 1px dotted #CCCCCC; padding: 4px; margin-top: 10px;">
<!-- Back Button -->
<a href="/menu/"><img src="/img/system/go_back.png" width="20" />&nbsp;<?= show($CONTENTS, 'back_to_list', 'Back to table list'); ?></a>
</div>
<div style="border-bottom: 1px dotted #CCCCCC; padding: 4px; margin-top: 10px;">
<h3><img src="/img/system/tools.png" width="25" />&nbsp;<?= show($CONTENTS, 'cimg_title', 'Content Image Manager'); ?></h3>
<div class="tr">
<!-- Path Navigation -->
<div class="th-top"><?= show($CONTENTS, 'current_path', 'Current Location'); ?></div>
<form method="method">
<div class="td">
<img src="/img/system/folders.png" width="25" />&nbsp;<input type="text" name="current_dir" value="<?= $CURRENT_PATH ?>" size="100" />
<a href="/cimgmanager/?current_dir=<?= $CURRENT_PATH ?>.."><img src="/img/system/back.png" width="25" /></a>
<input type="submit" value="<?= show($CONTENTS, 'change_dir_tag', 'Move Location'); ?>" />
</div>
</div>
</form>
<div style="padding-top: 10px;">
<table border="0" cellspacing="0" cellpadding="4">
<!-- Create Dir -->
<tr><th class="th-top" colspan="2"><?= show($CONTENTS, 'create_dir', 'Create Directory'); ?></th></tr>
<form action="/cimgmanager/mkdir/#list_area" method="post">
<tr>
<td class="td" colspan="2">
<img src="/img/system/folder.png" width="25" />&nbsp;<input type="text" name="dir" value="" />
<input type="hidden" name="current_dir" value="<?= $CURRENT_PATH; ?>" />
<input type="submit" name="submit" value="<?= show($CONTENTS, 'create_dir', 'Create Directory'); ?>" />
</td>
</tr>
</form>
<tr>
<!-- Upload Image -->
<th class="th" colspan="2"><?= show($CONTENTS, 'upload_tag', 'Upload Image'); ?></th>
</tr>
<form action="/cimgmanager/upload/#list_area" method="post" enctype="multipart/form-data">
<tr>
<td class="td" colspan="2">
<img src="/img/system/image.png" width="25" />&nbsp;<input type="file" name="image" value="" />
<input type="hidden" name="current_dir" value="<?= $CURRENT_PATH; ?>" />
<input type="submit" name="submit" value="<?= show($CONTENTS, 'upload_tag', 'Upload Image'); ?>" />
</td>
</tr>
</form>
<? if($LIST): ?>
<!-- Image/Dir List -->
<tr><th class="th" colspan="2"><?= show($CONTENTS, 'list_title', 'Directory List'); ?></th></tr>
<? foreach($LIST as $i => $item): ?>
<tr>
<? if($item['type'] == 'file'): ?>
<td class="td" style="font-size: 12px;">
<a href="javascript:void(null);" onclick="image_box('<?= $item['file_path']; ?>');" style="text-decoration: none;">
<img class="icon" src="/img/system/image.png" id="<?= $item['file_path']; ?>" width="50" />&nbsp;<?= $item['file_path']; ?>
</a>
</td>
<td class="td" style="text-align: right; border-left: 0;">
<button onclick="confirmation('<?= show($CONTENTS, 'remove_conf', 'Would you like to remove'); ?> <?= $item['name']; ?>?', '/cimgmanager/rmfile/?current_dir=<?= $CURRENT_PATH; ?>&file=<?= $item['name']; ?>#list_area');">
<?= show($CONTENTS, 'remove_tag', 'Remove'); ?>
</button>
</td>
<? elseif($item['type'] == 'prev'): ?>
<td class="td" colspan="2"><a href="/cimgmanager/menu/?current_dir=<?= '/'.$item['path']; ?>#list_area" style="text-decoration: none;">
<img src="/img/system/back.png" width="25" />&nbsp;<?= $item['name']; ?>
</a></td>
<? else: ?>
<td class="td"><a href="/cimgmanager/menu/?current_dir=<?= '/'.$item['path']; ?>#list_area" style="text-decoration: none;"><img src="/img/system/folder.png" width="25" />&nbsp;<?= $item['name']; ?></a></td>
<td class="td" style="text-align: right; border-left: 0;">
<button onclick="confirmation('<?= show($CONTENTS, 'remove_conf', 'Would you like to remove'); ?> <?= $item['name']; ?>?', '/cimgmanager/rmdir/?current_dir=<?= $CURRENT_PATH; ?>&dir=<?= $item['name']; ?>#list_area');">
<?= show($CONTENTS, 'remove_tag', 'Remove'); ?>
</button>
</td>
<? endif; ?>
</tr>
<? endforeach; ?>
<? endif; ?>
</table>
</div>
</div>
</div>
</div>
</div>
</div>
<script type="text/javascript" src="/js/ImageReplace.src.js"></script>
<script type="text/javascript">
var replacer = new ImageReplace('icon', 'id');
</script>
</body>
</html>
