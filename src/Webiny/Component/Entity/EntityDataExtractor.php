<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity;

use Webiny\Component\Entity\Attribute\AttributeType;
use Webiny\Component\Mongo\MongoTrait;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;


/**
 * EntityDataExtractor class converts EntityAbstract instance to an array representation.
 *
 * @package Webiny\Component\Entity
 */
class EntityDataExtractor
{
    use StdLibTrait;

    /**
     * @var EntityAbstract
     */
    protected $entity;

    protected static $currentLevel = 0;
    protected $nestedLevel = 1;
    protected $aliases = [];
    protected $dottedFields = ['_name'];

    public function __construct(EntityAbstract $entity, $nestedLevel = 1)
    {
        if ($nestedLevel < 0) {
            $nestedLevel = 1;
        }

        // Do not allow depth greater than 10
        if ($nestedLevel > 10) {
            $nestedLevel = 10;
        }

        $this->entity = $entity;
        $this->nestedLevel = $nestedLevel;
    }

    /**
     * Extract EntityAbstract data to array using specified list of attributes.
     * If no attributes are specified, only simple and Many2One attributes will be extracted.
     * If you need to get One2Many and Many2Many attributes, you need to explicitly specify a list of attributes.
     *
     * @param array $attributes Ex: 'title,author.name,comments.id,comments.text'
     *
     * @return array
     */
    public function extractData($attributes = [])
    {
        if ($this->isEmpty($attributes)) {
            $attributes = $this->getDefaultAttributes();
        }

        $data = [];
        $attributes = $this->buildEntityFields($attributes);

        foreach ($attributes as $attr => $subAttributes) {
            if ($attr == '_name') {
                continue;
            }

            try {
                $entityAttribute = $this->entity->getAttribute($attr);
            } catch (EntityException $e) {
                continue;
            }

            $entityAttributeValue = $entityAttribute->getValue();
            $isOne2Many = $this->isInstanceOf($entityAttribute, AttributeType::ONE2MANY);
            $isMany2Many = $this->isInstanceOf($entityAttribute, AttributeType::MANY2MANY);
            $isMany2One = $this->isInstanceOf($entityAttribute, AttributeType::MANY2ONE);
            $isArray = $this->isInstanceOf($entityAttribute, AttributeType::ARR);
            $isObject = $this->isInstanceOf($entityAttribute, AttributeType::OBJECT);

            if ($isMany2One) {
                if ($this->isNull($entityAttributeValue)) {
                    $data[$attr] = null;
                    continue;
                }

                if ($entityAttribute->hasToArrayCallback()) {
                    $data[$attr] = $entityAttribute->toArray();
                    continue;
                }

                if (self::$currentLevel < $this->nestedLevel) {
                    self::$currentLevel++;
                    $data[$attr] = $entityAttributeValue->toArray($subAttributes, $this->nestedLevel);
                    self::$currentLevel--;
                }
            } elseif ($isOne2Many || $isMany2Many) {
                $data[$attr] = [];
                foreach ($entityAttributeValue as $item) {
                    if (self::$currentLevel < $this->nestedLevel) {
                        self::$currentLevel++;
                        $data[$attr][] = $item->toArray($subAttributes, $this->nestedLevel);
                        self::$currentLevel--;
                    }
                }
            } elseif ($isObject) {
                $data[$attr] = $entityAttribute->toArray();
            } elseif ($isArray) {
                $value = $entityAttribute->toArray();
                if ($subAttributes) {
                    $subValues = [];
                    foreach ($value as $array) {
                        $subValues[] = $this->getSubAttributesFromArray($subAttributes, $array);
                    }
                    $value = $subValues;
                    $value['__webiny_array__'] = true;
                }

                $data[$attr] = $value;
            } else {
                $data[$attr] = $entityAttribute->toArray();
            }
        }
        $data['_name'] = $this->entity->getMaskedValue();

        // Populate alias value
        $copy = $data;
        $data = $this->arr($data);

        // If aliases were used, recreate the entire array to remove junk keys of aliased attributes
        if (count($this->aliases)) {
            $cleanData = $this->arr();
            foreach ($this->dottedFields as $key) {
                if (array_key_exists($key, $this->aliases)) {
                    $cleanData->keyNested($this->aliases[$key], $data->keyNested($key), true);
                    continue;
                }
                $cleanData->keyNested($key, $data->keyNested($key), true);
            }
            $data = $cleanData;
        }

        // Copy ArrayAttribute value from backup
        foreach ($copy as $key => $value) {
            if (is_array($value) && array_key_exists('__webiny_array__', $value)) {
                unset($value['__webiny_array__']);
                $data[$key] = $value;
            }
        }

        return $data->val();
    }

    /**
     * Parse fields string and build nested fields structure.<br>
     * If array is given, will just return that array.
     *
     * @param string|array $fields
     *
     * @return array
     */
    private function buildEntityFields($fields)
    {
        if (!$this->isArray($fields)) {
            $fields = $this->str($fields);

            if ($fields->contains('[')) {
                $fields = $this->parseGroupedNestedFields($fields);
            }

            $fields = $fields->explode(',')->filter()->map('trim')->val();
        } else {
            // Check if asterisk is present and replace it with actual attribute names
            if ($this->arr($fields)->keyExists('*')) {
                unset($fields['*']);
                $defaultFields = $this->str($this->getDefaultAttributes())->explode(',')->filter()->map('trim')->flip()->val();
                $fields = $this->arr($fields)->merge($defaultFields)->val();
            }

            return $fields;
        }

        $parsedFields = $this->arr();
        $unsetFields = [];

        foreach ($fields as $f) {
            $f = $this->str($f);

            if ($f->contains('@')) {
                list($f, $alias) = $f->explode('@')->val();
                $this->aliases[$f] = $alias;
                $f = $this->str($f);
            }

            $this->dottedFields[] = $f->val();

            if ($f->startsWith('!')) {
                $unsetFields[] = $f->trimLeft('!')->val();
                continue;
            }

            if ($f->val() == '*') {
                $defaultFields = $this->str($this->getDefaultAttributes())->explode(',')->filter()->map('trim')->val();
                foreach ($defaultFields as $df) {
                    $this->buildFields($parsedFields, $this->str($df));
                }
                continue;
            }
            $this->buildFields($parsedFields, $f);
        }

        foreach ($unsetFields as $field) {
            $parsedFields->removeKey($field);
        }

        return $parsedFields->val();
    }

    /**
     * Check if there are grouped nested keys (by using '[' and ']' and converts that string
     * into a plain version - a string that only contains comma-separated full paths of each field
     *
     * @param $string
     *
     * @return StringObject
     */
    private function parseGroupedNestedFields(StringObject $string)
    {
        $output = $this->str('');
        $currentPath = [
            'array'  => [],
            'string' => ''
        ];

        $parts = $string->explode('[');
        $lastPart = $parts->count() - 1;

        foreach ($parts as $index => $part) {

            $fields = explode(',', $part);

            $isLast = $index == $lastPart;

            if (!$isLast) {
                $newNestedKey = array_pop($fields) . '.';
            }

            foreach ($fields as $field) {

                $fullPath = '';

                if (substr($field, 0, 1) == '!') {
                    $fullPath = '!';
                    $field = ltrim($field, '!');
                }

                $closingBrackets = substr_count($field, ']');
                $field = rtrim($field, ']');

                $fullPath .= $currentPath['string'] . $field;

                $output->append($fullPath . ',');

                if ($closingBrackets > 0) {
                    $currentPath['array'] = array_slice($currentPath['array'], 0, count($currentPath['array']) - $closingBrackets);
                    $currentPath['string'] = implode('.', $currentPath['array']);
                }
            }

            if (!$isLast) {
                $currentPath['string'] .= $newNestedKey;
                $currentPath['array'][] = $newNestedKey;
            }
        }

        return $output->trimRight(',');
    }

    /**
     * Parse attribute key recursively
     *
     * @param array        $parsedFields Reference to array of parsed fields
     * @param StringObject $key Current key to parse
     */
    private function buildFields(&$parsedFields, StringObject $key)
    {
        if ($key->contains('.')) {
            $parts = $key->explode('.', 2)->val();
            if (!isset($parsedFields[$parts[0]])) {
                $parsedFields[$parts[0]] = [];
            }

            $this->buildFields($parsedFields[$parts[0]], $this->str($parts[1]));
        } else {
            $parsedFields[$key->val()] = '';
        }
    }

    private function buildNestedKeys($fields)
    {
        $keys = [];
        foreach ($fields as $f => $nestedFields) {
            if (is_array($nestedFields)) {
                $nestedKeys = $this->buildNestedKeys($nestedFields);
                foreach ($nestedKeys as $k) {
                    $keys[] = $f . '.' . $k;
                }
            } else {
                $keys[] = $f;
            }
        }

        return $keys;
    }

    private function getSubAttributesFromArray($subAttributes, $array)
    {
        $keys = $this->buildNestedKeys($subAttributes);

        $value = $this->arr();
        $entityAttributeValue = $this->arr($array);

        foreach ($keys as $key) {
            $key = $this->str($key);
            $value->keyNested($key, $entityAttributeValue->keyNested($key), true);
        }

        return $value->val();
    }

    /**
     * Get default list of entity attributes.<br>
     * Only simple and Many2One attributes are considered to be default attributes.
     *
     * @return string
     */
    private function getDefaultAttributes()
    {
        $attributes = [];
        foreach ($this->entity->getAttributes() as $name => $attribute) {
            $default = [
                $this->isInstanceOf($attribute, AttributeType::ONE2MANY),
                $this->isInstanceOf($attribute, AttributeType::MANY2MANY),
                $this->isInstanceOf($attribute, AttributeType::MANY2ONE),
                $this->isInstanceOf($attribute, AttributeType::ARR),
                $this->isInstanceOf($attribute, AttributeType::OBJECT)
            ];

            if (in_array(true, $default)) {
                continue;
            }

            $attributes[] = $name;
        }

        return $this->arr($attributes)->implode(',')->val();
    }
}