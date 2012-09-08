<?php
require_once(substr(__FILE__, 0, strpos(__FILE__, basename(__FILE__))) . 'ConfigParser.php');
require_once('Load.class.php');
// include -> template functions
include('template_funcs.php');
// check for smarty use
if (SMARTY){
	Load::core('smarty/Smarty');
}
Load::core('Message');
Load::core('Timer');
Load::core('Base');

/***************************************************************
- Usage for this script * The file extension needs to be the same as other scripts of GN
> Write your own cron scripts and place them under batch/
> Set up your cron in crontab
> e.i. * * * * * /usr/bin/php /GraceNote/core/Batch.php [target] [object::method/function]
> Avaiable arguments
-> target(Required) Name of your cron script to run
-> object::method(Optional) or function Name of your object::Name of your public method e.i. Test::pester or function e.i. myfunction
***************************************************************/
$base = new Base();
$self = $argv[0];
$target = false;

// check for the arguments
if (isset($argv[1])){
	$target = $argv[1];
	Load::batch($target);
}
else {
	$base->trace('Batch.php > Required argument "target" not found.');
	$base->trace($argv);
	exit();
}
if (isset($argv[2])){
	$sep = explode('::', $argv[2]);
	if (isset($sep[0]) && isset($sep[1])){
		$class = $sep[0];
		$method = $sep[1];
		try {
			$obj = new $class();
			$obj->$method();
		}
		catch (Exception $e){
			$base->trace('Batch.php > Error');
			$base->trace($e->getMessage());
		}
	}
	else {
		if (function_exists($argv[2])){
			$argv[2]();
			exit();
		}
		else {
			$base->trace('Batch.php > Invalid argument found.');
			$base->trace($argv);
			exit();
		}
	}
}
?>
