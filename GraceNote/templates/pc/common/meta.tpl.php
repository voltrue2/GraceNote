<title><?= text($HEADER, 'title', 'GraceNote Framework CMS'); ?></title>
<link rel="stylesheet" href="/css/pc/cms.css?<?= filemtime($CSS_PATH.'pc/cms.css'); ?>" type="text/css" media="all"></link>
<? /*
<? if($AUTO_CSS): ?>
<link rel="stylesheet" href="/css/<?= $AUTO_CSS; ?>" media="all"></link>
<? endif; ?>
*/ ?>
<script type="text/javascript" src="/js/GraceNote.js?<?= filemtime($JS_PATH.'GraceNote.js'); ?>"></script>
<script type="text/javascript" src="/js/Windows.js?<?= filemtime($JS_PATH.'Windows.js'); ?>"></script>
<script type="text/javascript" src="/js/Notify.src.js?<?= filemtime($JS_PATH.'Notify.src.js'); ?>"></script>
<script type="text/javascript" src="/js/cms.js?<?= filemtime($JS_PATH.'cms.js'); ?>"></script>
<? /*
<? if($AUTO_JS): ?>
<script type="text/javascript" src="/js/<?= $AUTO_JS; ?>"></script>
<? endif; ?>
*/ ?>

