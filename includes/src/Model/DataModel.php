<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Model;

use Exception;
use function Functional\select;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Iterator;
use JTL\DB\DbInterface;
use JTL\Shop;
use stdClass;

/**
 * Class DataModel
 * @package JTL\Model
 */
abstract class DataModel implements DataModelInterface, Iterator
{
    /**
     * @var array
     * Stores the property values
     */
    protected $members = [];

    /**
     * @var callable[]
     * List of setting handlers
     */
    protected $setters = [];

    /**
     * @var callable[]
     * List of getting handlers
     */
    protected $getters = [];

    /**
     * true when loaded from database
     * @var bool
     */
    protected $loaded = false;

    /**
     * @var array
     * Stores keynames for iterator interface
     */
    protected $iteratorKeys;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @inheritDoc
     */
    public function getDB(): ?DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritDoc
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * DataModel constructor.
     * @param null|array|object $attributes
     * @param DbInterface|null  $db
     */
    public function __construct($attributes = null, DbInterface $db = null)
    {
        $this->prepare($db);
        if ($attributes !== null) {
            $this->fill($attributes);
            if ($db !== null) {
                $this->init((array)$attributes);
            }
            $this->onInstanciation();
        } else {
            $this->fabricate();
        }
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return select(\array_keys(\get_object_vars($this)), function ($e) {
            return $e !== 'getters' && $e !== 'db' && $e !== 'setters';
        });
    }

    public function __wakeup()
    {
        $this->onRegisterHandlers();
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $attribute = \lcfirst(\substr($name, 3));
        if (\array_key_exists($attribute, $this->members)) {
            if (\strpos($name, 'get') === 0) {
                return $this->$attribute;
            }
            if (\strpos($name, 'set') === 0) {
                $this->$attribute = $arguments[0];

                return null;
            }
        }
        throw new InvalidArgumentException('Call to undefined method ' . $name);
    }

    /**
     * @param DbInterface|null $db
     */
    public function prepare(DbInterface $db = null): void
    {
        $this->db           = $db;
        $this->iteratorKeys = \array_keys($this->getAttributes());
        $this->onRegisterHandlers();
    }

    /**
     * @param null|array|object $attributes
     * @param DbInterface|null  $db
     * @return static
     */
    public static function newInstance($attributes = null, DbInterface $db = null): self
    {
        return new static($attributes, $db);
    }

    /**
     * @param string $name - name of the property
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getAttribValue($name);
    }

    /**
     * @param string $name - name of the property
     * @param mixed  $value - value of the poperty
     * @return void
     */
    public function __set($name, $value)
    {
        $this->setAttribValue($name, $value);
    }

    /**
     * @param string $name - name of the property
     * @return bool
     */
    public function __isset($name)
    {
        $attributes = $this->getAttributes();

        return (\array_key_exists($name, $attributes) && $this->getAttribValue($name) !== null)
            || ($this->hasMapping($name) && $this->getAttribValue($this->getMapping($name)) !== null);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $result = [];

        foreach ($this->iteratorKeys as $key) {
            $result[$key] = $this->$key;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public static function create($attributes, DbInterface $db, $option = self::NONE): DataModelInterface
    {
        $instance = static::newInstance($attributes, $db);
        $pkValue  = $db->insert($instance->getTableName(), $instance->getSqlObject());
        if (!empty($pkValue)) {
            $instance->setKey($pkValue);
        } elseif ($option === self::ON_EXISTS_UPDATE) {
            $instance->save();
        } elseif ($option === self::ON_INSERT_IGNORE) {
            return $instance;
        } else {
            throw new Exception(__METHOD__ . ': SQL error', self::ERR_DATABASE);
        }

        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function init(array $attributes, $option = self::NONE): DataModelInterface
    {
        try {
            $this->fill($attributes);
            $record = $this->db->select($this->getTableName(), $this->getKeyName(true), $this->getKey());
        } catch (Exception $e) {
            if ($e->getCode() === self::ERR_NO_PRIMARY_KEY) {
                $attribs = $this->getAttributes();
                $conds   = [];
                foreach ($attributes as $key => $value) {
                    $conds[$attribs[$this->getMapping($key)]->name] = $value;
                }
                $record = $this->db->select($this->getTableName(), \array_keys($conds), \array_values($conds));
            } else {
                throw $e;
            }
        }
        if (isset($record)) {
            $this->loaded = true;
        } else {
            switch ($option) {
                case self::ON_NOTEXISTS_NEW:
                    return $this;
                case self::ON_NOTEXISTS_CREATE:
                    return static::create($this, $this->db);
                default:
                    throw new Exception(__METHOD__ . ': No Data Found', self::ERR_NOT_FOUND);
            }
        }

        return $this->fill($record);
    }

    /**
     * @inheritDoc
     */
    public static function load($attributes, DbInterface $db, $option = self::ON_NOTEXISTS_NEW): DataModelInterface
    {
        $instance = static::newInstance($attributes, $db);

        try {
            $record = $db->select($instance->getTableName(), $instance->getKeyName(true), $instance->getKey());
        } catch (Exception $e) {
            if ($e->getCode() === self::ERR_NO_PRIMARY_KEY) {
                $attribs = $instance->getAttributes();
                $conds   = [];
                foreach ($attributes as $key => $value) {
                    $conds[$attribs[$instance->getMapping($key)]->name] = $value;
                }
                $record = $db->select($instance->getTableName(), \array_keys($conds), \array_values($conds));
            } else {
                throw $e;
            }
        }
        if (isset($record)) {
            $instance->loaded = true;
        } else {
            switch ($option) {
                case self::ON_NOTEXISTS_NEW:
                    return $instance;
                case self::ON_NOTEXISTS_CREATE:
                    return static::create($attributes, $instance->getDB());
                default:
                    throw new Exception(__METHOD__ . ': No Data Found', self::ERR_NOT_FOUND);
            }
        }

        return $instance->fill($record);
    }

    /**
     * @inheritDoc
     */
    public static function loadByAttributes(
        $attributes,
        DbInterface $db,
        $option = self::ON_NOTEXISTS_NEW
    ): DataModelInterface {
        $instance = static::newInstance($attributes);
        $instance->setDB($db);
        $attribs = $instance->getAttributes();
        $conds   = [];
        foreach ($attributes as $key => $value) {
            $mapped = $attribs[$key]->name ?? null;
            if ($mapped !== null) {
                $conds[$mapped] = $value;
            }
        }
        try {
            $record = $db->select($instance->getTableName(), \array_keys($conds), \array_values($conds));
        } catch (Exception $e) {
            if ($e->getCode() === self::ERR_NO_PRIMARY_KEY) {
                $attribs = $instance->getAttributes();
                $conds   = [];
                foreach ($attributes as $key => $value) {
                    $conds[$attribs[$instance->getMapping($key)]->name] = $value;
                }

                $record = $db->select($instance->getTableName(), \array_keys($conds), \array_values($conds));
            } else {
                throw $e;
            }
        }
        if (isset($record)) {
            $instance->loaded = true;
        } else {
            switch ($option) {
                case self::ON_NOTEXISTS_NEW:
                    return $instance;
                case self::ON_NOTEXISTS_CREATE:
                    return static::create($attributes, $db);
                default:
                    throw new Exception(__METHOD__ . ': No Data Found', self::ERR_NOT_FOUND);
            }
        }
        $instance->fill($record);
        $instance->onInstanciation();

        return $instance;
    }

    /**
     * @inheritDoc
     */
    public static function loadAll(DbInterface $db, $key, $value): Collection
    {
        $instance = static::newInstance(null, $db);
        $class    = static::class;

        return \collect($db->selectAll($instance->getTableName(), $key, $value))
            ->map(function ($value) use ($class) {
                return new $class($value);
            })
            ->each(function (DataModelInterface $e) {
                $e->wasLoaded();
            });
    }

    /**
     * Cast given value to value of given type
     *
     * @param mixed  $value - the value to cast for
     * @param string $type - the type to cast to, supported types are:
     *      - bool,boolean: will be cast to bool
     *      - int,tinyint,smallint,mediumint,integer,bigint,decimal,dec: will be cast to int
     *      - float,double: will be cast to float
     *      - string,date,time,year,datetime,timestamp,char,varchar,tinytext,text,mediumtext,enum: will be cast to
     *     string
     * @return mixed
     * @throws Exception - throws an exception with ERR_INVALID_PARAM if type is not a supported datatype
     */
    protected static function cast($value, $type)
    {
        $result = null;
        $type   = \strtolower($type);
        if (\strpos($type, 'app\\models\\') !== false || \strpos($type, 'jtl\\') === 0) {
            return $value;
        }

        $typeMap = [
            'bool|boolean',
            'int|tinyint|smallint|mediumint|integer|bigint|decimal|dec',
            'float|double',
            'string|date|time|year|datetime|timestamp|char|varchar|tinytext|text|mediumtext|enum',
        ];
        $phpType = \array_reduce($typeMap, function ($carry, $item) use ($type) {
            if (!isset($carry) && \preg_match("/{$item}/", $type)) {
                $carry = \explode('|', $item, 2)[0];
            }

            return $carry;
        });

        switch ($phpType) {
            case 'bool':
                $result = (bool)$value;
                break;
            case 'int':
                if (\is_numeric($value)) {
                    $result = (int)$value;
                }
                break;
            case 'float':
                if (\is_numeric($value)) {
                    $result = (float)$value;
                }
                break;
            case 'string':
                if (\is_scalar($value)) {
                    $result = (string)$value;
                }
                break;
            default:
                throw new Exception(__METHOD__ . ': unsupported data type(' . $type . ')', self::ERR_INVALID_PARAM);
        }

        return $result;
    }

    protected function onInstanciation(): void
    {
    }

    /**
     * This method can be overridden to register handlers for getter and/or setter or to add iteratorKeys
     */
    protected function onRegisterHandlers(): void
    {
    }

    protected function onBeforeInsert(): void
    {
    }

    protected function onBeforeUpdate(): void
    {
    }

    /**
     * Set a getter method for an attribute of this model and return model itself.
     * A good place to use this function is {@link onRegisterHandlers}.
     *
     * @param string   $attribName - name of the attribute the handler fired for
     * @param callable $getter - mixed function (mixed $value, mixed $default)
     *      - the specified callable gets two parameters - the internal value and a default value
     *      - the type of this parameters correspond to the type of the linked attribute
     *      - the handler can return a value of the type the public property will be have
     *      - for example: a getter for a datetime-field gets two string parameters and returns a DateTime-Object
     * @return static
     */
    protected function registerGetter($attribName, callable $getter): self
    {
        if (\is_callable($getter)) {
            $this->getters[$this->getMapping($attribName)] = $getter;
        }

        return $this;
    }

    /**
     * Set a setter method for an attribute of this model and return model itself.
     * A good place to use this function is {@link onRegisterHandlers}.
     *
     * @param string   $attribName - name of the attribute the handler fired for
     * @param callable $setter - mixed function (mixed $value)
     *      - the specified callable gets one parameter - the public value
     *      - the type of this value is the same as the return type of the corresponding getter handler
     *      - the handler should return a value of the type of the linked attribute
     *      - for example: a setter for a datetime-field gets a DateTime-Object as parameter and returns a string
     * @return static
     */
    protected function registerSetter($attribName, callable $setter): self
    {
        if (\is_callable($setter)) {
            $this->setters[$this->getMapping($attribName)] = $setter;
        }

        return $this;
    }

    /**
     * Add a key to the internal iterator array - this will be used to iterate over all public propertys of this model.
     * Basically the DataModel will only iterate over database attributes. If a persistent class property is defined in
     * descendents, this property must be added if it should be used for iteration. A good place to use this function
     * is {@link onRegisterHandlers}.
     *
     * @param string $keyName - the property/key to add to the list of iteratorkeys
     */
    protected function addIteratorKey($keyName): void
    {
        if (!\in_array($keyName, $this->iteratorKeys, true)) {
            $this->iteratorKeys[] = $keyName;
            \reset($this->iteratorKeys);
        }
    }

    /**
     * @inheritDoc
     */
    public function fill($attributes): DataModelInterface
    {
        if (\is_array($attributes)) {
            $attributes = (object)$attributes;
        }

        if (!\is_object($attributes)) {
            throw new Exception(__METHOD__ . ': invalid param', self::ERR_INVALID_PARAM);
        }
        foreach ($this->getAttributes() as $attribute) {
            $attribName = $attribute->name;
            if (\property_exists($attributes, $attribName)) {
                $this->setAttribValue($attribName, $attributes->$attribName);
            } elseif (\property_exists($attributes, $this->getMapping($attribName))) {
                $attribName = $this->getMapping($attribName);
                $this->setAttribValue($attribName, $attributes->$attribName);
            } elseif ($this->db !== null && $attribute->foreignKey !== null && \class_exists($attribute->dataType)) {
                $key       = $attribute->foreignKey;
                $className = $attribute->dataType;
                $value     = $className::loadAll($this->db, $attribute->foreignKeyChild ?? $key, $this->$key);
                if (isset($this->setters[$attribName])) {
                    $value = \call_user_func($this->setters[$attribName], $value, $this);
                }
                $this->members[$attribName] = $value;
            } else {
                $this->setAttribValue($attribName, $attribute->default);
            }
        }

        return $this;
    }

    /**
     * Produces an empty set of attribute values and return the model itself.
     * This will use the defined database attributes to fill property values with defaults.
     *
     * @return static
     */
    protected function fabricate(): self
    {
        foreach ($this->getAttributes() as $attribute) {
            $this->setAttribValue($attribute->name, $attribute->default);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function save(array $partial = null): bool
    {
        $noPrimaryKey = false;
        $keyValue     = null;
        $keyName      = null;
        $members      = $this->getSqlObject();
        $allKeyNames  = $this->getAllKeyNames(true);

        try {
            $keyValue = $this->getKey();
            $keyName  = $this->getKeyName(true);
            if (\count($allKeyNames) === 1) {
                unset($members->$keyName);
            }
        } catch (Exception $e) {
            if ($e->getCode() === self::ERR_NO_PRIMARY_KEY) {
                $noPrimaryKey = true;
            } else {
                throw $e;
            }
        }

        if (\is_array($partial) && \count($partial)) {
            foreach ($this->getAttributes() as $attributeName => $attribute) {
                if (!\in_array($attributeName, $partial, true) && !\in_array($attribute->name, $partial, true)) {
                    $memberName = $attribute->name;
                    unset($members->$memberName);
                }
            }
        }
        $definedMembers = \array_keys(\get_object_vars($members));
        foreach ($this->getAttributes() as $attributeName => $attribute) {
            if ($attribute->foreignKey !== null || $attribute->foreignKeyChild !== null) {
                continue;
            }
            $mapping = $attribute->name;
            if ($attribute->nullable === true
                && \in_array($mapping, $definedMembers, true)
                && $members->$mapping === null
            ) {
                $members->$mapping = '_DBNULL_';
            }
        }
        if (!$this->loaded || $noPrimaryKey || $keyValue === null) {
            $pkValue = $this->db->insert($this->getTableName(), $members);
            $this->updateChildModels();

            if (!($noPrimaryKey || empty($pkValue))) {
                $this->setKey($pkValue);

                return true;
            }

            return false;
        }
        // hack to allow updating tables like "tkategoriesprache" where no single primary key is present
        if (\count($allKeyNames) > 1) {
            $keyValue = null;
            $keyName  = [];
            foreach ($allKeyNames as $name) {
                $keyName[$name] = (int)$this->getAttribValue($name);
            }
        }
        $res = $this->db->update($this->getTableName(), $keyName, $keyValue, $members) >= 0;
        $this->updateChildModels();

        return $res;
    }

    /**
     * @inheritDoc
     */
    public function delete(): bool
    {
        try {
            $this->deleteChildModels();
            $result = $this->db->delete($this->getTableName(), $this->getKeyName(true), $this->getKey()) > 0;
        } catch (Exception $e) {
            if ($e->getCode() === self::ERR_NO_PRIMARY_KEY) {
                $keys   = [];
                $values = [];
                foreach ($this->rawArray() as $key => $value) {
                    if ($value !== null) {
                        $keys[]   = $key . '=?';
                        $values[] = $value;
                    }
                }
                $pdo = $this->db->getPDO();
                $s   = $pdo->prepare('DELETE FROM ' . $this->getTableName() . ' WHERE ' . \implode(' AND ', $keys));
                $s->execute($values);
                $result = $s->rowCount();
            } else {
                throw $e;
            }
        }

        return $result === true || $result > 0;
    }

    /**
     * @inheritDoc
     */
    public function reload()
    {
        $record = $this->db->select($this->getTableName(), $this->getKeyName(true), $this->getKey());

        if (!isset($record)) {
            throw new Exception(__METHOD__ . ': No Data Found', self::ERR_NOT_FOUND);
        }

        return $this->fill($record);
    }

    /**
     * @inheritDoc
     */
    public function getMapping($attribName): string
    {
        static $nameMapping = [];

        if (!isset($nameMapping[$attribName])) {
            foreach ($this->getAttributes() as $name => $attribute) {
                if ($attribute->name === $attribName) {
                    $nameMapping[$attribName] = $name;
                    break;
                }
            }
        }

        return $nameMapping[$attribName] ?? $attribName;
    }

    /**
     * @param string $attribName
     * @return bool
     */
    private function hasMapping(string $attribName): bool
    {
        static $mapping = [];

        if (!isset($mapping[$attribName])) {
            foreach ($this->getAttributes() as $name => $attribute) {
                if ($attribute->name === $attribName) {
                    $mapping[$attribName] = $name;
                    break;
                }
            }
        }

        return isset($mapping[$attribName]);
    }

    /**
     * @inheritDoc
     */
    public function getKey(): int
    {
        return (int)$this->getAttribValue($this->getKeyName());
    }

    /**
     * @inheritDoc
     */
    public function setKey($value)
    {
        $this->setAttribValue($this->getKeyName(), $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getKeyName(bool $realName = false): string
    {
        static $keyName = null;

        if (!isset($keyName)) {
            foreach ($this->getAttributes() as $attribute) {
                if ($attribute->isPrimaryKey) {
                    $keyName = $attribute->name;
                    break;
                }
            }
        }

        if (!isset($keyName)) {
            throw new Exception(__METHOD__ . ': no primary key exists', self::ERR_NO_PRIMARY_KEY);
        }

        return $realName ? $keyName : $this->getMapping($keyName);
    }

    /**
     * @inheritDoc
     */
    public function getAllKeyNames(bool $realName = false): array
    {
        static $keyNames = null;

        if ($keyNames === null) {
            foreach ($this->getAttributes() as $attribute) {
                if ($attribute->isPrimaryKey) {
                    $keyNames[] = $attribute->name;
                }
            }
        }

        if ($keyNames === null) {
            throw new Exception(__METHOD__ . ': no primary key exists', self::ERR_NO_PRIMARY_KEY);
        }

        return $realName ? $keyNames : \array_map([$this, 'getMapping'], $keyNames);
    }

    /**
     * @inheritDoc
     */
    public function getAttribValue($attribName, $default = null)
    {
        $attribName = $this->getMapping($attribName);

        if (!\array_key_exists($attribName, $this->members)) {
            if (\array_key_exists($attribName, $this->getAttributes())) {
                return $default;
            }
            throw new Exception(__METHOD__ . ': invalid attribute(' . $attribName . ')', self::ERR_INVALID_PARAM);
        }

        if (isset($this->getters[$attribName])) {
            return \call_user_func($this->getters[$attribName], $this->members[$attribName], $default);
        }

        return $this->members[$attribName];
    }

    /**
     * @inheritDoc
     */
    public function setAttribValue($attribName, $value)
    {
        $attribName = $this->getMapping($attribName);
        $attributes = $this->getAttributes();

        if (!\array_key_exists($attribName, $attributes)) {
            throw new Exception(__METHOD__ . ': invalid attribute(' . $attribName . ')', self::ERR_INVALID_PARAM);
        }
        if (isset($this->setters[$attribName])) {
            $this->members[$attribName] = self::cast(
                \call_user_func($this->setters[$attribName], $value, $this),
                $attributes[$attribName]->dataType
            );
        } else {
            $this->members[$attribName] = self::cast($value, $attributes[$attribName]->dataType);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function rawJSON(int $options = 0, bool $iterated = false)
    {
        return \json_encode($this->rawObject($iterated), $options);
    }

    /**
     * @inheritDoc
     */
    public function rawArray(bool $iterated = false): array
    {
        $result = [];
        if ($iterated) {
            foreach ($this as $member => $value) {
                if (\is_a($value, Collection::class)) {
                    $value = $value->map(function (DataModelInterface $e) {
                        return $e->rawArray(true);
                    })->toArray();
                } elseif ($value instanceof DataModelInterface) {
                    $value = $value->rawArray(true);
                }
                $result[$member] = $value;
            }
        } else {
            foreach ($this->getAttributes() as $name => $attribute) {
                $result[$attribute->name] = $this->members[$name];
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function rawObject(bool $iterated = false): stdClass
    {
        $result = new stdClass();
        if ($iterated) {
            foreach ($this as $member => $value) {
                $result->$member = $value;
            }
        } else {
            foreach ($this->getAttributes() as $name => $attribute) {
                $member          = $attribute->name;
                $result->$member = $this->members[$name];
            }
        }

        return $result;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function updateFromRequest(Request $request): self
    {
        foreach (\array_keys($this->getAttributes()) as $attr) {
            if ($request->has($attr)) {
                $value       = $request->get($attr);
                $this->$attr = $value;
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSqlObject(): stdClass
    {
        $result = new stdClass();
        foreach ($this->getAttributes() as $name => $attr) {
            if ($attr->foreignKey !== null || $attr->foreignKeyChild !== null || $attr->dynamic === true) {
                // do not add child relations to sql statement
                continue;
            }
            $member          = $attr->name;
            $result->$member = $this->members[$name];
        }

        return $result;
    }

    /**
     * @return $this
     */
    protected function updateChildModels(): self
    {
        foreach ($this->getChildModels() as $childModel) {
            if (\is_a($childModel, Collection::class)) {
                $childModel->each(function (DataModelInterface $model) {
                    $model->setDB($this->db);
                    $model->save();
                });
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteChildModels(): self
    {
        foreach ($this->getChildModels() as $childModel) {
            if (\is_a($childModel, Collection::class)) {
                $childModel->each(function (DataModelInterface $model) {
                    $model->setDB($this->db);
                    $model->delete();
                });
            } elseif ($childModel instanceof DataModelInterface) {
                $childModel->setDB($this->db);
                $childModel->delete();
            }
        }

        return $this;
    }

    /**
     * @return DataModelInterface[]
     */
    protected function getChildModels(): array
    {
        $result = [];
        foreach ($this->getAttributes() as $name => $attribute) {
            if ($attribute->foreignKey !== null || $attribute->foreignKeyChild !== null) {
                $result[] = $this->members[$name];
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function replicate(array $except = null)
    {
        $members = $this->rawObject();
        if (\is_array($except) && \count($except)) {
            foreach ($this->getAttributes() as $attributeName => $attribute) {
                if (\in_array($attributeName, $except, true) || \in_array($attribute->name, $except, true)) {
                    $memberName = $attribute->name;
                    unset($members->$memberName);
                }
            }
        }

        return static::newInstance($members, $this->db);
    }

    /**
     * @inheritDoc
     */
    public function wasLoaded(): void
    {
        $this->loaded = true;
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        $key = \current($this->iteratorKeys);

        return $this->$key;
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        \next($this->iteratorKeys);
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return \current($this->iteratorKeys);
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return \key($this->iteratorKeys) !== null;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        \reset($this->iteratorKeys);
    }
}
