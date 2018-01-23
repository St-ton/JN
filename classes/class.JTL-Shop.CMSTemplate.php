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
     * @var string
     */
    public $cName = '';

    /**
     * @var array
     */
    public $data = [];

    /**
     * @param int $kTemplate
     */
    public function __construct($kTemplate = 0)
    {
        if ($kTemplate > 0) {
            $oCMSTemplateDB = Shop::DB()->select('tcmstemplate', 'kTemplate', $kTemplate);

            if ($oCMSTemplateDB !== null) {
                $this->kTemplate = $oCMSTemplateDB->kTemplate;
                $this->cName     = $oCMSTemplateDB->cName;
                $this->data      = json_decode($oCMSTemplateDB->cJson, true);
            }
        }
    }

    public function getFullPreviewHtml()
    {
        try {
            return CMS::getInstance()
                ->createPortlet($this->data['portletId'])
                ->setProperties($this->data['properties'])
                ->setSubAreas($this->data['subAreas'])
                ->getFullPreviewHtml();
        } catch (Exception $e) {
            // the corresponding portlet of this template could not be created
            return '';
        }
    }

    /**
     * Save this CMS Template to the database
     */
    public function save()
    {
        $oCmsTemplateDB = Shop::DB()->select(
            'tcmstemplate',
            'cName',
            $this->cName
        );

        if ($oCmsTemplateDB === null) {
            $oCmsTemplateDB  = (object)[
                'cName' => $this->cName,
                'cJson' => json_encode($this->data)
            ];
            $this->kTemplate = Shop::DB()->insert('tcmstemplate', $oCmsTemplateDB);
        } else {
            $oCmsTemplateDB->cJson = json_encode($this->data);
            Shop::DB()->update('tcmstemplate', 'cName', $this->cName, $oCmsTemplateDB);
        }
    }

    /**
     * Remove this template from the database
     */
    public function remove()
    {
        Shop::DB()->delete('tcmstemplate', 'cName', $this->cName);
    }
}
