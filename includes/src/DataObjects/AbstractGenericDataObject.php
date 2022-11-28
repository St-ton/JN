<?php declare(strict_types=1);

namespace JTL\DataObjects;

use ReflectionClass;
use ReflectionProperty;

abstract class AbstractGenericDataObject implements DataObjectInterface
{
    abstract public function getMapping(): array;

    abstract public function getReverseMapping(): array;

    abstract public function getColumnMapping(): array;

    /**
     * will hydrate the DataObject with Data from an array
     * Keys may use mapped values
     * @param array $data
     * @return $this
     */
    public function hydrate(array $data): self
    {
        $attributeMap = $this->getMapping();
        foreach ($data as $attribute => $value) {
            if (\is_array($attributeMap) && \array_key_exists($attribute, $attributeMap)) {
                $attribute = $attributeMap[$attribute];
            }
            $method = 'set' . \str_replace(' ', '', \ucwords(\str_replace('_', ' ', $attribute)));
            if (\is_callable([$this, $method])) {
                $this->$method($value);
            }
            if ($attribute === $this->getPrimaryKey() && (int)$value > 0) {
                $this->$attribute = (int)$value;
            }
        }

        return $this;
    }

    /**
     * Will ship an array containing Keys and values of protected and public properties
     * @param bool $tableColumns
     * @return array
     */
    public function toArray(bool $tableColumns = true): array
    {
        if ($tableColumns) {
            $columnMap = $this->getColumnMapping();
        }
        $reflect    = new ReflectionClass($this);
        $properties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
        $toArray    = [];
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            if ($tableColumns) {
                $propertyName = $columnMap[$propertyName];
            }
            $toArray[$propertyName] = $property->getValue($this);
        }

        return $toArray;
    }

    /**
     * if $useReverseMapping is true the array shipped will use mapped class properties
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
            $method                      = 'get' . \ucfirst($attribute->name);
            $extracted[$attribute->name] = $this->$method();
        }

        return $extracted;
    }

    /**
     * Will accept data from an object.
     * @param $object
     * @return $this
     */
    public function hydrateWithObject($object): self
    {
        $attributeMap     = $this->getMapping();
        $objectAttributes = get_object_vars($object);
        foreach ($objectAttributes as $name => $attribute) {
            $propertyName = $name;
            if (\array_key_exists($name, $attributeMap)) {
                $propertyName = $attributeMap[$name];
            }
            $setMethod = 'set' . \ucfirst($propertyName);
            if (\method_exists($this, $setMethod)) {
                $this->$setMethod($object->{$name});
            }
        }

        return $this;
    }
}
