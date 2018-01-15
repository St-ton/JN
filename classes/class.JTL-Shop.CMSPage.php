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
     * @param int $kPage
     */
    public function __construct($kPage = 0)
    {
        if ($kPage > 0) {
            $oCMSPageDB = Shop::DB()->select('tcmspage', 'kPage', $kPage);

            if ($oCMSPageDB !== null) {
                $this->kPage         = $oCMSPageDB->kPage;
                $this->cIdHash       = $oCMSPageDB->cIdHash;
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
        if (!empty($this->data)) {
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
        }
    }

    /**
     * Save this CMS page instance to the database
     */
    public function save()
    {
        $oCmsPageDB = Shop::DB()->select('tcmspage', 'cIdHash', $this->cIdHash);

        if ($oCmsPageDB === null) {
            $oCmsPageDB  = (object)[
                'cIdHash' => $this->cIdHash,
                'cJson' => json_encode($this->data),
                'dLastModified' => date('Y-m-d H:i:s'),
                'cLockedBy' => $this->cLockedBy
            ];
            $this->kPage = Shop::DB()->insert('tcmspage', $oCmsPageDB);
        } else {
            $revision = new Revision();
            $revision->addRevision('cmspage', (int)$oCmsPageDB->kPage);
            $oCmsPageDB->cJson         = json_encode($this->data);
            $oCmsPageDB->dLastModified = date('Y-m-d H:i:s');
            $oCmsPageDB->cLockedBy     = $this->cLockedBy;
            Shop::DB()->update('tcmspage', 'cIdHash', $this->cIdHash, $oCmsPageDB);
        }
    }

    /**
     * Remove this CMS page instance from the database
     */
    public function remove()
    {
        Shop::DB()->delete('tcmspage', 'cIdHash', $this->cIdHash);
    }

    /**
     * @return array
     */
    public function getRevisions()
    {
        $revision = new Revision();

        return $revision->getRevisions('cmspage', $this->kPage);
    }

    public function lock($cLogin)
    {
        if ($this->cLockedBy !== ''
            && $this->cLockedBy !== $cLogin
            && strtotime($this->dLockedAt) + 60 > time()
        ) {
            throw new Exception("CMS Page is already locked by {$this->cLockedBy}.");
        }

        $this->cLockedBy = $cLogin;
        $pageUpdate      = (object)[
            'cIdHash'   => $this->cIdHash,
            'cLockedBy' => $this->cLockedBy,
            'dLockedAt' => date('Y-m-d H:i:s'),
        ];

        if (Shop::DB()->select('tcmspage', 'cIdHash', $this->cIdHash) === null) {
            $this->kPage = Shop::DB()->insert('tcmspage', $pageUpdate);
        } else {
            Shop::DB()->update('tcmspage', 'cIdHash', $this->cIdHash, $pageUpdate);
        }
    }

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
    }
}
