<?php declare(strict_types=1);

namespace JTL\License\Struct;

use DateTime;
use stdClass;

/**
 * Class License
 * @package JTL\License
 */
class License
{
    public const TYPE_FREE = 'free';

    public const TYPE_PROD = 'prod';

    public const TYPE_DEV = 'dev';

    public const TYPE_TEST = 'test';

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $type;

    /**
     * @var DateTime
     */
    private $created;

    /**
     * @var DateTime|null
     */
    private $validUntil;

    /**
     * @var Subscription
     */
    private $subscription;

    /**
     * @var bool
     */
    private $expired = false;

    /**
     * License constructor.
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
        if ($json->subscription === 'null') {
            $json->subscription = null;
        }
        $this->setKey($json->key);
        $this->setType($json->type);
        $this->setCreated($json->created);
        $this->setValidUntil($json->validUntil);
        $this->setSubscription(new Subscription($json->subscription));
        if ($this->getValidUntil() !== null) {
            $now = new DateTime();
            $this->setExpired($this->getValidUntil() < $now);
        }
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * @param DateTime|string $created
     * @throws \Exception
     */
    public function setCreated($created): void
    {
        $this->created = \is_a(DateTime::class, $created) ? $created : new DateTime($created);
    }

    /**
     * @return Subscription
     */
    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }

    /**
     * @param Subscription $subscription
     */
    public function setSubscription(Subscription $subscription): void
    {
        $this->subscription = $subscription;
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
        }
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
