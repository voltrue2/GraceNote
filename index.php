<?php
// use EventEmitter
Loader::import('lib', 'EventEmitter.class.php');
// use session and start session
Loader::import('lib', 'DbSession.class.php');
// use CMS auth handler
Loader::import('lib', 'CmsAuthHandler.class.php');
// use CMS data class
Loader::import('lib', 'CmsData.class.php');
// use text sanitize
Loader::import('lib', 'Sanitize.class.php');
// use Text
Loader::import('lib', 'Text.class.php');
Text::setup('CmsText');
// use Encrypt
Loader::import('lib', 'Encrypt.class.php');

?>
