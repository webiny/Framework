<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Component\Logger\Driver\Webiny\Handler;

use Webiny\Component\Logger\Driver\Webiny\Formatter\FormatterInterface;
use Webiny\Component\Logger\Driver\Webiny\Processor\ProcessorInterface;
use Webiny\Component\Logger\Driver\Webiny\Record;
use Webiny\Component\Logger\LoggerException;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Base Handler class providing the Handler structure
 * @package Webiny\Component\Logger\Driver\Webiny\Handler
 */
abstract class AbstractHandler
{
    use StdLibTrait;

    protected $levels = [];
    protected $bubble = true;
    protected $buffer = false;

    /**
     * @var FormatterInterface
     */
    protected $formatter = null;
    protected $processors;
    protected $bufferProcessors;
    protected $records = [];

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param Record $record
     *
     * @return void
     */
    abstract protected function write(Record $record);

    /**
     * Get default formatter for this handler
     *
     * @return FormatterInterface
     */
    abstract protected function getDefaultFormatter();

    /**
     * @param array $levels The minimum logging level at which this handler will be triggered
     * @param Boolean           $bubble Whether the messages that are handled can bubble up the stack or not
     * @param bool              $buffer
     *
     * @return AbstractHandler Instance of AbstractHandler
     */
    public function __construct($levels = [], $bubble = true, $buffer = false)
    {
        $this->levels = $this->arr($levels);
        $this->bubble = $bubble;
        $this->buffer = $buffer;
        $this->processors = $this->arr();
        $this->bufferProcessors = $this->arr();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        try {
            $this->stopHandling();
        } catch (\Exception $e) {
            // do nothing
        }
    }

    /**
     * Check if this handler can handle the given Record
     *
     * @param Record $record
     *
     * @return bool Boolean telling whether this handler can handle the given Record
     */
    public function canHandle(Record $record)
    {
        if ($this->levels->count() < 1) {
            return true;
        }

        return $this->levels->inArray($record->getLevel());
    }

    /**
     * Stop handling<br />
     * This will be called automatically when the object is destroyed
     *
     * @return void
     */
    public function stopHandling()
    {
        if ($this->buffer) {
            $this->processRecords($this->records);
        }
    }

    /**
     * Add processor to this handler
     *
     * @param mixed $callback Callable or instance of ProcessorInterface
     *
     * @param bool  $processBufferRecord
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function addProcessor($callback, $processBufferRecord = false)
    {
        if (!is_callable($callback) && !$this->isInstanceOf($callback, ProcessorInterface::class)) {
            throw new \InvalidArgumentException('Processor must be valid callable or an instance of ' . ProcessorInterface::class);
        }

        if ($processBufferRecord) {
            $this->bufferProcessors->prepend($callback);
        } else {
            $this->processors->prepend($callback);
        }


        return $this;
    }

    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * Process given record
     * This will pass given record to ProcessorInterface instance, then format the record and output it according to current AbstractHandler instance
     *
     * @param Record $record
     *
     * @return bool Bubble flag (this either continues propagation of the Record to other handlers, or stops the logger from processing this record any further)
     */
    public function process(Record $record)
    {

        if ($this->buffer) {
            $this->processRecord($record);
            $this->records[] = $record;

            return $this->bubble;
        }

        $this->processRecord($record);
        $this->getFormatter()->formatRecord($record);
        $this->write($record);

        return $this->bubble;
    }

    /**
     * Processes a record.
     *
     * @param Record $record
     *
     * @return Record Processed Record object
     */
    protected function processRecord(Record $record)
    {

        foreach ($this->processors as $processor) {
            if ($this->isInstanceOf($processor, ProcessorInterface::class)) {
                $processor->processRecord($record);
            } else {
                call_user_func($processor, $record);
            }
        }
    }

    /**
     * Process batch of records
     *
     * @param array $records Batch of records to process
     *
     * @return bool Bubble flag (this either continues propagation of the Record to other handlers, or stops the logger from processing this record any further)
     */
    protected function processRecords(array $records)
    {
        $record = new Record();
        $formatter = $this->getFormatter();
        if ($this->isInstanceOf($formatter, FormatterInterface::class)) {
            $formatter->formatRecords($records, $record);
        }

        $this->write($record);

        return $this->bubble;
    }

    /**
     * @throws \Webiny\Component\Logger\LoggerException
     * @return FormatterInterface Instance of formatter to use for record formatting
     */
    private function getFormatter()
    {
        if ($this->isNull($this->formatter)) {
            $this->formatter = $this->getDefaultFormatter();
            if (!$this->isInstanceOf($this->formatter, FormatterInterface::class)) {
                throw new LoggerException('Formatter must be an instance of ' . FormatterInterface::class);
            }
        }

        return $this->formatter;
    }
}
