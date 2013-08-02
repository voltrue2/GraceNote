<?php
class Dbmanager extends Controller {

	private $view;
	private $sess = null;	

	public function Dbmanager($view) {
		$this->view = $view;
		// check for authentication
		$sess = CmsAuthHandler::check($view, $this);		
		if ($sess) {
			// authenticated
			$this->sess = $sess;
			Text::get($view, $this, 'text');
			// create list of database groups
			$dbGroups = Config::get('Sql');
			$dbList = array();
			if ($dbGroups) {
				foreach ($dbGroups as $name => $conf) {
					$dbList[] = $name;
				}
			}
			$this->view->assign('dbList', $dbList);
			return;
		}
		// not authenticated remember where you were
		$this->view->redirect('/', 401);
	}

	public function index() {
		$this->view->assign('currentPage', null);
		$this->view->respondTemplate('dbmanager/index.html.php');
	}

	public function menu($db) {
		$this->view->assign('currentPage', null);
		$this->view->assign('selectedDb', $db);
		$this->view->respondTemplate('dbmanager/menu.html.php');
	}
	
	public function createTable($db) {
		// check permission
		if ($this->sess['permission'] != 1) {
			return $this->view->redirect('/');
		}
		$this->view->assign('selectedDb', $db);
		$this->view->assign('currentPage', 'createTable');
		$model = new DataModel($db);
		$anonymous = $model->table();
		$this->view->assign('columnTypes', $anonymous->getDataTypes());
		$this->view->respondTemplate('dbmanager/createTable.html.php');
	}
	
	public function tableList($db, $newTable = null) {
		$this->view->assign('selectedDb', $db);
		$this->view->assign('currentPage', 'tableList');
		$model = new DataModel($db);
		$table = $model->table($newTable);
		$useCache = false;
		$tableList = $table->tables($useCache);
		$list = array();
		if (!empty($tableList)) {
			foreach ($tableList as $table) {
				$item = array('name' => $table['tablename'], 'new' => false);
				// highlight the table
				if ($newTable === $table['tablename']) {
					$item['new'] = true;
				}
				$list[] = $item;
			}
		}
		sort($list);
		$this->view->assign('tableList', $list);
		$this->view->respondTemplate('dbmanager/tableList.html.php');
	}
	
	public function createNewTable() {
		$db = $this->getQuery('selectedDb');
		$table = $this->getQuery('table');
		$columns = $this->getQuery('columns');
		$model = new DataModel($db);
		$newTable = $model->table($table);
		$newTable->transaction();
		try {
			// we create an auto increment column called id
			$newTable->setAutoIncrementColumn('id', false, 'primary key');
			foreach ($columns as $i => $column) {
				// lazy column definition > no size
				$newTable->setColumn($column['name'], $column['type']);
			}
			$newTable->create();
			$newTable->commit();
		} catch (Exception $e) {
			$newTable->rollBack();
			Log::error('Dbmanager::createNewTable > ', $e->getMessage());
			return $this->view->respondError(500);
		}
		$this->view->assign('redirectUri', '/dbmanager/tableList/' . $db . '/' . $table . '/#' . $table);
		$this->view->respondJson();
	}
	
	public function deleteTable($db, $tableName) {
		$model = new DataModel($db);
		$table = $model->table($tableName);
		$table->transaction();
		try {
			$res = $table->drop();
			$table->commit();
		} catch (Exception $e) {
			$table->rollBack();
			Log::error('Dbmanager::deleteTable > ' . $e->getMessage());
			return $this->view->respondError(500);
		}
		$this->view->assign('redirectUri', '/dbmanager/tableList/' . $db . '/');
		$this->view->respondJson();
	}
}
