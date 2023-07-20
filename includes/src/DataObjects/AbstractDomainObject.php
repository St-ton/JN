<?php declare(strict_types=1);

namespace JTL\DataObjects;

use ReflectionClass;
use ReflectionProperty;

/**
 * Class AbstractDataObject
 * @package JTL\DataObjects
 */
abstract readonly class AbstractDomainObject implements DomainObjectInterface
{

    /**
     * @var array
     */
    private array $possibleBoolValues;


    public function __construct()
    {
        $this->possibleBoolValues = [
            'true'  => true,
            'y'     => true,
            'yes'   => true,
            'ja'    => true,
            '1'     => true,
            'false' => false,
            'n'     => false,
            'no'    => false,
            'nein'  => false,
            '0'     => false,
        ];
    }

    /**
     * @param bool|int|string $value
     * @return bool
     */
    protected function checkAndReturnBoolValue(bool|int|string $value = 0): bool
    {
        $value = \strtolower((string)$value);
        if (!\array_key_exists($value, $this->possibleBoolValues)) {
            return false;
        }

        return $this->possibleBoolValues[$value];
    }

    /**
     * Will return an array containing keys and values of protected and public properties
     * $tableColumns = true will return an array using table column names as array keys
     *
     * @return array
     */
    public function toArray(): array
    {
        $reflect    = new ReflectionClass($this);
        $properties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
        $toArray    = [];
        foreach ($properties as $property) {
            $propertyName           = $property->getName();
            $toArray[$propertyName] = $property->getValue($this);
        }

        return $toArray;
    }

    /**
     * $tableColumns = true will return an object using table column names as array keys
     *
     * @return object
     */
    public function toObject(): object
    {
        return (object)$this->toArray();
    }

    /**
     * if $useReverseMapping is true the array returned will use mapped class properties
     * @return array
     */
    public function extract(): array
    {
        $reflect    = new ReflectionClass($this);
        $attributes = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
        $extracted  = [];
        foreach ($attributes as $attribute) {
            $method                      = 'get' . \ucfirst($attribute->getName());
            $extracted[$attribute->name] = $this->$method();
        }

        return $extracted;
    }
}
