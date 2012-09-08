<?php
// ************ CMS Edit Page Text
$metadata['manage_edit']['category'] = 'CMS Contents';
$metadata['manage_edit']['description'] = 'CMS Edit Page Text Contents.';
$tables['manage_edit'] = array(
	'lang_id' => 'int',
	'back_to_list' => 'varchar',
	'edit_struct' => 'varchar',
	'not_editable' => 'varchar',
	'remove_conf' => 'varchar',
	'remove_tag'  => 'varchar',
	'add_column' => 'varchar',
	'column_name' => 'varchar',
	'column_type' => 'varchar',
	'column_default' => 'varchar',
	'new_data_title' => 'varchar',
	'auto_value' => 'varchar',
	'save_tag' => 'varchar',
	'desc_title' => 'varchar',
	'category_tag' => 'varchar',
	'edit_tag' => 'varchar',
	'back_btn' => 'varchar',
	'next_btn' => 'varchar',
	'list_title' => 'varchar',
	'exec_sql_title' => 'varchar',
	'exec_sql_btn' => 'varchar',
	'show_all_tag' => 'varchar',
	'edit_title' => 'varchar',
	'number_only' => 'varchar',
	'text' => 'varchar',
	'image_popup_tag' => 'varchar',
	'image_list_title' => 'varchar',
	'please_select' => 'varchar',
	'back_to_data_list' => 'varchar',
	'search_btn' => 'varchar',
	'found_tag' => 'varchar',
	'content_lang_tag' => 'varchar',
	'edit_structure_tag' => 'varchar',
	'edit_table_desc_tag' => 'varchar',
	'password_tag' => 'varchar',
	'column_name_tag' => 'varchar',
	'column_type_tag' => 'varchar',
	'column_comment_tag' => 'varchar',
	'min_length_tag' => 'varchar',
	'max_length_tag' => 'varchar',
	'required_tag' => 'varchar',
	'column_desc_tag' => 'varchar',
	'datetime' => 'varchar',
	'allow_html' => 'varchar',
	'image_field_tag' => 'varchar',
	'column_reference_tag' => 'varchar',
	'column_reference_label_tag' => 'varchar',
	'column_reference_column_tag' => 'varchar',
	'reset_attribute_tag' => 'varchar',
	'data_entry_from_csv' => 'varchar',
	'remove_checked_conf' => 'varchar',
	'remove_checked_tag' => 'varchar',
	'search_all' => 'varchar'
);
$metadata['cimgmanager_contents']['category'] = 'CMS Contents';
$metadata['cimgmanager_contents']['description'] = 'Media Management Contents.';
$tables['cimgmanager_contents'] = array(
	'lang_id' => 'int',
	'back_to_list' => 'varchar',
	'cimg_title' => 'varchar',
	'current_path' => 'varchar',
	'change_dir_tag' => 'varchar',
	'create_dir' => 'varchar',
	'upload_tag' => 'varchar',
	'list_title' => 'varchar',
	'remove_conf' => 'varchar',
	'remove_tag' => 'varchar',
	'create_dir_failed' => 'varchar',
	'remove_dir_failed' => 'varchar',
	'upload_failed' => 'varchar',
	'remove_file_failed' => 'varchar',
	'create_btn' => 'varchar',
	'width_tag' => 'varchar',
	'height_tag' => 'varchar',
	'aspect_ratio_tag' => 'varchar',
	'ignore_tag' => 'varchar',
	'file_name_tag' => 'varchar',
	'resize_title' => 'varchar',
	'crop_image_tag' => 'varchar',
	'edit_tag' => 'varchar',
);
$metadata['manage_create']['category'] = 'CMS Contents';
$metadata['manage_create']['description'] = 'Create Table Contents.';
$tables['manage_create'] = array(
	'lang_id' => 'int',
	'back_to_list' => 'varchar',
	'create_new' => 'varchar',
	'table_name' => 'varchar',
	'add_column' => 'varchar',
	'set_table_name' => 'varchar',
	'column_name' => 'varchar',
	'column_type' => 'varchar',
	'column_default' => 'varchar',
	'lang_tag' => 'varchar',
	'category_tag' => 'varchar',
);
$metadata['admin_menu']['category'] = 'CMS Contents';
$metadata['admin_menu']['description'] = 'CMS Menu Contents.';
$tables['admin_menu'] = array(
	'lang_id' => 'int',
	'create_table' => 'varchar',
	'edit_tables' => 'varchar',
	'edit_tag' => 'varchar',
	'drop_tag' => 'varchar',
	'drop_msg' => 'varchar',
	'back_btn' => 'varchar',
	'next_btn' => 'varchar',
	'create_dbf_tag' => 'varchar',
	'delete_dbf_tag' => 'varchar',
);
$metadata['permissionmanager_contents']['category'] = 'CMS Contents';
$metadata['permissionmanager_contents']['description'] = 'CMS User Management Contents.';
$tables['permissionmanager_contents'] = array(
	'lang_id' => 'int',
	'back_to_list' => 'varchar',
	'please_select' => 'varchar',
	'delete_btn' => 'varchar',
	'update_btn' => 'varchar',
	'create_btn' => 'varchar',
	'select_btn' => 'varchar',
	'admin_user_title_tag' => 'varchar',
	'admin_tag' => 'varchar',
	'admin_permission_tag' => 'varchar',
	'admin_delete_tag' => 'varchar',
	'admin_name_tag' => 'varchar',
	'admin_password_tag' => 'varchar',
	'create_permission_tag' => 'varchar',
	'create_admin_tag' => 'varchar',
	'permission_name_tag' => 'varchar',
	'admin_permission_title_tag' => 'varchar',
	'access_name_tag' => 'varchar',
	'access_flag_tag' => 'varchar',
	'admin_delete_msg' => 'varchar',
	'admin_media_restriction_tag' => 'varchar',
	'root_flag_tag' => 'varchar',
	'access_table_tag' => 'varchar',
);
$metadata['admin_init']['category'] = 'CMS Contents';
$metadata['admin_init']['description'] = 'CMS Initial Page Contents.';
$tables['admin_init'] = array(
	'lang_id' => 'int',
	'login_from_here' => 'varchar',
	'start_from_here' => 'varchar',
	'pass_tag' => 'varchar',
	'welcome' => 'varchar',
);
$metadata['login_init']['category'] = 'CMS Contents';
$metadata['login_init']['description'] = 'CMS Login Contents.';
$tables['login_init'] = array(
	'lang_id' => 'int',
	'login_title' => 'varchar',
	'user_tag' => 'varchar',
	'pass_tag' => 'varchar',
	'msg' => 'varchar',
	'error_message' => 'varchar',
	'login_tag' => 'varchar',
);
$metadata['header']['category'] = 'CMS Contents';
$metadata['header']['description'] = 'CMS Header Contents.';
$tables['header'] = array(
	'lang_id' => 'int',
	'title' => 'varchar',
	'user_tag' => 'varchar',
	'permission_tag' => 'varchar',
	'logout_tag' => 'varchar',
);

/*********************************
      DO NOT CHANGE THESE
*********************************/
define('TABLES', serialize($tables));
define('METADATA', serialize($metadata));
?>