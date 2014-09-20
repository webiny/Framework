<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Logger\Driver\Webiny\Processor;

use Webiny\Component\Logger\Driver\Webiny\Record;

/**
 * FileLineProcessor adds 'file' and 'line' values to the Record 'extra' data
 *
 * @package Webiny\Component\Logger\Driver\Webiny\Processor
 */
class FileLineProcessor implements ProcessorInterface
{

    /**
     * Processes a log record.
     *
     * @param Record $record A record to process
     *
     */
    public function processRecord(Record $record)
    {

        $backtrace = debug_backtrace();
        $backtrace = $backtrace[5];

        $extraData = $record->getExtra();

        $extraData['file'] = $backtrace['file'];
        $extraData['line'] = $backtrace['line'];

        $record->setExtra($extraData);
    }
}