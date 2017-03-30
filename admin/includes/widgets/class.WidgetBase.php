<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WidgetBase
 */
class WidgetBase
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
    public function __construct($oSmarty = null, $oDB = null, &$oPlugin)
    {
        $this->oSmarty = Shop::Smarty();
        $this->oDB     = Shop::DB();
        $this->oPlugin = $oPlugin;
        $this->init();
    }

    /**
     *
     */
    public function init()
    {
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return '';
    }

    /**
     * @param string $cURL
     * @param string $cDataName
     * @param string $cTpl
     * @param string $cWrapperID
     * @param string $cPost
     * @param bool $bDecodeUTF8
     * @return IOResponse
     */
    public static function getRemoteDataIO($cURL, $cDataName, $cTpl, $cWrapperID, $cPost = null, $cCallback = null, $bDecodeUTF8 = false)
    {
        $response = new IOResponse();
        $cData    = http_get_contents($cURL, 15, $cPost);
        $oData    = json_decode($cData);
        $oData    = $bDecodeUTF8 ? utf8_convert_recursive($oData) : $oData;
        Shop::Smarty()->assign($cDataName, $oData);;
        $cWrapper = Shop::Smarty()->fetch('tpl_inc/' . $cTpl);
        $response->assign($cWrapperID, 'innerHTML', $cWrapper);

        if ($cCallback !== null) {
            $response->script("if(typeof {$cCallback} === 'function') {$cCallback}({$cData});");
        }

        return $response;
    }
}
