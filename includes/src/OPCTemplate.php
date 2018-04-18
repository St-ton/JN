<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class OPCTemplate
 */
class OPCTemplate
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
     * @var string
     */
    public $fullPreviewHtml = '';

    /**
     * @param int $kTemplate
     */
    public function __construct($kTemplate = 0)
    {
        if ($kTemplate > 0) {
            $oCMSTemplateDB = Shop::DB()->select('topctemplate', 'kTemplate', $kTemplate);

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
            return OPC::getInstance()
                ->createPortlet($this->data['portletId'])
                ->setProperties($this->data['properties'])
                ->setSubAreas($this->data['subAreas'])
                ->getFullPreviewHtml();
        } catch (Exception $e) {
            // the corresponding portlet of this template could not be created
            return '';
        }
    }

    public function renderFullPreviewHtml()
    {
        $this->fullPreviewHtml = $this->getFullPreviewHtml();

        return $this;
    }

    /**
     * Save this OPC Template to the database
     */
    public function save()
    {
        $oCmsTemplateDB = Shop::DB()->select(
            'topctemplate',
            'cName',
            $this->cName
        );

        if ($oCmsTemplateDB === null) {
            $oCmsTemplateDB  = (object)[
                'cName' => $this->cName,
                'cJson' => json_encode($this->data)
            ];
            $this->kTemplate = Shop::DB()->insert('topctemplate', $oCmsTemplateDB);
        } else {
            $oCmsTemplateDB->cJson = json_encode($this->data);
            Shop::DB()->update('topctemplate', 'cName', $this->cName, $oCmsTemplateDB);
        }
    }

    /**
     * Remove this template from the database
     */
    public function remove()
    {
        Shop::DB()->delete('topctemplate', 'cName', $this->cName);
    }
}
