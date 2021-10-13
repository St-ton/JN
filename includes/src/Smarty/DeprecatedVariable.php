<?php declare(strict_types=1);

namespace JTL\Smarty;

use Shop;

/**
 * Class DeprecatedVariable
 * @package \JTL\Smarty
 */
class DeprecatedVariable
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    public $nocache = false;

    /**
     * @param mixed  $value
     * @param string $name
     */
    public function __construct($value, string $name)
    {
        $this->value = $value;
        $this->name  = $name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($name === 'value') {
            \trigger_error('Smarty variable ' . $this->name . ' is deprecated.', \E_USER_DEPRECATED);

            return $this->value;
        }

        return null;
    }
}
