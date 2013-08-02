<?
// include main.php of scripts core (Required)
include('core/main.php');
// optional
$db = $argv[1];
if (!$db) {
	// default
	$db = 'GraceNote';
}
// setup DataModel object
$dm = new DataModel($db);
/******************************
* create report table *
******************************/
$report = $dm->table('report');
// dorp the table if exists
$report->drop();
// now create the table
$report->setColumn('user', 'varchar');
$report->setColumn('type', 'varchar');
$report->setColumn('value', 'varchar');
$report->setColumn('created', 'varchar');
$report->create('MYISAM');
