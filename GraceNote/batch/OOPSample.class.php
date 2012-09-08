<?php
class Test extends Base {
	
	public function printer(){
		exec('touch /var/www/admin.connectree/oopsample'.date('YmdHis'));
	}
}
?>
