<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class CMSTemplate
 */
class CMSTemplate
{
    /**
     * @var int
     */
    public $kTemplate = 0;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var array
     */
    public $cFinalHtml_arr = null;

    /**
     * @param int $kTemplate
     * @throws Exception
     */
    public function __construct($kTemplate = 0)
    {
        if ($kTemplate > 0) {
            $oCMSTemplateDB = Shop::DB()->select('tcmstemplate', 'kTemplate', $kTemplate);

            if ($oCMSTemplateDB === null) {
                throw new Exception('No CMS Template found with the given template id.');
            }

            $this->kTemplate     = $oCMSTemplateDB->kPage;
            $this->data          = json_decode($oCMSTemplateDB->cJson, true);
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
     * Save this CMS Template instance to the database
     */
    public function save()
    {
        $oCmsTemplateDB = Shop::DB()->select(
            'tcmstemplate',
            'cName',
            $this->cName
        );

        if ($oCmsTemplateDB === null) {
            $oCmsTemplateDB = (object)[
                'cName' => $this->cName,
                'cJson' => json_encode($this->data)
            ];
            $this->kTemplate = Shop::DB()->insert('tcmstemplate', $oCmsTemplateDB);
        } else {
            $revision = new Revision();
            $revision->addRevision('cmstempate', (int)$oCmsTemplateDB->kTemplate);
            $oCmsTemplateDB->cJson         = json_encode($this->data);
            $oCmsTemplateDB->dLastModified = date('Y-m-d H:i:s');
            Shop::DB()->update(
                'tcmstemplate',
                'cName',
                $this->cName,
                $oCmsTemplateDB
            );
        }
    }

    /**
     * Remove this CMS page instance from the database
     */
    public function remove()
    {
        Shop::DB()->delete('tcmstemplate', 'cName', $this->cName);
    }

    /**
     * @return array
     */
    public function getRevisions()
    {
        $revision = new Revision();

        return $revision->getRevisions('cmstemplate', $this->kTemplate);
    }
}
