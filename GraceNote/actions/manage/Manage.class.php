<?php 
Load::model('ManageModel');
Load::action('header/Header');
Load::lib('Validate');
Load::lib('custom/LoginManager');

class Manage extends Base{
	
	private $validate;
	private $loginmanager;
	private $session;
	private $model;
	private $c;
	private $q;
	private $item_num = 30;
	
	public function Manage($view){
		$this->c = $view;
		$this->validate = new Validate();
		$this->loginmanager = new LoginManager($this->c);
		$this->q = $this->c->get('QUERIES');
		$header = new Header($this->c);
		$this->session = $this->loginmanager->get();
		if (!$this->session){
			// not logged in -> redirect
			$this->c->redirect('http://'.HOST.'/login/', 302);
		}	
	}

	public function csv_upload(){
		$table = $this->c->queries('table');
		$model = new ManageModel($table);
		// check permission
		if (!$model->check_permission($this->session['permission'])){
			// permission denied
			$this->c->redirect('/');
		}
		$data = $this->c->queries('csv');
		if (isset($data['tmp_name'])){
			// parse the csv into a numeric array
			$csv = $this->csv_to_array($data['tmp_name']);
			// rearrange the array to the model structure
			$fields = $model->describe();
			$src = $this->reconstruct_array($fields, $csv);
			$meta = $model->read_table_meta();
			if ($src){
				foreach ($src as $data_block){
					$this->insert_data($model, $meta, $data_block);
				}
			}
		}
		$this->c->redirect('/manage/table/'.$table.'/#record_list');
	}
	
	private function csv_to_array($input, $delimiter = ',') { 
		$csv = false;
		if (($handle = fopen($input, 'r')) !== false) {
		    while (($data = fgetcsv($handle, 10000, $delimiter)) !== false) {
		        $num = count($data);
		        for ($i = 0; $i < $num; $i++) {
		            $csv[] = $data[$i];
		        }
		    }
		    fclose($handle);
		}
		return $csv;
	}
	
	public function csv_export($table) {
		$this->model = new ManageModel($table);
		// check permission
		if (!$this->model->check_permission($this->session['permission'])){
			// permission denied
			$this->c->redirect('/');
		}
		$fields = $this->model->describe();
		$csv = $this->array_to_csv($fields);
		$this->c->push('csv', $csv, $table.'.csv');
	}
	
	private function array_to_csv($fields, $delimiter = ',') {
		if (!empty($fields)){
			$data = array();
			$buffer = fopen('php://temp', 'r+');
			$len = count($fields);
			$columns = array();
			foreach ($fields as $item){
				$f = $item['field'];
				// we ignore id and modtime
				if ($f !== 'id' && $f !== 'modtime'){
					$columns[] = $f;
					$data[] = $f;
				}
			}
			$list = $this->model->get_list();
			if (!empty($list)){
				foreach ($list as $i => $item){
					foreach ($columns as $c){
						$data[] = $item[$c];
					}
				}
			}
		}
		$len = count($data);
		fputcsv($buffer, $data, $delimiter);
		rewind($buffer);
		$csv = fgets($buffer);
		fclose($buffer);
		return $csv;
	} 
	
	private function reconstruct_array($fields, $src){
		// extract fields and values
		$n = count($fields) - 2; // we are ignoring id and modtime
		$c = 0;
		$flist = false;
		$vlist = false;
		$res = false;
		if ($src && $fields){
			foreach ($src as $data){
				if ($n > $c){
					// field
					foreach ($fields as $i => $item){
						if ($item['field'] == trim($data)){
							$flist[] = $item['field'];
							$c++;	
							break;
						}
					}
				}
				else {
					// value
					$vlist[] = $data;
				}
			}
			// reconstruct the array into an associative array to match the table structure
			$n = count($flist);
			$c = 0;
			$index = 0;
			if ($flist && $vlist){
				foreach ($vlist as $value){
					$res[$index][$flist[$c]] = $value;
					$c++;
					if ($c == $n){
						$c = 0;
						$index++;
					}
				}
			}
		}
		return $res;
	}
	
	private function insert_data($model, $meta, $data_block){
		$errors = false;
		$params = array();
		foreach ($data_block as $column => $value){
			$good = $this->check_fields($meta, $column, $data_block);
			if (!$good){
				$errors[$column] = $value;
			}
			else {
				$key = $column;
				if ($value != ''){
					if (isset($meta[$key]) && isset($meta[$key]['attribute']) && $meta[$key]['attribute'] == 'password'){
						$value = md5(str_replace(' ', '', $value));
					}
					else if (isset($meta[$key]) && isset($meta[$key]['attribute']) && $meta[$key]['attribute'] != 'html'){
						$value = mb_ereg_replace('<(.|\n)*?>', '', $value);
					}
					$params[$key] = $value;
				}
			}
		}
		if ($errors){
			return false;
		}
		// create new
		$res = $model->insert($params);
		$result = '';
		return true;
	}
	
	public function bulk_delete($table, $ids){
		$this->model = new ManageModel($table);
		// check permission
		if (!$this->model->check_permission($this->session['permission'])){
			// permission denied
			$this->c->redirect('/');
		}
		$sep = explode('-', $ids);
		if (!empty($sep)){
			foreach ($sep as $id){
				if ($id){
					$res = $this->model->delete($id);
				}
			}
		}
		$this->c->redirect('/manage/table/'.$table.'/#record_list');
	}

	// user with permission
	public function table($table_name, $item_id = false){
		if (isset($table_name)){
			// edit tables
			$this->c->contents('manage_edit');
			$table = $table_name;
			$this->c->assign('TABLE', $table);
			$this->model = new ManageModel($table);
			// check permission
			if (!$this->model->check_permission($this->session['permission'])){
				// permission denied
				$this->c->redirect('/');
			}
			$fields = $this->model->describe();	
			// display
			if ($item_id){
				// eidt check
				$this->check_edit($fields, $item_id);
				// edit
				$data = $this->model->get($item_id);
				if (!$data && !$this->c->queries('delete')){
					$this->c->return_error(404);	
				}
				$this->c->assign('DATA', $data);
			}
			else {
				// eidt check -> create new 
				$this->check_edit($fields, false);
			}
			// get page position
			$from = $this->c->queries('from');
			if (!$from){
				$from = 0;
			}
			$this->c->assign('FROM', $from);
			$this->c->assign('ITEM_NUM', $this->item_num);
			// create page 
			$page = ($from + $this->item_num) / $this->item_num;
			$this->c->assign('PAGE', $page);
			// fields
			$fields = $this->model->describe();	
			$this->c->assign('FIELDS', $fields);
			$data_types = $this->model->data_types();
			$this->c->assign('DATA_TYPES', $data_types);
			$list = $this->model->get_list($this->c->queries('column'), $this->c->queries('search'), $from, $this->item_num);
			$total = $this->model->get_list_total($this->c->queries('column'), $this->c->queries('search'));
			if (!$list[0] && $from > 0){
				// try to get it from page 1
				if ($item_id){
					$extra = $item_id.'/';
				}
				else {
					$extra = '';
				}
				$this->c->redirect('/manage/table/'.$table.'/'.$extra.'#record_list');
			}
			// check for exec sql
			$res = $this->exec_sql();
			if ($res['results']){
				$list = $res['results'];
				$query = $res['query'];
			}
			else {
				$query = '';
			}
			$data_types = $this->model->data_types();
			$this->c->assign('COLUMN', $this->c->queries('column'));
			$this->c->assign('SEARCH', $this->c->queries('search'));
			$this->c->assign('DATA_TYPES', $data_types);
			$this->c->assign('TOTAL', $total);
			$this->c->assign('TABLE', $table);
			$this->c->assign('LANGS', $this->model->get_langs());
			$this->c->assign('QUERY', $query);
			$this->c->assign('LIST', $list);
			$desc = $this->model->get_desc();
			$this->c->assign('DESC', $desc);
			$meta = $this->model->read_table_meta();
			$this->c->assign('META', $meta);
			$model = new SelectModel();
			$refs = false;
			if ($meta){
				foreach ($meta as $column => $m){
					foreach ($m as $v){
						if (is_array($v)){
							$table = $model->table($v['table']);
							$fields = $table->show();
							foreach ($fields as $item){
								if ($item['field'] == 'lang_id'){
									$table->cond('lang_id = '.$table->escape($this->c->lang()));
									break;
								}
							}
							$table->select($v['label'].' AS label');
							$table->select($v['column'].' AS column');
							$table->order($v['label'], 'ASC');
							$tmp = $table->find_all();
							$ret = array();
							$seen = array();
							foreach ($tmp as $frag){
								if (!isset($seen[$frag['column']])){
									$seen[$frag['column']] = true;
									$ret[] = $frag;
								}
							}
							$refs[$column] = $ret;
						}
					}
				}
			}
			$this->c->assign('COLUMN_REFS', $refs);
			$table = $model->table('table_desc');
			$table->join('permissions');
			$table->cond('permissions.permission = ?', $this->session['permission']);
			$table->cond('permissions.table_name = table_desc.table_name');
			$table->order('table_desc.table_name');
			$this->c->assign('TABLE_LIST', $table->find_all());
			$this->c->display('edit');
		}
		else {
			// no table to edit given
			$this->c->return_error(404);
		}
	}
	
	// root only
	public function create(){
		// create a table
		$this->c->contents('manage_create');
		if (isset($this->q['table_name']) && $this->q['table_name'] && $this->session['permission'] === 0){
			// ready to create table
			$encoding = mb_detect_encoding($this->q['table_name'], 'auto');
                        $tname = mb_convert_kana($this->q['table_name'], 'sn', $encoding);
                        $tname = str_replace(' ', '', $tname);
                        $tname = strtolower($tname);
			$this->model = new ManageModel($tname);
			$data_types = $this->model->data_types();
			$this->c->assign('DATA_TYPES', $data_types);
			$this->c->assign('TABLE_NAME', $tname);
			if (isset($this->q['create_table'])){
				// set up columns for table creation
				foreach ($this->q['column_name'] as $i => $item){
					if (trim($item)){
						if ($this->q['column_default'][$i]){
							$default = $this->q['column_default'][$i];
						}
						else {
							$default = false;
						}
						$encoding = mb_detect_encoding($item, 'auto');
						$item = mb_convert_kana($item, 'sn', $encoding);
						$item = str_replace(' ', '', $item);
						$item = strtolower($item);
						$this->model->set_column($item, $this->q['column_type'][$i], false, $default);
					}
				}
				$this->model->set_multi_lingual($this->q['multi_lang']);
				$this->model->set_table_category($this->q['table_category']);
				$this->model->create();
				// update session to give root access to the newly created table
				$session = $this->c->get_session(session_id());
				if (!isset($session['root_access'])){
					$session['root_access'] = array();
				}
				$session['root_access'][$tname] = true;
				$this->c->set_session(session_id(), $session);
				// redirect back to the page
				$this->c->redirect('/menu/');
			}
		}
		$this->c->display('create');
	}
	
	// rename column name * root ONLY
	public function rename_column(){
		$table = $this->c->queries('table');
		$this->model = new ManageModel($table);
		// check permission
		if ($this->session['permission'] !== 0){
			// permission denied
			$this->c->return_error(403);
		}
		$from = $this->c->queries('column_from');
		$to = $this->c->queries('column_to');
		$res = $this->model->rename_column($from, $to);
		$this->c->redirect('/manage/table/'.$table.'/#table_struc_area');
	}
	
	// change column data type * root ONLY
	public function change_data_type(){
		$table = $this->c->queries('table');
		$this->model = new ManageModel($table);
		// check permission
		if ($this->session['permission'] !== 0){
			// permission denied
			$this->c->return_error(403);
		}
		$column = $this->c->queries('column');
		$type = $this->c->queries('type');
		$res = $this->model->change_data_type($column, $type);
		$result = '';
		if (!$res){
			//$result = '?error=true';
		}
		$this->c->redirect('/manage/table/'.$table.'/#table_struc_area');
	}
	
	// edit column comment
	public function meta(){
		$table = $this->c->queries('table');
		if (!isset($this->session['root_access'][$table]) || !$this->session['root_access'][$table]){
			// permission denined
			$this->c->return_error(403);
		}
		else {
			$column = $this->c->queries('column');
			$desc = $this->c->queries('desc');
			$min = $this->c->queries('min');
			$max = $this->c->queries('max');
			$required = $this->c->queries('required');
			$attribute = $this->c->queries('attribute');
			$ref = $this->c->queries('reference');
			if (isset($ref['table']) && isset($ref['label']) && isset($ref['column'])){
				$attribute = $ref;
			}
			$meta['desc'] = $desc;
			$meta['min'] = $min;
			$meta['max'] = $max;
			$meta['required'] = $required;
			$meta['attribute'] = $attribute;
			$model = new ManageModel($table);
			$model->save_table_meta($column, $meta);
			$this->c->redirect('/manage/table/'.$table.'/#table_struc_area');
		}
	}

	/*
	// rename table
	public function rename(){
		$table = $this->c->queries('table');
		$this->model = new ManageModel($table);
		// check permission
		if (!$this->model->check_permission($this->session['permission'])){
			// permission denied -> redirect
			$this->c->redirect('http://'.HOST.'/menu/');
		}
		$name = $this->c->queries('name');
		$this->model->rename($table, $name);
		$this->c->redirect('/manage/table/'.$name.'/');
	}
	*/
	
	// root only
	public function drop(){
		if (isset($this->q['CODE']) && $this->session['permission'] === 0){
			$this->model = new ManageModel($this->q['CODE']);
			// check permission
			if (!$this->model->check_permission($this->session['permission'])){
				// permission denied
				$this->c->return_error(403);
			}
			$this->model->drop();
		}
		$this->c->redirect('/menu/');
	}
	
	public function ref_table_column($table, $ref_table_name){
		if (isset($this->session['root_access'][$table]) && $this->session['root_access'][$table]){
			$model = new SelectModel();
			$table = $model->table($ref_table_name);
			$data = $table->show();
			$this->c->push('json', $data);
		}
	}

	private function check_fields($meta, $key, $fields){
		$good = true;
		$value = $fields[$key];
		if (isset($meta[$key])){
			$m = $meta[$key];
			if (!empty($m)){
				foreach ($m as $name => $item){
					$encoding = mb_detect_encoding($value);
					if ($name != 'desc' && $name == 'required' && $item && !trim($value)){
						$good = false;
						break;
					}
					else if ($name != 'desc' && $name == 'max' && $item && mb_strlen($value, $encoding) > $item){
						$good = false;
						break;
					}
					else if ($name != 'desc' && $name == 'min' && $item  && mb_strlen($value, $encoding) < $item){
						$good = false;
						break;
					}
				}
			}
		}
		return $good;
	}
	
	private function check_edit($fields, $id = false){
		if (isset($this->q['edit'])){
			$errors = false;
			$params = array();
			$meta = $this->model->read_table_meta();
			foreach ($fields as $i => $item){
				if (isset($this->q[$item['field']])){
					$good = $this->check_fields($meta, $item['field'], $this->q);
					if (!$good){
						$errors[$item['field']] = $this->q[$item['field']];
					}
					else {
						$key = $item['field'];
						$value = $this->q[$item['field']];
						if ($value != ''){
							if (isset($meta[$key]) && isset($meta[$key]['attribute']) && $meta[$key]['attribute'] == 'password'){
								$value = md5(str_replace(' ', '', $value));
							}
							else if (!isset($meta[$key]) || !isset($meta[$key]['attribute']) || $meta[$key]['attribute'] != 'html'){
								$value = mb_ereg_replace('<(.|\n)*?>', '', $value);
							}
							$params[$key] = $value;
						}
					}
				}
			}
			if ($errors){
				//$this->c->assign('DATA', $errors);
				//$this->c->display('edit');
			}
			if ($id){
				// edit
				$res = $this->model->update($id, $params);
				$result = '';
				$this->c->redirect('/manage/table/'.$this->c->queries('CODE').'/'.$this->c->queries('EXTRA').'/'.$result.'#data_entry_area');
			}
			else {
				// create new
				$res = $this->model->insert($params);
				$result = '';
				$this->c->redirect('/manage/table/'.$this->c->queries('CODE').'/'.$result.'#record_list');
			}
			return true;
		}
		else if(isset($this->q['delete'])) {
			if ($id){
				$res = $this->model->delete($id);
				$result = '';
				$this->c->redirect('/manage/table/'.$this->c->queries('CODE').'/'.$result.'#record_list');
			}
			return true;
		}
		else if(isset($this->q['edit_desc'])) {	
			if (!isset($this->session['root_access'][$this->c->queries('CODE')]) || !$this->session['root_access'][$this->c->queries('CODE')]){
				$this->c->return_error(403);
			}		
			$res = $this->model->save_table_desc($this->q['description'], $this->q['category']);
			$result = '';
			if (!$res){
				//$result = '?error=true';
			}
			$this->c->redirect('/manage/table/'.$this->c->queries('CODE').'/'.$result.'#table_desc_area');
		}
		else if (isset($this->q['edit_struct']) && $this->session['permission'] === 0) {
			if ($this->q['default']){
				$default = $this->q['default'];
			}	
			else {
				$default = false;
			}
			$res = $this->model->alter_table($this->q['name'], $this->q['type'], $default, 'add');
			$result = '';
			if (!$res){
				//$result = '?error=true';
			}
			$this->c->redirect('/manage/table/'.$this->c->queries('CODE').'/'.$this->c->queries('EXTRA').$result.'#table_struc_area');
		}
		else if (isset($this->q['remove']) && isset($this->q['column']) && $this->session['permission'] === 0){
			$res = $this->model->alter_table($this->q['column'], false, false, 'remove');
			$result = '';
			if (!$res){
				//$result = '?error=true';
			}
			$this->c->redirect('/manage/table/'.$this->c->queries('CODE').'/'.$this->c->queries('EXTRA').$result.'#table_struc_area');
		}
		else {
			return false;
		}
	}
	
	private function exec_sql(){
		$exec_sql = $this->c->queries('exec_sql');
		$sql = $this->c->queries('sql');
		$res = false;
		if ($exec_sql && $sql){
			// execute SQL
			$table = $this->c->queries('CODE');
			$model = new ManageModel($table);
			$res = $model->exec_select($sql);
			if (!isset($res[0])){
				$tmp = $res;
				$res = array($tmp);
			}
		}
		return array('results' => $res, 'query' => $sql);
	}
}
?>
