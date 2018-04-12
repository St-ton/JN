<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class OPCPage
 */
class OPCPage
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
            $opcPageDB = Shop::DB()->select('topcpage', 'kPage', $kPage);

            if ($opcPageDB !== null) {
                $opcPageDB->cJson    = empty($opcPageDB->cJson) ? '{}' : $opcPageDB->cJson;
                $this->kPage         = $opcPageDB->kPage;
                $this->cIdHash       = $opcPageDB->cIdHash;
                $this->cPageUrl      = $opcPageDB->cPageURL;
                $this->data          = json_decode($opcPageDB->cJson, true);
                $this->dLastModified = $opcPageDB->dLastModified;
                $this->cLockedBy     = $opcPageDB->cLockedBy;
                $this->dLockedAt     = $opcPageDB->dLockedAt;
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
                    $cHtml .= OPC::getInstance()->createPortlet($portlet['portletId'])
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
                    $cHtml .= OPC::getInstance()->createPortlet($portlet['portletId'])
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
     * Save this OPC page instance to the database
     */
    public function save()
    {
        $oCmsPageDB = Shop::DB()->select('topcpage', 'cIdHash', $this->cIdHash);

        if (!empty($this->cIdHash) && $oCmsPageDB === null) {
            $oCmsPageDB  = (object)[
                'cIdHash' => $this->cIdHash,
                'cPageURL' => $this->cPageUrl,
                'cJson' => json_encode($this->data),
                'dLastModified' => date('Y-m-d H:i:s'),
                'cLockedBy' => $this->cLockedBy
            ];
            $this->kPage = Shop::DB()->insert('topcpage', $oCmsPageDB);
        } else {
            $newPageJson = json_encode($this->data);

            if ($newPageJson !== $oCmsPageDB->cJson) {
                $revision = new Revision();
                $revision->addRevision('opcpage', (int)$oCmsPageDB->kPage);
            }

            $oCmsPageDB->cJson         = $newPageJson;
            $oCmsPageDB->cPageURL      = $this->cPageUrl;
            $oCmsPageDB->dLastModified = date('Y-m-d H:i:s');
            $oCmsPageDB->cLockedBy     = $this->cLockedBy;

            if (Shop::DB()->update('topcpage', 'cIdHash', $this->cIdHash, $oCmsPageDB) === -1) {
                throw new Exception('Could not update the page in topcpage');
            }
        }

        return $this;
    }

    /**
     * Remove this OPC page instance from the database
     */
    public function remove()
    {
        Shop::DB()->delete('topcpage', 'cIdHash', $this->cIdHash);

        return $this;
    }

    /**
     * @return array
     */
    public function getRevisions()
    {
        $revision = new Revision();

        return $revision->getRevisions('opcpage', $this->kPage);
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

        if (!empty($this->cIdHash) && Shop::DB()->select('topcpage', 'cIdHash', $this->cIdHash) === null) {
            $this->kPage = Shop::DB()->insert('topcpage', $pageUpdate);
        } else {
            Shop::DB()->update('topcpage', 'cIdHash', $this->cIdHash, $pageUpdate);
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

        if (Shop::DB()->select('topcpage', 'cIdHash', $this->cIdHash) === null) {
            $this->kPage = Shop::DB()->insert('topcpage', $pageUpdate);
        } else {
            Shop::DB()->update('topcpage', 'cIdHash', $this->cIdHash, $pageUpdate);
        }

        return $this;
    }
}
