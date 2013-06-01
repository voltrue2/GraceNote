<?php
class Tabledata extends Controller {

	private $view;
	private $sess = null;

	public function Tabledata($view) {
		$this->view = $view;
		// check for authentication
		$sess = CmsAuthHandler::check($view, $this);
		if ($sess) {
			// authenticated
			$this->sess = $sess;
			$this->view->assign('currentPage', 'tableList');
			Text::get($view, $this, 'text');
			return;
		}
		// not authenticated remember where you were
		$this->view->redirect('/', 401);
	}

	public function index($db, $tableName) {
		// check permission
		if ($this->sess['permission'] != 1) {
			return $this->view->redirect('/tabledata/dataList/' . $db . '/' . $tableName . '/');
		}
		$model = new DataModel($db);
		$table = $model->table($tableName);
		$useCache = false;
		$desc = $table->describe($useCache);
		$this->view->assign('selectedDb', $db);
		$this->view->assign('tableName', $tableName);
		$this->view->assign('columnTypes', $table->getDataTypes());
		$this->view->assign('tableDesc', $desc);
		$this->view->respondTemplate('tabledata/index.html.php');
	}

	public function editTableStructure($db, $tableName) {
		// check permission
		if ($this->sess['permission'] != 1) {
			return $this->respondError(401);
		}
		$columns = $this->getQuery('columns');
		$model = new DataModel($db);
		$table = $model->table($tableName);
		// original structure
		$desc = $table->describe();
		// compare the changes and update
		$table->transaction();
		try {
			$count = count($columns);
			for ($i = 0; $i < $count; $i++) {
				if (isset($desc[$i])) {
					// edit existing column
					$columnName = $desc[$i]['field'];
					if ($desc[$i]['field'] !== $columns[$i]['name']) {
						// update column name
						$table->renameColumn($desc[$i]['field'], $columns[$i]['name']);
						$columnName = $columns[$i]['name'];
						Log::debug('>>>> name updated > ' . $desc[$i]['field'] . ' > ' . $columns[$i]['name']);
					}
					if ($desc[$i]['type'] !== $columns[$i]['type']) {
						// update column type
						$table->changeDataType($columnName, $columns[$i]['type']);
						Log::debug('>>>> type updated > ' . $desc[$i]['type'] . ' > ' . $columns[$i]['type']);
					}
				} else {
					// add a new column
					$table->setColumn($columns[$i]['name'], $columns[$i]['type']);
					$table->addColumns();
					Log::debug('>>>> added new column', $columns[$i]);
				}
			}
			$table->commit();
		} catch (Exception $e) {
			Log::error('Tabledata::editTableStructure > ', $e->getMessage());
			$table->rollBack();
			return $this->view->responseError(500);
		}
		$this->view->assign('redirectUri', '/tabledata/index/' . $db . '/' . $tableName . '/');
		$this->view->respondJson();	
	}
	
	public function removeColumn($db, $tableName) {
		// check permission
		if ($this->sess['permission'] != 1) {
			return $this->respondError(401);
		}
		$columnName = $this->getQuery('columnName');
		$model = new DataModel($db);
		$table = $model->table($tableName);
		$table->transaction();
		try {
			$table->setColumn($columnName);
			$table->removeColumns();
			$table->commit();
			Log::debug('>>>> column removed > ' . $columnName);
		} catch (Exception $e) {
			Log::error('Tabledata::removeColumn > ', $e->getMessage());
			$table->rollBack();
			return $this->view->responseError(500);	
		}
		$this->view->assign('redirectUri', '/tabledata/index/' . $db . '/' . $tableName . '/');
		$this->view->respondJson();
	}

	public function getData($db, $tableName) {
		$columns = $this->getQuery('columns');
		$dm = new DataModel($db);
		$table = $dm->table($tableName);
		// table structure
		$desc = $table->describe($useCache = false);
		// get data
		$data = null;
		if (!empty($columns)) {
			$i = 0;
			foreach ($desc as $item) {
				foreach ($columns as $col => $val) {
					if ($item['field'] === $col && $val !== null && !is_array($val)) {
						if ($i === 0) {
							$table->where($col . ' = ?', $val);
						} else {
							$table->and($col . ' = ?', $val);
						}
						$i += 1;
					}	
				}
			}
			$data = $table->getOne();
		}
		// respond
		$this->view->assign('data', $data);
		$this->view->assign('desc', $desc);
		$this->view->respondJson();
	}

	public function getDataList($db, $tableName) {
		$cols = $this->getQuery('columns');
		$dm = new DataModel($db);
		$table = $dm->table($tableName);
		for ($i = 0, $len = count($cols); $i < $len; $i++) {
			$table->select($cols[$i]);
		}
		$list = $table->getMany();
		$this->view->assign('dataList', $list);
		$this->view->respondJson();
	}

	public function dataList($db, $tableName, $from = 0, $searchCol = null, $search = null) {
		$to = 100;
		$dm = new DataModel($db);
		$table = $dm->table($tableName);
		$desc = $table->describe();
		if ($searchCol && $search) {
			$where = false;
			$search = urldecode($search);
			for ($i = 0, $len = count($desc); $i < $len; $i++) {
				if ($searchCol === $desc[$i]['field']) {
					if ($desc[$i]['type'] === 'int') {
						$where = array($searchCol . ' = ?', $search);
					} else {
						$where = array($searchCol . ' LIKE ?', '%' . $search . '%');
					}
					break;
				}
			}
			if ($where) {
				$table->where($where[0], $where[1]);
			}
		}
		$table->limit($from, $to);
		$list = $table->getMany();
		$this->view->assign('desc', $desc);
		$this->view->assign('list', $list);
		$this->view->assign('selectedDb', $db);
		$this->view->assign('tableName', $tableName);
		$this->view->assign('from', $from);
		$this->view->assign('to', $to);
		$this->view->assign('searchCol', $searchCol);
		$this->view->assign('search', $search);
		$tableList = $table->tables();
		$list = array();
		for ($i = 0, $len = count($tableList); $i < $len; $i++) {
			$list[] = $tableList[$i]['tablename'];
		}
		sort($list);
		$this->view->assign('tableList', $list);
		$this->view->respondTemplate('tabledata/datalist.html.php');
	}

	public function updateData($db, $tableName) {
		// check permission
		if ($this->sess['permission'] != 1 && $this->sess['permission'] != 2) {
			return $this->view->respondError(401);
		}
		$prevData = $this->getQuery('prevData');
		$data = $this->getQuery('data');
		$dm = new DataModel($db);
		$table = $dm->table($tableName);
		$table->transaction();
		try {	
			$i = 0;
			foreach ($prevData as $col => $val) {
				if ($val !== null) {
					if ($i === 0) {
						$table->where($col . ' = ?', $val);
					} else {
						$table->and($col . ' = ?', $val);
					}
					$i += 1;
				}
			}
			$res = $table->getOne();
			if (!$res) {
				throw new Exception('expected data missing', $prevData);
			}
			// check for change(s)
			$i = 0;
			foreach ($res as $col => $val) {
				// the reason we dont do !== is because php is really lazy on data type
				if (isset($data[$col]) && $val != $data[$col]) {
					// change
					Log::debug($col . ' > ' . $val . ' : ' . $data[$col]);
					$table->set($col, $data[$col]);
				} else {
					if ($i === 0) {
						$table->where($col . ' = ?', $val);
					} else {
						$table->and($col . ' = ?', $val);
					}
					$i += 1;		
				}
			}
			$table->update();
			$table->commit();
		} catch (Exception $e) {
			$table->rollBack();
			Log::error($e->getMessage());
			return $this->view->respondError(404);
		}
		$this->view->respondJson();
	}

	public function createData($db, $tableName) {
		// check permission
		if ($this->sess['permission'] != 1 && $this->sess['permission'] != 2) {
			return $this->view->respondError(401);
		}
		$data = $this->getQuery('data');
		$dm = new DataModel($db);
		$table = $dm->table($tableName);
		$table->transaction();
		try {
			// check for duplicate(s)
			$i = 0;
			foreach ($data as $col => $val) {
				if ($i === 0) {
					$table->where($col . ' = ?', $val);
				} else {
					$table->and($col . ' = ?', $val);
				}
				$i += 1;
			}
			$res = $table->getOne();
			if ($res) {
				// duplicate found
				Log::error($res);
				throw new Exception('data already exists');
			}
			// create new data
			foreach ($data as $col => $val) {
				$table->set($col, $val);
			}
			$table->save();
			$table->commit();
		} catch (Exception $e) {
			$table->rollBack();
			Log::error($e->getMessage(), $data);
			return $this->view->respondError(404);
		}
		$this->view->respondJson();
	}

	public function deleteData($db, $tableName) {
		// check permission
		if ($this->sess['permission'] != 1 && $this->sess['permission'] != 2 && $this->sess['permission'] != 3) {
			return $this->view->respondError(401);
		}
		$data = $this->getQuery('data');
		$dm = new DataModel($db);
		$table = $dm->table($tableName);
		$table->transaction();
		try {
			$i = 0;
			foreach ($data as $col => $val) {
				if ($val) { 
					if ($i === 0) {
						$table->where($col . ' = ?', $val);
					} else {
						$table->and($col . ' = ?', $val);
					}
				}
				$i += 1;
			}
			$res = $table->delete();
			if (!$res) {
				throw new Exception('failed to delete');
			}
			$table->commit();
		} catch (Exception $e) {
			$table->rollBack();
			Log::error($e->getMessage(), $data);
			return $this->view->respondError(404);

		}
		$this->view->respondJson();
	}

	public function export($db, $tableName) {
		// check permission
		if ($this->sess['permission'] != 1 && $this->sess['permission'] != 2 && $this->sess['permission'] != 3) {
			return $this->view->respondError(401);
		}
		$dm = new DataModel($db);
		$table = $dm->table($tableName);
					
	}
}
?>
