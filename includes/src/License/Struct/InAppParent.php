<?php declare(strict_types=1);

namespace JTL\License\Struct;

use stdClass;

/**
 * Class InAppParent
 * @package JTL\License\Struct
 */
class InAppParent
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $exsid;

    /**
     * InAppParent constructor.
     * @param stdClass|null $json
     */
    public function __construct(?stdClass $json = null)
    {
        if ($json !== null && isset($json->parent)) {
            $this->setName($json->parent->name);
            $this->setExsID($json->parent->exsid);
        }
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getExsID(): ?string
    {
        return $this->exsid;
    }

    /**
     * @param string|null $exsid
     */
    public function setExsID(?string $exsid): void
    {
        $this->exsid = $exsid;
    }
}
