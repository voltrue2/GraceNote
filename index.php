<?php
// start session
session_start();
// use EventEmitter
Loader::import('lib', 'EventEmitter.class.php');
// use session
Loader::import('lib', 'DbSession.class.php');
// use CMS auth handler
Loader::import('lib', 'CmsAuthHandler.class.php');
// use CMS data class
Loader::import('lib', 'CmsData.class.php');
// use Text
Loader::import('lib', 'Text.class.php');
Text::setup('CmsText');
// use Encrypt
Loader::import('lib', 'Encrypt.class.php');
// use  Report
Loader::import('lib', 'Report.class.php');
// set up CMS report
Loader::import('lib', 'cmsReport.php');

?>
