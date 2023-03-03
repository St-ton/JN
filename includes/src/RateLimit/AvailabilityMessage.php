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
    protected int $timeLimit = 2;

    /**
     * @inheritdoc
     */
    public function check(?array $args = null): bool
    {
        $items = $this->db->getSingleObject(
            'SELECT COUNT(*) AS cnt
                FROM tfloodprotect
                WHERE cIP = :ip
                    AND cTyp = :tpe
                    AND TIMESTAMPDIFF(MINUTE, dErstellt, NOW()) < :td',
            [
                'ip'  => $this->ip,
                'tpe' => $this->type,
                'td'  => $this->getCleanupMinutes(),
            ]
        );

        return ($items->cnt ?? 0) < 1;
    }

    /**
     * @inheritDoc
     */
    public function getCleanupMinutes(): int
    {
        return $this->timeLimit;
    }

    /**
     * @inheritDoc
     */
    public function setCleanupMinutes(int $minutes): void
    {
        $this->timeLimit = $minutes;
    }
}
