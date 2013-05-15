<?
// include main.php of scripts core (Required)
include('core/main.php');
// import Encrypt
Loader::import('lib', 'Encrypt.class.php');
// optional
$db = $argv[1];
if (!$db) {
	// default
	$db = 'GraceNote';
}
// setup DataModel object
$dm = new DataModel($db);
/******************************
* create cms admin user table *
*******************************/
$sess = $dm->table('sessions');
$sess->transaction();
try {
	// delete the table if there is one
	$sess->drop();
	// create table
	$sess->setColumn('session_id', 'varchar');
	$sess->setColumn('value', 'text');
	$sess->setColumn('expr', 'int');
	$sess->create();
	if (!$res) {
		throw new Exception('Failed to create default user');	
	}
	$user = $cmsAdmin->getOne();
	$cmsAdmin->commit();
} catch (Exception $error) {
	error_log('*** Error >> ' . $error->getMessage());
	$sess->rollBack();	
}
?>
