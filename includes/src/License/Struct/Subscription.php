<?php declare(strict_types=1);

namespace JTL\License\Struct;

use DateTime;
use stdClass;

/**
 * Class Subscription
 * @package JTL\License
 */
class Subscription
{
    /**
     * @var DateTime
     */
    private $validUntil;

    /**
     * Subscription constructor.
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
        $this->setValidUntil($json->validUntil);
    }

    /**
     * @return DateTime
     */
    public function getValidUntil(): DateTime
    {
        return $this->validUntil;
    }

    /**
     * @param DateTime|string $validUntil
     */
    public function setValidUntil($validUntil): void
    {
        $this->validUntil = \is_a(DateTime::class, $validUntil) ? $validUntil : new DateTime($validUntil);
    }
}
