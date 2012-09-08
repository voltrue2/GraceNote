<?php
/*
 * Smarty plugin
 * ---------------------
 * File:     convert_encoding
 * Type:     function
 * Name:     convert_encoding
 * Purpose:  convert output strings to SJIS-WIN
 * ---------------------
 */
function smarty_outputfilter_convert_encoding($args, &$smarty){
    return mb_convert_encoding($args, 'SJIS-WIN', 'UTF-8');
}
?>