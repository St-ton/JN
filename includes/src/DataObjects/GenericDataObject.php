<?php  declare(strict_types=1);

namespace JTL\DataObjects;

use JTL\DataObjects\DataObjectInterface;
use ReflectionClass;
use ReflectionProperty;

abstract class GenericDataObject implements DataObjectInterface
{
    abstract public function getMapping(): array;

    abstract public function getReverseMapping(): array;


    /**
     * will hydrate the DataObject with Data from an array
     * Keys may use mapped values
     * @param $data
     * @param bool $useMapping
     * @return $this
     */
    public function hydrate(array $data, bool $useMapping = false): self
    {
        $attributeMap = $this->getMapping();
        foreach ($data as $attribute => $value) {
            if ($useMapping === true && \is_array($attributeMap) && \in_array($attribute, $attributeMap, true)) {
                $attribute = $attributeMap[$attribute];
            }
            $method = 'set' . str_replace(' ', '', \ucwords(str_replace('_', ' ', $attribute)));
            if (\is_callable(array($this, $method))) {
                $this->$method($value);
            }
            if ((int)$value > 0 && $attribute === $this->getPrimaryKey()) {
                $this->$attribute = (int)$value;
            }
        }

        return $this;
    }

    /**
     * Will ship an array containing Keys and values of protected and public properties
     * @return array
     */
    public function toArray(): array
    {
        $reflect    = new ReflectionClass($this);
        $properties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
        $toArray    = [];
        foreach ($properties as $property) {
            $toArray[$property->getName()] = $property->getValue($this);
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
            if ($useReverseMapping === true && \in_array($attribute, $attributeMap, true)) {
                $attribut = $attributeMap[$attribut];
            }
            $method                      = 'get' . \ucfirst($attribute->name);
            $extracted[$attribute->name] = $this->$method();
        }

        return $extracted;
    }

    /**
     * Will accept data from an object.
     * @param $object
     * @param bool $useMapping
     * @return $this
     */
    public function hydrateWithObject($object): self
    {
        //Mapping has to be implemented later
        $reflect    = new ReflectionClass($this);
        $attributes = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
        foreach ($attributes as $attribut) {
            $getMethod = 'get' . \ucfirst($attribut->name);
            $setMethod = 'set' . \ucfirst($attribut->name);
            if (\method_exists($object, $getMethod)) {
                $this->$setMethod($object->$getMethod());
            } elseif (\property_exists($object, $attribut->name)) {
                $this->$setMethod($object->{$attribut->name});
            }
        }

        return $this;
    }
}
