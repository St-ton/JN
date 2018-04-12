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
    protected $lastModified = '0000-00-00 00:00:00';

    /**
     * @var string
     */
    protected $lockedBy = '';

    /**
     * @var string
     */
    protected $lockedAt = '0000-00-00 00:00:00';

    /**
     * @var bool
     */
    protected $replace = false;

    /**
     * @var null|AreaList
     */
    protected $areaList = null;

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
    public function getKey() : int
    {
        return $this->key;
    }

    /**
     * @param int $key
     * @return $this
     */
    public function setKey(int $key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId(string $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getRevId() : int
    {
        return $this->revId;
    }

    /**
     * @param int $revId
     * @return Page
     */
    public function setRevId(int $revId) : Page
    {
        $this->revId = $revId;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastModified() : string
    {
        return $this->lastModified;
    }

    /**
     * @param string $lastModified
     * @return $this
     */
    public function setLastModified(string $lastModified)
    {
        $this->lastModified = $lastModified;

        return $this;
    }

    /**
     * @return string
     */
    public function getLockedBy() : string
    {
        return $this->lockedBy;
    }

    /**
     * @param string $lockedBy
     * @return $this
     */
    public function setLockedBy(string $lockedBy)
    {
        $this->lockedBy = $lockedBy;

        return $this;
    }

    /**
     * @return string
     */
    public function getLockedAt() : string
    {
        return $this->lockedAt;
    }

    /**
     * @param string $lockedAt
     * @return $this
     */
    public function setLockedAt(string $lockedAt)
    {
        $this->lockedAt = $lockedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReplace()
    {
        return $this->replace;
    }

    /**
     * @param $replace
     * @return $this
     */
    public function setReplace($replace)
    {
        $this->replace = (bool)$replace;

        return $this;
    }

    /**
     * @return AreaList
     */
    public function getAreaList()
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
        $this->setId($data['id'] ?? $this->getId());
        $this->setUrl($data['url'] ?? $this->getUrl());
        $this->setRevId($data['revId'] ?? $this->getRevId());
        //$this->setReplace($data['replace'] ?? $this->)

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
