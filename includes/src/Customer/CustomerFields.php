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
    private $fields = [];

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
        $this->fields = [];
        $this->langID = $langID;

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
            $this->fields[$customerField->kKundenfeld] = new CustomerField($customerField);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getNonEditableFields(): array
    {
        $result = [];
        foreach ($this->fields as $field) {
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
        return new ArrayIterator($this->fields);
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
        return \array_key_exists($offset, $this->fields);
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
        if (!isset($this->fields[$offset])) {
            return null;
        }

        if (!\is_a($this->fields[$offset], CustomerField::class)) {
            $this->fields[$offset] = new CustomerField($this->fields[$offset]);
        }

        return $this->fields[$offset];
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
            $this->fields[$offset] = $value;
        } elseif (\is_object($value)) {
            $this->fields[$offset] = new CustomerField($value);
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
        unset($this->fields[$offset]);
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
        return \count($this->fields);
    }
}
