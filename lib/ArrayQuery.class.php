<?php

class ArrayQuery {

	private $json = null;
	private $columnSchema = array();
	private $columnMap = array();
	private $sortOn = null;
	private $indexMap = array();

	// $jsonObj needs to be an sql table like structure
	public function ArrayQuery($jsonObj) {
		if (!isset($jsonObj[0])) {
			// for the jsonObj to be like a table structure
			$jsonObj = array($jsonObj);
		}
		$this->json = $jsonObj;
		$this->createColumnSchema();
	}

	public function describe() {
		return $this->columnSchema;
	}

	public function createIndex($column) {
		if (!isset($this->columnMap[$column])) {
			throw new Exception('ArrayQuery::createIndex > invalid column to be indexed (' . $column . ')');
		}
		if (isset($this->indexMap[$column])) {
			Log::warn('ArrayQuery::createIndex > already indexed (' . $column . ')');
			return;
		}
		$this->indexMap[$column] = array();
		foreach ($this->json as $index => $item) {
			if (isset($item[$column])) {
				$this->indexMap[$column][$item[$column]] = $index;
			}
		}
	}

	public function getOne($index = 0) {
		if (isset($this->json[$index])) {
			return $this->json[$index];
		}
		return null;
	}

	public function getMany($indexList) {
		$map = array();
		foreach ($indexList as $index) {
			$map[$index] = $this->getOne($index);
		}
		return $map;
	}

	// $value MUST be unique and indexed
	public function getOneByIndex($column, $value) {
		if (!isset($this->columnMap[$column])) {
			throw new Exception('ArrayQuery::getOneByIndex > invalid column given (' . $column . ')');
		}
		if (!isset($this->indexMap[$column])) {
			Log::warn('ArrayQuery::getOneByIndex > ' . $column . ' not indexed');
			return null;
		}
		if (isset($this->indexMap[$column][$value])) {
			$index = $this->indexMap[$column][$value];
			return $this->json[$index];
		}
		return null;
	}

	public function getManyByIndex($column, $values) {
		$map = array();
		foreach ($values as $val) {
			$res = $this->getOneByIndex($column, $val);
			$map[$val] = $res;
		}
		return $map;
	}

	public function searchOne($conditions = null, $index = 0) {
		$res = $this->queryData($conditions);
		if ($res && isset($res[$index])) {
			return $res[$index];
		}
		return null;
	}

	// $sortOn = 'columnName'
	public function searchMany($conditions = null, $sortOn = null) {
		$res = $this->queryData($conditions);
		if (isset($res[0]) && $sortOn) {
			$this->sortOn = $sortOn;
			usort($res, array($this, 'sortResults'));
			$this->sortOn = null;
		}
		return $res;
	}

	private function sortResults($a, $b) {
		$a = isset($a[$this->sortOn]) ? $a[$this->sortOn] : null;
		$b = isset($b[$this->sortOn]) ? $b[$this->sortOn] : null;
		if ($a === null || $b === null) {
			return false;
		}
		if (is_string($a) && is_string($b)) {
			return strcasecmp($a, $b);
		} else if (is_numeric($a) && is_numeric($b)) {
			return $a - $b;
		} else {
			$a = JSON::stringify($a);
			$b = JSON::stringify($b);
			return strcasecmp($a, $b);
		}
	}

	// this method relies on the first row of the json object to create a schema
	private function createColumnSchema() {
		$row = $this->json[0];
		foreach ($row as $column => $value) {
			$type = gettype($value);
			$this->columnSchema[] = array(
				'field' => $column,
				'type' => $type
			);
			$this->columnMap[$column] = $type;
		}
	}

	/**
	 * $conditions = array(
	 *	array(
	 *		'columnName',
	 *		'operator' // =, !=, >. <, >=, <=, like/LIKE,
	 *		'value'
	 *	)
	 * );
	 * */
	private function queryData($conditions) {
		$res = array();
		$conLen = count($conditions);
		foreach ($this->json as $item) {
			if ($conLen) {
				$trueNum = 0;
				foreach ($conditions as $condition) {
					if ($this->checkCondition($condition, $item)) {
						$trueNum += 1;
					}
					if ($trueNum === $conLen) {
						$res[] = $item;
					}
				}
			} else {
				$res[] = $item;
			}
		}
		return $res;
	}

	private function checkCondition($condition, $dataObj) {
		$col = $condition[0];
		$op = $condition[1];
		$val = $condition[2];
		if (isset($dataObj[$col])) {
			$data = $dataObj[$col];
		} else {
			// missing column in dataObj
			return false;
		}
		try {
			switch (strtolower($op)) {
				case '=':
					return $data === $val;
				case '!=':
					return $data !== $val;
				case '>':
					return $data > $val;
				case '<':
					return $data < $val;
				case '>=':
					return $val >= $val;
				case '<=':
					return $data <= $val;
				case 'like':
					if (!is_string($val)) {
						$val = JSON::stringify($val);
					}
					if (!is_string($data)) {
						$data = JSON::stringify($data);
					}
					return mb_ereg_match($val, $data);
				default:
					return false;
			}
		} catch (Exception $e) {
			// log error here
			Log::error('ArrayQuery::checkCondition > ' . $e->getMessage());
			return false;
		}
	}
}
