<?php 
class Editdatablock extends Controller {
	
	private $view;
	private $dataBlockTypes;
	private $itemNum = 60;

	public function Editdatablock($view) {
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
			$this->view->assign('currentPage', 'manageDataBlock');
			$this->view->assign('dbList', $dbList);
			// create data block type list
			$dm = new DataModel('StaticData');
			$sd = $dm->staticData();
			$this->dataBlockTypes = $sd->getMany('system/cms/dataBlockTypes.csv');
			$this->view->assign('dataBlockTypes', $this->dataBlockTypes);
			return;
		}
		// not authenticated remember where you were
		//$sess['prevUri'] = $this->getUri();
		//$this->setSession($sess);
		$this->redirect('/', 401);
	}
	
	public function index($db, $srcId, $from = 0) {
		$this->view->assign('selectedDb', $db);
		$this->view->assign('srcId', $srcId);
		$this->view->assign('from', $from);
		$this->view->assign('to', $this->itemNum + $from);
		$this->view->assign('num', $this->itemNum);
		$dm = $this->dm($db);
		$table = $this->table($dm);
		$table->where('id = ?', $srcId);
		$src = $table->getOne();
		$this->view->assign('src', $src);
		$datablocks = $this->dataBlocks($dm, $srcId);
		$this->view->assign('blocks', $datablocks);
		if ($src) {
			$selectLists = array();
			$mainTable = $dm->table($src['main_table']);
			$mainTable->select('*');
			// inflate
			for ($i = 0, $len = count($datablocks); $i < $len; $i++) {
				$item = $datablocks[$i];
				if ($item['source_table'] !== $src['main_table']) {
					$mainTable->select($src['main_column'] . ' AS ' . $item['source_column']);
					$mainTable->inflate($src['main_table'], $item['source_column'], $item['source_table'], $item['source_ref_column']);
				}
				if ($item['reference_table']) {
					$mainTable->inflate($item['source_table'], $item['source_column'], $item['reference_table'], $item['reference_column']);
				}
				// check for select list
				if (!isset($selectLists[$item['source_table']]) && $item['datablock_type'] === 'selectList') {
					$listName = $item['source_table'];
					if ($item['reference_table']) {
						$listName = $item['reference_table']; 
					}
					$listTable = $dm->table($listName);
					$listTable->order($item['reference_column_display']);
					$selectLists[$listName] = $listTable->getMany();
				}
			}
			$mainTable->limit($from, $this->itemNum);
			$mainTable->order($src['main_column'], 'DESC');
			$list = $mainTable->getMany();
			$this->view->assign('selectLists', $selectLists);
			$this->view->assign('list', $list);
			$this->view->respondTemplate('editdatablock/index.html.php');
		} else {
			return $this->view->respondError(404);
		}
	}

	public function search($db, $srcId, $searchColumn = null, $searchThis = null, $from = 0) {
		$searchThis = urldecode($searchThis);
		$this->view->assign('selectedDb', $db);
		$this->view->assign('srcId', $srcId);
		$this->view->assign('from', $from);
		$this->view->assign('to', $this->itemNum + $from);
		$this->view->assign('num', $this->itemNum);
		$this->view->assign('searchMode', true);
		$this->view->assign('searchColumn', $searchColumn);
		$this->view->assign('searchThis', $searchThis);
		$dm = $this->dm($db);
		$table = $this->table($dm);
		$table->where('id = ?', $srcId);
		$src = $table->getOne();
		$this->view->assign('src', $src);
		$datablocks = $this->dataBlocks($dm, $srcId);
		$this->view->assign('blocks', $datablocks);
		if ($src) {
			$selectLists = array();
			$mainTable = $dm->table($src['main_table']);
			// get all columns
			$mainDesc = $mainTable->describe();
			$type = null;
			for ($i = 0, $len = count($mainDesc); $i < $len; $i++) {
				if ($mainDesc[$i]['field'] === $searchColumn) {
					$type = $mainDesc[$i]['type'];
				}
			}
			$mainTable->select('*');
			if ($searchColumn && $searchThis) {
				if ($type === 'int') {
					// check for smart searching
					/*
					* valid statements:
						1. <operator> <space> <value> Example: >= 10
						2. <value> Example: 10 
					*/
					$operators = array('=', '!=', '<>', '>', '<', '<=', '>=');
					$sep = mb_split(' ', urldecode($searchThis));
					Log::debug($sep);
					$operator = '=';
					if (in_array($sep[0], $operators)) {
						$operator = $sep[0];
						$searchThis = $sep[1];	
					}
					$mainTable->where($searchColumn . ' ' . $operator . ' ?', $searchThis);
				} else {
					$st = '%' . $searchThis . '%';
					$mainTable->where($searchColumn . ' LIKE ?', $st);
					$mainTable->or($searchColumn . ' LIKE ?', strtolower($st));
				}
			}
			// inflate
			for ($i = 0, $len = count($datablocks); $i < $len; $i++) {
				$item = $datablocks[$i];
				if ($item['source_table'] !== $src['main_table']) {
					$mainTable->select($src['main_column'] . ' AS ' . $item['source_column']);
					$mainTable->inflate($src['main_table'], $item['source_column'], $item['source_table'], $item['source_ref_column']);
				}
				if ($item['reference_table']) {
					$mainTable->inflate($item['source_table'], $item['source_column'], $item['reference_table'], $item['reference_column']);
				}
				// check for select list
				if (!isset($selectLists[$item['source_table']]) && $item['datablock_type'] === 'selectList') {
					$listName = $item['source_table'];
					if ($item['reference_table']) {
						$listName = $item['reference_table']; 
					}
					$listTable = $dm->table($listName);
					$listTable->order($item['reference_column_display']);
					$selectLists[$listName] = $listTable->getMany();
				}
			}
			$mainTable->limit($from, $this->itemNum);
			$mainTable->order($src['main_column'], 'DESC');
			$list = $mainTable->getMany();
			$this->view->assign('selectLists', $selectLists);
			$this->view->assign('list', $list);
			$this->view->respondTemplate('editdatablock/index.html.php');
		} else {
			return $this->view->respondError(404);
		}
	}

	public function updateData($db, $srcId) {
		$sess = $this->getSession();
		if (!$sess || !isset($sess['permission']) || ($sess['permission'] != 1 && $sess['permission'] != 2)) {
			return $this->view->respondError(401);
		} 
		$table = $this->getQuery('table');
		$id = $this->getQuery('id');
		$refColumn = $this->getQuery('refColumn');
		$column = $this->getQuery('column');
		$value = $this->getQuery('value');
		$rules = $this->getQuery('rules');
		// apply rules
		if ($rules['required'] && $value === null) {
			Log::error('missing value');
			return $this->view->respondError(404);
		}
		if ($rules['type'] === 'number' && !is_numeric($value)) {
			Log::error('value type must be number');
			return $this->view->respondError(404);
		}
		if ($rules['type'] !== 'selectList' && mb_strlen($value) > $rules['limit']) {
			Log::error('value size too big', mb_strlen($value) . ' > ' . $rules['limit']);
			return $this->view->respondError(404);
		}
		// save
		$dm = new DataModel($db);
		$table = $dm->table($table);
		$table->transaction();
		try {
			$table->set($column, $value);
			$table->where($refColumn . ' = ?', $id);
			$res = $table->update();
			if (!$res) {
				throw new Exception('failed to update');
			}
			$table->commit();
		} catch (Exception $e) {
			Log::error($e);
			$table->rollBack();
			return $this->view->respondError(404);
		}
		$this->view->assign('success', true);
		$this->view->respondJson();
	}

	public function getRefList($db, $srcId) {
		$table = $this->getQuery('table');
		$ref = $this->getQuery('refColumn');
		$display = $this->getQuery('displayColumn');
		$whereColumn = $this->getQuery('whereColumn');
		$whereValue = $this->getQuery('whereValue');
		$dm = new DataModel($db);
		$table = $dm->table($table);
		if ($ref !== 'ident') {
			$ref .= ' AS ident';
		}
		if ($display !== 'name') {
			$display .= ' AS name';
		}
		$table->select($ref);
		$table->select($display);
		// check where column
		$desc = $table->describe();
		$exists = false;
		for ($i = 0, $len = count($desc); $i < $len; $i++) {
			if ($desc[$i]['field'] === $whereColumn) {
				$exists = true;
				break;
			}
		}
		if ($exists && $whereValue) {
			$table->where($whereColumn . ' = ?', $whereValue);
		}
		$table->order($display);
		$this->view->assign('list', $table->getMany());
		$this->view->respondJson();
		
	}

	public function addItemToList($db, $srcId, $from) {
		$sess = $this->getSession();
		if (!$sess || !isset($sess['permission']) || ($sess['permission'] != 1 && $sess['permission'] != 2)) {
			return $this->view->respondError(401);
		} 
		$table = $this->getQuery('table');
		$refColumn = $this->getQuery('refColumn');
		$ref = $this->getQuery('ref');
		$valColumn = $this->getQuery('valColumn');
		$val = $this->getQuery('val');
		$rules = $this->getQuery('rules');
		$dm = new DataModel($db);
		$table = $dm->table($table);
		$table->where($refColumn . ' = ?', $ref);
		$list = $table->getMany();
		// check limit
		$count = count($list);
		if ($count >= $rules['limit']) {
			Log::error('addItemToList > already reached max allowed number');
			return $this->view->respondError(404);
		}
		// check double
		for ($i = 0; $i < $count; $i++) {
			if ($list[$i][$valColumn] === $val) {
				// double
				Log::error('addItemToList > same item already exists', $ref, $val);
				return $this->view->respondError(404);
			}
		}
		$table->transaction();
		try {
			// save
			$table->set($refColumn, $ref);
			$table->set($valColumn, $val);
			$table->save();
			// response
			$table = $this->table($dm);
			$table->where('id = ?', $srcId);
			$src = $table->getOne();
			// list
			$mainTable = $dm->table($src['main_table']);
			$mainTable->select('*');
			// inflate
			$datablocks = $this->dataBlocks($dm, $srcId);
			for ($i = 0, $len = count($datablocks); $i < $len; $i++) {
				$item = $datablocks[$i];
				if ($item['source_table'] !== $src['main_table']) {
					$mainTable->select($src['main_column'] . ' AS ' . $item['source_column']);
					$mainTable->inflate($src['main_table'], $item['source_column'], $item['source_table'], $item['source_ref_column']);
				}
				if ($item['reference_table']) {
					$mainTable->inflate($item['source_table'], $item['source_column'], $item['reference_table'], $item['reference_column']);
				}
			}
			if (!$from) {
				$from = 0;
			}
			$mainTable->limit($from, $this->itemNum);
			$mainTable->order($src['main_column'], 'DESC');
			$list = $mainTable->getMany();
			$this->view->assign('list', $list);
			// commit
			$table->commit();
		} catch (Exception $e) {
			$table->rollBack();
			Log::error($e);
			return $this->view->respondError(404);
		}
		$this->view->respondJson();
	}

	public function removeItem($db, $srcId, $from) {
		$sess = $this->getSession();
		if (!$sess || !isset($sess['permission']) || ($sess['permission'] != 1 && $sess['permission'] != 2)) {
			return $this->view->respondError(401);
		} 
		$table = $this->getQuery('table');
		$refColumn = $this->getQuery('refColumn');
		$ref = $this->getQuery('ref');
		$assocColumn = $this->getQuery('assocColumn');
		$assoc = $this->getQuery('assoc');
		$rules = $this->getQuery('rules');
		$dm = new DataModel($db);
		$table = $dm->table($table);
		$table->where($assocColumn . ' = ?', $assoc);
		$list = $table->getMany();
		// check required
		$count = count($list);
		if ($rules['required'] && $count === 1) {
			Log::error('removeItem > cannot have 0 item', $list);
			return $this->view->respondError(404);
		}
		$table->transaction();
		try {
			// delete
			$table->where($refColumn . ' = ?', $ref);
			$table->and($assocColumn . ' = ?', $assoc);
			$res = $table->delete();
			if (!$res) {
				throw new Exception('failed delete');
			}
			// response
			$table = $this->table($dm);
			$table->where('id = ?', $srcId);
			$src = $table->getOne();
			// list
			$mainTable = $dm->table($src['main_table']);
			$mainTable->select('*');
			// inflate
			$datablocks = $this->dataBlocks($dm, $srcId);
			for ($i = 0, $len = count($datablocks); $i < $len; $i++) {
				$item = $datablocks[$i];
				if ($item['source_table'] !== $src['main_table']) {
					$mainTable->select($src['main_column'] . ' AS ' . $item['source_column']);
					$mainTable->inflate($src['main_table'], $item['source_column'], $item['source_table'], $item['source_ref_column']);
				}
				if ($item['reference_table']) {
					$mainTable->inflate($item['source_table'], $item['source_column'], $item['reference_table'], $item['reference_column']);
				}
			}
			if (!$from) {
				$from = 0;
			}
			$mainTable->limit($from, $this->itemNum);
			$mainTable->order($src['main_column'], 'DESC');
			$list = $mainTable->getMany();
			$this->view->assign('list', $list);
			// commit
			$table->commit();
		} catch (Exception $e) {
			$table->rollBack();
			Log::error($e);
			return $this->view->respondError(404);
		}
		$this->view->respondJson();
	}

	public function createNew($db, $srcId) {
		$sess = $this->getSession();
		if (!$sess || !isset($sess['permission']) || ($sess['permission'] != 1 && $sess['permission'] != 2)) {
			return $this->view->respondError(401);
		} 
		$newData = $this->getQuery('newData');
		$dm = new DataModel($db);
		// prepare update sql queries
		$mainTable = null;
		$mainTableName = null;
		$mainColumnName = null;
		$refValue = null;
		$main = array();
		$sub = array();
		for ($i = 0, $len = count($newData); $i < $len; $i++) {
			$data = $newData[$i];
			if (isset($data['mainTable']) && $data['mainTable']) {
				$mainTableName = $data['table'];
				$mainColumnName = $data['column'];
				$refValue = $data['value'];
				$mainTable = $dm->table($mainTableName);
				$main[] = array('column' => $data['column'], 'value' => $data['value']);
			} else {
				// main table value
				if ($data['table'] === $mainTableName) {
					$main[] = array('column' => $data['column'], 'value' => $data['value']);
				} else if ($data['table'] !== $mainTableName && isset($data['value'])) {
					if (is_array($data['value'])) {
						// other table with value as a list
						for ($j = 0, $jlen = count($data['value']); $j < $jlen; $j++) {
							$subData = $data;
							$subData['value'] = $data['value'][$j];
							$sub[] = $subData;
						}
					} else {
						// other table with value
						$sub[] = $data;
					}
				}
			}
		}
		$mainTable->transaction();
		try {
			// check redundancy
			$mainTable->select($mainColumnName);
			$mainTable->where($mainColumnName . ' = ?', $refValue);
			$redundant = $mainTable->getOne(0, $useCache = false);
			if ($redundant) {
				Log::error('duplicate record...');
				throw new Exception ('a record already exists with ' . $mainColumnName . ' = ' . $refValue);
			}
			// save main
			for ($i = 0, $len = count($main); $i < $len; $i++) {
				$mainTable->set($main[$i]['column'], $main[$i]['value']);
			}
			$res = $mainTable->save($mainColumnName);
			if (!$res) {
				Log::error('failed to create a record...');
				throw new Exception('failed to create new record');
			}
			for ($i = 0, $len = count($sub); $i < $len; $i++) {
				$data = $sub[$i];
				$table = $dm->table($data['table']);
				$table->set($data['refColumn'], $refValue);
				$table->set($data['column'], $data['value']);
				$res = $table->save();
				if (!$res) {
					Log::error('failed to create s sub record...');
					throw new Exception('failed to create new record');
				}
			}
			$mainTable->commit();
		} catch (Exception $e) {
			$mainTable->rollBack();
			Log::error($e);
			$this->view->respondError(404);
		}
		$this->view->respondJson();
	}

	public function deleteData($db, $srcId, $dataId) {
		$sess = $this->getSession();
		if (!$sess || !isset($sess['permission']) || ($sess['permission'] != 1 && $sess['permission'] != 2)) {
			return $this->view->respondError(401);
		} 
		$dm = new DataModel($db);
		$src = $this->table($dm);
		$dataBlocks = $this->dataBlocks($dm, $srcId);
		$src->transaction();
		try {
			// get source data
			$src->where('id = ?', $srcId);
			$srcData = $src->getOne();
			if (!$srcData) {
				throw new Exception('source data (id: "' . $srcId . '") not found in cms_datablock_source');
			}
			// get main data 
			$mainTable = $dm->table($srcData['main_table']);
			$mainTable->where($srcData['main_column'] . ' = ?', $dataId);
			$mainData = $mainTable->getOne();
			if (!$mainData) {
				throw new Exception('main data (id: "' . $dataId . '") not found in ' . $srcData['main_table']);
			}
			// delete main data
			$mainTable->where($srcData['main_column'] . ' = ?', $dataId);
			$res = $mainTable->delete();
			if (!$res) {
				throw new Exception('failed to delete (id: "' . $srcId . '") from  ' . $srcData['main_table']);
			}
			// casecade delete(s)
			for ($i = 0, $len = count($dataBlocks); $i < $len; $i++) {
				$block = $dataBlocks[$i];
				if ($block['source_table'] !== $srcData['main_table']) {
					$table = $dm->table($block['source_table']);
					$table->where($block['source_ref_column'] . ' = ?', $mainData[$srcData['main_column']]);
					$res = $table->delete();
				}
			}
			// commit
			$src->commit();
		} catch (Exception $e) {
			$src->rollBack();
			Log::error($e);
			return $this->view->respondError(404);
		}
		$this->view->respondJson();
	}

	private function table($dm) {
		return $dm->table('cms_datablock_source');
	}

	private function dataBlocks($dm, $srcId) {
		$d = $dm->table('cms_datablock');
		$d->where('source_id = ?', $srcId);
		$d->order('id');
		return $d->getMany();
	}

	private function dm($db) {
		return new DataModel($db);
	}
}
?>
