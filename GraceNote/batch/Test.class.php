<?php
Load::model('SelectModel');
$m = new SelectModel();
$users = $m->table('users');
//$users->debug(false);
$users->inflate($users, 'gender', 'user_genders', 'gender_id', array('lang_id' => array('=', 1)));
$users->inflate('user_genders', 'lang_id', 'languages', 'lang_id');
$users->inflate($users, 'occupation_id', 'occupations', 'occupation_id', array('lang_id' => array('=', 1)));
$users->inflate('occupations', 'lang_id', 'languages', 'lang_id');
$users->order('id', 'ASC');
$user_list = $users->find(13);
//$users->cond('id IN '.$users->escape(18, 19, 17, 16));
//$user_list = $users->find();

error_log(print_r($user_list, true));

?>
