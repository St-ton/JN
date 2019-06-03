<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 * @package       jtl-shop
 * @since
 */

namespace JTL\Customer;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JTL\DB\ReturnType;
use JTL\Shop;
use Traversable;

/**
 * Class CustomerFields
 * @package JTL\Customer
 */
class CustomerFields implements ArrayAccess, IteratorAggregate, Countable
{
    /** @var CustomerField[] */
    private static $fields = [];

    /** @var int */
    private $langID = 0;

    /**
     * CustomerFields constructor.
     * @param int $langID
     */
    public function __construct(int $langID = 0)
    {
        if ($langID === 0) {
            $langID = Shop::getLanguageID();
        }

        if ($langID > 0) {
            $this->load($langID);
        }
    }

    public function load(int $langID): self
    {
        $this->langID = $langID;
        if (!isset(self::$fields[$langID])) {
            self::$fields[$langID] = [];

            foreach (Shop::Container()->getDB()->queryPrepared(
                'SELECT kKundenfeld, kSprache, cName, cWawi, cTyp, nSort, nPflicht, nEditierbar
                FROM tkundenfeld
                WHERE kSprache = :langID
                ORDER BY nSort',
                [
                    'langID' => $langID,
                ],
                ReturnType::ARRAY_OF_OBJECTS
            ) as $customerField) {
                self::$fields[$langID][$customerField->kKundenfeld] = new CustomerField($customerField);
            }
        }

        return $this;
    }

    /**
     * @return CustomerField[]
     */
    private function getFields(): array
    {
        return self::$fields[$this->langID] ?? [];
    }

    /**
     * @return array
     */
    public function getNonEditableFields(): array
    {
        $result = [];
        foreach ($this->getFields() as $field) {
            if (!$field->isEditable()) {
                $result[] = $field->getID();
            }
        }

        return $result;
    }

    /**
     * Retrieve an external iterator
     * @link  https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->getFields());
    }

    /**
     * Whether a offset exists
     * @link  https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     * @return boolean true on success or false on failure.
     *                      </p>
     *                      <p>
     *                      The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset): bool
    {
        return \array_key_exists($offset, $this->getFields());
    }

    /**
     * Offset to retrieve
     * @link  https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        $fields = $this->getFields();
        if (!isset($fields[$offset])) {
            return null;
        }

        if (!\is_a($fields[$offset], CustomerField::class)) {
            $fields[$offset] = new CustomerField($fields[$offset]);
        }

        return $fields[$offset];
    }

    /**
     * Offset to set
     * @link  https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value): void
    {
        if (\is_a($value, CustomerField::class)) {
            self::$fields[$this->langID][$offset] = $value;
        } elseif (\is_object($value)) {
            self::$fields[$this->langID][$offset] = new CustomerField($value);
        } else {
            throw new \InvalidArgumentException(
                self::class . '::' . __METHOD__ . ' - value must be an object, ' . \gettype($value) . ' given.'
            );
        }
    }

    /**
     * Offset to unset
     * @link  https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset): void
    {
        unset(self::$fields[$this->langID][$offset]);
    }

    /**
     * Count elements of an object
     * @link  https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count(): int
    {
        return \count($this->getFields());
    }

    /**
     * This method is called by var_dump() when dumping an object to get the properties that should be shown.
     * If the method isn't defined on an object, then all public, protected and private properties will be shown.
     * @return array
     * @since PHP 5.6.0
     *
     * @link  https://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.debuginfo
     */
    public function __debugInfo(): array
    {
        return [
            'langID' => $this->langID,
            'fields' => $this->getFields(),
        ];
    }
}
