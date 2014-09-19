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
    protected $_entity;

    protected static $_loadedEntities = null;

    protected static $_currentLevel = 0;
    protected $_nestedLevel = 1;

    public function __construct(EntityAbstract $entity, $nestedLevel = 1)
    {
        if ($nestedLevel < 0) {
            $nestedLevel = 1;
        }
        $this->_entity = $entity;
        $this->_nestedLevel = $nestedLevel;

        if (!self::$_loadedEntities) {
            self::$_loadedEntities = $this->arr();
        }
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
        $checkKey = get_class($this->_entity) . '-' . $this->_entity->getId();
        if (self::$_loadedEntities->keyExists($checkKey)) {
            return [
                '__reference__' => true,
                'class'         => get_class($this->_entity),
                'id'            => $this->_entity->getId()->getValue()
            ];
        } else {
            self::$_loadedEntities->key($checkKey, true);
        }

        if ($this->isEmpty($attributes)) {
            $attributes = $this->_getDefaultAttributes();
        }

        $data = [];
        $attributes = $this->_buildEntityFields($attributes);

        foreach ($attributes as $attr => $subAttributes) {
            $entityAttribute = $this->_entity->getAttribute($attr);
            $entityAttributeValue = $entityAttribute->getValue();
            $isOne2Many = $this->isInstanceOf($entityAttribute, AttributeType::ONE2MANY);
            $isMany2Many = $this->isInstanceOf($entityAttribute, AttributeType::MANY2MANY);
            $isMany2One = $this->isInstanceOf($entityAttribute, AttributeType::MANY2ONE);

            if ($isMany2One) {
                if ($this->isNull($entityAttributeValue)) {
                    $data[$attr] = null;
                    continue;
                }
                if (self::$_currentLevel < $this->_nestedLevel) {
                    self::$_currentLevel++;
                    $attrDataExtractor = new EntityDataExtractor($entityAttributeValue, $this->_nestedLevel);
                    $data[$attr] = $attrDataExtractor->extractData($subAttributes);
                    self::$_currentLevel--;
                }
            } elseif ($isOne2Many || $isMany2Many) {
                $data[$attr] = [];
                foreach ($entityAttributeValue as $item) {
                    if (self::$_currentLevel < $this->_nestedLevel) {
                        self::$_currentLevel++;
                        $attrDataExtractor = new EntityDataExtractor($item, $this->_nestedLevel);
                        $data[$attr][] = $attrDataExtractor->extractData($subAttributes);
                        self::$_currentLevel--;
                    }
                }
            } else {
                $data[$attr] = $entityAttribute->getToArrayValue();
            }
        }
        self::$_loadedEntities->removeKey($checkKey);

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
    private function _buildEntityFields($fields)
    {
        if (!$this->isArray($fields)) {
            $fields = $this->str($fields)->explode(',')->filter()->map('trim')->val();
        } else {
            // Check if asterisk is present and replace it with actual attribute names
            if ($this->arr($fields)->keyExists('*')) {
                unset($fields['*']);
                $defaultFields = $this->str($this->_getDefaultAttributes())
                                      ->explode(',')
                                      ->filter()
                                      ->map('trim')
                                      ->flip()
                                      ->val();
                $fields = $this->arr($fields)->merge($defaultFields)->val();
            }

            return $fields;
        }
        $parsedFields = [];

        foreach ($fields as $f) {
            if ($f == '*') {
                $defaultFields = $this->str($this->_getDefaultAttributes())->explode(',')->filter()->map('trim')->val();
                foreach ($defaultFields as $df) {
                    $this->_buildFields($parsedFields, $df);
                }
                continue;
            }
            $this->_buildFields($parsedFields, $f);
        }

        return $parsedFields;
    }

    /**
     * Parse attribute key recursively
     *
     * @param $parsedFields Reference to array of parsed fields
     * @param $key          Current key to parse
     */
    private function _buildFields(&$parsedFields, $key)
    {
        if ($this->str($key)->contains('.')) {
            $parts = $this->str($key)->explode('.', 2)->val();
            if (!isset($parsedFields[$parts[0]])) {
                $parsedFields[$parts[0]] = [];
            }

            $this->_buildFields($parsedFields[$parts[0]], $parts[1]);
        } else {
            $parsedFields[$key] = '';
        }
    }

    /**
     * Get default list of entity attributes.<br>
     * Only simple and Many2One attributes are considered to be default attributes.
     *
     * @return string
     */
    private function _getDefaultAttributes()
    {
        $attributes = [];
        foreach ($this->_entity->getAttributes() as $name => $attribute) {
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