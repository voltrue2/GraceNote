<?php
/*
 * Smarty plugin
 * ---------------------
 * File:     function.z2h.php
 * Type:     function
 * Name:     z2h
 * Purpose:  converts zenkaku characters to hankaku characters 
 * ---------------------
 */
function smarty_function_z2h($args, &$smarty){
       if (isset($args["value"])){
       		$s = $args["value"];
		$encoding = mb_detect_encoding($s);
		return mb_convert_kana($s, "aks", $encoding);
       }
       else {
		return "Smarty::z2h > Invalid Parameter Name -> Correct Parameter Name is 'value'";
       }
}
?>