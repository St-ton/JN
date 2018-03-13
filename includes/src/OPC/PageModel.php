<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

/**
 * Class OPCPageModel
 * @package OPC
 */
class PageModel implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $kPage = 0;

    /**
     * @var string
     */
    protected $cPageId = '';

    /**
     * @var string
     */
    protected $cPageUrl = '';

    /**
     * @var string
     */
    protected $cAreasJson = '{}';

    /**
     * @var string
     */
    protected $dLastModified = '0000-00-00 00:00:00';

    /**
     * @var string
     */
    protected $cLockedBy = '';

    /**
     * @var string
     */
    protected $dLockedAt = '0000-00-00 00:00:00';

    /**
     * @var bool
     */
    protected $bReplace = false;

    /**
     * @var array cJson decoded as an associative array
     */
    protected $areasData = [];

    /**
     * @return $this
     * @throws \Exception
     */
    public function load($revisionId = 0)
    {
        if (strlen($this->cPageId) !== 32) {
            throw new \Exception("The OPC page id '{$this->cPageId}' is invalid.");
        }

        if ($revisionId > 0) {
            $revision  = new \Revision();
            $revision  = $revision->getRevision($revisionId);
            $opcPageDB = json_decode($revision->content);
        } else {
            $opcPageDB = \Shop::DB()->select('topcpage', 'cPageId', $this->cPageId);
        }

        if (!is_object($opcPageDB)) {
            throw new \Exception("The OPC page with the id '{$this->cPageId}' could not be found.");
        }

        $this
            ->setKey($opcPageDB->kPage)
            ->setId($opcPageDB->cPageId)
            ->setUrl($opcPageDB->cPageUrl)
            ->setAreasJson($opcPageDB->cAreasJson)
            ->setLastModified($opcPageDB->dLastModified)
            ->setLockedBy($opcPageDB->cLockedBy)
            ->setLockedAt($opcPageDB->dLockedAt)
            ->setReplace($opcPageDB->bReplace);

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function saveLockStatus()
    {
        $opcPageDB = (object)[
            'cLockedBy' => $this->getLockedBy(),
            'dLockedAt' => $this->getLockedAt(),
        ];

        if ($this->existsInDB()) {
            if (\Shop::DB()->update('topcpage', 'cPageId', $this->cPageId, $opcPageDB) === -1) {
                throw new \Exception('The OPC page could not be updated in the DB.');
            }
        } else {
            $key = \Shop::DB()->insert('topcpage', $opcPageDB);

            if ($key === 0) {
                throw new \Exception('The OPC page could not be inserted into the DB.');
            }

            $this->setKey($key);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function save()
    {
        if ($this->cPageUrl === '' || $this->cAreasJson === '' || $this->dLastModified === ''
            || $this->dLockedAt === '' || strlen($this->cPageId) !== 32
        ) {
            throw new \Exception('The OPC page data to be saved is incomplete or invalid.');
        }

        $opcPageDB = (object)$this->jsonSerialize();

        if ($this->existsInDB()) {
            $oldAreasJson = \Shop::DB()->select('topcpage', 'cPageId', $this->cPageId)->cAreasJson;
            $newAreasJson = $this->getAreasJson();

            if ($oldAreasJson !== $newAreasJson) {
                $revision = new \Revision();
                $revision->addRevision('opcpage', (int)$this->getKey());
            }

            if (\Shop::DB()->update('topcpage', 'cPageId', $this->cPageId, $opcPageDB) === -1) {
                throw new \Exception('The OPC page could not be updated in the DB.');
            }
        } else {
            $key = \Shop::DB()->insert('topcpage', $opcPageDB);

            if ($key === 0) {
                throw new \Exception('The OPC page could not be inserted into the DB.');
            }

            $this->setKey($key);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        \Shop::DB()->delete('topcpage', 'cPageId', $this->cPageId);

        return $this;
    }

    /**
     * @return bool
     */
    public function existsInDB()
    {
        return is_object(\Shop::DB()->select('topcpage', 'cPageId', $this->cPageId));
    }

    /**
     * @return int
     */
    public function getKey()
    {
        return $this->kPage;
    }

    /**
     * @param int $kPage
     * @return $this
     */
    public function setKey($kPage)
    {
        $this->kPage = $kPage;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->cPageId;
    }

    /**
     * @param $cId
     * @return $this
     */
    public function setId($cId)
    {
        $this->cPageId = (string)$cId;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->cPageUrl;
    }

    /**
     * @param $cUrl
     * @return $this
     */
    public function setUrl($cUrl)
    {
        $this->cPageUrl = (string)$cUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getAreasJson()
    {
        return $this->cAreasJson;
    }

    /**
     * @param $cJson
     * @return $this
     */
    public function setAreasJson($cJson)
    {
        $this->cAreasJson = (string)$cJson;
        $this->areasData  = json_decode($this->cAreasJson, true);

        return $this;
    }

    /**
     * @return string
     */
    public function getLastModified()
    {
        return $this->dLastModified;
    }

    /**
     * @param $dLastModified
     * @return $this
     */
    public function setLastModified($dLastModified)
    {
        $this->dLastModified = (string)$dLastModified;

        return $this;
    }

    /**
     * @return string
     */
    public function getLockedBy()
    {
        return $this->cLockedBy;
    }

    /**
     * @param $cLockedBy
     * @return $this
     */
    public function setLockedBy($cLockedBy)
    {
        $this->cLockedBy = (string)$cLockedBy;

        return $this;
    }

    /**
     * @return string
     */
    public function getLockedAt()
    {
        return $this->dLockedAt;
    }

    /**
     * @param $dLockedAt
     * @return $this
     */
    public function setLockedAt($dLockedAt)
    {
        $this->dLockedAt = (string)$dLockedAt;

        return $this;
    }

    /**
     * @return array
     */
    public function getAreasData()
    {
        return $this->areasData;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setAreasData($data)
    {
        $this->areasData  = $data;
        $this->cAreasJson = json_encode($this->areasData);

        return $this;
    }

    /**
     * @return bool
     */
    public function isReplace()
    {
        return $this->bReplace;
    }

    /**
     * @param bool $bReplace
     * @return PageModel
     */
    public function setReplace($bReplace)
    {
        $this->bReplace = $bReplace;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'kPage'         => $this->getKey(),
            'cPageId'       => $this->getId(),
            'cPageUrl'      => $this->getUrl(),
            'cAreasJson'    => $this->getAreasJson(),
            'dLastModified' => $this->getLastModified(),
            'cLockedBy'     => $this->getLockedBy(),
            'dLockedAt'     => $this->getLockedAt(),
            'bReplace'      => $this->isReplace(),
        ];
    }
}
