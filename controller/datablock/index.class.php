<?php
class Datablock extends Controller {
	
	private $view;
	private $datablockS = 'cms_datablock_source';
	private $datablock = 'cms_datablock';
	private $dataBlockTypes = array();

	public function Datablock($view) {
		$this->view = $view;
		// check for authentication
		$sess = CmsAuthHandler::check($view, $this);
		if ($sess) {
			// authenticated
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
			// create data block type list
			$dm = new DataModel('StaticData');
			$dataTypes = $dm->staticData();
			$this->dataBlockTypes = $dataTypes->getMany('system/cms/dataBlockTypes.csv');
			$this->view->assign('dataBlockTypes', $this->dataBlockTypes);
			return;
		}
		// not authenticated remember where you were
		$this->view->redirect('/', 401);
	}

	public function index($db) {
		$dm = new DataModel($db);
		$dbSrc = $dm->table($this->datablockS);
		$dbSrc->order('name');
		$list = $dbSrc->getMany();
		$this->view->assign('currentPage', 'manageDataBlock');
		$this->view->assign('selectedDb', $db);
		$this->view->assign('datablockList', $list);
		$this->view->respondTemplate('datablock/index.html.php');
	}
	
	public function create($db) {
		$sess = $this->getSession();
		if (!$sess || !isset($sess['permission']) && $sess['permission'] != 1) {
			return $this->view->respondError(401);
		}
		$this->view->assign('currentPage', 'manageDataBlock');
		$this->view->assign('selectedDb', $db);
		$dm = new DataModel($db);
		// get list of all tables
		$this->getTableList($dm);
		$this->view->respondTemplate('datablock/create.html.php');
	}
	
	public function createNewDataBlockSource($db) {
		$sess = $this->getSession();
		if (!$sess || !isset($sess['permission']) && $sess['permission'] != 1) {
			return $this->view->respondError(401);
		}
		$this->view->assign('currentPage', 'manageDataBlock');
		$this->view->assign('selectedDb', $db);
		$dm = new DataModel($db);
		// get list of all tables
		$tableList = $this->getTableList($dm);
		// start creating
		$name = trim($this->getQuery('name'));
		$mainTable = trim($this->getQuery('mainTable'));
		$mainColumn = trim($this->getQuery('mainColumn'));
		$desc = $this->getQuery('desc');
		// check for required fileds
		if (!$name || !$mainTable || !$mainColumn) {
			return $this->view->respondError(404, 'Datablock::createNewDataBlockSource > Missing name, mainTable, or mainColumn >> ', $name, $mainTable, $mainColumn);
		}
		// validate table name
		if (!in_array($mainTable, $tableList)) {
			return $this->view->respondError(404, 'Datablock::createNewDataBlockSource > Invalid mainTable >> ' . $mainTable);
		}
		// now create
		$src = $dm->table($this->datablockS);
		$src->transaction();
		try {
			$src->set('name', $name);
			$src->set('main_table', $mainTable);
			$src->set('main_column', $mainColumn);
			$src->set('description', $desc);
			$res = $src->save('id');
			if (!$res) {
				throw new Exception('Failed to insert');
			}
			$src->commit();
		} catch (Exception $e) {
			$this->src->rollBack();
			return $this->view->respondError(404, $e->getMessage());
		}
		// success > respond with JSON
		$this->view->assign('redirectUri', '/datablock/editDataBlock/' . $db . '/' . $res['lastId'] . '/');
		$this->view->respondJson();
	}
	
	public function editDataBlock($db, $srcId) {
		$sess = $this->getSession();
		if (!$sess || !isset($sess['permission']) && $sess['permission'] != 1) {
			return $this->view->respondError(401);
		}
		$this->view->assign('currentPage', 'manageDataBlock');
		$this->view->assign('selectedDb', $db);
		$dm = new DataModel($db);
		// get list of all tables
		$tableList = $this->getTableList($dm);
		// get data block source data
		$src = $dm->table($this->datablockS);
		$src->where('id = ?', $srcId);
		$srcData = $src->getOne();
		$this->view->assign('srcData', $srcData);
		// get data blocks associated to source
		$blocks = $dm->table($this->datablock);
		$blocks->select('id, name, required, datablock_type As type, data_length_limit As data_limit, source_table As srctable, source_ref_column AS srcrefcolumn, source_column As srccolumn, reference_table As reftable, reference_column_display As refdisplay, reference_column As refcolumn');
		$blocks->where('source_id = ?', $srcId);
		$blocks->order('id');
		$blockList = $blocks->getMany();
		$this->view->assign('dataBlockList', $blockList);
		$this->view->respondTemplate('datablock/edit.html.php');
	}
	
	public function updateDataBlockSource($db, $id) {
		$sess = $this->getSession();
		if (!$sess || !isset($sess['permission']) && $sess['permission'] != 1) {
			return $this->view->respondError(401);
		}
		$name = trim($this->getQuery('name'));
		$mainTable = trim($this->getQuery('mainTable'));
		$mainColumn = trim($this->getQuery('mainColumn'));
		$desc = $this->getQuery('desc');
		$dm = new DataModel($db);
		// get list of all tables
		$tableList = $this->getTableList($dm);
		// check for required fileds
		if (!$name || !$mainTable || !$mainColumn) {
			return $this->view->respondError(404, 'Datablock::createNewDataBlockSource > Missing name or mainTable >> ', $name, $mainTable);
		}
		// validate table name
		if (!in_array($mainTable, $tableList)) {
			return $this->view->respondError(404, 'Datablock::createNewDataBlockSource > Invalid mainTable >> ' . $mainTable);
		}
		// now update
		$src = $dm->table($this->datablockS);
		$src->transaction();
		try {
			$src->where('id = ?', $id);
			$src->set('name', $name);
			$src->set('main_table', $mainTable);
			$src->set('main_column', $mainColumn);
			$src->set('description', $desc);
			$src->update();
			$src->commit();
		} catch (Exception $e) {
			$src->rollBack();
			return $this->view->respondError(404, $e->getMessage());
		}
		// success > respond with JSON
		$this->view->assign('redirectUri', '/datablock/editDataBlock/' . $db . '/' . $id . '/');
		$this->view->respondJson();
	}
	
	public function deleteDataBlockSource($db) {
		$sess = $this->getSession();
		if (!$sess || !isset($sess['permission']) && $sess['permission'] != 1) {
			return $this->view->respondError(401);
		}
		$id = $this->getQuery('id');
		$dm = new DataModel($db);
		$src = $dm->table($this->datablockS);
		$src->transaction();
		try {
			$src->where('id = ?', $id);
			$src->delete();
			$src->commit();
		} catch (Exception $e) {
			$src->rollBack();
			return $this->view->respondError(404, $e->getMessage());
		}
		// success > respond with JSON
		$this->view->assign('redirectUri', '/datablock/index/' . $db . '/');
		$this->view->respondJson();
	}

	public function updateDataBlocks($db, $srcId) {
		$sess = $this->getSession();
		if (!$sess || !isset($sess['permission']) && $sess['permission'] != 1) {
			return $this->view->respondError(401);
		}
		$dataBlocks = $this->getQuery('dataBlocks');
		$dm = new DataModel($db);
		$tableList = $this->getTableList($dm);
		$table = $dm->table($this->datablock);
		$table->transaction();
		try {
			for ($i = 0, $len = count($dataBlocks); $i < $len; $i++) {
				$dataBlock = $dataBlocks[$i];
				// check for the required field
				if (!$dataBlock['name'] || !isset($dataBlock['required']) || !$dataBlock['type']  || !$dataBlock['limit'] || !$dataBlock['srcTable'] || !$dataBlock['srcRefColumn'] || !$dataBlock['srcColumn']) {
					throw new Exception('missing required field(s)');
				}
				// validate source table name
				if (!in_array($dataBlock['srcTable'], $tableList)) {
					throw new Exception('Datablock::updateDataBlocks > Invalid srcTable >> ' . $dataBlock['srcTable']);
				}
				$validated = 0;
				$srcTable = $dm->table($dataBlock['srcTable']);
				$srcDesc = $srcTable->describe();
				// validate source ref column
				for ($c = 0, $cnt = count($srcDesc); $c < $cnt; $c++) {
					if ($dataBlock['srcRefColumn'] === $srcDesc[$c]['field']) {
						$validated += 1;
						break;
					}
				}
				// validate source column
				for ($c = 0, $cnt = count($srcDesc); $c < $cnt; $c++) {
					if ($dataBlock['srcColumn'] === $srcDesc[$c]['field']) {
						$validated += 1;
						break;
					}
				}
				if ($validated < 2) {
					throw new Exception('Datablock::updateDataBlocks > Invalid srcRefColumn or srcColumn >> ' . $dataBlock['srcRefColumn'] . ' or ' . $dataBlock['srcColumn']);
				}
				// validate reference table if given
				if ($dataBlock['refTable']) {
					// validate ref table
					if ( !in_array($dataBlock['refTable'], $tableList)) {
						throw new Exception('Datablock::updateDataBlocks > Invalid refTable >> ' . $dataBlock['refTable']);
					}
					// validate ref display column and column
					$validated = 0;
					$srcTable = $dm->table($dataBlock['refTable']);
					$srcDesc = $srcTable->describe();
					for ($c = 0, $cnt = count($srcDesc); $c < $cnt; $c++) {
						if ($dataBlock['refColumn'] === $srcDesc[$c]['field'] || $dataBlock['refDisplayColumn'] === $srcDesc[$c]['field']) {
							$validated += 1;
						}
						if ($validated === 2) {
							break;
						}
					}
					if ($validated < 2) {
						throw new Exception('Datablock::updateDataBlocks > Invalid refColumn or refDisplayColumn >> ' . $dataBlock['refColumn'] . ', ' . $dataBlock['refDisplayColumn']);
					}
				}
				// update
				$table->set('source_id', $srcId);
				$table->set('name', $dataBlock['name']);
				$table->set('datablock_type', $dataBlock['type']);
				$table->set('data_length_limit', $dataBlock['limit']);
				$table->set('required', $dataBlock['required'] ? $dataBlock['required'] : 0);
				$table->set('source_table', $dataBlock['srcTable']);
				$table->set('source_ref_column', $dataBlock['srcRefColumn']);
				$table->set('source_column', $dataBlock['srcColumn']);
				$table->set('reference_table', $dataBlock['refTable']);
				$table->set('reference_column_display', $dataBlock['refDisplayColumn']);
				$table->set('reference_column', $dataBlock['refColumn']);
				if ($dataBlock['id']) {
					// update/edit
					$table->where('id = ?', $dataBlock['id']);
					$res = $table->update();
				} else {
					// create/new
					$res = $table->save();
				}
				if (!$res) {
					throw new Exception('failed to write...');
				}
			}
		} catch (Exception $e) {
			$table->rollBack();
			return $this->view->respondError(404, 'Datablock::updateDataBlocks > Failed to update >> ' . $e->getMessage());
		}
		$table->commit();
		$this->view->assign('redirectUri', '/datablock/editDataBlock/' . $db . '/' . $srcId . '/');
		$this->view->respondJson();
	}

	public function deleteDataBlock($db, $srcId) {
		$sess = $this->getSession();
		if (!$sess || !isset($sess['permission']) && $sess['permission'] != 1) {
			return $this->view->respondError(401);
		}
		$id = $this->getQuery('id');
		$dm = new DataModel($db);
		$table = $dm->table($this->datablock);
		$table->transaction();
		try {
			$table->where('id = ?', $id);
			$table->delete();
			$table->commit();
		} catch (Exception $e) {
			$table->rollBack();
			return $this->view->respondError(404, 'Datablock::deleteDataBlock > Failed to delete' . $e->getMessage());
		}
		$this->view->assign('redirectUri', 'datablock/editDataBlock/' + $db . '/' . $srcId . '/');
		$this->view->respondJson();
	}
	
	public function getColumnList($db, $table) {
		$dm = new DataModel($db);
		$table = $dm->table($table);
		$desc = $table->describe();
		$this->view->assign('list', $desc);
		$this->view->respondJson();
	}
	
	private function getTableList($dm) {
		$anonymous = $dm->table();
		$useCache = false;
		$tableList = $anonymous->tables($useCache);
		$list = array();
		for ($i = 0, $len = count($tableList); $i < $len; $i++) {
			$list[] = $tableList[$i]['tablename'];
		}
		sort($list);
		$this->view->assign('tableList', $list);
		return $list;
	}
}
?>
