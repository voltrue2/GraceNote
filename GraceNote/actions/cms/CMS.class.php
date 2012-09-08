<?php
Load::lib('custom/LoginManager');
Load::action('header/Header');
Load::model('EditModel');

class CMS {
	
	private $view;
	private $lm;
	private $write;
	private $cms;
	private $cms_data;
	private $data_name = 'cms_management_data';
	private $session;

	public function CMS($view){
		$this->view = $view;
		$this->lm = new LoginManager($this->view);
		$this->q = $this->view->get('QUERIES');
		$header = new Header($this->view);
		$this->session = $this->lm->get();
		if (!$this->session){
			// not logged in -> redirect
			$this->view->redirect('http://'.HOST.'/login/', 302);
		}
		$this->write = new EditModel();
		$this->cms = $this->write->table('cms_management');
		$this->cms_data = $this->write->table($this->data_name);
		// set up content
		$this->view->contents('cms_management_contents');
	}
	
	// displays a list of cms items
	public function init(){
		$conds = array('lang_id' => array('=', $this->view->lang()));
		$this->cms->inflate($this->cms, 'id', $this->data_name, 'cms_id', $conds);
		$list = $this->cms->find_all();
		
		trace($list);
	}
	
	// creates cms edit page root ONLY
	public function define($id = null){
		if (isset($this->session['root_access']) && isset($this->session['root_access']['cms']) && $this->session['root_access']['cms']){
			// you are good
		}
		else {
			$this->view->return_error(403);
		}
		// set up the list of accessable list of talbe for the user
		$table_desc = $this->write->table('table_desc');
		$table_desc->join('permissions');
		$table_desc->where('table_desc.table_name = permissions.table_name');
		$table_desc->and('permissions.permission = ?', $this->session['permission']);
		$this->view->assign('TABLE_LIST', $table_desc->find_all());
		$this->view->assign('ID', $id);
		// if there is id then we are in edit mode
		$this->view->assign('DATA', $this->get_data($id));
		// set up the list of languages
		$lang = $this->write->table('languages');
		$this->view->assign('LANGUAGES', $lang->find_all());
		// display UI		
		$this->view->display('define');
	}
	
	private function get_data ($id) {
		$cms = $this->write->table('cms_management');
		$cms->where('id = ?', $id);
		$cms->inflate($cms, 'id', 'cms_management_data', 'cms_id', array('property' => array('!=', 'language_common_column')));
		$data = $cms->find();
		$cms_data = $this->write->table('cms_management_data');
		$cms_data->select('property, value, cms_id');
		$cms_data->where('cms_id = ?', $id);
		$cms_data->and('property = ?', 'language_common_column');
		$cms_data->inflate($cms_data, 'cms_id', 'cms_management_tables', 'cms_id');
		$meta = $cms_data->find();
		if (is_array($data) && is_array($meta)){
			// parse language_common_column value
			$sep = explode('.', $meta['value']);
			$meta['value'] = $sep[1];
			$tables = array($sep[0] => $meta);
			$res = array_merge($data, array('tables' => $tables));

			trace($res);
		
			return $res;
		}
		else {
			return false;
		}
	}
	
	public function save_define(){
		if (isset($this->session['root_access']) && isset($this->session['root_access']['cms']) && $this->session['root_access']['cms']){
			// you are good
		}
		else {
			$this->view->return_error(403);
		}
		// check for new/edit
		$q = $this->view->queries();
		// create a definition in cms_management
		$cms_management = $this->write->table('cms_management');
		$this->write->transaction();
		$cms_management->set('identifier', epoch());
		$res = $cms_management->save();
		if ($res['last_id']){
			$cms_id = $res['last_id'];
			// create the meta data for the definition
			$cms_management_data = $this->write->table('cms_management_data');
			$title = $this->view->queries('title');
			// create the cms page title
			if ($title){
				foreach ($title as $lang_id => $value){
					$cms_management_data->set('cms_id', $cms_id);
					$cms_management_data->set('property', 'title');
					$cms_management_data->set('value', $value);
					$cms_management_data->set('lang_id', $lang_id);
					$cms_management_data->save();
				}
			}
			else {
				$this->write->rollback();
				// need to redirect to define page with the entered data
			}
			// create the table/column definition
			$cms_management_tables = $this->write->table('cms_management_tables');
			$table = $this->view->queries('table');
			if ($table){
				foreach ($table as $table_name => $table_values){
					// multi-lingual table create the language common column definition
					if (isset($table_values['lang_common_column'])){
						$lang_common_column = $table_values['lang_common_column'];
						$cms_management_data->set('cms_id', $cms_id);
						$cms_management_data->set('property', 'language_common_column');
						$cms_management_data->set('value', $table_name.'.'.$lang_common_column);
						$res = $cms_management_data->save();
						if (!$res){
							$this->write->rollback();
							// need to redirect to define page with the entered data
						}
					}
					// column names and attributes
					foreach ($table_values['columns'] as $i => $column){
						foreach ($column['label'] as $lang_id => $value){
							$cms_management_tables->set('cms_id', $cms_id);
							$cms_management_tables->set('lang_id', $lang_id);
							$cms_management_tables->set('label', $value);
							$cms_management_tables->set('table_name', $table_name);
							$cms_management_tables->set('column_name', $column['column_name']);
							$cms_management_tables->set('attributes', serialize($column['attributes']));
							$res = $cms_management_tables->save();
							if (!$res){
								$this->write->rollback();
								// need to redirect to define page with the entered data
							}
						}
					}
				}
				// done!
				trace('we got it');
				$this->write->commit();
			}
			else {
				$this->write->rollback();
				// need to redirect to define page with the entered data
			}
		}
		else {
			$this->write->rollback();
			// need to redirect to define page with the entered data
		}
		
		trace($q);
		
		if (!isset($q['id'])){
			// new
			
		}
		else {
			// edit
		}
		// chage this to redirect later
		$this->define();
	}
	
	// list of defined cms 
	public function cmslist () {
		$cms = $this->write->table('cms_management');
		$cms->inflate($cms, 'id', 'cms_management_data', 'cms_id', array('lang_id' => array('=', $this->view->lang())));
		$list = $cms->find_all();
		$this->view->assign('LIST', $list);
		$this->view->display('cmslist');
	}
	
	// ajax response to get columns for a given table
	public function table_columns($table_name){
		$table = $this->write->table($table_name);
		$this->view->push('json', $table->show());
	}
}
?>
