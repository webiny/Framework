<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Component\Logger\Driver\Webiny\Formatter;

use Webiny\Component\Logger\Driver\Webiny\Record;

/**
 * Interface for Formatter
 * @package Webiny\Component\Logger\Driver\Webiny\Formatter
 */
interface FormatterInterface
{
    /**
     * Formats a log record.
     * Change Record object as you see fit.
     *
     * Assign formatted value using $record->setFormattedRecord($formattedRecord)
     *
     * @param Record $record A record to format
     */
    public function formatRecord(Record $record);

    /**
     * Formats multiple log records
     * The second parameter contains the Record object that will be passed to HandlerAbstract->write($record) method.
     * Modify the Record object as you see fit.
     *
     * Assign formatted value using $record->setFormattedRecord($formattedRecord)
     *
     * @param  array $records A set of records to format
     * @param Record $record  A final record object
     */
    public function formatRecords(array $records, Record $record);
}
