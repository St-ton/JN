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
    public $cKey = '';

    /**
     * @var int
     */
    public $kKey = 0;

    /**
     * @var int
     */
    public $kSprache = 0;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var array
     */
    public $cFinalHtml_arr = null;

    /**
     * @param int $kPage
     * @throws Exception
     */
    public function __construct($kPage = 0)
    {
        if ($kPage > 0) {
            $oCMSPageDB = Shop::DB()->select('tcmspage', 'kPage', $kPage);

            if ($oCMSPageDB === null) {
                throw new Exception('No CMS Page found with the given page id.');
            }

            $this->kPage    = $oCMSPageDB->kPage;
            $this->cKey     = $oCMSPageDB->cKey;
            $this->kKey     = $oCMSPageDB->kKey;
            $this->kSprache = $oCMSPageDB->kSprache;
            $this->data     = json_decode($oCMSPageDB->cJson, true);
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
                    $cHtml .= CMS::createPortlet($portlet['portletId'])
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

    /**
     * Save this CMS page instance to the database
     */
    public function save()
    {
        $oCmsPageDB = Shop::DB()->select(
            'tcmspage', ['cKey', 'kKey', 'kSprache'], [$this->cKey, $this->kKey, $this->kSprache]
        );

        if ($oCmsPageDB === null) {
            $oCmsPageDB = (object)[
                'cKey' => $this->cKey,
                'kKey' => $this->kKey,
                'kSprache' => $this->kSprache,
                'cJson' => json_encode($this->data),
            ];
            $this->kPage = Shop::DB()->insert('tcmspage', $oCmsPageDB);
        } else {
            $oCmsPageDB->cJson = json_encode($this->data);
            Shop::DB()->update(
                'tcmspage',
                ['cKey', 'kKey', 'kSprache'],
                [$this->cKey, $this->kKey, $this->kSprache], $oCmsPageDB
            );
        }
    }

    /**
     * Remove this CMS page instance from the database
     */
    public function remove()
    {
        Shop::DB()->delete('tcmspage', ['cKey', 'kKey', 'kSprache'], [$this->cKey, $this->kKey, $this->kSprache]);
    }
}
