<?php declare(strict_types=1);

namespace JTL\RateLimit;

/**
 * class Upload
 * @package JTL\RateLimit
 */
class Upload extends AbstractRateLimiter
{
    /**
     * @var string
     */
    protected $type = 'upload';

    protected const FLOOD_MINUTES = 60;

    /**
     * @var int
     */
    private $limit = 10;

    /**
     * @inheritdoc
     */
    public function check(?array $args = null): bool
    {
        $items = $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM tfloodprotect
                WHERE cIP = :ip
                    AND cTyp = :tpe
                    AND TIMESTAMPDIFF(MINUTE, dErstellt, NOW()) < :td',
            'cnt',
            [
                'ip'  => $this->ip,
                'tpe' => $this->type,
                'td'  => $this->getFloodMinutes()
            ]
        );

        return $items <= $this->getLimit();
    }

    /**
     * @inheritDoc
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @inheritDoc
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }
}
