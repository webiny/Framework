<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Logger\Driver\Webiny\Handler;

use Webiny\Component\Logger\Driver\Webiny\Formatter\FileFormatter;
use Webiny\Component\Logger\Driver\Webiny\Formatter\FormatterAbstract;
use Webiny\Component\Logger\Driver\Webiny\Record;
use Webiny\Component\Logger\LoggerException;
use Webiny\Component\StdLib\StdObject\StdObjectException;
use Webiny\Component\Storage\File\LocalFile;

/**
 * FileHandler class stores log messages to log file
 *
 * @package         Webiny\Component\Logger\Driver\Webiny\Handler
 */
class FileHandler extends HandlerAbstract
{
    /**
     * @var LocalFile
     */
    private $_file;

    public function __construct(LocalFile $file, $levels = [], $bubble = true, $buffer = false)
    {
        parent::__construct($levels, $bubble, $buffer);
        try {
            $this->_file = $file;
        } catch (StdObjectException $e) {
            throw new LoggerException($e->getMessage());
        }

    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param Record $record
     *
     * @return void
     */
    protected function write(Record $record)
    {
        $this->_file->setContents($record->getFormattedRecord(), true);
    }

    /**
     * Get default formatter for this handler
     *
     * @return FormatterAbstract
     */
    protected function _getDefaultFormatter()
    {
        return new FileFormatter();
    }
}