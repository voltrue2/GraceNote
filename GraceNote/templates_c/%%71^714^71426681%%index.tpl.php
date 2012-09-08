<?php /* Smarty version 2.6.26, created on 2011-02-09 20:49:00
         compiled from /usr/local/www/nobu.qpon.jp/GraceNote/templates/pc/list/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'mtime', '/usr/local/www/nobu.qpon.jp/GraceNote/templates/pc/list/index.tpl', 11, false),)), $this); ?>
<?php echo '<?xml'; ?>
 version="1.0" encoding="UTF-8"<?php echo '?>'; ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<html>
<head>
<?php if ($this->_tpl_vars['QUERIES']['TITLE']): ?>
<title><?php echo $this->_tpl_vars['QUERIES']['TITLE']; ?>
</title>
<?php endif; ?>
<link rel="stylesheet" type="text/css" href="/css/base.css<?php echo smarty_function_mtime(array('path' => "CSS|base.css"), $this);?>
" />
<?php if ($this->_tpl_vars['AUTO_CSS']): ?>
<link rel="stylesheet" type="text/css" href="/css/<?php echo $this->_tpl_vars['AUTO_CSS']; ?>
" />
<?php endif; ?>
<script type="text/javascript" src="/js/Basic.js<?php echo smarty_function_mtime(array('path' => "JS|Basic.js"), $this);?>
"></script>
<script type="text/javascript" src="/js/PhotoGallery.src.js<?php echo smarty_function_mtime(array('path' => "JS|PhotoGallery.src.js"), $this);?>
"></script>
</head>
<body>

<br />
<img src="/ajax/Graph.php" />
<br />

Device : <?php echo $this->_tpl_vars['DEVICE']; ?>
<br />
User Agent : <?php echo $this->_tpl_vars['USER_AGENT']; ?>
<br />
<?php if ($this->_tpl_vars['QUERIES']): ?>
QUERIES : <br />
<?php $_from = $this->_tpl_vars['QUERIES']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['val']):
?>
<?php echo $this->_tpl_vars['i']; ?>
 = <?php echo $this->_tpl_vars['val']; ?>
<br />
<?php endforeach; endif; unset($_from); ?>
<?php endif; ?>
<br />
Top Page Contents go <strong><u>HERE</u></strong>
<br />
<br />
<a href="http://nobu.qpon.jp/login/">Lon In</a>
</body>
</html>