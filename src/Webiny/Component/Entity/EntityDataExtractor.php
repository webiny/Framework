<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity;

use Webiny\Component\Entity\Attribute\AbstractAttribute;
use Webiny\Component\Entity\Attribute\AttributeType;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;


/**
 * EntityDataExtractor class converts AbstractEntity instance to an array representation.
 *
 * @package Webiny\Component\Entity
 */
class EntityDataExtractor
{
    use StdLibTrait;

    /**
     * @var AbstractEntity
     */
    protected $entity;

    protected static $currentLevel = 0;
    protected static $cache = [];
    protected $nestedLevel = 10; // Maximum depth is 10 which is hard to achieve

    public function __construct(AbstractEntity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Extract AbstractEntity data to array using specified list of attributes.
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

        /* @var array $attributes Array that contains all fields, aliases and dotted fields */
        $attributes = $this->buildEntityFields($attributes);

        foreach ($attributes['fields'] as $attr => $subAttributes) {
            $parts = explode(':', $attr);
            $attrName = $parts[0];
            $params = array_slice($parts, 1);

            try {
                $entityAttribute = $this->entity->getAttribute($attrName);
            } catch (EntityException $e) {
                continue;
            }

            $entityAttributeValue = $entityAttribute->getValue($params);
            $isOne2Many = $this->isInstanceOf($entityAttribute, AttributeType::ONE2MANY);
            $isMany2Many = $this->isInstanceOf($entityAttribute, AttributeType::MANY2MANY);
            $isMany2One = $this->isInstanceOf($entityAttribute, AttributeType::MANY2ONE);
            $isArray = $this->isInstanceOf($entityAttribute, AttributeType::ARR);
            $isObject = $this->isInstanceOf($entityAttribute, AttributeType::OBJECT);
            $isDynamic = $this->isInstanceOf($entityAttribute, AttributeType::DYNAMIC);

            if ($isMany2One) {
                if ($this->isNull($entityAttributeValue)) {
                    $data[$attrName] = null;
                    continue;
                }

                if ($entityAttribute->hasToArrayCallback()) {
                    $data[$attrName] = $entityAttribute->toArray($params);
                    continue;
                }

                if (self::$currentLevel < $this->nestedLevel) {
                    self::$currentLevel++;
                    $data[$attrName] = $entityAttributeValue->toArray($subAttributes, $this->nestedLevel);
                    self::$currentLevel--;
                }
            } elseif ($isOne2Many || $isMany2Many) {
                $data[$attrName] = [];
                foreach ($entityAttributeValue as $item) {
                    if (self::$currentLevel < $this->nestedLevel) {
                        self::$currentLevel++;
                        $data[$attrName][] = $item->toArray($subAttributes, $this->nestedLevel);
                        self::$currentLevel--;
                    }
                }
            } elseif ($isObject) {
                $value = $entityAttribute->toArray($params);

                if ($subAttributes) {
                    $keys = $this->buildNestedKeys($subAttributes);
                    $value = $this->arr();
                    foreach ($keys as $key) {
                        $value->keyNested($key, $entityAttribute->getValue()->keyNested($key));
                    }
                    $value = $value->val();
                }
                $data[$attrName] = $value;
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

                $data[$attrName] = $value;
            } elseif ($isDynamic) {
                $data[$attrName] = $entityAttribute->toArray($subAttributes, $params);
            } else {
                $data[$attrName] = $entityAttribute->toArray($params);
            }
        }

        // Populate alias value
        $copy = $data;
        $data = $this->arr($data);

        // If aliases were used, recreate the entire array to remove junk keys of aliased attributes
        if (count($attributes['aliases'])) {
            $cleanData = $this->arr();
            foreach ($attributes['dottedFields'] as $key) {
                if (array_key_exists($key, $attributes['aliases'])) {
                    $cleanData->keyNested($attributes['aliases'][$key], $data->keyNested($key), true);
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
    public function buildEntityFields($fields)
    {
        $aliases = [];
        $dottedFields = [];

        if (!$this->isArray($fields)) {
            $cacheKey = $fields;
            if (array_key_exists($cacheKey, self::$cache)) {
                return self::$cache[$cacheKey];
            }

            $fields = $this->str($fields);

            if ($fields->contains('[')) {
                $fields = $this->parseGroupedNestedFields($fields);
            }

            $fields = $fields->explode(',')->filter()->map('trim')->val();
        } else {
            $cacheKey = serialize($fields);
            if (array_key_exists($cacheKey, self::$cache)) {
                return self::$cache[$cacheKey];
            }

            // Check if asterisk is present and replace it with actual attribute names
            if ($this->arr($fields)->keyExists('*')) {
                unset($fields['*']);
                $defaultFields = $this->str($this->getDefaultAttributes())->explode(',')->filter()->map('trim')->flip()->val();
                $fields = $this->arr($fields)->merge($defaultFields)->val();
            }

            return self::$cache[$cacheKey] = [
                'fields'  => $fields,
                'aliases' => $aliases,
                'dottedFields' => $dottedFields
            ];
        }

        $parsedFields = $this->arr(['id' => true]);
        $unsetFields = [];

        foreach ($fields as $f) {
            $f = $this->str($f);

            if ($f->contains('@')) {
                list($f, $alias) = $f->explode('@')->val();
                $aliases[$f] = $alias;
                $f = $this->str($f);
            }

            $dottedFields[] = $f->val();

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

        return self::$cache[$cacheKey] = [
            'fields'  => $parsedFields->val(),
            'aliases' => $aliases,
            'dottedFields' => $dottedFields
        ];
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
     * @param ArrayObject        $parsedFields Reference to array of parsed fields
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
        $attributes = ['id'];
        foreach ($this->entity->getAttributes() as $name => $attribute) {
            /* @var AbstractAttribute $attribute */
            if ($attribute->getToArrayDefault()) {
                $attributes[] = $name;
            }
        }

        return $this->arr($attributes)->implode(',')->val();
    }
}