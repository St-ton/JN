<?php declare(strict_types=1);

namespace JTL\RateLimit;

/**
 * class AvailabilityMessage
 * @package JTL\RateLimit
 */
class AvailabilityMessage extends AbstractRateLimiter
{
    /**
     * @var string
     */
    protected string $type = 'availabilityMessage';

    /**
     * @var int
     */
    protected int $floodMinutes = 2;

    /**
     * @var int
     */
    protected int $cleanupMinutes = 3;

    /**
     * @var int
     */
    protected int $entryLimit = 1;

     /**
     * @inheritDoc
     */
    public function getCleanupMinutes(): int
    {
        return $this->cleanupMinutes;
    }

    /**
     * @inheritDoc
     */
    public function setCleanupMinutes(int $minutes): void
    {
        $this->cleanupMinutes = $minutes;
    }

    /**
     * @inheritDoc
     */
    public function getFloodMinutes(): int
    {
        return $this->floodMinutes;
    }

    /**
     * @inheritDoc
     */
    public function setFloodMinutes(int $minutes): void
    {
        $this->floodMinutes = $minutes;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->entryLimit;
    }

    /**
     * @param int $limit
     * @return void
     */
    public function setLimit(int $limit): void
    {
        $this->entryLimit = $limit;
    }
}
