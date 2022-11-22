<?php  declare(strict_types=1);

namespace JTL\DataObjects;

use JTL\DataObjects\DataObjectInterface;
use ReflectionClass;
use ReflectionProperty;

abstract class GenericDataObject implements DataObjectInterface
{
    abstract public function getMapping(): array;

    abstract public function getReverseMapping(): array;



    public function hydrate($data, bool $useMapping = false): self
    {
        $attributeMap = $this->getMapping();
        foreach ($data as $attribute => $value) {
            if ($useMapping === true && in_array($attribute, $attributeMap, true)) {
                $attribute = $attributeMap[$attribute];
            }
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $attribute)));
            if (is_callable(array($this, $method))) {
                $this->$method($value);
            }
            if ($attribute === $this->getPrimaryKey() && (int)$value > 0) {
                $this->$attribute = $value;
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function extract(bool $useReverseMapping = false): array
    {
        $attributeMap = $this->getMapping();
        $reflect      = new ReflectionClass($this);
        $attributes   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
        $extracted    = [];
        foreach ($attributes as $attribute) {
            if ($useReverseMapping === true && in_array($attribut, $attributeMap, true)) {
                $attribut = $attributeMap[$attribut];
            }
            $method                      = 'get' . \ucfirst((string)$attribute->name);
            $extracted[$attribute->name] = $this->$method();
        }

        return $extracted;
    }

    public function hydrateWithObject($object, bool $useMapping = false): self
    {
        $reflect = new ReflectionClass($this);
        $props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
        foreach ($props as $prop) {
            $getMethod = 'get' . ucfirst((string)$prop->name);
            $setMethod = 'set' . ucfirst((string)$prop->name);
            if (method_exists($object, $getMethod)) {
                $this->$setMethod($object->$getMethod());
            }
        }

        return $this;
    }
}
