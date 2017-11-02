<?php
/**
 * @see https://github.com/illuminate/support/blob/master/Collection.php
 * @license MIT
 */

/**
 * Class Collection
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * @var array
     */
    public $items = [];

    /**
     * Collection constructor
     * @param mixed $items
     */
    public function __construct($items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * @param mixed $item
     * @return $this
     */
    public function addItem($item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Results array of items from Collection or Arrayable
     *
     * @param  mixed $items
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        }
        if ($items instanceof self) {
            return $items->getItems();
        }
        if ($items instanceof JsonSerializable) {
            return $items->jsonSerialize();
        }
        if ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array)$items;
    }

    /**
     * Convert the object into something JSON serializable
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            }

            return $value;
        }, $this->items);
    }

    /**
     * Reduce the collection to a single value
     *
     * @param  callable $callback
     * @param  mixed    $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Get the sum of the given values
     *
     * @param  callable|string|null $callback
     * @return mixed
     */
    public function sum($callback = null)
    {
        if ($callback === null) {
            return array_sum($this->items);
        }
        $callback = $this->valueRetriever($callback);

        return $this->reduce(function ($result, $item) use ($callback) {
            return $result + $callback($item);
        }, 0);
    }

    /**
     * Get a value retrieving callback
     *
     * @param  string $value
     * @return callable
     */
    protected function valueRetriever($value)
    {
        if ($this->useAsCallable($value)) {
            return $value;
        }

        return function ($item) use ($value) {
            return $this->data_get($item, $value);
        };
    }

    /**
     * Get an item from an array or object using "dot" notation
     *
     * @param  mixed        $target
     * @param  string|array $key
     * @param  mixed        $default
     * @return mixed
     */
    protected function data_get($target, $key, $default = null)
    {
        if ($key === null) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (($segment = array_shift($key)) !== null) {
            if ($segment === '*') {
                if ($target instanceof self) {
                    $target = $target->getItems();
                } elseif (!is_array($target)) {
                    return $default instanceof Closure ? $default() : $default;
                }

                $result = Arr::pluck($target, $key);

                return in_array('*', $key, true) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return $default instanceof Closure ? $default() : $default;
            }
        }

        return $target;
    }

    /**
     * Get the values of a given key
     *
     * @param  string|array $value
     * @param  string|null  $key
     * @return static
     */
    public function pluck($value, $key = null)
    {
        return new static(Arr::pluck($this->items, $value, $key));
    }

    /**
     * Determine if the given value is callable, but not a string
     *
     * @param  mixed $value
     * @return bool
     */
    protected function useAsCallable($value)
    {
        return !is_string($value) && is_callable($value);
    }

    /**
     * Get the average value of a given key
     *
     * @param  callable|string|null $callback
     * @return int|null
     */
    public function avg($callback = null)
    {
        if ($count = $this->count()) {
            return $this->sum($callback) / $count;
        }

        return null;
    }

    /**
     * map a callback function to every item
     *
     * @param  callable $callback
     * @return static
     */
    public function map(callable $callback)
    {
        return new static(array_map($callback, $this->items));
    }

    /**
     * Transform each item in the collection using a callback
     *
     * @param  callable $callback
     * @return $this
     */
    public function transform(callable $callback)
    {
        $this->items = $this->map($callback)->getItems();

        return $this;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback)
    {
        return new static(array_filter($this->items, $callback));
    }

    /**
     * Execute a callback over each item
     *
     * @param  callable $callback
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Get an item from the collection by key name
     *
     * @param  mixed $key
     * @param  mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->items[$key];
        }

        return $default;
    }

    /**
     * Determine if an item exists in the collection by key name
     *
     * @param  mixed $key
     * @return bool
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Get all items from the collection
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get an iterator for the collection
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Count the number of items in the collection
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Sort through each item with a callback
     *
     * @param  callable|null $callback
     * @return static
     */
    public function sort(callable $callback = null)
    {
        $items = $this->items;
        $callback
            ? uasort($items, $callback)
            : asort($items);

        return new static($items);
    }

    /**
     * Sort the collection using the given callback.
     *
     * @param  callable|string $callback
     * @param  int             $options
     * @param  bool            $descending
     * @return static
     */
    public function sortBy($callback, $options = SORT_REGULAR, $descending = false)
    {
        $results  = [];
        $callback = $this->valueRetriever($callback);
        // First we will loop through the items and get the comparator from a callback
        // function which we were given. Then, we will sort the returned values and
        // and grab the corresponding values for the sorted keys from this array.
        foreach ($this->items as $key => $value) {
            $results[$key] = $callback($value, $key);
        }
        $descending ? arsort($results, $options)
            : asort($results, $options);
        // Once we have sorted all of the keys in the array, we will loop through them
        // and grab the corresponding model so we can set the underlying items list
        // to the sorted version. Then we'll just return the collection instance.
        foreach (array_keys($results) as $key) {
            $results[$key] = $this->items[$key];
        }

        return new static($results);
    }

    /**
     * Sort the collection in descending order using the given callback.
     *
     * @param  callable|string $callback
     * @param  int             $options
     * @return static
     */
    public function sortByDesc($callback, $options = SORT_REGULAR)
    {
        return $this->sortBy($callback, $options, true);
    }

    /**
     * Create a new collection instance if the value isn't one already
     *
     * @param  mixed $items
     * @return static
     */
    public static function make($items = [])
    {
        return new static($items);
    }

    /**
     * Determine if an item exists at an offset
     *
     * @param  mixed $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get an item at a given offset
     *
     * @param  mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if ($key === null) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset
     *
     * @param  string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }
}
