<?php
/************************************
	Setting Varibles
************************************/
// Name this file Config.php and change the values
$host                = 'your.domain.com';
$shost               = 'secure.your.domain.com';
$cdn_host            = false;
$memcache_host       = 'localhost';
$memcache_port       = 11211;
$memcache_expiration = 60 * (60 * 24); // 24 hours
$base                = '/var/www/your.application/';     // location where GraceNote Framework is placed
$document_root       = '/var/www/htdocs/';
$smarty_tpl_type     = '.tpl';
$tpl_type            = '.tpl.php';
$php_extension       = '.class.php';
$default_page_method = 'init'; // action object method to be called when no method is provided in the URI
$uri_queries         = 'TYPE,LOCATION,CODE,EXTRA';       // read URI as queries
$default_language_id = 1;                                // 2 for japanese 
$cookie_expiration   = 24;                               // hours
$session_expiration  = '60 minutes';                       // life time for session
$language_query      = 'lang';                           // URI query name for language
$debug               = true;                             // print error message in the browser
$error_log           = true;                             // log errors and wanrings into a log file
// Settings for DB
$db['admin']['type']     = 'pgsql'; // mysql or pqsql
$db['admin']['host']     = 'localhost';
$db['admin']['user']     = 'user';
$db['admin']['password'] = 'pass';
$db['admin']['db']       = 'dbname';
$db['ref']['type']     = 'pgsql'; // mysql or pqsql
$db['ref']['host']     = 'localhost';
$db['ref']['user']     = 'user';
$db['ref']['password'] = 'pass';
$db['ref']['db']       = 'dbname';
// User agents to detect e.i. uagents['user agent name pattern'] = 'browser name,device name';
$uagents['MSIE']     = 'IE,PC';
$uagents['Firefox']  = 'FireFox,PC';
$uagents[') Safari'] = 'Safari,PC';
$uagents[') Chrome'] = 'Chrome,PC';
$uagents['Opera']    = 'ChromeOpera,PC';
// Smart Phone
$uagents['iPhone']   = 'iPhone,SMARTPHONE';
$uagents['Android']  = 'Android,SMARTPHONE';
// Mobiles
$uagents['SoftBank'] = 'SoftBank,MOBILE';
$uagents['DoCoMo']   = 'Docomo,MOBILE';
$uagents['KDDI']     = 'AU,MOBILE';
// tempate paths per device
$tpl['DEFAULT']    = 'pc/'; // template directory to load templates from with no user agent match
$tpl['PC']         = 'pc/';
$tpl['MOBILE']     = 'mobile/';
$tpl['SMARTPHONE'] = 'smart/';
// Output template headers
$headers['DEFAULT']    = '<?xml version="1.0" encoding="UTF-8"?>';
$headers['PC']         = '<?xml version="1.0" encoding="UTF-8"?>';
$headers['MOBILE']     = '<?xml version="1.0" encoding="UTF-8"?>'; 
$headers['SMARTPHONE'] = '<?xml version="1.0" encoding="UTF-8"?>';  
// set up error pages
$error_pages[404] = 'error/not_found';
// settings for URI routing  * Remap URI and executed action PHP object relationship
$routes['/']             = 'admin/';
$routes['menu/*']        = 'admin/menu';
$routes['cimgmanager']   = 'cimgmanager/menu';
$routes['error/*']       = '/'; // block the regular access to error pages
// set up auto image formats
/**
- Define image formats to be applied automatically when images are uploaded
$image_formats[file name prefix][width] = int
$image_formats[file name prefix][height] = int
$image_formats[file name prefix][aspect_ratio] = string with/height/ignore
$image_formats[file name prefix][crop] = boolean (optional)
$image_formats[file name prefix][file_type] = string (optional)
e.i. 
$image_formats['50x50']['width'] = 50
$image_formats['50x50']['height'] = 50
$image_formats['50x50']['aspect_ratio'] = width
$image_formats['50x50']['crop'] = false
$image_formats['50x50']['file_type'] = 'png';
>> will generate an image with 50px width and 50px height scaled image along with the original image
**/
// CMS uses this as image thumbnails
$image_formats['thumb.']['width'] = 50;
$image_formats['thumb.']['height'] = 50;
$image_formats['thumb.']['aspect_ratio'] = 'width';
$image_formats['thumb.']['crop'] = false;
?>
