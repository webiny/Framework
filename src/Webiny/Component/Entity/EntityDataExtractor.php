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
            } else {
                $data[$attr] = $entityAttribute->toArray();
            }
        }
        $data['_name'] = $this->entity->getMaskedValue();

        return $data;
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

            $isLast = $index < $lastPart;

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
     * @param $parsedFields Reference to array of parsed fields
     * @param $key          Current key to parse
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
            $isOne2Many = $this->isInstanceOf($attribute, AttributeType::ONE2MANY);
            $isMany2Many = $this->isInstanceOf($attribute, AttributeType::MANY2MANY);

            if ($isOne2Many || $isMany2Many) {
                continue;
            }

            $attributes[] = $name;
        }

        return $this->arr($attributes)->implode(',')->val();
    }
}