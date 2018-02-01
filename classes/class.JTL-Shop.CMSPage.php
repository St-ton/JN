<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class CMSPage
 */
class CMSPage
{
    /**
     * @var int
     */
    public $kPage = 0;

    /**
     * @var string
     */
    public $cIdHash = '';

    /**
     * @var string
     */
    public $cPageUrl = '';

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var string
     */
    public $dLastModified = '';

    /**
     * @var string
     */
    public $cLockedBy = '';

    /**
     * @var string
     */
    public $dLockedAt = '0000-00-00 00:00:00';

    /**
     * @var array
     */
    public $cFinalHtml_arr = null;

    /**
     * @var array - maps each root area to the editable preview html content
     */
    public $cPreviewHtml_arr = [];

    /**
     * @param int $kPage
     */
    public function __construct($kPage = 0)
    {
        if ($kPage > 0) {
            $oCMSPageDB = Shop::DB()->select('tcmspage', 'kPage', $kPage);

            if ($oCMSPageDB !== null) {
                $oCMSPageDB->cJson   = empty($oCMSPageDB->cJson) ? '{}' : $oCMSPageDB->cJson;
                $this->kPage         = $oCMSPageDB->kPage;
                $this->cIdHash       = $oCMSPageDB->cIdHash;
                $this->cPageUrl      = $oCMSPageDB->cPageURL;
                $this->data          = json_decode($oCMSPageDB->cJson, true);
                $this->dLastModified = $oCMSPageDB->dLastModified;
                $this->cLockedBy     = $oCMSPageDB->cLockedBy;
                $this->dLockedAt     = $oCMSPageDB->dLockedAt;
            }
        }
    }

    /**
     * Render the final HTML content and stores it in cFinalHtml_arr for each area-id
     */
    public function renderFinal()
    {
        $this->cFinalHtml_arr = [];

        foreach ($this->data as $areaId => $areaPortlets) {
            $cHtml = '';

            foreach ($areaPortlets as $portlet) {
                try {
                    $cHtml .= CMS::getInstance()->createPortlet($portlet['portletId'])
                        ->setProperties($portlet['properties'])
                        ->setSubAreas($portlet['subAreas'])
                        ->getFinalHtml();
                } catch (Exception $e) {
                    $cHtml .= '';
                }
            }

            $this->cFinalHtml_arr[$areaId] = $cHtml;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function renderPreview()
    {
        $this->cPreviewHtml_arr = [];

        foreach ($this->data as $areaId => $areaPortlets) {
            $cHtml = '';

            foreach ($areaPortlets as $portlet) {
                try {
                    $cHtml .= CMS::getInstance()->createPortlet($portlet['portletId'])
                        ->setProperties($portlet['properties'])
                        ->setSubAreas($portlet['subAreas'])
                        ->getFullPreviewHtml();
                } catch (Exception $e) {
                    // one portlet in this sub area could not be created
                    $cHtml .= '';
                }
            }

            $this->cPreviewHtml_arr[$areaId] = $cHtml;
        }

        return $this;
    }

    /**
     * Save this CMS page instance to the database
     */
    public function save()
    {
        $oCmsPageDB = Shop::DB()->select('tcmspage', 'cIdHash', $this->cIdHash);

        if (!empty($this->cIdHash) && $oCmsPageDB === null) {
            $oCmsPageDB  = (object)[
                'cIdHash' => $this->cIdHash,
                'cPageURL' => $this->cPageUrl,
                'cJson' => json_encode($this->data),
                'dLastModified' => date('Y-m-d H:i:s'),
                'cLockedBy' => $this->cLockedBy
            ];
            $this->kPage = Shop::DB()->insert('tcmspage', $oCmsPageDB);
        } else {
            $newPageJson = json_encode($this->data);

            if ($newPageJson !== $oCmsPageDB->cJson) {
                $revision = new Revision();
                $revision->addRevision('cmspage', (int)$oCmsPageDB->kPage);
            }

            $oCmsPageDB->cJson         = $newPageJson;
            $oCmsPageDB->cPageURL      = $this->cPageUrl;
            $oCmsPageDB->dLastModified = date('Y-m-d H:i:s');
            $oCmsPageDB->cLockedBy     = $this->cLockedBy;
            Shop::DB()->update('tcmspage', 'cIdHash', $this->cIdHash, $oCmsPageDB);
        }

        return $this;
    }

    /**
     * Remove this CMS page instance from the database
     */
    public function remove()
    {
        Shop::DB()->delete('tcmspage', 'cIdHash', $this->cIdHash);

        return $this;
    }

    /**
     * @return array
     */
    public function getRevisions()
    {
        $revision = new Revision();

        return $revision->getRevisions('cmspage', $this->kPage);
    }

    /**
     * @param $revisionID
     */
    public function loadRevision($revisionID)
    {
        $revision = new Revision();
        $revision = $revision->getRevision($revisionID);
        $content  = json_decode($revision->content);

        $content->data = json_decode($content->cJson, true);

        $this->kPage         = $content->kPage;
        $this->cIdHash       = $content->cIdHash;
        $this->cPageUrl      = $content->cPageUrl;
        $this->data          = json_decode($content->cJson, true);
        $this->dLastModified = $content->dLastModified;
        $this->cLockedBy     = $content->cLockedBy;
        $this->dLockedAt     = $content->dLockedAt;
    }

    /**
     * @param $cLogin string - name of the user who wants to lock this page
     * @return bool - true if lock was granted
     */
    public function lock($cLogin)
    {
        if ($this->cLockedBy !== '' &&
            $this->cLockedBy !== $cLogin &&
            strtotime($this->dLockedAt) + 60 > time()
        ) {
            return false;
        }

        $this->cLockedBy = $cLogin;
        $pageUpdate      = (object)[
            'cIdHash'   => $this->cIdHash,
            'cLockedBy' => $this->cLockedBy,
            'dLockedAt' => date('Y-m-d H:i:s'),
        ];

        if (!empty($this->cIdHash) && Shop::DB()->select('tcmspage', 'cIdHash', $this->cIdHash) === null) {
            $this->kPage = Shop::DB()->insert('tcmspage', $pageUpdate);
        } else {
            Shop::DB()->update('tcmspage', 'cIdHash', $this->cIdHash, $pageUpdate);
        }

        return true;
    }

    /**
     * @return $this
     */
    public function unlock()
    {
        $this->cLockedBy = '';
        $pageUpdate      = (object)[
            'cIdHash'   => $this->cIdHash,
            'cLockedBy' => $this->cLockedBy,
            'dLockedAt' => null,
        ];

        if (Shop::DB()->select('tcmspage', 'cIdHash', $this->cIdHash) === null) {
            $this->kPage = Shop::DB()->insert('tcmspage', $pageUpdate);
        } else {
            Shop::DB()->update('tcmspage', 'cIdHash', $this->cIdHash, $pageUpdate);
        }

        return $this;
    }
}
