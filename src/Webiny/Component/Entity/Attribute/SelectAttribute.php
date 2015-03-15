<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;


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
    public function validate(&$value)
    {
        if (!$this->isString($value) && !$this->isNumber($value)) {
            throw new ValidationException(ValidationException::ATTRIBUTE_VALIDATION_FAILED, [
                    $this->attribute,
                    'string or number',
                    gettype($value)
                ]
            );
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