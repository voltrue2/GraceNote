<?php
// root path of the framework
$root = substr(__FILE__, 0, strpos(__FILE__, basename(__FILE__))) . '../../';
// include Config
include($root . 'core/Config.class.php');
Config::parse($root);
// include Loader
include($root . 'core/Loader.class.php');
// include Cache
include($root . 'core/Cache.class.php');
// include Log
include($root . 'core/Log.class.php');
// set up Loader
Loader::setRoot($root);
// import datasources
Loader::import('datasources', 'sql/SqlConfig.class.php');
Loader::import('datasources', 'sql/SqlConnection.class.php');
Loader::import('datasources', 'sql/SqlRead.class.php');
Loader::import('datasources', 'sql/SqlData.class.php');
Loader::import('datasources', 'sql/SqlWrite.class.php');
Loader::import('datasources', 'sql/QueryBuilder.class.php');
Loader::import('datasources', 'DataModel.class.php');
?>
