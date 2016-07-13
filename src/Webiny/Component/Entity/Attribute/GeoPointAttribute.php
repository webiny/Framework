<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\Attribute\Validation\ValidationException;
use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;

/**
 * GeoPointAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class GeoPointAttribute extends AbstractAttribute
{
    public function __construct($name = null, AbstractEntity $parent = null)
    {
        parent::__construct($name, $parent);

        $this->defaultValue = [
            'lat' => 0,
            'lng' => 0
        ];
    }

    public function getDbValue()
    {
        $value = $this->getValue();

        $dbValue = [
            'type'        => 'Point',
            'coordinates' => [(float)$value['lat'], (float)$value['lng']]
        ];

        return $this->processToDbValue($dbValue);
    }

    public function setValue($value = null, $fromDb = false)
    {
        if ($fromDb) {
            $this->value = [
                'lat' => $value['coordinates'][0],
                'lng' => $value['coordinates'][1]
            ];

            return $this;
        }

        if (!$this->canAssign()) {
            return $this;
        }

        $value = $this->processSetValue($value);
        $this->validate($value);

        // Make sure only these two keys are assigned
        $value = [
            'lat' => $value['lat'],
            'lng' => $value['lng']
        ];

        $this->value = $value;

        return $this;
    }

    protected function validate(&$value)
    {
        if ($this->isNull($value)) {
            return $this;
        }

        if (!$this->isArray($value) && !$this->isArrayObject($value)) {
            $this->expected('array or ArrayObject', gettype($value));
        }

        $value = StdObjectWrapper::toArray($value);

        if (!array_key_exists('lat', $value) || !array_key_exists('lng', $value)) {
            $ex = new ValidationException(ValidationException::VALIDATION_FAILED);
            $ex->addError($this->attribute, 'GeoPointAttribute value must contain `lat` and `lng`');
            throw $ex;
        }
    }
}