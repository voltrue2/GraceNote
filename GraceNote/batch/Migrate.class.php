<?php
/************************************
# /GrageNote/batch/Migrate.class.php
- Useage for this script: Creates DB tables for GraceNote as defined in /configs/Migration.php
- Execute this script to create DB tables * Tables that exist will be ignored
* The syntax and rules to define DB tables in /configs/Migration.php
$tables[table name] = array(
	column => column type, ...
);
$metadata[table name][category] = category name;
$metadata[table name][description] = text for table description
************************************/

// ********* Table Definitions
Load::config('Migration');
$tables = unserialize(TABLES);
$metadata = unserialize(METADATA);
// ***** Set up Model
Load::model('EditModel');
$base = new Base();
$model = new EditModel();
$table_desc = $model->table('table_desc');
$permissions = $model->table('permissions');
// ***** Execute
if (!empty($tables)){
	foreach ($tables as $table => $def){
		$table_desc->cond('table_name = ?', $table);
		if (!$table_desc->find()){
			// the table does not exist yet -> create table
			create_table($model, $permissions, $table_desc, $metadata[$table], $table, $def);
		}
	}
}
// ***** Function Definition
function create_table($model, $permissions, $table_desc, $metadata, $table_name, $def){
	if (!empty($def)){
		$table = $model->table($table_name);
		$table->set_auto_increment_column('id', false, 'primary');
		foreach ($def as $column => $type){
			$table->set_column($column, $type);
		}
		$table->create();
		$res = $table->show();
		if ($res){
			// Set up Table Description
			$table_desc->set('table_name', $table_name);
			$table_desc->set('category', $metadata['category']);
			$table_desc->set('description', $metadata['description']);
			$table_desc->save();
			// Set up Permission for root
			$permissions->set('table_name', $table_name);
			$permissions->set('permission', 0);
			$permissions->save();
			// done
			error_log('Migration.class.php : Table ['.$table_name.'] created.');
		}
	}
}
?>
