<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config\Drivers;

use Webiny\Component\Config\ConfigException;
use Webiny\Component\StdLib\StdObject\StdObjectException;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;

/**
 * Description
 *
 * @package   Webiny\Component\Config\Drivers;
 */
class IniDriver extends DriverAbstract
{
    private $_delimiter = '.';
    private $_useSections = true;

    /**
     * Get config data as string
     *
     * @return string
     */
    protected function _getString()
    {
        $data = $this->_getArray();

        return $this->_getIni($data);
    }


    /**
     * Set delimiting character for nested properties, ex: a.b.c or a-b-c
     *
     * @param string $delimiter Default: '.'
     *
     * @return $this
     */
    public function setDelimiter($delimiter)
    {
        $this->_delimiter = $delimiter;

        return $this;
    }

    /**
     * Should parser use sections or not, ex: [section]
     *
     * @param boolean $useSections Default: true
     *
     * @return $this
     */
    public function useSections($useSections)
    {
        $this->_useSections = $useSections;

        return $this;
    }

    /**
     * Parse config resource and build config array
     * @return array
     */
    protected function _getArray()
    {
        if ($this->isArray($this->_resource)) {
            return $this->_resource;
        }

        return $this->_parseIniString($this->_resource);
    }

    /**
     * Parse INI string and return config array
     *
     * @param array $data
     *
     * @return array
     */
    private function _parseIniString($data)
    {
        $config = $this->arr();
        $data = parse_ini_string($data, true);
        foreach ($data as $section => $value) {
            $config = $config->mergeRecursive($this->_processValue($section, $value));
        }

        return $config;
    }

    /**
     * Process given section and it's value
     * Config array is empty by default, but it's a nested recursive call, it will be populated with data from previous calls
     *
     * @param string       $section
     * @param string|array $value
     * @param array        $config
     *
     * @return array
     */
    private function _processValue($section, $value, $config = [])
    {
        // Need to catch Exception in case INI string is not properly formed
        try {
            // Make sure $config is an ArrayObject
            $config = $this->arr($config);
        } catch (StdObjectException $e) {
            $config = $this->arr();
        }


        // Create StringObject and trim invalid characters
        $section = $this->str($section);
        $this->_validateSection($section);

        // Handle nested sections, ex: parent.child.property
        if ($section->contains($this->_delimiter)) {
            /**
             * Explode section and only take 2 elements
             * First element will be the new array key, and second will be passed for recursive processing
             * Ex: parent.child.property will be split into 'parent' and 'child.property'
             */
            $sections = $section->explode($this->_delimiter, 2)->removeFirst($section);
            $localConfig = $config->key($section, [], true);
            $config->key($section, $this->_processValue($sections->last()->val(), $value, $localConfig));
        } else {
            // If value is an array, we need to process it's keys
            if ($this->isArray($value)) {
                foreach ($value as $k => $v) {
                    $localConfig = $config->key($section, [], true);
                    $config->key($section, $this->_processValue($k, $v, $localConfig));
                }
            } else {
                $config->key($section, $value);
            }
        }

        return $config->val();
    }

    private function _validateSection(StringObject $section)
    {
        $tmp = $section->explode($this->_delimiter);
        if ($tmp->first()->contains('-') || $this->isNumber($tmp->first()->val())) {
            throw new ConfigException(sprintf('Invalid config key "%s"', $section->val()));
        }
    }

    private function _getIni($data)
    {
        $string = '';

        // Determine what to do with values outside of sections
        foreach ($data as $k => $v) {
            if (!$this->isArray($v)) {
                unset($data[$k]);
            }
        }

        foreach (array_keys($data) as $key) {
            $string .= '[' . $key . "]\n";
            $string .= $this->_getSection($data[$key], '') . "\n";
        }

        return $string;
    }

    private function _getSection(&$ini, $prefix)
    {
        $string = '';
        foreach ($ini as $key => $val) {
            if (is_array($val)) {
                $string .= $this->_getSection($ini[$key], $prefix . $key . $this->_delimiter);
            } else {
                $string .= $prefix . $key . ' = ' . str_replace("\n", "\\\n", $this->_setValue($val)) . "\n";
            }
        }

        return $string;
    }

    private function _setValue($val)
    {
        if ($val === true) {
            return 'true';
        } else {
            if ($val === false) {
                return 'false';
            }
        }

        return $val;
    }
}