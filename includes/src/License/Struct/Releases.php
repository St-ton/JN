<?php declare(strict_types=1);

namespace JTL\License\Struct;

use stdClass;

/**
 * Class Releases
 * @package JTL\License\Struct
 */
class Releases
{
    /**
     * @var Release|null
     */
    private $latest;

    /**
     * @var Release|null
     */
    private $available;

    /**
     * Link constructor.
     * @param stdClass|null $json
     */
    public function __construct(?stdClass $json)
    {
        if ($json !== null) {
            $this->fromJSON($json);
        }
    }

    /**
     * @param stdClass $json
     */
    public function fromJSON(stdClass $json): void
    {
        $this->setAvailable(new Release($json->available ?? null));
        $this->setLatest(new Release($json->latest ?? null));
    }

    /**
     * @return Release|null
     */
    public function getLatest(): ?Release
    {
        return $this->latest;
    }

    /**
     * @param Release|null $latest
     */
    public function setLatest(?Release $latest): void
    {
        $this->latest = $latest;
    }

    /**
     * @return Release|null
     */
    public function getAvailable(): ?Release
    {
        return $this->available;
    }

    /**
     * @param Release|null $available
     */
    public function setAvailable(?Release $available): void
    {
        $this->available = $available;
    }
}