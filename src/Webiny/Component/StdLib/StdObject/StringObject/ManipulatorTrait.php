<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 * @package   WebinyFramework
 */

namespace Webiny\Component\StdLib\StdObject\StringObject;

use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
use Webiny\Component\StdLib\StdObject\StdObjectManipulatorTrait;

/**
 * StringObject manipulator trait.
 *
 * @package         Webiny\Component\StdLib\StdObject\StringObject
 */
trait ManipulatorTrait
{
    use StdObjectManipulatorTrait;

    /**
     * Appends the given string to the current string.
     *
     * @param string $str String you wish to append
     *
     * @return $this
     */
    public function append($str)
    {
        $value = $this->val();
        $this->val($value . $str);

        return $this;
    }

    /**
     * Prepend the given string to the current string.
     *
     * @param string $str String you wish to append
     *
     * @return $this
     */
    public function prepend($str)
    {
        $value = $this->val();
        $this->val($str . $value);

        return $this;
    }

    /**
     * Strip whitespace (or other characters) from the beginning and end of a string.
     *
     * @param string|null $char Char you want to trim.
     *
     * @throws StringObjectException
     * @return $this
     */
    public function trim($char = null)
    {
        if ($this->isNull($char)) {
            $value = trim($this->val());
        } else {
            if (!$this->isString($char)) {
                throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                        '$char',
                        'string'
                    ]
                );
            }

            $value = trim($this->val(), $char);
        }

        $this->val($value);

        return $this;
    }

    /**
     * Make a string lowercase.
     *
     * @return $this
     */
    public function caseLower()
    {
        $this->val(mb_strtolower($this->val(), self::DEF_ENCODING));

        return $this;
    }

    /**
     * Make a string uppercase.
     *
     * @return $this
     */
    public function caseUpper()
    {
        $this->val(mb_strtoupper($this->val(), self::DEF_ENCODING));

        return $this;
    }

    /**
     * Make the first string character upper.
     *
     * @return $this
     */
    public function caseFirstUpper()
    {
        $string = clone $this;
        $string->subString(0, 1)->caseUpper()->val();
        $this->subString(1, $this->length() - 1)->caseLower();

        $this->val($string . $this->val());

        return $this;
    }

    /**
     * Make the first character of every word upper.
     *
     * @return $this
     */
    public function caseWordUpper()
    {
        $this->val(mb_convert_case($this->val(), MB_CASE_TITLE, self::DEF_ENCODING));

        return $this;
    }

    /**
     * Make a string's first character lowercase.
     *
     * @return $this
     */
    public function caseFirstLower()
    {
        $this->val(lcfirst($this->val()));

        return $this;
    }

    /**
     * Inserts HTML line breaks before all newlines in a string.
     *
     * @return $this
     */
    public function nl2br()
    {
        $this->val(nl2br($this->val()));

        return $this;
    }

    /**
     * Replace HTML line break with newline character.
     *
     * @return $this
     */
    public function br2nl()
    {
        $search = array(
            '<br>',
            '<br/>',
            '<br />'
        );
        $replace = "\n";

        $this->replace($search, $replace);

        return $this;
    }

    /**
     * Strips trailing slash from the current string.
     * If there are two or more slashes at the end of the string, all of them will be stripped.
     *
     * @return $this
     */
    public function stripTrailingSlash()
    {
        $this->val(rtrim($this->val(), '/'));

        return $this;
    }

    /**
     * Strips a slash from the start of the string.
     * If there are two or more slashes at the beginning of the string, all of them will be stripped.
     *
     * @return $this
     */
    public function stripStartingSlash()
    {
        $this->val(ltrim($this->val(), '/'));

        return $this;
    }

    /**
     * Strip the $char from the start of the string.
     *
     * @param string $char Char you want to trim.
     *
     * @throws StringObjectException
     * @return $this
     */
    public function trimLeft($char)
    {
        if (!$this->isString($char)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$char',
                    'string'
                ]
            );
        }
        $this->val(ltrim($this->val(), $char));

        return $this;
    }

    /**
     * Strip the $char from the end of the string.
     *
     * @param string $char Char you want to trim.
     *
     * @throws StringObjectException
     * @return $this
     */
    public function trimRight($char)
    {
        if (!$this->isString($char)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$char',
                    'string'
                ]
            );
        }

        $this->val(rtrim($this->val(), $char));

        return $this;
    }

    /**
     * Returns a substring from the current string.
     *
     * @param int $startPosition From which char position will the substring start.
     * @param int $length        Where will the substring end. If 0 - to the end of string.
     *
     * @throws StringObjectException
     * @return $this
     */
    public function subString($startPosition, $length)
    {
        if (!$this->isNumber($startPosition)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$startPosition',
                    'integer'
                ]
            );
        }

        if (!$this->isNumber($length)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$length',
                    'integer'
                ]
            );
        }

        if ($length == 0) {
            $length = null;
        }
        $value = mb_substr($this->val(), $startPosition, $length, self::DEF_ENCODING);
        $this->val($value);

        return $this;
    }

    /**
     * Replaces the occurrences of $search inside the current string with $replace.
     * This function is CASE-INSENSITIVE.
     *
     * @param string|array $search  String, or array of strings, that will replaced.
     * @param string|array $replace String, or array of strings, with whom $search occurrences will be replaced.
     *
     * @throws StringObjectException
     * @return $this
     */
    public function replace($search, $replace)
    {
        try {
            $value = str_ireplace($search, $replace, $this->val(), $count);
            $this->val($value);
        } catch (\ErrorException $e) {
            throw new StringObjectException($e->getMessage());
        }

        return $this;
    }

    /**
     * Explode the current string with the given delimiter and return ArrayObject with the exploded values.
     *
     * @param string   $delimiter String upon which the current string will be exploded.
     * @param null|int $limit     Limit the number of exploded items.
     *
     * @return ArrayObject ArrayObject object containing exploded values.
     * @throws StringObjectException
     */
    public function explode($delimiter, $limit = null)
    {
        if (!$this->isString($delimiter)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$delimiter',
                    'string'
                ]
            );
        }

        if ($this->isNull($limit)) {
            $arr = explode($delimiter, $this->val());
        } else {
            if (!$this->isNumber($limit)) {
                throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                        '$limit',
                        'integer'
                    ]
                );
            }

            $arr = explode($delimiter, $this->val(), $limit);
        }

        if (!$arr) {
            throw new StringObjectException(StringObjectException::MSG_UNABLE_TO_EXPLODE, [$delimiter]);
        }

        return new ArrayObject($arr);
    }

    /**
     * Split the string into chunks.
     *
     * @param int $chunkSize Size of each chunk. Set it to 1 if you want to get all the characters from the string.
     *
     * @throws StringObjectException
     * @return ArrayObject ArrayObject containing string chunks.
     */
    public function split($chunkSize = 1)
    {
        if (!$this->isNumber($chunkSize)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$chunkSize',
                    'integer'
                ]
            );
        }

        $arr = str_split($this->val(), $chunkSize);

        return new ArrayObject($arr);
    }

    /**
     * Generate a hash value from the current string using the defined algorithm.
     *
     * @param string $algo Name of the algorithm used for calculation (md5, sha1, ripemd160,...).
     *
     * @throws StringObjectException
     * @return $this
     */
    public function hash($algo = 'sha1')
    {
        $algos = new ArrayObject(hash_algos());
        if (!$algos->inArray($algo)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_HASH_ALGO, [$algo]);
        }

        $this->val(hash($algo, $this->val()));

        return $this;
    }

    /**
     * Decode html entities in the current string.
     *
     * @return $this
     */
    public function htmlEntityDecode()
    {
        $this->val(html_entity_decode($this->val()));

        return $this;
    }

    /**
     * Convert all HTML entities to their applicable characters.
     * For more info visit: http://www.php.net/manual/en/function.htmlentities.php
     *
     * @param string|null $flags    Default flags are set to ENT_COMPAT | ENT_HTML401
     * @param string      $encoding Which encoding will be used in the conversion. Default is UTF-8.
     *
     * @throws StringObjectException
     * @return $this
     */
    public function htmlEntityEncode($flags = null, $encoding = 'UTF-8')
    {
        try {
            if ($this->isNull($flags)) {
                $this->val(htmlentities($this->val(), ENT_COMPAT | ENT_HTML401, $encoding));
            } else {
                $this->val(htmlentities($this->val(), $flags, $encoding));
            }
        } catch (\ErrorException $e) {
            throw new StringObjectException($e->getMessage());
        }


        return $this;
    }

    /**
     * Quote string slashes.
     * To remove slashes use StringObject::stripSlashes().
     *
     * @return $this
     */
    public function addSlashes()
    {
        $this->val(addslashes($this->val()));

        return $this;
    }

    /**
     * Un-quote string quoted with StringObject::addSlashes()
     *
     * @return $this
     */
    public function stripSlashes()
    {
        $this->val(stripslashes($this->val()));

        return $this;
    }

    /**
     * Split the string into chunks with each chunk ending with $endChar.
     * This function is multi-byte safe.
     *
     * @param int    $chunkSize Size of each chunk.
     * @param string $endChar   String that will be appended to the end of each chunk.
     *
     * @throws StringObjectException
     * @return $this
     */
    public function chunkSplit($chunkSize = 76, $endChar = "\n")
    {
        if (!$this->isNumber($chunkSize)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$chunkSize',
                    'integer'
                ]
            );
        }

        if (!$this->isString($endChar)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$endChar',
                    'string'
                ]
            );
        }

        $tmp = array_chunk(preg_split("//u", $this->val(), -1, PREG_SPLIT_NO_EMPTY), $chunkSize);
        $str = "";
        foreach ($tmp as $t) {
            $str .= join("", $t) . $endChar;
        }

        $this->val($str);

        return $this;
    }

    /**
     * Hash current string using md5 algorithm.
     *
     * @return $this
     */
    public function md5()
    {
        $this->hash('md5');

        return $this;
    }

    /**
     * Calculates the crc32 polynomial of a string.
     *
     * @return $this
     */
    public function crc32()
    {
        $this->val(crc32($this->val()));

        return $this;
    }

    /**
     * Calculate the sha1 hash of a string.
     *
     * @return $this
     */
    public function sha1()
    {
        $this->hash('sha1');

        return $this;
    }

    /**
     * Parse current string as a query string and return ArrayObject with results.
     *
     * @return ArrayObject ArrayObject from the parsed string.
     */
    public function parseString()
    {
        parse_str($this->val(), $arr);

        return new ArrayObject($arr);
    }

    /**
     * Quote meta characters.
     * Meta characters are: . \ + * ? [ ^ ] ( $ )
     *
     * @return $this
     */
    public function quoteMeta()
    {
        $this->val(quotemeta($this->val()));

        return $this;
    }

    /**
     * Format the string according to the provided $format.
     *
     * @param string|array $args   Arguments used for string formatting.
     *                             For more info visit http://www.php.net/manual/en/function.sprintf.php
     *
     * @return $this
     */
    public function format($args)
    {
        if ($this->isArray($args)) {
            $value = vsprintf($this->val(), $args);
        } else {
            if ($this->isInstanceOf($args, new ArrayObject([]))) {
                $value = vsprintf($this->val(), $args->val());
            } else {
                $value = sprintf($this->val(), $args);
            }
        }
        $this->val($value);

        return $this;
    }

    /**
     * Pad the string to a certain length with another string.
     *
     * @param int    $length    Length to which to pad.
     * @param string $padString String that will be appended.
     *
     * @throws StringObjectException
     * @return $this
     */
    public function padLeft($length, $padString)
    {
        if (!$this->isNumber($length)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$length',
                    'integer'
                ]
            );
        }

        if (!$this->isString($padString)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$padString',
                    'string'
                ]
            );
        }

        $this->val(str_pad($this->val(), $length, $padString, STR_PAD_LEFT));

        return $this;
    }

    /**
     * Pad the string to a certain length with another string.
     *
     * @param int    $length    Length to which to pad.
     * @param string $padString String that will be appended.
     *
     * @throws StringObjectException
     * @return $this
     */
    public function padRight($length, $padString)
    {
        if (!$this->isNumber($length)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$length',
                    'integer'
                ]
            );
        }

        if (!$this->isString($padString)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$padString',
                    'string'
                ]
            );
        }

        $this->val(str_pad($this->val(), $length, $padString, STR_PAD_RIGHT));

        return $this;
    }

    /**
     * Pad the string to a certain length with another string.
     *
     * @param int    $length    Length to which to pad.
     * @param string $padString String that will be appended.
     *
     * @throws StringObjectException
     * @return $this
     */
    public function padBoth($length, $padString)
    {
        if (!$this->isNumber($length)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$length',
                    'integer'
                ]
            );
        }

        if (!$this->isString($padString)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$padString',
                    'string'
                ]
            );
        }

        $this->val(str_pad($this->val(), $length, $padString, STR_PAD_BOTH));

        return $this;
    }

    /**
     * Repeats the current string $multiplier times.
     *
     * @param int $multiplier How many times to repeat the string.
     *
     * @return $this
     * @throws StringObjectException
     */
    public function repeat($multiplier)
    {
        if (!$this->isNumber($multiplier)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$multiplier',
                    'integer'
                ]
            );
        }
        $this->val(str_repeat($this->val(), $multiplier));

        return $this;
    }

    /**
     * Shuffle characters in current string.
     *
     * @return $this
     */
    public function shuffle()
    {
        $this->val(str_shuffle($this->val()));

        return $this;
    }

    /**
     * Remove HTML tags from the string.
     *
     * @param string $whiteList A list of allowed HTML tags that you don't want to strip. Example: '<p><a>'
     *
     * @throws StringObjectException
     * @return $this
     */
    public function stripTags($whiteList = '')
    {
        if (!$this->isString($whiteList)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$whiteList',
                    'integer'
                ]
            );
        }

        $this->val(strip_tags($this->val(), $whiteList));

        return $this;
    }

    /**
     * Reverse the string.
     *
     * @return $this
     */
    public function reverse()
    {
        $this->val(strrev($this->val()));

        return $this;
    }

    /**
     * Wraps a string to a given number of characters using a string break character.
     *
     * @param int    $length The number of characters at which the string will be wrapped.
     * @param string $break  The line is broken using the optional break parameter.
     * @param bool   $cut    If the cut is set to TRUE, the string is always wrapped at or before the specified width.
     *                       So if you have a word that is larger than the given width, it is broken apart.
     *
     * @throws StringObjectException
     * @return $this
     */
    public function wordWrap($length, $break = "\n", $cut = false)
    {
        if (!$this->isNumber($length)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$length',
                    'integer'
                ]
            );
        }

        if (!$this->isString($break)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$break',
                    'string'
                ]
            );
        }

        if (!$this->isBool($cut)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$cut',
                    'boolean'
                ]
            );
        }
        $this->val(wordwrap($this->val(), $length, $break, $cut));

        return $this;
    }

    /**
     * Truncate the string to the given length without breaking words.
     *
     * @param int    $length   Length to which you which to trim.
     * @param string $ellipsis Ellipsis is calculated in the string $length.
     *
     * @throws StringObjectException
     * @return $this
     */
    public function truncate($length, $ellipsis = '')
    {
        if (!$this->isNumber($length)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$length',
                    'integer'
                ]
            );
        }

        if (!$this->isString($ellipsis)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$ellipsis',
                    'string'
                ]
            );
        }

        if ($this->length() <= $length) {
            return $this;
        }

        if ($ellipsis != '') {
            $length = $length - strlen($ellipsis);
        }

        $this->wordWrap($length)->subString(0, $this->stringPosition("\n"));

        $this->val($this->val() . $ellipsis);

        return $this;
    }

    /**
     * Preg matches current string against the given regular expression.
     * If you just wish if string is contained within the current string, use StringObject::contains().
     *
     * @param string $regEx    Regular expression to match.
     * @param bool   $matchAll Use preg_match_all, or just preg_match. Default is preg_match_all.
     *
     * @throws StringObjectException
     * @return ArrayObject|bool    If there are matches, an ArrayObject with the the $matches is returned, else, false is returned.
     */
    public function match($regEx, $matchAll = true)
    {
        // validate regex delimiters
        $delimiter = substr($regEx, 0, 1);
        $validDelimiters = [
            '/',
            '+',
            '#',
            '%',
            '|',
            '{'
        ];
        // if we cannot match a delimiter, try to add them

        if (!in_array($delimiter, $validDelimiters)) {
            $regEx = '/' . str_replace([
                                           '\/',
                                           '/'
                                       ], '\/', $regEx
                ) . '/';
        }

        if ($matchAll) {
            preg_match_all($regEx, $this->val(), $matches);

            if (count($matches[0]) > 0) {
                return new ArrayObject($matches);
            }
        } else {
            preg_match($regEx, $this->val(), $matches);

            if (count($matches) > 0) {
                return new ArrayObject($matches);
            }
        }

        return false;
    }

    /**
     * Url encodes the current string.
     *
     * @return $this
     */
    function urlEncode()
    {
        $this->val(urlencode($this->val()));

        return $this;
    }

    /**
     * Url decodes the current string.
     *
     * @return $this
     */
    function urlDecode()
    {
        $this->val(urldecode($this->val()));

        return $this;
    }

    /**
     * Encode string using base64_encode
     * @return $this
     */
    public function base64Encode()
    {
        $this->val(base64_encode($this->val()));

        return $this;
    }

    /**
     * Decode base64 encoded string
     * @return $this
     */
    public function base64Decode()
    {
        $this->val(base64_decode($this->val()));

        return $this;
    }

    public function cryptEncode($key, $salt = '')
    {
        /**
         * @TODO: implement me
         */
    }

    public function cryptDecode($key, $salt = '')
    {
        /**
         * @TODO: implement me
         */
    }

    public function baseConvert()
    {
        // @TODO
    }
}