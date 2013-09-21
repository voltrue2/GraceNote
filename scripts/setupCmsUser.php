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
$cmsAdmin = $dm->table('cms_admin');
error_log('Creating "cms_admin" table...');
$cmsAdmin->transaction();
try {
	// delete the table if there is one
	$cmsAdmin->drop();
	// create table
	$cmsAdmin->setAutoIncrementColumn('id', false, 'primary key');
	$cmsAdmin->setColumn('name', 'varchar', 15);
	$cmsAdmin->setColumn('permission', 'int');
	$cmsAdmin->setColumn('file_restriction', 'text');
	$cmsAdmin->setColumn('hash', 'varchar');
	$cmsAdmin->setColumn('last_login', 'varchar', 100);
	$cmsAdmin->setColumn('created', 'varchar', 100);
	$cmsAdmin->create();
	error_log('"cms_admin" table created');
	error_log('creating a default root user...');
	// create default root user
	$password = 'changeme';
	$passHash = Encrypt::createHash($password);
	$cmsAdmin->set('name', 'root');
	$cmsAdmin->set('permission', 1);
	$cmsAdmin->set('file_restriction', '/');
	$cmsAdmin->set('hash', $passHash);
	$cmsAdmin->set('created', time());
	$res = $cmsAdmin->save();
	if (!$res) {
		throw new Exception('Failed to create default user');	
	}
	error_log('default root user created');
	// show the default user
	$cmsAdmin->where('id = ?', 1);
	$user = $cmsAdmin->getOne();
	$cmsAdmin->commit();
} catch (Exception $error) {
	error_log('*** Error >> ' . $error->getMessage());
	$cmsAdmin->rollBack();	
}
