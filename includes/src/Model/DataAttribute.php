<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Model;

/**
 * Class DataAttribute
 * @package JTL\Model
 */
class DataAttribute
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $dataType;

    /**
     * @var bool
     */
    public $nullable;

    /**
     * @var mixed
     */
    public $default;

    /**
     * @var bool
     */
    public $isPrimaryKey;

    /**
     * @var string|null
     */
    public $foreignKey;

    /**
     * @var string|null
     */
    public $foreignKeyChild;

    /**
     * DataAttribute constructor.
     *
     * @param string      $name - name of the attribute
     * @param string      $dataType - type of the attribute
     * @param null|mixed  $default - default value of the attribute
     * @param bool        $nullable - true if the attribute is nullable, false otherwise
     * @param bool        $isPrimaryKey - true if the attribute is the primary key, false otherwise
     * @param string      $foreignKey
     * @param string|null $foreignKeyChild
     */
    public function __construct(
        string $name,
        string $dataType,
        $default = null,
        bool $nullable = true,
        bool $isPrimaryKey = false,
        string $foreignKey = null,
        $foreignKeyChild = null)
    {
        $this->name            = $name;
        $this->dataType        = $dataType;
        $this->default         = $default;
        $this->nullable        = $nullable;
        $this->isPrimaryKey    = $isPrimaryKey;
        $this->foreignKey      = $foreignKey;
        $this->foreignKeyChild = $foreignKeyChild;
    }

    /**
     * Creates a new DataAttribute instance
     *
     * @param string      $name - name of the attribute
     * @param string      $dataType - type of the attribute
     * @param null|mixed  $default - default value of the attribute
     * @param bool        $nullable - true if the attribute is nullable, false otherwise
     * @param bool        $isPrimaryKey - true if the attribute is the primary key, false otherwise
     * @param string      $foreignKey
     * @param string|null $foreignKeyChild
     * @return self
     */
    public static function create(
        string $name,
        string $dataType,
        $default = null,
        bool $nullable = true,
        bool $isPrimaryKey = false,
        string $foreignKey = null,
        $foreignKeyChild = null
    ): self
    {
        return new self($name, $dataType, $default, $nullable, $isPrimaryKey, $foreignKey, $foreignKeyChild);
    }
}
