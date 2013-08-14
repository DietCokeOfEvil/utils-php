<?php

/**
 * Utility methods.
 */
class Utils {
    /**
     * Use this to sanitize data that somes in via $_GET or $_POST params;
     * this is to avoid injection attacks.
     */
    public static function sanitizeUrlParam($in) {
        return trim(preg_replace('/[^[:alnum:]\.,@_-\s]/', '', $in));
    }
    
    /**
     * Even more restrictive sanitization: will only accept integer values
     * between $min and $max.
     * @param mixed $in
     * @param integer $min
     * @param integer $max
     * @return mixed the sanitized input or null if it's invalid.
     */
    public static function sanitizeUrlParamIntRange($in, $min, $max) {
         if (!is_numeric($in)) return null;
         $numVal = intval($in);
         if ($numVal > $max) return null;
         if ($numVal < $in) return null;
         return $numVal;
    }
    
    /**
     * Sanitizes things that are intended to be used as paths or URLs.
     * @param type $in
     * @return type
     */
    public static function sanitizeUrlOrPath($in) {
        return trim(preg_replace('/[^[:alnum:]\.,@_-\s\/\:\?&]/', '', $in));
    }
    
    public static function getBaseUrl() {
        $topLevelPath = str_replace(
            Utils::sanitizeUrlOrPath($_SERVER['DOCUMENT_ROOT']), 
            "", 
            Utils::getBasePath());
        
        $httpsOn = (   isset($_SERVER["HTTPS"]) 
                    && 0 == stricmp($_SERVER["HTTPS"], "on"));
        
        $pageURL = $httpsOn ? 'https://' : 'http://';
        $pageURL .= Utils::sanitizeUrlOrPath($_SERVER["SERVER_NAME"]);
        
        $port = Utils::sanitizeUrlParamIntRange(
            $_SERVER["SERVER_PORT"], 
            0, 
            65535);
        if (   (!$httpsOn && $port != "80")
            || ($httpsOn && $port != "443")) {
            
            $pageURL .= ":$port";
        }
        
        return Utils::concatenatePaths($pageURL, $topLevelPath);
    }
    
    public static function getBasePath() {
        return realpath(Utils::concatenatePaths(__DIR__, '..'));
    }
    
    /**
     * Concatenates a set of paths, ensuring that double separators are not
     * retained.
     *
     * This is a variable-arity function.  Pass in as many tokens as you like;
     * they will be concatenated in the order passed. 
     */
    public static function concatenatePaths() {
        $args = func_get_args();
        $path = "";
        foreach ($args as $arg) {
            $path .= $arg;
            $path .= DIRECTORY_SEPARATOR;
        }
        
        if (0 == strcmp("/", DIRECTORY_SEPARATOR)) {
            $path = str_replace("http://", "http:////", $path);
            $path = str_replace("https://", "https:////", $path);
        }
        
        $processed = str_replace(
            DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, 
            DIRECTORY_SEPARATOR, 
            $path);
        
        $endSlashRemoved = strrchr($path, DIRECTORY_SEPARATOR);
        if (false == $endSlashRemoved) {
            return $processed;
        }
        
        $len = strlen($processed);
        return ($len <= 0 ? $processed : substr($processed, 0, $len - 1));
    }
}
?>