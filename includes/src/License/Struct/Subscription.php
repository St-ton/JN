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
     * @var DateTime|null
     */
    private $validUntil;

    /**
     * @var bool
     */
    private $expired = false;

    /**
     * Subscription constructor.
     * @param stdClass|null $json
     */
    public function __construct(stdClass $json = null)
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
        $this->setValidUntil($json->valid_until);
        $now = new DateTime();
        $this->setExpired($json->valid_until !== null && $this->getValidUntil() < $now);
    }

    /**
     * @return DateTime|null
     */
    public function getValidUntil(): ?DateTime
    {
        return $this->validUntil;
    }

    /**
     * @param DateTime|string|null $validUntil
     * @throws \Exception
     */
    public function setValidUntil($validUntil): void
    {
        if ($validUntil !== null) {
            $this->validUntil = \is_a(DateTime::class, $validUntil) ? $validUntil : new DateTime($validUntil);
        } else {
            $this->validUntil = null;
        }
    }

    /**
     * @return int
     */
    public function getDaysRemaining(): int
    {
        if ($this->getValidUntil() === null) {
            return 0;
        }

        return (int)(new DateTime())->diff($this->getValidUntil())->format('%R%a');
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expired;
    }

    /**
     * @param bool $expired
     */
    public function setExpired(bool $expired): void
    {
        $this->expired = $expired;
    }
}
