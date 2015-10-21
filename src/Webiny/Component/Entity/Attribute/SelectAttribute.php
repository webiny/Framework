<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\Validation\ValidationException;


/**
 * SelectAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class SelectAttribute extends AttributeAbstract
{

    protected $options = [];

    /**
     * Perform validation against given value
     *
     * @param $value
     *
     * @throws ValidationException
     * @return $this
     */
    protected function validate(&$value)
    {
        if (!$this->isString($value) && !$this->isNumber($value)) {
            $this->expected('string or number', gettype($value));
        }

        return $this;
    }

    /**
     * Set select box options (in form of key => value pairs)
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions($options = [])
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get select box options (in form of key => value pairs)
     *
     * @return $this
     */
    public function getOptions()
    {
        return $this->options;
    }
}