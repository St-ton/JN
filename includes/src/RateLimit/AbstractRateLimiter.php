<?php declare(strict_types=1);

namespace JTL\RateLimit;

use JTL\DB\DbInterface;
use JTL\Model\DataModelInterface;

/**
 * class AbstractRateLimiter
 * @package JTL\RateLimit
 */
class AbstractRateLimiter implements RateLimiterInterface
{
    /**
     * @var string
     */
    protected $ip;

    /**
     * @var int
     */
    protected $key;

    /**
     * @var string
     */
    protected string $type = 'generic';

    protected const LIMIT = 3;

    protected const CLEANUP_MINUTES = 60;

    protected const FLOOD_MINUTES = 5;

    /**
     * @param DbInterface $db
     */
    public function __construct(protected DbInterface $db)
    {
    }

    /**
     * @inheritdoc
     */
    public function init(string $ip, int $key = 0): void
    {
        $this->ip  = $ip;
        $this->key = $key;
    }

    /**
     * @inheritdoc
     */
    public function getModel(): DataModelInterface
    {
        return new Model($this->db);
    }

    /**
     * @inheritdoc
     */
    public function initModel(): DataModelInterface
    {
        /** @var Model $model */
        $model = $this->getModel();
        $model->setIP($this->ip);
        $model->setReference($this->key);
        $model->setProtectedType($this->type);
        $model->setTime('now()');

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function persist(): bool
    {
        return $this->initModel()->save();
    }

    /**
     * @inheritdoc
     */
    public function check(?array $args = null): bool
    {
        $items = $this->db->getSingleObject(
            'SELECT COUNT(*) AS cnt
                FROM tfloodprotect
                WHERE cIP = :ip
                    AND reference = :rid
                    AND cTyp = :tpe
                    AND TIMESTAMPDIFF(MINUTE, dErstellt, NOW()) < :td',
            [
                'ip'  => $this->ip,
                'rid' => $this->key,
                'tpe' => $this->type,
                'td'  => $this->getFloodMinutes()
            ]
        );

        return ($items->cnt ?? 0) < $this->getLimit();
    }

    public function cleanup(): void
    {
        $this->db->queryPrepared(
            'DELETE
                FROM tfloodprotect
                WHERE dErstellt < DATE_SUB(NOW(), INTERVAL :min MINUTE)
                    AND cTyp = :tpe',
            ['min' => $this->getCleanupMinutes(), 'tpe' => $this->type]
        );
    }

    /**
     * @inheritDoc
     */
    public function getLimit(): int
    {
        return self::LIMIT;
    }

    /**
     * @inheritDoc
     */
    public function setLimit(int $limit): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getCleanupMinutes(): int
    {
        return self::CLEANUP_MINUTES;
    }

    /**
     * @inheritDoc
     */
    public function setCleanupMinutes(int $minutes): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getFloodMinutes(): int
    {
        return self::FLOOD_MINUTES;
    }

    /**
     * @inheritDoc
     */
    public function setFloodMinutes(int $minutes): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritDoc
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function getIP(): string
    {
        return $this->ip;
    }

    /**
     * @inheritDoc
     */
    public function setIP(string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @inheritDoc
     */
    public function getKey(): int
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function setKey(int $key): void
    {
        $this->key = $key;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
