<?php

// record start time
$startTime = microtime(true);
// Framework root path
$_root = substr(__FILE__, 0, strpos(__FILE__, basename(__FILE__)));
$_root = substr($_root, 0, strrpos($_root, 'core/'));
// include Config globally
include('Config.class.php');
// setup Config
Config::parse($_root);
// include Log globally
include('Log.class.php');
// set up error catcher
include('ErrorHandler.class.php');
// register static error path to ErrorHandler
ErrorHandler::setStaticPage($_root, 'error/index.html');
// include Json globally
include('Json.class.php');
// include Cache globally
include('Cache.class.php');
// include Loader globally
include('Loader.class.php');
// set up Loader
Loader::setRoot($_root);
// import Router
Loader::import('core', 'Router.class.php');
// import UserAgent globally
Loader::import('core', 'UserAgent.class.php');
// import Controller globally
Loader::import('core', 'Controller.class.php');
// import View globally
Loader::import('core', 'View.class.php');
// import Asset globally
Loader::import('core', 'asset/Asset.class.php');
// import FileData globally
Loader::import('datasources', 'staticdata/StaticData.class.php');
// import SqlConfig, SqlConnection, SqlRead, SqlData, QueryBuildber, and SqlWrite globally
Loader::import('datasources', 'sql/SqlConfig.class.php');
Loader::import('datasources', 'sql/SqlConnection.class.php');
Loader::import('datasources', 'sql/SqlRead.class.php');
Loader::import('datasources', 'sql/SqlData.class.php');
Loader::import('datasources', 'sql/SqlWrite.class.php');
Loader::import('datasources', 'sql/QueryBuilder.class.php');
// import DataModle globally
Loader::import('datasources', 'DataModel.class.php');
// import FileSystem globally
Loader::import('core', 'FileSystem.class.php');
// import index.php
Loader::index('root', 'index.php');
// create router
$router = new Router();
// create view
$view = new View($router);
// create controller
$controller = $router->createController($view, $startTime);
