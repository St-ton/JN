<?php declare(strict_types=1);

namespace JTL\License\Struct;

use DateTime;
use stdClass;

/**
 * Class ExsLicense
 * @package JTL\License
 */
class ExsLicense
{
    public const TYPE_PLUGIN = 'plugin';

    public const TYPE_TEMPLATE = 'template';

    public const TYPE_PORTLET = 'portlet';

    public const STATE_ACTIVE = 1;

    public const STATE_UNBOUND = 0;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $exsid;

    /**
     * @var Vendor
     */
    private $vendor;

    /**
     * @var License
     */
    private $license;

    /**
     * @var Releases
     */
    private $releases;

    /**
     * @var Link[]
     */
    private $links;

    /**
     * @var DateTime
     */
    private $queryDate;

    /**
     * @var int
     */
    private $state = self::STATE_UNBOUND;

    /**
     * @var ReferencedItemInterface|null
     */
    private $referencedItem;

    /**
     * ExsLicenseData constructor.
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
        $this->setID($json->id);
        $this->setType($json->type ?? self::TYPE_PLUGIN); // @todo: this should always be provided by the api!
        $this->setName($json->name);
        $this->setExsid($json->exsid);
        if (isset($json->license)) {
            $this->setLicense(new License($json->license));
        }
        $this->setVendor(new Vendor($json->vendor));
        $this->releases = new Releases($json->releases);
        foreach ($json->links as $link) {
            $this->links[] = new Link($link);
        }
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setID(string $id): void
    {
        $this->id = $id;
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getExsid(): string
    {
        return $this->exsid;
    }

    /**
     * @param string $exsid
     */
    public function setExsid(string $exsid): void
    {
        $this->exsid = $exsid;
    }

    /**
     * @return Vendor
     */
    public function getVendor(): Vendor
    {
        return $this->vendor;
    }

    /**
     * @param Vendor $vendor
     */
    public function setVendor(Vendor $vendor): void
    {
        $this->vendor = $vendor;
    }

    /**
     * @return License
     */
    public function getLicense(): License
    {
        return $this->license;
    }

    /**
     * @param License $license
     */
    public function setLicense(License $license): void
    {
        $this->license = $license;
    }

    /**
     * @return Releases
     */
    public function getReleases(): Releases
    {
        return $this->releases;
    }

    /**
     * @param Releases $releases
     */
    public function setReleases(Releases $releases): void
    {
        $this->releases = $releases;
    }

    /**
     * @return Link[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param Link[] $links
     */
    public function setLinks(array $links): void
    {
        $this->links = $links;
    }

    /**
     * @return DateTime
     */
    public function getQueryDate(): DateTime
    {
        return $this->queryDate;
    }

    /**
     * @param DateTime|string $queryDate
     * @throws \Exception
     */
    public function setQueryDate($queryDate): void
    {
        $this->queryDate = \is_a(DateTime::class, $queryDate) ? $queryDate : new DateTime($queryDate);
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void
    {
        $this->state = $state;
    }

    /**
     * @return ReferencedItemInterface|null
     */
    public function getReferencedItem(): ?ReferencedItemInterface
    {
        return $this->referencedItem;
    }

    /**
     * @param ReferencedItemInterface|null $referencedItem
     */
    public function setReferencedItem(?ReferencedItemInterface $referencedItem): void
    {
        $this->referencedItem = $referencedItem;
    }
}
