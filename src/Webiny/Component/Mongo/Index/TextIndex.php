<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 */

namespace Webiny\Component\Mongo\Index;

use Webiny\Component\Mongo\MongoException;

/**
 * Text index
 *
 * @package Webiny\Component\Mongo\Index
 */
class TextIndex extends AbstractIndex
{
    private $language;

    /**
     * @param string       $name Index name
     * @param string|array $field Index field(s)
     * @param bool         $sparse Is index sparse?
     * @param bool         $unique Is index unique?
     * @param bool         $dropDuplicates Drop duplicate documents (only if $unique is used)
     * @param string       $language Default language
     *
     * @throws MongoException
     */
    public function __construct($name, $field, $sparse = false, $unique = false, $dropDuplicates = false, $language = 'english')
    {
        $this->language = $language;
        $fields = $this->isArray($field) ? $field : [$field];

        parent::__construct($name, $fields, $sparse, $unique, $dropDuplicates);
    }

    public function getOptions()
    {
        $options = parent::getOptions();
        $options['default_language'] = $this->language;

        return $options;
    }

    public function getDefaultLanguage()
    {
        return $this->language;
    }

    protected function normalizeFields()
    {
        $normalizedFields = [];
        foreach ($this->fields as $key => $field) {
            if ($this->isNumber($key)) {
                $normalizedFields[$this->str($field)->trimLeft('-+')->val()] = 'text';
            } else {
                $normalizedFields[$key] = 'text';
            }
        }
        $this->fields = $normalizedFields;
    }
}