<?php
// Load Config.php
require_once(substr(__FILE__, 0, strpos(__FILE__, basename(__FILE__))) . '../configs/Config.php');
// Error Display
ini_set('display_errors', $debug); 
ini_set('log_errors', $error_log); 
error_reporting(E_ALL);
// array constants
$def['IMAGE_FORMATS'] = $image_formats;
$def['ERROR_PAGES'] = $error_pages;
$def['ROUTES'] = $routes;
$def['UAGENTS']        = $uagents;
$def['TPL_DIR']        = $tpl;
$def['TEMPLATE_HEADERS'] = $headers;
$def['DB'] = $db;
// regular constants
if (isset($_SERVER['HTTP_HOST'])){
	$chost = $_SERVER['HTTP_HOST'];
}
else {
	$chost = false;
}
$smarty = false;
define('DEFAULT_LANG', $default_language_id);
define('LANG_QUERY_NAME', $language_query);
define('MCACHE_HOST', $memcache_host);
define('MCACHE_PORT', $memcache_port);
define('MCACHE_EXP', $memcache_expiration);
define('URI_QUERIES', $uri_queries);
define('PHP_EXTENSION', $php_extension);
define('DEFAULT_PAGE_METHOD', $default_page_method);
define('SMARTY', $smarty);
define("SMARTY_TPL_TYPE", $smarty_tpl_type);
define("TPL_TYPE", $tpl_type);
define('DISPLAY_ERRORS', $debug);
define("ERROR_LOG", $error_log);
define("BASE", $base);
define("COOKIE_EXPIRATION", $cookie_expiration);
define("SESSION_EXPIRATION", $session_expiration);
define("DOC_ROOT", $document_root);
define("CSS_PATH", DOC_ROOT."css/");
define("JS_PATH", DOC_ROOT."js/");
define("IMG_PATH", DOC_ROOT."img/");
define("CIMG_PATH", DOC_ROOT."img/contents/");
define("BASE_PATH", $base."GraceNote/");
define("TPL_PATH", BASE_PATH."templates/");
define("DEFAULT_TPL", $tpl['DEFAULT']);
define("PC_TPL", $tpl['PC']);
define("MOBILE_TPL", $tpl['MOBILE']);
define("SMART_TPL", $tpl['SMARTPHONE']);
define("PFC_PATH", $base."pfc/");
define("CORE_PATH", BASE_PATH."core/");
define("LIB_PATH", BASE_PATH."lib/");
define("ACTIONS_PATH", BASE_PATH."actions/");
define("BATCH_PATH", BASE_PATH."batch/");
define("MODELS_PATH", BASE_PATH."models/");
define("CURRENT_HOST", $chost);
define("HOST", $host);
define("SHOST", $shost);
define("CDN", $cdn_host);
// definitions
define("DEF", serialize($def));
?>