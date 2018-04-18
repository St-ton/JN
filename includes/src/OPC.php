<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class OPC
 */
class OPC
{
    /**
     * @var OPC
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
     * OPC constructor initializes the current page id hash
     */
    private function __construct()
    {
        $this->curPageParameters             = Shop::getParameters();
        $this->curPageParameters['kSprache'] = Shop::getLanguage();
        $this->curPageID                     = md5(serialize($this->curPageParameters));
    }

    /**
     * @return OPC
     */
    public static function getInstance()
    {
        return self::$instance === null ? (self::$instance = new self()) : self::$instance;
    }

    /**
     * @param $pageID - current page ID
     * @param bool $bRenderPreview
     * @return OPCPage
     */
    public function getPage($pageID, $bRenderPreview = false)
    {
        $pageDB = Shop::DB()->select('topcpage', 'cIdHash', $pageID, null, null, null, null, false, 'kPage');

        if ($pageDB === null) {
            $page          = new \OPCPage();
            $page->cIdHash = $pageID;
        } else {
            $page = new \OPCPage($pageDB->kPage);
        }

        if ($bRenderPreview) {
            $page->renderPreview();
        }

        return $page;
    }

    /**
     * @param $pageID string
     * @param $revisionID int
     * @param bool $bRenderPreview
     * @return OPCPage
     */
    public function getPageRevision($pageID, $revisionID, $bRenderPreview = false)
    {
        $page          = new \OPCPage();
        $page->cIdHash = $pageID;
        $page->loadRevision($revisionID);

        if ($bRenderPreview) {
            $page->renderPreview();
        }

        return $page;
    }

    /**
     * @param $pageID
     * @return array
     */
    public function getPageRevisions($pageID)
    {
        return $this
            ->getPage($pageID)
            ->getRevisions();
    }

    /**
     * @return OPCPage
     */
    public function getCurrentPage()
    {
        return $this->getPage($this->curPageID);
    }

    /**
     * @param string $pageID
     * @param string $pageURL
     * @param array $pageData
     */
    public function savePage($pageID, $pageURL, $pageData)
    {
        $oCmsPage           = new \OPCPage();
        $oCmsPage->cIdHash  = $pageID;
        $oCmsPage->cPageUrl = $pageURL;
        $oCmsPage->data     = $pageData;
        $oCmsPage->save();
    }

    /**
     * @param $cTemplateName string
     * @param $templateData array
     */
    public function storeTemplate($cTemplateName, $templateData)
    {
        $oCmsTemplate        = new \OPCTemplate();
        $oCmsTemplate->cName = $cTemplateName;
        $oCmsTemplate->data  = $templateData;
        $oCmsTemplate->save();
    }

    /**
     * @param $kTemplate int
     */
    public function deleteTemplate($kTemplate)
    {
        $oCmsTemplate = new \OPCTemplate($kTemplate);
        $oCmsTemplate->remove();
    }

    /**
     * @param string $pageID
     * @return bool - true if lock was granted
     */
    public function lockPage($pageID)
    {
        return $this->getPage($pageID)->lock($this->oAccount->account()->cLogin);
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
     * @return OPCPortlet
     * @throws Exception
     */
    public function createPortlet($kPortlet)
    {
        $oDbPortlet = Shop::DB()->select('topcportlet', 'kPortlet', $kPortlet);

        if (!is_object($oDbPortlet)) {
            throw new Exception("Portlet ID $kPortlet could not be found in the database.", 404);
        }

        if (isset($oDbPortlet->kPlugin) && $oDbPortlet->kPlugin > 0) {
            $oPlugin    = new Plugin($oDbPortlet->kPlugin);
            $cClass     = 'Portlet' . $oPlugin->oPluginEditorPortletAssoc_arr[$kPortlet]->cClass;
            $cClassPath = $oPlugin->oPluginEditorPortletAssoc_arr[$kPortlet]->cClassAbs;
            require_once $cClassPath;
        } else {
            $cClass = '\Portlets\Portlet' . $oDbPortlet->cClass;
        }

        return new $cClass($kPortlet);
    }

    /**
     * @return OPCPortlet[]
     */
    public function getPortlets()
    {
        $oDbPortlet_arr = Shop::DB()->selectAll('topcportlet', [], []);
        $oPortlet_arr   = [];

        foreach ($oDbPortlet_arr as $i => $oDbPortlet) {
            $oPortlet_arr[$oDbPortlet->cGroup][] = $this->createPortlet($oDbPortlet->kPortlet);
        }

        return $oPortlet_arr;
    }

    /**
     * @param string $cName
     * @return OPCTemplate
     * @throws Exception
     */
    public function createTemplate($cName)
    {
        $oDbTemplate = Shop::DB()->select('topctemplate', 'cName', $cName);

        if (!is_object($oDbTemplate)) {
            throw new Exception("Template name '$cName' could not be found in the database.", 404);
        }

        return new \OPCTemplate($oDbTemplate->kTemplate);
    }

    /**
     * @return OPCTemplate[]
     */
    public function getTemplates()
    {
        $oDbTemplate_arr = Shop::DB()->selectAll('topctemplate', [], []);
        $oTemplate_arr   = [];

        foreach ($oDbTemplate_arr as $i => $oDbTemplate) {
            $oTemplate_arr[] = $this->createTemplate($oDbTemplate->cName)->renderFullPreviewHtml();
        }

        return $oTemplate_arr;
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
     * @param array $portletData
     * @return string
     */
    public function getPortletFullPreviewHtml($kPortlet, $portletData)
    {
        return $this->createPortlet($kPortlet)
            ->setProperties($portletData['properties'])
            ->setSubAreas($portletData['subAreas'])
            ->getFullPreviewHtml();
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

    /**
     * @param $oAccount AdminAccount
     * @return $this
     */
    public function setAdminAccount($oAccount)
    {
        $this->oAccount = $oAccount;

        return $this;
    }

    public function getFilterOptions($filtersEnabled = [])
    {
        $productFilter     = new ProductFilter();
        $filtersEnabledMap = [];

        foreach ($filtersEnabled as $filterEnabled) {
            $filtersEnabledMap[$filterEnabled['className'] . ':' . $filterEnabled['value']] = true;
            $productFilter->addActiveFilter(new $filterEnabled['className']($productFilter), $filterEnabled['value']);
        }

        $productFilter->getProducts();
        $searchResults = $productFilter->getSearchResults(false);

        $res = [];

        foreach (['Category', 'Manufacturer', 'Rating', 'SearchSpecial', 'Tag', 'Attribute', 'PriceRange'] as $term) {
            /** @var FilterOption[] $filterOptions */
            $filterOptions = $searchResults->{"get{$term}FilterOptions"}();

            $res[$term] = [];

            foreach ($filterOptions as $filterOption) {
                if (!array_key_exists(
                    $filterOption->getClassName() . ':' . $filterOption->getValue(),
                    $filtersEnabledMap
                )) {
                    $res[$term][] = [
                        'name'      => $filterOption->getName(),
                        'term'      => $term,
                        'className' => $filterOption->getClassName(),
                        'value'     => $filterOption->getValue(),
                        'count'     => $filterOption->getCount(),
                    ];
                }
            }
        }

        return $res;
    }
}
