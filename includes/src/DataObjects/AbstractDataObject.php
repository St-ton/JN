<?php declare(strict_types=1);

namespace JTL\DataObjects;

use ReflectionClass;
use ReflectionProperty;

/**
 * Class AbstractDataObject
 * @package JTL\DataObjects
 */
abstract class AbstractDataObject implements DataObjectInterface
{
    abstract public function getMapping(): array;

    abstract public function getReverseMapping(): array;

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $map = $this->getMapping();

        if (isset($map[$name])) {
            $method = 'set' . \str_replace(' ', '', \ucwords(\str_replace('_', ' ', $map[$name])));
            $this->$method($value);
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        $map = $this->getMapping();

        if (isset($map[$name])) {
            $prop = $map[$name];

            return $this->$prop;
        }

        return null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->$name);
    }

    /**
     * @param string $name
     * @return void
     */
    public function __unset(string $name): void
    {
        unset($this->$name);
    }

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
        }

        return $this;
    }

    /**
     * Will accept data from an object.
     * @param object $object
     * @return $this
     */
    public function hydrateWithObject(object $object): self
    {
        $attributeMap     = $this->getMapping();
        $objectAttributes = \get_object_vars($object);
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

    /**
     * Will ship an array containing Keys and values of protected and public properties
     * $tableColumns = true will ship an array using table column names as array keys
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
     * $tableColumns = true will ship an object using table column names as array keys
     *
     * @param bool $tableColumns
     * @return object
     */
    public function toObject(bool $tableColumns = true): object
    {
        return (object)$this->toArray($tableColumns);
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
            $method                      = 'get' . \ucfirst($attribute->getName());
            $extracted[$attribute->name] = $this->$method();
        }

        return $extracted;
    }
}
