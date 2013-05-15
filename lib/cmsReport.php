<?php

// log/report CMS user actions

// set up
Report::createType('AUTH.LOGIN');
Report::createType('FILE.UPLOAD');

// report on login
GlobalEvent::on('auth.authenticate', null, 'reportAuth');
function reportAuth($params) {
	Report::send('AUTH.LOGIN', $params, $params[0]['user']);
}

// report on file upload
GlobalEvent::on('staticfile.upload', null, 'reportFileUpload');
function reportFileUpload($params) {
	Report::send('FILE.UPLOAD', $params[1], $params[0]['user']);
}

?>
