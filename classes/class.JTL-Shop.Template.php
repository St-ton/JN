<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Template
 */
class Template
{
    /**
     * @var bool
     */
    public static $bMobil = false;

    /**
     * @var string
     */
    public static $cTemplate = null;

    /**
     * @var int
     */
    public static $nVersion;

    /**
     * @var Template
     */
    private static $frontEndInstance = null;

    /**
     * @var bool
     */
    private static $isAdmin = false;

    /**
     * @var string|null
     */
    private static $parent = null;

    /**
     * @var TemplateHelper
     */
    private static $helper = null;

    /**
     * @var string
     */
    public $xmlData = null;

    /**
     * @var string
     */
    public $name = null;

    /**
     * @var string
     */
    public $author = null;

    /**
     * @var string
     */
    public $url = null;

    /**
     * @var int
     */
    public $version = null;

    /**
     * @var int
     */
    public $shopVersion = null;

    /**
     * @var string
     */
    public $preview = null;

    /**
     *
     */
    public function __construct()
    {
        self::$helper = TemplateHelper::getInstance(false);
        $this->init();
        $this->xmlData          = self::$helper->getData(self::$cTemplate, false);
        self::$frontEndInstance = $this;
    }

    /**
     * @return Template
     */
    public static function getInstance()
    {
        return (self::$frontEndInstance !== null) ? self::$frontEndInstance : new self();
    }

    /**
     * @return string
     */
    public function init()
    {
        if (isset($_SESSION['template']->cTemplate)) {
            self::$cTemplate   = $_SESSION['template']->cTemplate;
            self::$parent      = $_SESSION['template']->parent;
            $this->name        = $_SESSION['template']->name;
            $this->author      = $_SESSION['template']->author;
            $this->url         = $_SESSION['template']->url;
            $this->version     = $_SESSION['template']->version;
            $this->shopVersion = $_SESSION['template']->shopversion;
            $this->preview     = $_SESSION['template']->preview;

            return $this;
        }
        $bMobil  = (isset($_COOKIE['bMobil']) && $_COOKIE['bMobil']);
        $cacheID = 'current_template_' . (($bMobil === true) ? 'mobile' : 'nonmobile') . ((self::$isAdmin === true) ? '_admin' : '');
        if (($oTemplate = Shop::Cache()->get($cacheID)) !== false) {
            self::$cTemplate   = $oTemplate->cTemplate;
            self::$parent      = $oTemplate->parent;
            $this->name        = $oTemplate->name;
            $this->author      = $oTemplate->author;
            $this->url         = $oTemplate->url;
            $this->version     = $oTemplate->version;
            $this->shopVersion = $oTemplate->shopversion;
            $this->preview     = $oTemplate->preview;

            return $this;
        }
        $type      = $bMobil ? 'mobil' : 'standard';
        $oTemplate = Shop::DB()->select('ttemplate', 'eTyp', $type);
        if (empty($oTemplate)) {
            // fallback if no mobile/standard template exists
            $oTemplate = Shop::DB()->query("SELECT * FROM ttemplate WHERE eTyp IN('mobil', 'standard')", 1);
        }
        if (!empty($oTemplate)) {
            self::$cTemplate   = $oTemplate->cTemplate;
            self::$parent      = (!empty($oTemplate->parent)) ? $oTemplate->parent : null;
            $this->name        = $oTemplate->name;
            $this->author      = $oTemplate->author;
            $this->url         = $oTemplate->url;
            $this->version     = $oTemplate->version;
            $this->shopVersion = $oTemplate->shopversion;
            $this->preview     = $oTemplate->preview;

            $tplObject              = new stdClass();
            $tplObject->cTemplate   = self::$cTemplate;
            $tplObject->isMobile    = self::$bMobil;
            $tplObject->parent      = self::$parent;
            $tplObject->name        = $this->name;
            $tplObject->version     = $this->version;
            $tplObject->author      = $this->author;
            $tplObject->url         = $this->url;
            $tplObject->shopversion = $this->shopVersion;
            $tplObject->preview     = $this->preview;
            $_SESSION['template']   = $tplObject;
            $_SESSION['cTemplate']  = self::$cTemplate;

            Shop::Cache()->set($cacheID, $oTemplate, array(CACHING_GROUP_TEMPLATE));
        }

        return $this;
    }

    /**
     * returns current template's name
     *
     * @return string|null
     */
    public function getFrontendTemplate()
    {
        $frontendTemplate = Shop::DB()->query("SELECT * FROM ttemplate WHERE eTyp = 'standard'", 1);
        self::$cTemplate  = (!empty($frontendTemplate->cTemplate)) ? $frontendTemplate->cTemplate : null;
        self::$parent     = (!empty($frontendTemplate->parent)) ? $frontendTemplate->parent : null;

        return self::$cTemplate;
    }

    /**
     * @param null|string $dir
     * @return null|SimpleXMLElement|SimpleXMLObject
     */
    public function leseXML($dir = null)
    {
        $dir = ($dir !== null) ? $dir : self::$cTemplate;

        return self::$helper->getXML($dir);
    }

    /**
     * get registered plugin resources (js/css)
     *
     * @return array
     */
    public function getPluginResources()
    {
        $pluginResCSS = Shop::DB()->query("
            SELECT * FROM tplugin_resources
                JOIN tplugin ON tplugin.kPlugin = tplugin_resources.kPlugin
                WHERE tplugin_resources.type = 'CSS'
                    AND tplugin.nStatus = 2
                    AND (tplugin_resources.conditional IS NULL
                    OR tplugin_resources.conditional = '')
                ORDER BY tplugin_resources.priority DESC", 2
        );
        if (!is_array($pluginResCSS)) {
            $pluginResCSS = array();
        }
        $pluginResCSSconditional = Shop::DB()->query("
            SELECT * FROM tplugin_resources
                JOIN tplugin ON tplugin.kPlugin = tplugin_resources.kPlugin
                WHERE tplugin_resources.type='CSS'
                    AND tplugin.nStatus = 2
                    AND tplugin_resources.conditional IS NOT NULL
                    AND tplugin_resources.conditional != ''
                ORDER BY tplugin_resources.priority DESC", 2
        );
        if (!is_array($pluginResCSSconditional)) {
            $pluginResCSSconditional = array();
        }
        $pluginResJSHead = Shop::DB()->query("
            SELECT * FROM tplugin_resources
                JOIN tplugin
                    ON tplugin.kPlugin = tplugin_resources.kPlugin
                WHERE tplugin_resources.type = 'JS'
                    AND tplugin_resources.position = 'head'
                    AND tplugin.nStatus = 2
                ORDER BY tplugin_resources.priority DESC", 2
        );
        if (!is_array($pluginResJSHead)) {
            $pluginResJSHead = array();
        }
        $pluginResJSBody = Shop::DB()->query("
            SELECT * FROM tplugin_resources
                JOIN tplugin
                    ON tplugin.kPlugin = tplugin_resources.kPlugin
                WHERE tplugin_resources.type = 'JS'
                    AND tplugin_resources.position = 'body'
                    AND tplugin.nStatus = 2
                ORDER BY tplugin_resources.priority DESC", 2
        );
        if (!is_array($pluginResJSBody)) {
            $pluginResJSBody = array();
        }

        return array(
            'css'             => $this->getPluginResourcesPath($pluginResCSS),
            'css_conditional' => $this->getPluginResourcesPath($pluginResCSSconditional),
            'js_head'         => $this->getPluginResourcesPath($pluginResJSHead),
            'js_body'         => $this->getPluginResourcesPath($pluginResJSBody)
        );
    }

    /**
     * get resource path for single plugins
     *
     * @param array $items
     * @return array
     */
    private function getPluginResourcesPath($items)
    {
        foreach ($items as &$item) {
            $item->abs = PFAD_ROOT . PFAD_PLUGIN . $item->cVerzeichnis . '/' .
                PFAD_PLUGIN_VERSION . $item->nVersion . '/' .
                PFAD_PLUGIN_FRONTEND . $item->type . '/' . $item->path;
            $item->rel = PFAD_PLUGIN . $item->cVerzeichnis . '/' .
                PFAD_PLUGIN_VERSION . $item->nVersion . '/' .
                PFAD_PLUGIN_FRONTEND . $item->type . '/' . $item->path;
        }

        return $items;
    }

    /**
     * parse node of js/css files for insertion conditions and validate them
     *
     * @param SimpleXMLElement $node
     * @return bool
     */
    private function checkCondition($node)
    {
        $_settingsGroup     = constant((string) $node->attributes()->DependsOnSettingGroup);
        $_settingValue      = (string) $node->attributes()->DependsOnSettingValue;
        $_settingComparison = (string) $node->attributes()->DependsOnSettingComparison;
        $_setting           = (string) $node->attributes()->DependsOnSetting;
        $conf               = Shop::getConfig(array($_settingsGroup));
        $hierarchy          = explode('.', $_setting);
        $iterations         = count($hierarchy);
        $i                  = 0;
        $optionsOK          = false;
        if (empty($_settingComparison)) {
            $_settingComparison = '==';
        }
        foreach ($hierarchy as $_h) {
            $conf = (isset($conf[$_h])) ? $conf[$_h] : null;
            if ($conf === null) {
                return false;
            }
            if (++$i === $iterations) {
                switch ($_settingComparison) {
                    case '==' :
                        return ($conf == $_settingValue);
                    case '===' :
                        return ($conf === $_settingValue);
                    case '>=' :
                        return ($conf >= $_settingValue);
                    case '<=' :
                        return ($conf <= $_settingValue);
                    case '>' :
                        return ($conf > $_settingValue);
                    case '<' :
                        return ($conf < $_settingValue);
                    default :
                        return false;
                }
            }
        }

        return $optionsOK;
    }

    /**
     * get array of static resources in minify compatible format
     *
     * @param bool $absolute
     * @return array|mixed
     */
    public function getMinifyArray($absolute = false)
    {
        $cOrdner    = $this->getDir();
        $folders    = array();
        $res        = array();
        $parentHash = '';
        if (self::$parent !== null) {
            $parentHash = self::$parent;
            $folders[]  = self::$parent;
        }
        $folders[] = $cOrdner;
        $cacheID   = 'tpl_mnfy_dt_' . $cOrdner . $parentHash;
        if (($tplGroups_arr = Shop::Cache()->get($cacheID)) === false) {
            $tplGroups_arr = array();
            foreach ($folders as $cOrdner) {
                $oXML = self::$helper->getXML($cOrdner);
                if (isset($oXML->Minify->CSS)) {
                    foreach ($oXML->Minify->CSS as $oCSS) {
                        $name = (string) $oCSS->attributes()->Name;
                        if (!isset($tplGroups_arr[$name])) {
                            $tplGroups_arr[$name] = array();
                        }
                        foreach ($oCSS->File as $oFile) {
                            $cFile     = (string) $oFile->attributes()->Path;
                            $cFilePath = (self::$isAdmin === false) ?
                                PFAD_ROOT . PFAD_TEMPLATES . $oXML->Ordner . '/' . $cFile :
                                PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES . $oXML->Ordner . '/' . $cFile;
                            if (file_exists($cFilePath) && (empty($oFile->attributes()->DependsOnSetting) || $this->checkCondition($oFile) === true)) {
                                $_file           = PFAD_TEMPLATES . $cOrdner . '/' . (string) $oFile->attributes()->Path;
                                $cCustomFilePath = str_replace('.css', '_custom.css', $cFilePath);
                                if (file_exists($cCustomFilePath)) { //add _custom file if existing
                                    $_file                  = str_replace('.css', '_custom.css', PFAD_TEMPLATES . $cOrdner . '/' . (string) $oFile->attributes()->Path);
                                    $tplGroups_arr[$name][] = array(
                                        'idx' => str_replace('.css', '_custom.css', (string) $oFile->attributes()->Path),
                                        'abs' => realpath(PFAD_ROOT  . $_file),
                                        'rel' => $_file
                                    );
                                } else { //otherwise add normal file
                                    $tplGroups_arr[$name][] = array(
                                        'idx' => $cFile,
                                        'abs' => realpath(PFAD_ROOT  . $_file),
                                        'rel' => $_file
                                    );
                                }
                            }
                        }
                    }
                } else {
                    $tplGroups_arr['admin_css'] = array();
                }
                if (isset($oXML->Minify->JS)) {
                    foreach ($oXML->Minify->JS as $oJS) {
                        $name = (string) $oJS->attributes()->Name;
                        if (!isset($tplGroups_arr[$name])) {
                            $tplGroups_arr[$name] = array();
                        }
                        foreach ($oJS->File as $oFile) {
                            if (empty($oFile->attributes()->DependsOnSetting) || $this->checkCondition($oFile) === true) {
                                $_file    = PFAD_TEMPLATES . $cOrdner . '/' . (string) $oFile->attributes()->Path;
                                $newEntry = array(
                                    'idx' => (string) $oFile->attributes()->Path,
                                    'abs' => PFAD_ROOT  . $_file,
                                    'rel' => $_file
                                );

                                $found = false;
                                if (!empty($oFile->attributes()->override) && (string) $oFile->attributes()->override === 'true') {
                                    $idxToOverride = (string) $oFile->attributes()->Path;
                                    $max           = count($tplGroups_arr[$name]);
                                    for ($i = 0; $i < $max; $i++) {
                                        if ($tplGroups_arr[$name][$i]['idx'] === $idxToOverride) {
                                            $tplGroups_arr[$name][$i] = $newEntry;
                                            $found                    = true;
                                            break;
                                        }
                                    }
                                }
                                if ($found === false) {
                                    $tplGroups_arr[$name][] = $newEntry;
                                }
                            }
                        }
                    }
                }
                $pluginRes                   = $this->getPluginResources();
                $tplGroups_arr['plugin_css'] = array();
                foreach ($pluginRes['css'] as $_cssRes) {
                    $cCustomFilePath = str_replace('.css', '_custom.css', $_cssRes->abs);
                    if (file_exists($cCustomFilePath)) {
                        $tplGroups_arr['plugin_css'][] = array(
                            'idx' => $_cssRes->cName,
                            'abs' => $cCustomFilePath,
                            'rel' => str_replace('.css', '_custom.css', $_cssRes->rel)
                        );
                    } else {
                        $tplGroups_arr['plugin_css'][] = array(
                            'idx' => $_cssRes->cName,
                            'abs' => $_cssRes->abs,
                            'rel' => $_cssRes->rel
                        );
                    }
                }
                $tplGroups_arr['plugin_css_conditional'] = array();
                foreach ($pluginRes['css_conditional'] as $_csscRes) {
                    $tplGroups_arr['css_conditional'][] = array(
                        'idx' => $_csscRes->cName,
                        'abs' => $_csscRes->abs,
                        'rel' => $_csscRes->rel
                    );
                }
                $tplGroups_arr['plugin_js_head'] = array();
                foreach ($pluginRes['js_head'] as $_jshRes) {
                    $tplGroups_arr['plugin_js_head'][] = array(
                        'idx' => $_jshRes->cName,
                        'abs' => $_jshRes->abs,
                        'rel' => $_jshRes->rel
                    );
                }
                $tplGroups_arr['plugin_js_body'] = array();
                foreach ($pluginRes['js_body'] as $_jsbRes) {
                    $tplGroups_arr['plugin_js_body'][] = array(
                        'idx' => $_jsbRes->cName,
                        'abs' => $_jsbRes->abs,
                        'rel' => $_jsbRes->rel
                    );
                }
            }
            $cacheTags = array(CACHING_GROUP_OPTION, CACHING_GROUP_TEMPLATE, CACHING_GROUP_PLUGIN);
            executeHook(HOOK_CSS_JS_LIST, array('groups' => &$tplGroups_arr, 'cache_tags' => &$cacheTags));
            Shop::Cache()->set($cacheID, $tplGroups_arr, $cacheTags);
        }
        foreach ($tplGroups_arr as $name => $_tplGroup) {
            $res[$name] = array();
            foreach ($_tplGroup as $_file) {
                $res[$name][] = ($absolute === true) ?
                    $_file['abs'] :
                    $_file['rel'];
            }
        }

        return $res;
    }

    /**
     * @return bool|mixed
     */
    private function getMobileTemplate()
    {
        $cacheID = 'mobile_template';
        if (($oTemplate = Shop::Cache()->get($cacheID)) === false) {
            $oTemplate = Shop::DB()->query("SELECT cTemplate FROM ttemplate WHERE eTyp='mobil'", 1);
            if ($oTemplate === false) {
                Shop::Cache()->set($cacheID, 'false', array(CACHING_GROUP_TEMPLATE));
            } else {
                Shop::Cache()->set($cacheID, $oTemplate, array(CACHING_GROUP_TEMPLATE));
            }
        }
        //workaround for saving bool values to cache
        if ($oTemplate === 'false') {
            $oTemplate = false;
        }

        return $oTemplate;
    }

    /**
     * check if is mobile
     *
     * @return bool
     */
    public function hasMobileTemplate()
    {
        $oTemplate = $this->getMobileTemplate();

        return $oTemplate ? true : false;
    }

    /**
     * check if mobile is active
     *
     * @return bool
     */
    public function isMobileTemplateActive()
    {
        $oTemplate = $this->getMobileTemplate();
        if ($oTemplate && $oTemplate->cTemplate === $_SESSION['cTemplate']) {
            return true;
        }

        return false;
    }

    /**
     * get current template's active skin
     *
     * @return string
     */
    public function getSkin()
    {
        $cSkin = Shop::DB()->query("
            SELECT cWert
                FROM ttemplateeinstellungen
                WHERE cName='theme_default'
                    AND cSektion='theme'
                    AND cTemplate='" . self::$cTemplate . "'", 1
        );

        return (isset($cSkin->cWert)) ? $cSkin->cWert : null;
    }

    /**
     * @param bool $bMobil
     * @return $this
     */
    public function setzeKundenTemplate($bMobil = false)
    {
        if (!$this->hasMobileTemplate()) {
            $bMobil = false;
        }
        setcookie('bMobil', $bMobil);
        self::$bMobil = $bMobil;
        unset($_SESSION['template']);
        unset($_SESSION['cTemplate']);
        $this->init();

        return $this;
    }

    /**
     * @param string      $folder - the current template's dir name
     * @param string|null $parent
     * @return array|null
     */
    public function leseEinstellungenXML($folder, $parent = null)
    {
        self::$cTemplate = $folder;
        $oDBSettings     = $this->getConfig();
        $folder          = array($folder);
        if ($parent !== null) {
            $folder[] = $parent;
        }
        $oSection_arr = array();
        foreach ($folder as $cOrdner) {
            $oXML = self::$helper->getXML($cOrdner);
            if ($oXML && isset($oXML->Settings) && isset($oXML->Settings->Section)) {
                foreach ($oXML->Settings->Section as $oXMLSection) {
                    $sectionID = (string) $oXMLSection->attributes()->Key;
                    $exists    = false;
                    foreach ($oSection_arr as &$_section) {
                        if ($_section->cKey === $sectionID) {
                            $exists   = true;
                            $oSection = $_section;
                            break;
                        }
                    }
                    if (!$exists) {
                        $oSection                = new stdClass();
                        $oSection->cName         = utf8_decode((string) $oXMLSection->attributes()->Name);
                        $oSection->cKey          = $sectionID;
                        $oSection->oSettings_arr = array();
                    }
                    foreach ($oXMLSection->Setting as $XMLSetting) {
                        $oSetting                = new stdClass();
                        $oSetting->rawAttributes = array();
                        $atts                    = $XMLSetting->attributes();
                        foreach ($atts as $_k => $_attr) {
                            $oSetting->rawAttributes[$_k] = (string) $_attr;
                        }
                        $oSetting->cName        = utf8_decode((string) $XMLSetting->attributes()->Description);
                        $oSetting->cKey         = utf8_decode((string) $XMLSetting->attributes()->Key);
                        $oSetting->cType        = (string) $XMLSetting->attributes()->Type;
                        $oSetting->cValue       = (string) $XMLSetting->attributes()->Value;
                        $oSetting->bEditable    = (string) $XMLSetting->attributes()->Editable;
                        $oSetting->cPlaceholder = (string) $XMLSetting->attributes()->Placeholder;

                        $settingExists = false;
                        foreach ($oSection->oSettings_arr as $_setting) {
                            if ($_setting->cKey === $oSetting->cKey) {
                                $settingExists = true;
                                $oSetting      = $_setting;
                                break;
                            }
                        }
                        $oSetting->bEditable = (strlen($oSetting->bEditable) === 0) ? true : (boolean) intval($oSetting->bEditable);
                        if (isset($oDBSettings[$oSection->cKey][$oSetting->cKey]) && $oSetting->bEditable) {
                            $oSetting->cValue = $oDBSettings[$oSection->cKey][$oSetting->cKey];
                        }
                        if (isset($XMLSetting->Option)) {
                            if (!isset($oSetting->oOptions_arr)) {
                                $oSetting->oOptions_arr = array();
                            }
                            foreach ($XMLSetting->Option as $XMLOption) {
                                $oOption                  = new stdClass();
                                $oOption->cName           = (string) $XMLOption;
                                $oOption->cValue          = (string) $XMLOption->attributes()->Value;
                                $oOption->cOrdner         = $cOrdner; //add current folder to option - useful for theme previews
                                $oSetting->oOptions_arr[] = $oOption;
                            }
                        }
                        if (isset($XMLSetting->Optgroup)) {
                            if (!isset($oSetting->oOptgroup_arr)) {
                                $oSetting->oOptgroup_arr = array();
                            }
                            foreach ($XMLSetting->Optgroup as $XMLOptgroup) {
                                $oOptgroup              = new stdClass();
                                $oOptgroup->cName       = (string) $XMLOptgroup->attributes()->label;
                                $oOptgroup->oValues_arr = array();
                                foreach ($XMLOptgroup->Option as $XMLOptgroupOption) {
                                    $oOptgroupValues          = new stdClass();
                                    $oOptgroupValues->cName   = (string) $XMLOptgroupOption;
                                    $oOptgroupValues->cValue  = (string) $XMLOptgroupOption->attributes()->Value;
                                    $oOptgroup->oValues_arr[] = $oOptgroupValues;
                                }
                                $oSetting->oOptgroup_arr[] = $oOptgroup;
                            }
                        }
                        if (!$settingExists) {
                            $oSection->oSettings_arr[] = $oSetting;
                        }
                    }
                    if (!$exists) {
                        $oSection_arr[] = $oSection;
                    }
                }
            }
        }

        return count($oSection_arr) > 0 ? $oSection_arr : null;
    }

    /**
     * @param string|null $cOrdner
     * @return array|bool
     */
    public function getBoxLayoutXML($cOrdner = null)
    {
        $cOrdner   = ($cOrdner !== null) ? $cOrdner : self::$cTemplate;
        $oItem_arr = array();
        if (self::$parent !== null) {
            $oXML = self::$helper->getXML(self::$parent);
            if ($oXML && isset($oXML->Boxes) && count($oXML->Boxes) === 1) {
                $oXMLBoxes_arr = $oXML->Boxes[0];
                foreach ($oXMLBoxes_arr as $oXMLContainer) {
                    $cPosition             = (string) $oXMLContainer->attributes()->Position;
                    $bAvailable            = (boolean) intval($oXMLContainer->attributes()->Available);
                    $oItem_arr[$cPosition] = $bAvailable;
                }
            }
        }
        $oXML = self::$helper->getXML($cOrdner);
        if ($oXML && isset($oXML->Boxes) && count($oXML->Boxes) === 1) {
            $oXMLBoxes_arr = $oXML->Boxes[0];
            foreach ($oXMLBoxes_arr as $oXMLContainer) {
                $cPosition             = (string) $oXMLContainer->attributes()->Position;
                $bAvailable            = (boolean) intval($oXMLContainer->attributes()->Available);
                $oItem_arr[$cPosition] = $bAvailable;
            }
        }

        return (count($oItem_arr) > 0) ? $oItem_arr : false;
    }

    /**
     * @param string $cOrdner
     * @return array|bool
     * @todo: self::$parent
     */
    public function leseLessXML($cOrdner)
    {
        $oXML = self::$helper->getXML($cOrdner);
        if ($oXML && isset($oXML->Lessfiles)) {
            $oLessFiles_arr = array();
            foreach ($oXML->Lessfiles->THEME as $oXMLTheme) {
                $oTheme             = new stdClass();
                $oTheme->cName      = (string) $oXMLTheme->attributes()->Name;
                $oTheme->oFiles_arr = array();
                foreach ($oXMLTheme->File as $cFile) {
                    $oThemeFiles          = new stdClass();
                    $oThemeFiles->cPath   = (string) $cFile->attributes()->Path;
                    $oTheme->oFiles_arr[] = $oThemeFiles;
                }
                $oLessFiles_arr[$oTheme->cName] = $oTheme;
            }

            return $oLessFiles_arr;
        }

        return array();
    }

    /**
     * set new frontend template
     *
     * @param string $cOrdner
     * @param string $eTyp
     * @return mixed
     */
    public function setTemplate($cOrdner, $eTyp = 'standard')
    {
        Shop::DB()->query("DELETE FROM ttemplate WHERE eTyp = '" . $eTyp . "' OR cTemplate = '" . $cOrdner . "'", 4);
        $tplConfig = self::$helper->getXML($cOrdner);
        if (!empty($tplConfig->Parent)) {
            if (!is_dir(PFAD_ROOT . PFAD_TEMPLATES . $tplConfig->Parent)) {
                return false;
            }
            self::$parent = $tplConfig->Parent;
        }
        $tplObject              = new stdClass();
        $tplObject->cTemplate   = $cOrdner;
        $tplObject->eTyp        = $eTyp;
        $tplObject->parent      = !empty($tplConfig->Parent) ? (string) $tplConfig->Parent : '_DBNULL_';
        $tplObject->name        = (string) $tplConfig->Name;
        $tplObject->author      = (string) $tplConfig->Author;
        $tplObject->url         = (string) $tplConfig->URL;
        $tplObject->version     = (float) $tplConfig->Version;
        $tplObject->shopversion = (int) $tplConfig->ShopVersion;
        $tplObject->preview     = (string) $tplConfig->Preview;
        $bCheck                 = Shop::DB()->insert('ttemplate', $tplObject);
        if ($bCheck) {
            if (!$dh = @opendir(PFAD_ROOT . PFAD_COMPILEDIR)) {
                return false;
            }
            while (($obj = readdir($dh))) {
                if ($obj{0} == '.') {
                    continue;
                }
                if (!is_dir(PFAD_ROOT . PFAD_COMPILEDIR . $obj)) {
                    unlink(PFAD_ROOT . PFAD_COMPILEDIR . $obj);
                }
            }
        }
        Shop::Cache()->flushTags(array(CACHING_GROUP_OPTION, CACHING_GROUP_TEMPLATE));

        return $bCheck;
    }

    /**
     * get template configuration
     *
     * @return array|bool
     */
    public function getConfig()
    {
        return self::$helper->getConfig(self::$cTemplate);
    }

    /**
     * set template configuration
     *
     * @param string $cOrdner
     * @param string $cSektion
     * @param string $cName
     * @param string $cWert
     * @return $this
     */
    public function setConfig($cOrdner, $cSektion, $cName, $cWert)
    {
        $oSetting = Shop::DB()->select('ttemplateeinstellungen', 'cTemplate', $cOrdner, 'cSektion', $cSektion, 'cName', $cName);
        if (isset($oSetting->cTemplate)) {
            $_keys       = array('cTemplate', 'cSektion', 'cName');
            $_values     = array($cOrdner, $cSektion, $cName);
            $_upd        = new stdClass();
            $_upd->cWert = $cWert;
            Shop::DB()->update('ttemplateeinstellungen', $_keys, $_values, $_upd);
        } else {
            $_ins            = new stdClass();
            $_ins->cTemplate = $cOrdner;
            $_ins->cSektion  = $cSektion;
            $_ins->cName     = $cName;
            $_ins->cWert     = $cWert;
            Shop::DB()->insert('ttemplateeinstellungen', $_ins);
        }
        Shop::Cache()->flushTags(array(CACHING_GROUP_OPTION, CACHING_GROUP_TEMPLATE));

        return $this;
    }

    /**
     * @return bool
     */
    public function IsMobile()
    {
        return self::$bMobil;
    }

    /**
     * @param bool $absolute
     * @return string
     */
    public function getDir($absolute = false)
    {
        return ($absolute) ? (PFAD_ROOT . PFAD_TEMPLATES . self::$cTemplate) : self::$cTemplate;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getParent()
    {
        return self::$parent;
    }

    /**
     * @return float
     */
    public function getVersion()
    {
        return floatval($this->version);
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * @return TemplateHelper
     */
    public function getHelper()
    {
        return self::$helper;
    }

    /**
     * @return string
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * @return int
     */
    public function getShopVersion()
    {
        return $this->shopVersion;
    }

    /**
     * @param bool $bRedirect
     */
    public function check($bRedirect = true)
    {
        if (isset($_GET['mt'])) {
            $this->setzeKundenTemplate((boolean) intval($_GET['mt']));
            $cUrlShop_arr    = parse_url(Shop::getURL());
            $ref             = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
            $cUrlReferer_arr = parse_url($ref);
            if ($bRedirect && $ref !== '' && (strtolower($cUrlShop_arr['host']) === strtolower($cUrlReferer_arr['host']))) {
                $cReferer = preg_replace('/&?mt=[^&]*/', '', $_SERVER['HTTP_REFERER']);
                header('Location: ' . $cReferer);
                exit;
            }
        }
    }

    /**
     * get the current template folder
     *
     * @param bool $bCache
     * @return string|null
     * @deprecated since 4.0
     */
    public function holeAktuellenTemplateOrdner($bCache = true)
    {
        return self::$cTemplate;
    }

    /**
     * @param string|null $cOrdner
     * @return array|bool
     * @deprecated since 4.0
     */
    public function leseBoxenContainerXML($cOrdner = null)
    {
        return $this->getBoxLayoutXML($cOrdner);
    }

    /**
     * @param bool $bCache
     * @return string
     * @deprecated since 4.0
     */
    public function getShopTemplate($bCache = true)
    {
        return $this->getDir();
    }

    /**
     * check if is mobile
     *
     * @return bool
     * @deprecated since 4.0
     */
    public function hatMobilTemplate()
    {
        return $this->hasMobileTemplate();
    }

    /**
     * check if mobile is active
     *
     * @return bool
     * @deprecated since 4.0
     */
    public function mobilTemplateAktiv()
    {
        return $this->isMobileTemplateActive();
    }
}
