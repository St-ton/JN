<?php declare(strict_types=1);

namespace JTL\Smarty;

/**
 * Class DeprecatedVariable
 * @package \JTL\Smarty
 */
class DeprecatedVariable
{
    /**
     * @var bool
     */
    public $nocache = false;

    /**
     * DeprecatedVariable constructor.
     * @param mixed  $value
     * @param string $name
     * @param string $version
     */
    public function __construct(private $value, private string $name, private string $version)
    {
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
            \trigger_error(
                'Smarty variable ' . $this->name . ' is deprecated since JTL-Shop version '. $this->version . '.',
                \E_USER_DEPRECATED
            );

            return $this->value;
        }

        return null;
    }
}
