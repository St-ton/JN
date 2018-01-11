<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class CMS
 */
class CMS
{
    /**
     * @var CMS
     */
    private static $instance = null;

    /**
     * @var array
     */
    public $curPageParameters = [];

    /**
     * @var string
     */
    public $curPageID = '';

    /**
     * @var AdminAccount|null
     */
    public $oAccount = null;

    /**
     * CMS constructor initializes the current page id hash
     */
    private function __construct()
    {
        $this->curPageParameters = Shop::getParameters();
        $this->curPageID         = md5(serialize($this->curPageParameters));
    }

    /**
     * @return CMS
     */
    public static function getInstance()
    {
        return self::$instance === null ? (self::$instance = new self()) : self::$instance;
    }

    /**
     * @param $pageID - current page ID
     * @return CMSPage
     */
    public function getPage($pageID)
    {
        $pageDB = Shop::DB()->select('tcmspage', 'cIdHash', $pageID, null, null, null, null, false, 'kPage');

        if ($pageDB === null) {
            $page          = new CMSPage();
            $page->cIdHash = $pageID;
            return $page;
        }

        return new CMSPage($pageDB->kPage);
    }

    /**
     * @param $pageID string
     * @param $revisionID string
     */
    public function getPageRevision($pageID, $revisionID)
    {
    }

    /**
     * @return CMSPage
     */
    public function getCurrentPage()
    {
        return $this->getPage($this->curPageID);
    }

    /**
     * @param $pageID string
     * @param $pageData array
     */
    public function savePage($pageID, $pageData)
    {
        $oCmsPage          = new CMSPage();
        $oCmsPage->cIdHash = $pageID;
        $oCmsPage->data    = $pageData;
        $oCmsPage->save();
    }

    /**
     * @param $pageID string
     */
    public function lockPage($pageID)
    {
        $this->getPage($pageID)->lock($this->oAccount->account()->cLogin);
    }

    /**
     * @param $pageID string
     */
    public function unlockPage($pageID)
    {
        $this->getPage($pageID)->unlock();
    }

    /**
     * @param int $kPortlet
     * @return CMSPortlet
     * @throws Exception
     */
    public function createPortlet($kPortlet)
    {
        $oDbPortlet = Shop::DB()->select('tcmsportlet', 'kPortlet', $kPortlet);

        if (!is_object($oDbPortlet)) {
            throw new Exception("Portlet ID $kPortlet could not be found in the database.", 404);
        }

        if (isset($oDbPortlet->kPlugin) && $oDbPortlet->kPlugin > 0) {
            $oPlugin    = new Plugin($oDbPortlet->kPlugin);
            $cClass     = 'Portlet' . $oPlugin->oPluginEditorPortletAssoc_arr[$kPortlet]->cClass;
            $cClassPath = $oPlugin->oPluginEditorPortletAssoc_arr[$kPortlet]->cClassAbs;
            require_once $cClassPath;
        } else {
            $cClass = 'Portlet' . $oDbPortlet->cClass;
        }

        return new $cClass($kPortlet);
    }

    /**
     * @return CMSPortlet[]
     */
    public function getPortlets()
    {
        $oDbPortlet_arr = Shop::DB()->selectAll('tcmsportlet', [], []);
        $oPortlet_arr   = [];

        foreach ($oDbPortlet_arr as $i => $oDbPortlet) {
            $oPortlet_arr[] = $this->createPortlet($oDbPortlet->kPortlet);
        }

        return $oPortlet_arr;
    }

    /**
     * @param int $kPortlet
     * @param array $properties
     * @return string
     */
    public function getPortletPreviewHtml($kPortlet, $properties)
    {
        return $this->createPortlet($kPortlet)
            ->setProperties($properties)
            ->getPreviewHtml();
    }

    /**
     * @param int $kPortlet
     * @param array $properties
     * @return string
     */
    public function getPortletConfigPanelHtml($kPortlet, $properties)
    {
        return $this->createPortlet($kPortlet)
            ->setProperties($properties)
            ->getConfigPanelHtml();
    }

    /**
     * @param $kPortlet
     * @return array
     */
    public function getPortletDefaultProps($kPortlet)
    {
        return $this->createPortlet($kPortlet)
            ->getDefaultProps();
    }

    public function setAdminAccount($oAccount)
    {
        $this->oAccount = $oAccount;

        return $this;
    }
}
