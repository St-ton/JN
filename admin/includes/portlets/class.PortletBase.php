<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PortletBase
 */
class PortletBase
{
    /**
     * @var JTLSmarty
     */
    public $oSmarty;

    /**
     * @var NiceDB
     */
    public $oDB;

    /**
     * @var Plugin
     */
    public $oPlugin;

    /**
     * @param JTLSmarty $oSmarty
     * @param NiceDB    $oDB
     * @param Plugin    $oPlugin
     */
    public function __construct($oSmarty = null, $oDB = null, $oPlugin = null)
    {
        $this->oSmarty = Shop::Smarty();
        $this->oDB     = Shop::DB();
        $this->oPlugin = $oPlugin;
    }

    /**
     * @return string
     */
    public function getPreviewContent($settings = null)
    {
        return '';
    }

    /**
     * @return string
     */
    public function getHTMLContent($portletData)
    {
        return '';
    }

    /**
     * @return string
     */
    public function getSettingsHTML($settings)
    {
        return '';
    }

    /**
     * @return array - associative array that maps setting names to default values
     */
    public function getInitialSettings()
    {
        return [];
    }

    /**
     * @param $kPortlet
     * @param $smarty
     * @param $shopDB
     * @param null $plugin
     * @return PortletBase
     */
    public static function createInstance($kPortlet, $smarty, $shopDB, $plugin = null)
    {
        $oPortlet   = Shop::DB()->select('teditorportlets', 'kPortlet', $kPortlet);
        $cClass     = 'Portlet' . $oPortlet->cClass;
        $cClassFile = 'class.' . $cClass . '.php';
        $cClassPath = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . $cClassFile;

        $oPlugin = null;
        if (isset($oPortlet->kPlugin) && $oPortlet->kPlugin > 0) {
            $oPlugin    = new Plugin($oPortlet->kPlugin);
            $cClass     = 'Portlet' . $oPlugin->oPluginEditorPortletAssoc_arr[$oPortlet->kPortlet]->cClass;
            $cClassPath = $oPlugin->oPluginEditorPortletAssoc_arr[$oPortlet->kPortlet]->cClassAbs;
        }

        require_once $cClassPath;

        return new $cClass($smarty, $shopDB, $plugin);
    }
}
