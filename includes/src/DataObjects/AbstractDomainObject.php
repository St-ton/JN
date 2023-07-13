<?php declare(strict_types=1);

namespace JTL\DataObjects;

use ReflectionClass;
use ReflectionProperty;

/**
 * Class AbstractDataObject
 * @package JTL\DataObjects
 */
abstract class AbstractDomainObject implements DataObjectInterface
{
    abstract public function getMapping(): array;

    abstract public function getColumnMapping(): array;

    abstract public function getReverseMapping(): array;

     /**
     * Will return an array containing keys and values of protected and public properties
     * $tableColumns = true will return an array using table column names as array keys
     *
     * @param bool $tableColumns
     * @return array
     */
    public function toArray(bool $tableColumns = true): array
    {
        $columnMap = [];
        if ($tableColumns) {
            $columnMap = $this->getColumnMapping();
        }
        $reflect        = new ReflectionClass($this);
        $properties     = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
        $toArray        = [];
        $primaryKeyName = \method_exists($this, 'getPrimaryKey') ? $this->getPrimaryKey() : null;
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            if (($propertyName === $primaryKeyName || $primaryKeyName === $columnMap[$propertyName])
                && (int)$property->getValue($this) === 0) {
                continue;
            }
            if ($tableColumns) {
                $propertyName = $columnMap[$propertyName];
            }
            $toArray[$propertyName] = $property->getValue($this);
        }

        return $toArray;
    }

    /**
     * $tableColumns = true will return an object using table column names as array keys
     *
     * @param bool $tableColumns
     * @return object
     */
    public function toObject(bool $tableColumns = true): object
    {
        return (object)$this->toArray($tableColumns);
    }

    /**
     * if $useReverseMapping is true the array returned will use mapped class properties
     * @param bool $useReverseMapping
     * @return array
     */
    public function extract(bool $useReverseMapping = false): array
    {
        $attributeMap = $this->getReverseMapping();
        $reflect      = new ReflectionClass($this);
        $attributes   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
        $extracted    = [];
        foreach ($attributes as $attribute) {
            if ($useReverseMapping === true && \array_key_exists($attribute->getName(), $attributeMap)) {
                $attribute = $attributeMap[$attribute->getName()];
            }
            $method                      = 'get' . \ucfirst($attribute->getName());
            $extracted[$attribute->name] = $this->$method();
        }

        return $extracted;
    }
}
