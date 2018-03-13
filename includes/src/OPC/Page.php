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
     * @var PageModel
     */
    protected $model;

    /**
     * @var PageLocker
     */
    protected $locker;

    /**
     * @var Area[]
     */
    protected $areas = [];

    /**
     * @var bool
     */
    protected $previewHtmlEnabled = false;

    /**
     * @var bool
     */
    protected $finalHtmlEnabled = false;

    /**
     * Page constructor.
     * @param array $data
     * @throws \Exception
     */
    public function __construct($data)
    {
        $this->model = new PageModel();
        $this->model->setId($data['id']);

        if ($this->model->existsInDB()) {
            $revisionId = isset($data['revisionId']) ? (int)$data['revisionId'] : 0;
            $this->model->load($revisionId);

            foreach ($this->model->getAreasData() as $areaData) {
                $this->putArea(new Area($areaData));
            }
        }

        $this->locker = new PageLocker($this->model);
        $this->setPreviewHtmlEnabled(isset($data['previewHtmlEnabled']) ? $data['previewHtmlEnabled'] : false);
        $this->setFinalHtmlEnabled(isset($data['finalHtmlEnabled']) ? $data['finalHtmlEnabled'] : false);

        if (isset($data['url']) && is_string($data['url'])) {
            $this->setPageUrl($data['url']);
        }

        if (isset($data['areas']) && is_array($data['areas'])) {
            foreach ($data['areas'] as $areaData) {
                $this->putArea(new Area($areaData));
            }
        }
    }

    /**
     * Lock this page to only be manipulated by this one user
     * @param $userName
     * @return bool
     * @throws \Exception
     */
    public function lock($userName)
    {
        return $this->locker->lock($userName);
    }

    /**
     * Release this page if it was locked
     * @throws \Exception
     */
    public function unlock()
    {
        $this->locker->unlock();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->model->getId();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->model->getUrl();
    }

    /**
     * @return string
     */
    public function getLastModified()
    {
        return $this->model->getLastModified();
    }

    /**
     * @return string
     */
    public function getLockedBy()
    {
        return $this->model->getLockedBy();
    }

    /**
     * @return string
     */
    public function getLockedAt()
    {
        return $this->model->getLockedAt();
    }

    /**
     * @return bool
     */
    public function isReplace()
    {
        return $this->model->isReplace();
    }

    /**
     * @param Area $area
     * @return Page
     */
    public function putArea($area)
    {
        $this->areas[$area->getId()] = $area;

        return $this;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasArea($id)
    {
        return array_key_exists($id, $this->areas);
    }

    /**
     * @param string $id
     * @return Area
     */
    public function getArea($id)
    {
        return $this->areas[$id];
    }

    /**
     * @return bool
     */
    public function existsInDB()
    {
        return $this->model->existsInDB();
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setPageUrl($url)
    {
        $this->model->setUrl($url);

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function save()
    {
        $areasData = [];

        foreach ($this->areas as $id => $area) {
            $areasData[$id] = $area->jsonSerialize();
        }

        $this->model->setAreasData($areasData);
        $this->model->save();

        return $this;
    }

    /**
     * @param bool $previewHtmlEnabled
     * @return Page
     */
    public function setPreviewHtmlEnabled($previewHtmlEnabled)
    {
        $this->previewHtmlEnabled = $previewHtmlEnabled;

        return $this;
    }

    /**
     * @param bool $finalHtmlEnabled
     * @return Page
     */
    public function setFinalHtmlEnabled($finalHtmlEnabled)
    {
        $this->finalHtmlEnabled = $finalHtmlEnabled;

        return $this;
    }

    /**
     * @return string[] the rendered HTML content of this page
     */
    public function getPreviewHtml()
    {
        $result = [];

        foreach ($this->areas as $id => $area) {
            $result[$id] = $area->getPreviewHtml();
        }

        return $result;
    }

    /**
     * @return string[] the rendered HTML content of this page
     */
    public function getFinalHtml()
    {
        $result = [];

        foreach ($this->areas as $id => $area) {
            $result[$id] = $area->getFinalHtml();
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getRevisions()
    {
        $revision = new \Revision();

        return $revision->getRevisions('opcpage', $this->model->getKey());
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $result = [
            'id'           => $this->model->getId(),
            'url'          => $this->model->getUrl(),
            'areasJson'    => $this->model->getAreasJson(),
            'lastModified' => $this->model->getLastModified(),
            'lockedBy'     => $this->model->getLockedBy(),
            'lockedAt'     => $this->model->getLockedAt(),
            'replace'      => $this->model->isReplace(),
        ];

        if ($this->previewHtmlEnabled) {
            $result['previewHtml'] = $this->getPreviewHtml();
        }

        if ($this->finalHtmlEnabled) {
            $result['finalHtml'] = $this->getFinalHtml();
        }

        return $result;
    }
}
