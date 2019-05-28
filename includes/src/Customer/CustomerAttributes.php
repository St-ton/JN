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
 * Class CustomerAttributes
 * @package JTL\Customer
 */
class CustomerAttributes implements ArrayAccess, IteratorAggregate, Countable
{
    /** @var CustomerAttribute[] */
    private $attributes = [];

    /** @var int */
    private $customerID = 0;

    /**
     * CustomerAttributes constructor.
     * @param int $customerID
     */
    public function __construct(int $customerID = 0)
    {
        if ($customerID > 0) {
            $this->load($customerID);
        }
    }

    /**
     * @param int $customerID
     * @return self
     */
    public function load(int $customerID): self
    {
        $this->attributes = [];
        $this->customerID = $customerID;

        foreach (Shop::Container()->getDB()->queryPrepared(
            'SELECT tkundenattribut.kKundenAttribut, COALESCE(tkundenattribut.kKunde, :customerID) kKunde,
                    tkundenfeld.kKundenfeld, tkundenfeld.cName, tkundenfeld.cWawi, tkundenattribut.cWert,
                    tkundenfeld.nSort,
                    IF(tkundenattribut.kKundenAttribut IS NULL, 1, tkundenfeld.nEditierbar) nEditierbar
                FROM tkundenfeld
                LEFT JOIN tkundenattribut ON tkundenattribut.kKunde = :customerID
                    AND tkundenattribut.kKundenfeld = tkundenfeld.kKundenfeld
                WHERE tkundenfeld.kSprache = :langID
                ORDER BY tkundenfeld.nSort, tkundenfeld.cName',
            [
                'customerID' => $customerID,
                'langID'     => Shop::getLanguageID(),
            ],
            ReturnType::ARRAY_OF_OBJECTS
        ) as $customerAttribute) {
            $this->attributes[$customerAttribute->kKundenfeld] = new CustomerAttribute($customerAttribute);
        }

        return $this;
    }

    /**
     * @return self
     */
    public function save(): self
    {
        $nonEditables = (new CustomerFields)->getNonEditableFields();
        $usedIDs      = [];

        /** @var CustomerAttribute $attribute */
        foreach ($this as $attribute) {
            if ($attribute->isEditable()) {
                $attribute->save();
                $usedIDs[] = $attribute->getID();
            } else {
                $this->attributes[$attribute->getCustomerFieldID()] = CustomerAttribute::load($attribute->getId());
            }
        }

        Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tkundenattribut
                WHERE kKunde = :customerID' . (\count($nonEditables) > 0
                ? ' AND kKundenfeld NOT IN (' . \implode(', ', $nonEditables) . ')' : '') . (\count($usedIDs) > 0
                ? ' AND kKundenAttribut NOT IN (' . \implode(', ', $usedIDs) . ')' : ''),
            [
                'customerID' => $this->customerID,
            ],
            ReturnType::DEFAULT
        );

        return $this;
    }

    /**
     * @param CustomerAttributes $customerAttributes
     * @return self
     */
    public function assign(CustomerAttributes $customerAttributes): self
    {
        $this->attributes = [];

        /** @var CustomerAttribute $customerAttribute */
        foreach ($customerAttributes as $customerAttribute) {
            $record                                 = $customerAttribute->getRecord();
            $this->attributes[$record->kKundenfeld] = new CustomerAttribute($record);
        }

        return $this->sort();
    }

    /**
     * @return self
     */
    public function sort(): self
    {
        \uasort($this->attributes, static function (CustomerAttribute $lft, CustomerAttribute $rgt): int {
            if ($lft->getOrder() === $rgt->getOrder()) {
                return \strcmp($lft->getName(), $rgt->getName());
            }

            return $lft->getOrder() < $rgt->getOrder() ? -1 : 1;
        });

        return $this;
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
        return new ArrayIterator($this->attributes);
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
        return \array_key_exists($offset, $this->attributes);
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
        if (!isset($this->attributes[$offset])) {
            return null;
        }

        if (!\is_a($this->attributes[$offset], CustomerAttribute::class)) {
            $this->attributes[$offset] = new CustomerAttribute($this->attributes[$offset]);
        }

        return $this->attributes[$offset];
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
        if (\is_a($value, CustomerAttribute::class)) {
            $this->attributes[$offset] = $value;
        } elseif (\is_object($value)) {
            $this->attributes[$offset] = new CustomerAttribute($value);
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
        unset($this->attributes[$offset]);
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
        return \count($this->attributes);
    }
}
