<?php

/**
 * Utility methods.
 * TODO: this is becoming a God object.  Split it up.
 */
class Utils {
    /**
     * Use this to sanitize data that comes in via external params;
     * this is to avoid injection attacks.
     */
    public static function sanitizeUrlParam($in) {
        return trim(preg_replace('/[^[:alnum:]\.,@_-\s]/', '', $in));
    }

    /**
     * Even more restrictive sanitization: will only accept integer values
     * between $min and $max.
     *
     * - If $in is not numeric, returns null.
     * - If $in is greater than $max, returns $max
     * - If $in is less than $min, returns $min.
     *
     * @param mixed $in
     * @param integer $min
     * @param integer $max
     * @return mixed the sanitized input or null if it's invalid.
     */
    public static function sanitizeUrlParamIntRange($in, $min, $max) {
         if (!is_numeric($in)) return null;
         $numVal = intval($in);
         if ($numVal > $max) return $max;
         if ($numVal < $in) return $min;
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

    /*
     * Gets the base URL of the HTTP server where the script is being run.
     */
    public static function getBaseUrl() {
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

        return $pageURL;
    }

    /*
     * Gets the server-side base path of the server where the script is
     * being run.
     */
    public static function getBasePath() {
        // Only works in Apache.
        return realpath(Utils::sanitizeUrlOrPath($_SERVER['DOCUMENT_ROOT']));
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

    /**
     * Convenience method for getting the first match of an XPATH query
     * in an XML document.
     *
     * @param type $xpath    The XPATH instance.
     * @param type $domNode  The DOM node under which to execute the query.
     * @param type $query    The actual query string.
     * @return The first matched value, or null.
     */
    public static function getNodeValue($xpath, $domNode, $query) {
        $values = Utils::getNodeValues($xpath, $domNode, $query);
        if (null == $values || 0 == count($values)) return null;

        return $values[0];
    }

    /**
     * Convenience method for getting all matched values of an XPATH query
     * in an XML document.
     *
     * @param type $xpath    The XPATH instance.
     * @param type $domNode  The DOM node under which to execute the query.
     * @param type $query    The actual query string.
     * @return type          All matched values, in the order they occur in
     *                          the XML document.  Returns an empty array
     *                          if no values are found.
     */
    public static function getNodeValues($xpath, $domNode, $query) {
        $values = array();
        $nodes = Utils::getNodes($xpath, $domNode, $query);
        foreach($nodes as $node) {
            $values[] = $node->nodeValue;
        }

        return $values;
    }

    /**
     * Convenience method for getting all matched nodes an XPATH query
     * in an XML document.
     *
     * @param type $xpath    The XPATH instance.
     * @param type $domNode  The DOM node under which to execute the query.
     * @param type $query    The actual query string.
     * @return type          All matched nodes, in the order they occur in
     *                          the XML document.  Returns an empty array
     *                          if no values are found.
     */
    public static function getNodes($xpath, $domNode, $query) {
        $nodes = array();
        $nodeList = $xpath->query($query, $domNode);
        if (!is_null($nodeList)) {
            for ($i=0; $i<$nodeList->length; ++$i) {
                $nodes[] = $nodeList->item($i);
            }
        }

        return $nodes;
    }

    /**
     * Replaces occurrences of multiple consecutive whitespace characters with
     * single spaces.  Also replaces newlines and tabs with single spaces and
     * trims leading and trailing whitespace.
     *
     * @param string $in The text to be condensed.
     * @return string A condensed copy of $in.
     */
    public static function condenseWhiteSpace($in) {
        // Could use /\s+/, but that's too greedy. It will replace all the
        // single spaces as well, which is pointless and expensive.
        return trim(preg_replace('/\s{2,}\t|\n/', " ", $in));
    }
}
?>