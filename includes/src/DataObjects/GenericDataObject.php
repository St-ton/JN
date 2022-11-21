<?php  declare(strict_types=1);

namespace JTL\DataObjects;

use JTL\DataObjects\DataObjectInterface;
use ReflectionClass;
use ReflectionProperty;

abstract class GenericDataObject implements DataObjectInterface
{
    abstract public function getMapping();

    abstract public function getReverseMapping();

    public function hydrate($data, bool $useMapping = false): self
    {
        foreach ($data as $attribut => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $attribut)));
            if (is_callable(array($this, $method))) {
                $this->$method($value);
            }
            if ($attribut === $this->primaryKey && (int)$value > 0) {
                $this->$attribut = $value;
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
        $reflect   = new ReflectionClass($this);
        $props     = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
        $extracted = [];
        foreach ($props as $prop) {
            $method                   = 'get' . \ucfirst((string)$prop->name) . '()';
            $extracted[$prop['name']] = $this->$method;
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
