<title><?= show($HEADER, 'title', 'GraceNote Framework CMS'); ?></title>
<link rel="stylesheet" href="/css/pc/cms.css?<?= filemtime($CSS_PATH.'pc/cms.css'); ?>" type="text/css" media="all"></link>
<? if($AUTO_CSS): ?>
<link rel="stylesheet" href="/css/<?= $AUTO_CSS; ?>" media="all"></link>
<? endif; ?>
<script type="text/javascript" src="/js/GraceNote.js?<?= filemtime($JS_PATH.'GraceNote.js'); ?>"></script>
<script type="text/javascript">
function confirmation(msg, path) {
	var answer = confirm(msg);
	if (answer) {
		window.location.href = path;
	};
};

function number(input){
	if (input.value){
		if (input.prev){
			var new_val = str_replace(input.prev, '', input.value);
		}
		else {
			input.prev = '';
			var new_val = input.value;
		};
		if (isNaN(new_val) || new_val == ' '){
			// only number is allowed
			input.value = input.prev;
		};
		input.prev = input.value;
	}
	else {
		input.prev = '';
	};	
};

function time(input){
	if (input.value){
		if (input.prev){
			var new_val = str_replace(input.prev, '', input.value);
		}
		else {
			input.prev = '';
			var new_val = input.value;
		};
		allowed = {0:true, 1:true, 2:true, 3:true, 4:true, 5:true, 6:true, 7:true, 8:true, 9:true, ':':true, ' ':true, '-':true};
		if (!allowed[new_val]){
			input.value = input.prev;
		};
		input.prev = input.value;
	}
	else {
		input.prev = '';
	};	
};

function form_auto_focus(){
	$('input').ready(auto_focus);
	
	function auto_focus(obj){
		if (!window.focus_lock && obj.type == 'text') {	
			obj.focus();
			window.focus_lock = true;
		};
	};
};
</script>
