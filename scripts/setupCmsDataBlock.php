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
* create cms datablock tables *
*******************************/
error_log('Creating cms_datablock_source, cms_datablock...');
$dbSrc = $dm->table('cms_datablock_source');
$db = $dm->table('cms_datablock');
$dbSrc->transaction();
try {
	// source table
	error_log('createing cms_datablock_source...');
	$dbSrc->drop();
	$dbSrc->setAutoIncrementColumn('id', false, 'primary key');
	$dbSrc->setColumn('name', 'varchar', 50);
	$dbSrc->setColumn('description', 'text');
	$dbSrc->setColumn('main_table', 'varchar', 50);
	$dbSrc->setColumn('main_column', 'varchar', 50);
	$dbSrc->create();
	// datablock table
	error_log('createing cms_datablock...');
	$db->drop();
	$db->setAutoIncrementColumn('id', false, 'primary key');
	$db->setColumn('source_id', 'int');
	$db->setColumn('name', 'varchar', 50);
	$db->setColumn('required', 'int');
	$db->setColumn('datablock_type', 'varchar', 50);
	$db->setColumn('data_length_limit', 'int');
	$db->setColumn('source_table', 'varchar', 50);
	$db->setColumn('source_ref_column', 'varchar', 50);
	$db->setColumn('source_column', 'varchar', 50);
	$db->setColumn('reference_table', 'varchar', 50);
	$db->setColumn('reference_column_display', 'varchar', 50);
	$db->setColumn('reference_column', 'varchar', 50);
	$db->create();
	// commit
	error_log('cms datablock tables have been created...');
	$dbSrc->commit();
} catch (Exception $error) {
	error_log('*** Error: ' . $error->getMessage());
	$dbSrc->rollBack();
}
?>
