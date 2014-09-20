<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Component\Logger\Driver\Webiny\Processor;

use Webiny\Component\Logger\Driver\Webiny\Record;

/**
 * Interface for processors
 *
 * @package Webiny\Component\Logger\Driver\Processor
 */
interface ProcessorInterface
{
    /**
     * Processes a log record.
     *
     * @param Record $record A record to process
     */
    public function processRecord(Record $record);
}