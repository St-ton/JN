<?php declare(strict_types=1);

namespace JTL\License\Struct;

use DateTime;
use JTL\Shop;
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
        $this->setSubscription(new Subscription($json->subscription));
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
     * @param DateTime|null $validUntil
     */
    public function setValidUntil(?DateTime $validUntil): void
    {
        $this->validUntil = $validUntil;
    }
}
