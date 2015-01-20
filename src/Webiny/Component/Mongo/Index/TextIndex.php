<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 */

namespace Webiny\Component\Mongo\Index;

/**
 * Text index
 *
 * @package Webiny\Component\Mongo\Index
 */
class TextIndex extends IndexAbstract
{
    private $_language;

    /**
     * @param string $name           Index name
     * @param array  $fields         Index fields
     * @param bool   $sparse         Is index sparse?
     * @param bool   $unique         Is index unique?
     * @param bool   $dropDuplicates Drop duplicate documents (only if $unique is used)
     * @param string $language       Default language
     *
     * @throws MongoException
     */
    public function __construct($name, array $fields, $sparse = false, $unique = false, $dropDuplicates = false,
                                $language = 'english')
    {
        $this->_language = $language;

        if(count($fields) < 2) {
            throw new MongoException(MongoException::COMPOUND_INDEX_NOT_ENOUGH_FIELDS);
        }

        parent::__construct($name, $fields, $sparse, $unique, $dropDuplicates);
    }

    public function getOptions()
    {
        $options = parent::getOptions();
        $options['default_language'] = $this->_language;

        return $options;
    }

    public function setDefaultLanguage($language)
    {
        $this->_language = $language;

        return $this;
    }

    public function getDefaultLanguage()
    {
        return $this->_language;
    }

    protected function _normalizeFields()
    {
        $normalizedFields = [];
        foreach ($this->_fields as $key => $field) {
            if($this->isNumber($key)) {
                $normalizedFields[$this->str($field)->trimLeft('-+')->val()] = 'text';
            } else {
                $normalizedFields[$key] = 'text';
            }
        }
        $this->_fields = $normalizedFields;
    }
}