<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Logger\Driver\Webiny\Processor;

use Webiny\Component\Logger\Driver\Webiny\Record;

/**
 * MemoryUsageProcessor adds 'memoryUsage' (current allocated amount of memory) to the Record 'extra' data
 *
 * @package Webiny\Component\Logger\Driver\Webiny\Processor
 */
class MemoryUsageProcessor implements ProcessorInterface
{

    /**
     * Processes a log record.
     *
     * @param Record $record Log record to process
     */
    public function processRecord(Record $record)
    {
        $extraData = $record->getExtra();
        $extraData['memoryUsage'] = memory_get_usage(true);
        $record->setExtra($extraData);
    }
}