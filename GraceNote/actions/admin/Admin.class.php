<?php 
Load::model('AdminModel');
Load::model('SelectModel');
Load::action('header/Header');
Load::lib('custom/LoginManager');

class Admin {
	
	private $model;
	private $loginmanager;
	private $c;
	private $q;
	private $item_num = 30;
	
	public function Admin($view){
		$this->c = $view;
		$this->model = new AdminModel();
		$this->loginmanager = new LoginManager($this->c);
		$this->q = $this->c->get('QUERIES');
		$header = new Header($this->c);
	}
	
	public function init(){
		// get text content for the page
		$content = $this->c->contents('admin_init');
		// check to see if the table exists and there is at least a record
		$res = $this->model->check_table();
		if (!$res){
			// start the process to create the initial tables and records
			$this->c->assign('INIT', true);
			// form response
			if (isset($this->q['action']) && isset($this->q['password'])){
				if ($this->q['action'] == 'initial' && $this->q['password']){
					// create admin table and root
					$this->model->create_admin_table($this->q['password']);
					$this->c->assign('INIT', false);
				}
			}
		}
		else {
			//$this->c->assign('INIT', false);
			$this->c->redirect('/menu/');
		}
		// display template
		$this->c->display('init');
	}
	
	public function menu(){
		// get text content for the page
		$res = $this->c->contents('admin_menu');
		if ($this->loginmanager->get()){
			// admin user logged in
			$session = $this->loginmanager->get();
			$categories = $this->model->get_table_categories($session['permission']);
			$this->c->assign('CATEGORIES', $categories);
			if (isset($this->q['category'])){
				$category = $this->q['category'];
				$this->c->set_session('category', $category);
			}
			else {
				if ($this->c->get_session('category')){
					$category = $this->c->get_session('category');
				}
				else{
					$category = false;
				}
			}
			$this->c->assign('CATEGORY', $category);
			// get current page position
			$from = $this->c->queries('LOCATION');
			$to = $this->item_num;
			if (!$from){
				$from = 0;
			}
			// category all
			if ($category == 'all'){
				$category = false;
			}
			$tables = $this->model->get_tables($session['permission'], $category, $from, $to);
			$table_total = $this->model->get_table_total($session['permission'], $category);
			if (isset($table_total['total'])){
				$table_total = $table_total['total']; 
			}
			/*
			// check for dbf
			if ($tables){
				foreach ($tables as $i => $item){
					$dbf = new DBF($item['table_name']);
					$res = $dbf->check();
					if ($res){
						$tables[$i]['dbf'] = true;
					}
				}
			}
			*/
			if (!$tables && $from > 0){
				// try to get the items from 0 to item_num
				if ($category){
					$category = '?category='.$category;
				}
				$this->c->redirect('/menu/'.$category);
			}
			// create page number
			$page = ($from + $this->item_num) / $this->item_num;
			$this->c->assign('TABLE_TOTAL', $table_total);
			$this->c->assign('PAGE', $page);
			$this->c->assign('FROM', $from);
			$this->c->assign('ITEM_NUM', $this->item_num);
			$this->c->assign('CATEGORY', $category);
			$this->c->assign('TABLES', $tables);
			$this->c->display('menu');
		}
		else {
			// admin user NOT logged in
			$this->c->redirect('/login/');
		}
	}
}
?>