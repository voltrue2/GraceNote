<?php
/*
 * Smarty plugin
 * ---------------------
 * File:     tosjis
 * Type:     function
 * Name:     z2h
 * Purpose:  converts zenkaku characters to hankaku characters 
 * ---------------------
 */
function smarty_outputfilter_convert_encoding($args, &$smarty){
    return mb_convert_encoding($output, 'SJIS-WIN', 'UTF-8');
}
?>