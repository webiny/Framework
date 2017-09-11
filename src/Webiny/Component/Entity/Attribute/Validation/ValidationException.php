<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Component\Entity\Attribute\Validation;

use Traversable;
use Webiny\Component\StdLib\Exception\AbstractException;

/**
 * Exception class for the Entity attribute
 *
 * @package         Webiny\Component\Entity
 */
class ValidationException extends AbstractException implements \IteratorAggregate
{
    const VALIDATION_FAILED = 101;
    const DATA_TYPE = 102;
    const REQUIRED = 103;

    protected $errors = [];
    protected $attribute = null;

    protected static $messages = [
        101 => "Validation failed",
        102 => "Invalid data type provided. Expecting '%s', got '%s'",
        103 => "Missing required value"
    ];

    /**
     * Add error for given key
     *
     * This is useful when you are validating an array attribute which can have validators on every nested key.
     * When validating a simple attribute with no nested values, use this method to set error message for the attribute itself.
     *
     * @param string     $key Attribute name or nested attribute key
     * @param string|int $message
     *
     * @param null|array $params
     *
     * @return $this
     */
    public function addError($key, $message, $params = null)
    {
        if (!is_string($message)) {
            $message = vsprintf(static::$messages[$message], is_array($params) ? $params : []);
        }
        $this->errors[$key] = $message;

        return $this;
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        if (!count($this->errors)) {
            $this->errors[$this->attribute] = $this->getMessage();
        }

        return new \ArrayIterator($this->errors);
    }

    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param null $attribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
    }

}