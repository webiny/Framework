<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Logger\Driver\Webiny\Formatter;

use Webiny\Component\Logger\Driver\Webiny\Record;
use Webiny\Component\Logger\Logger;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Base Formatter class providing the Handler structure
 * @package Webiny\Component\Logger\Driver\Webiny\Formatter
 */
abstract class FormatterAbstract implements FormatterInterface
{
    use StdLibTrait;

    protected $config = null;

    /**
     * Normalize record values, convert objects and resources to string representation, encode arrays to json, etc.
     */
    public function normalizeValues(Record $record)
    {

        foreach ($record as $key => $value) {
            $setter = 'set' . ucfirst($key);
            $record->$setter($this->normalizeValue($value));
        }
    }

    private function normalizeValue($data)
    {
        if ($this->isNull($data) || $this->isScalar($data)) {
            return $data;
        }

        if ($this->isStdObject($data)) {
            if ($this->isDateTimeObject($data)) {
                if ($this->isNull($this->config->DateFormat)) {
                    $format = Logger::getConfig()->Configs->Formatter->Default->DateFormat;
                } else {
                    $format = $this->config->DateFormat;
                }

                return $data->format($format);
            }
            $data = $data->val();
        }

        if ($this->isString($data)) {
            return $data;
        }

        if ($this->isArray($data) || $data instanceof \Traversable) {
            $normalized = array();
            foreach ($data as $key => $value) {
                $normalized[$key] = $this->normalizeValue($value);
            }

            return $normalized;
        }

        if ($this->isObject($data)) {
            if (method_exists($data, '___toString')) {
                return '' . $data;
            }

            return sprintf("[object] (%s: %s)", get_class($data), $this->jsonEncode($data));
        }

        if ($this->isResource($data)) {
            return '[resource]';
        }

        return '[unknown(' . gettype($data) . ')]';
    }
}
