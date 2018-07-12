<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

/**
 * Class Page
 * @package OPC
 */
class Page implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $key = 0;

    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var null|string
     */
    protected $publishFrom = null;

    /**
     * @var null|string
     */
    protected $publishTo = null;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var int
     */
    protected $revId = 0;

    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var string
     */
    protected $lastModified = null;

    /**
     * @var string
     */
    protected $lockedBy = '';

    /**
     * @var string
     */
    protected $lockedAt = null;

    /**
     * @var bool
     */
    protected $replace = false;

    /**
     * @var null|AreaList
     */
    protected $areaList;

    /**
     * Page constructor.
     */
    public function __construct()
    {
        $this->areaList = new AreaList();
    }

    /**
     * @return int
     */
    public function getKey(): int
    {
        return $this->key;
    }

    /**
     * @param int $key
     * @return $this
     */
    public function setKey(int $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPublishFrom()
    {
        return $this->publishFrom;
    }

    /**
     * @param null|string $publishFrom
     * @return Page
     */
    public function setPublishFrom($publishFrom): self
    {
        $this->publishFrom = $publishFrom === '0000-00-00 00:00:00' ? null : $publishFrom;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPublishTo()
    {
        return $this->publishTo;
    }

    /**
     * @param null|string $publishTo
     * @return Page
     */
    public function setPublishTo($publishTo): self
    {
        $this->publishTo = $publishTo === '0000-00-00 00:00:00' ? null : $publishTo;

        return $this;
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
     * @return Page
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getRevId(): int
    {
        return $this->revId;
    }

    /**
     * @param int $revId
     * @return Page
     */
    public function setRevId(int $revId): self
    {
        $this->revId = $revId;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @param null|string $lastModified
     * @return $this
     */
    public function setLastModified($lastModified): self
    {
        $this->lastModified = $lastModified === '0000-00-00 00:00:00' ? null : $lastModified;

        return $this;
    }

    /**
     * @return string
     */
    public function getLockedBy(): string
    {
        return $this->lockedBy;
    }

    /**
     * @param string $lockedBy
     * @return $this
     */
    public function setLockedBy(string $lockedBy): self
    {
        $this->lockedBy = $lockedBy;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLockedAt()
    {
        return $this->lockedAt;
    }

    /**
     * @param null|string $lockedAt
     * @return $this
     */
    public function setLockedAt($lockedAt): self
    {
        $this->lockedAt = $lockedAt === '0000-00-00 00:00:00' ? null : $lockedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReplace(): bool
    {
        return $this->replace;
    }

    /**
     * @param bool $replace
     * @return $this
     */
    public function setReplace(bool $replace): self
    {
        $this->replace = $replace;

        return $this;
    }

    /**
     * @return AreaList
     */
    public function getAreaList(): AreaList
    {
        return $this->areaList;
    }

    /**
     * @param string $json
     * @return $this
     */
    public function fromJson($json)
    {
        $this->deserialize(json_decode($json, true));

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function deserialize($data)
    {
        $this->setKey($data['key'] ?? $this->getKey());
        $this->setId($data['id'] ?? $this->getId());
        $this->setPublishFrom($data['publishFrom'] ?? $this->getPublishFrom());
        $this->setPublishTo($data['publishTo'] ?? $this->getPublishTo());
        $this->setName($data['name'] ?? $this->getName());
        $this->setUrl($data['url'] ?? $this->getUrl());
        $this->setRevId($data['revId'] ?? $this->getRevId());

        if (isset($data['areas']) && is_array($data['areas'])) {
            $this->getAreaList()->deserialize($data['areas']);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $result = [
            'key'          => $this->getKey(),
            'id'           => $this->getId(),
            'publishFrom'  => $this->getPublishFrom(),
            'publishTo'    => $this->getPublishTo(),
            'name'         => $this->getName(),
            'revId'        => $this->getRevId(),
            'url'          => $this->getUrl(),
            'lastModified' => $this->getLastModified(),
            'lockedBy'     => $this->getLockedBy(),
            'lockedAt'     => $this->getLockedAt(),
            'replace'      => $this->isReplace(),
            'areaList'     => $this->getAreaList()->jsonSerialize(),
        ];

        return $result;
    }
}
