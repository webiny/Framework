<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Compiler;

use Webiny\Component\Rest\Parser\ParsedApi;
use Webiny\Component\Rest\Parser\ParsedClass;
use Webiny\Component\Rest\Parser\PathTransformations;
use Webiny\Component\Rest\Rest;

/**
 * Compiler transforms ParsedClass instances into a special array.
 * This array is then written on the disk in the compile path.
 *
 * @package         Webiny\Component\Rest\Compiler
 */
class Compiler
{
    /**
     * @var string Name of the api configuration.
     */
    private $_api;

    /**
     * @var bool Should the class name and the method name be normalized.
     */
    private $_normalize;


    /**
     * Base constructor.
     *
     * @param string $api       Name of the api configuration.
     * @param bool   $normalize Should the class name and the method name be normalized.
     */
    public function __construct($api, $normalize)
    {
        $this->_api = $api;
        $this->_normalize = $normalize;
    }

    /**
     * Based on the given ParsedApi instance, the method will create several cache file and update the
     * cache index.
     *
     * @param ParsedApi $parsedApi
     */
    public function writeCacheFiles(ParsedApi $parsedApi)
    {
        $writtenCacheFiles = [];

        foreach ($parsedApi->versions as $v => $parsedClass) {
            $cacheFile = Cache::getCacheFilename($this->_api, $parsedApi->apiClass, $v);

            $this->_deleteExisting($cacheFile);

            $compileArray = $this->_compileCacheFile($parsedClass, $v);

            $this->_compileCacheTemplate($compileArray, $cacheFile, $v);

            $writtenCacheFiles[$v] = [
                'class'        => $parsedClass->class,
                'version'      => $v,
                'compileArray' => $compileArray,
                'cacheFile'    => $cacheFile
            ];
        }

        // write current and latest versions (just include return a specific version)
        $this->_compileCacheAliasTemplate($writtenCacheFiles[$parsedApi->latestVersion], 'latest');
        $this->_compileCacheAliasTemplate($writtenCacheFiles[$parsedApi->currentVersion], 'current');
    }

    /**
     * Creates the cache file based on the cache template file.
     *
     * @param array  $compileArray Array holding information about the specific class and version.
     * @param string $cacheFile    Cache file into which the content will be written.
     * @param string $version      Version name.
     */
    private function _compileCacheTemplate($compileArray, $cacheFile, $version)
    {
        // template
        $cacheFileTemplate = file_get_contents(__DIR__ . '/Templates/Cache.tpl');

        // export array
        $array = var_export($compileArray, true);

        // build map based on template keys
        $map = [
            'export'    => $array,
            'class'     => $compileArray['class'],
            'version'   => 'v' . $version,
            'buildDate' => date('D, d. M, Y H:i:s')
        ];

        $file = $cacheFileTemplate;
        foreach ($map as $k => &$v) {
            $file = str_replace('|' . $k . '|', $v, $file);
        }

        file_put_contents($cacheFile, $file);
    }

    /**
     * This method writes the alias cache files.
     * Aliases are "current" and "latest" api versions.
     * Aliases just include some other cache file.
     *
     * @param array  $compileArray Array holding different meta data regarding the api and the class.
     * @param string $aliasVersion Name of the version.
     */
    private function _compileCacheAliasTemplate($compileArray, $aliasVersion)
    {
        // template
        $cacheFileTemplate = file_get_contents(__DIR__ . '/Templates/CacheAlias.tpl');

        // build map based on template keys
        $map = [
            'export'       => 'include "' . $compileArray['cacheFile'] . '"',
            'class'        => $compileArray['class'],
            'version'      => 'v' . $compileArray['version'],
            'aliasVersion' => $aliasVersion,
            'buildDate'    => date('D, d. M, Y H:i:s')
        ];

        $file = $cacheFileTemplate;
        foreach ($map as $k => &$v) {
            $file = str_replace('|' . $k . '|', $v, $file);
        }

        $cacheFile = str_replace('v' . $compileArray['version'], $aliasVersion, $compileArray['cacheFile']);

        file_put_contents($cacheFile, $file);
    }

    /**
     * Deletes an existing cache file.
     *
     * @param string $cacheFile Cache file location.
     */
    private function _deleteExisting($cacheFile)
    {
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * This method does the actual processing of ParsedClass instance into a compiled array that is later
     * written into a cache file.
     *
     * @param ParsedClass $parsedClass ParsedClass instance that will be compiled into an array.
     * @param string      $version     Version of the API.
     *
     * @return array The compiled array.
     */
    private function _compileCacheFile(ParsedClass $parsedClass, $version)
    {
        $compileArray = [];
        $compileArray['class'] = $parsedClass->class;
        $compileArray['cacheKeyInterface'] = $parsedClass->cacheKeyInterface;
        $compileArray['accessInterface'] = $parsedClass->accessInterface;
        $compileArray['version'] = $version;


        foreach ($parsedClass->parsedMethods as $m) {
            $url = $this->_buildUrlMatchPattern($m->name, $m->params);
            $compileArray[$m->method][$url] = [
                'default'     => $m->default,
                'role'        => ($m->role) ? $m->role : false,
                'method'      => $m->name,
                'urlPattern'  => $m->urlPattern,
                'cache'       => $m->cache,
                'header'      => $m->header,
                'rateControl' => $m->rateControl,
                'params'      => []
            ];

            foreach ($m->params as $p) {
                $compileArray[$m->method][$url]['params'][$p->name] = [
                    'required' => $p->required,
                    'type'     => $p->type,
                    'default'  => $p->default
                ];
            }
        }

        return $compileArray;
    }

    /**
     * Builds the url match pattern for each of the method inside the api.
     *
     * @param string $methodName Method name.
     * @param array  $parameters List of the ParsedParameter instances.
     *
     * @return string The url pattern.
     */
    private function _buildUrlMatchPattern($methodName, array $parameters)
    {
        $url = $methodName;
        if ($this->_normalize) {
            $url = PathTransformations::methodNameToUrl($methodName);
        }

        foreach ($parameters as $p) {
            $matchType = $this->_getParamMatchType($p->type);
            $url = $url . '/' . $matchType;
        }

        return $url . '/';
    }

    /**
     * Returns a different match pattern, based on the given $paramType.
     *
     * @param string $paramType Parameter type name.
     *
     * @return string Match pattern.
     */
    private function _getParamMatchType($paramType)
    {
        switch ($paramType) {
            case 'string':
                return '([\w-]+)';
                break;
            case 'bool':
                return '(0|1|true|false)';
                break;
            case 'integer':
                return '([\d]+)';
                break;
            case 'float':
                return '([\d.]+)';
                break;
            default:
                return '([\w-]+)';
                break;
        }
    }

}