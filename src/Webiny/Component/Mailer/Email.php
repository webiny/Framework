<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer;

/**
 * This is the Email class used to normalize use of email/name data between different bridges
 *
 * @package         Webiny\Component\Mailer
 */
class Email implements \ArrayAccess
{
    public $email;
    public $name;

    public function __construct($email, $name = null)
    {
        $this->email = $email;
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name ? $this->name . ' <' . $this->email . '>' : $this->email;
    }

    public function toArray()
    {
        return [
            'email' => $this->email,
            'name'  => $this->name
        ];
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        $this->$offset = null;
    }
}