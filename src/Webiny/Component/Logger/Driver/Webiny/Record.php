<?php

namespace Webiny\Component\Logger\Driver\Webiny;

use Webiny\Component\StdLib\StdObject\DateTimeObject\DateTimeObject;

/**
 * Logger record container class
 *
 * @package Webiny\Component\Logger\Driver\Webiny
 */
class Record implements \IteratorAggregate
{
    protected $loggerName;
    protected $message;
    protected $level;
    protected $context;
    protected $datetime;
    protected $extra = [];
    protected $formattedRecord;

    /**
     * Set log message context (can be any data you want to log with your message)
     *
     * @param mixed $context
     *
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get log message context
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set log message datetime
     *
     * @param DateTimeObject|string $datetime
     *
     * @return $this
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * Get log message datetime
     *
     * @return DateTimeObject|string
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set extra log message data
     *
     * @param array $extra
     *
     * @return $this
     */
    public function setExtra(array $extra)
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * Get log message extra data
     * @return array
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * Set formatted record
     *
     * @param mixed $formatted
     *
     * @return $this
     */
    public function setFormattedRecord($formatted)
    {
        $this->formattedRecord = $formatted;

        return $this;
    }

    /**
     * Get formatted record
     *
     * @return mixed
     */
    public function getFormattedRecord()
    {
        return $this->formattedRecord;
    }

    /**
     * Set log level for this message
     *
     * @param string $level
     *
     * @return $this
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get log level of the current message
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set log message
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get log message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set logger name (this is the name of the logger that was used to log this message)
     *
     * @param string $name
     *
     * @return $this
     */
    public function setLoggerName($name)
    {
        $this->loggerName = $name;

        return $this;
    }

    /**
     * Get logger name
     *
     * @return string
     */
    public function getLoggerName()
    {
        return $this->loggerName;
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        $recordData = [
            'loggerName' => $this->getLoggerName(),
            'message'    => $this->getMessage(),
            'level'      => $this->getLevel(),
            'context'    => $this->getContext(),
            'datetime'   => $this->getDatetime(),
            'extra'      => $this->getExtra()
        ];

        return new \ArrayIterator($recordData);
    }

}