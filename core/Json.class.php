<?php

class JSON {

    public static function stringify($array, $option = null) {
        try {
            $res = json_encode($array, $option);
            $error = json_last_error();
            if ($error) {
                throw new Exception($error);
            }
            return $res;
        } catch  (Exception $e) {
            Log::error('tried to stringify:', $array);
            self::handleError($e->getMessage());
        }
        return null;
    }

    public static function parse($string, $asArray = true) {
        try {
            $res = json_decode($string, $asArray);
            $error = json_last_error();
            if ($error) {
                throw new Exception($error);
            }
            return $res;
        } catch (Exception $e) {
            Log::error('tried to parse: ', $string);
            self::handleError($e->getMessage());
        }
        return null;
    }   

    private static function handleError($errorCode) {
        switch ($errorCode) {
        case JSON_ERROR_DEPTH:
            Log::error(' - Maximum stack depth exceeded');
            break;
        case JSON_ERROR_STATE_MISMATCH:
            Log::error(' - Underflow or the modes mismatch');
            break;
        case JSON_ERROR_CTRL_CHAR:
            Log::error(' - Unexpected control character found');
            break;
        case JSON_ERROR_SYNTAX:
            Log::error(' - Syntax error, malformed JSON');
            break;
        case JSON_ERROR_UTF8:
            Log::error(' - Malformed UTF-8 characters, possibly incorrectly encoded');
            break;
        default:
            Log::error(' - Unknown error');
            break;
        }
    } 
}
