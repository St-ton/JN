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
     * @var string
     */
    public static $cTemplate;

    /**
     * @var int
     */
    public static $nVersion;

    /**
     * @var Template
     */
    private static $frontEndInstance;

    /**
     * @var bool
     */
    private static $isAdmin = false;

    /**
     * @var string
     */
    private static $parent;

    /**
     * @var TemplateHelper
     */
    private static $helper;

    /**
     * @var string
     */
    public $xmlData;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $author;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $version;

    /**
     * @var int
     */
    public $shopVersion;

    /**
     * @var string
     */
    public $preview;

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
    public static function getInstance(): self
    {
        return self::$frontEndInstance ?? new self();
    }

    /**
     * @return $this
     */
    public function init(): self
    {
        if (isset($_SESSION['template']->cTemplate)) {
            self::$cTemplate   = $_SESSION['template']->cTemplate;
            self::$parent      = $_SESSION['template']->parent;
            $this->name        = $_SESSION['template']->name;
            $this->author      = $_SESSION['template']->author;
            $this->url         = $_SESSION['template']->url;
            $this->version     = $_SESSION['template']->version;
            $this->shopVersion = (int)$_SESSION['template']->shopversion;
            $this->preview     = $_SESSION['template']->preview;

            return $this;
        }
        $cacheID = 'current_template_' .
            (self::$isAdmin === true ? '_admin' : '');
        if (($oTemplate = Shop::Cache()->get($cacheID)) !== false) {
            self::$cTemplate   = $oTemplate->cTemplate;
            self::$parent      = $oTemplate->parent;
            $this->name        = $oTemplate->name;
            $this->author      = $oTemplate->author;
            $this->url         = $oTemplate->url;
            $this->version     = $oTemplate->version;
            $this->shopVersion = (int)$oTemplate->shopversion;
            $this->preview     = $oTemplate->preview;

            return $this;
        }
        $oTemplate = Shop::Container()->getDB()->select('ttemplate', 'eTyp', 'standard');
        if (!empty($oTemplate)) {
            self::$cTemplate   = $oTemplate->cTemplate;
            self::$parent      = !empty($oTemplate->parent) ? $oTemplate->parent : null;
            $this->name        = $oTemplate->name;
            $this->author      = $oTemplate->author;
            $this->url         = $oTemplate->url;
            $this->version     = $oTemplate->version;
            $this->shopVersion = (int)$oTemplate->shopversion;
            $this->preview     = $oTemplate->preview;

            $tplObject              = new stdClass();
            $tplObject->cTemplate   = self::$cTemplate;
            $tplObject->isMobile    = false;
            $tplObject->parent      = self::$parent;
            $tplObject->name        = $this->name;
            $tplObject->version     = $this->version;
            $tplObject->author      = $this->author;
            $tplObject->url         = $this->url;
            $tplObject->shopversion = (int)$this->shopVersion;
            $tplObject->preview     = $this->preview;
            $_SESSION['template']   = $tplObject;
            $_SESSION['cTemplate']  = self::$cTemplate;

            Shop::Cache()->set($cacheID, $oTemplate, [CACHING_GROUP_TEMPLATE]);
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
        $frontendTemplate = Shop::Container()->getDB()->select('ttemplate', 'eTyp', 'standard');
        self::$cTemplate  = empty($frontendTemplate->cTemplate) ? null : $frontendTemplate->cTemplate;
        self::$parent     = empty($frontendTemplate->parent) ? null : $frontendTemplate->parent;

        return self::$cTemplate;
    }

    /**
     * @param null|string $dir
     * @return null|SimpleXMLElement|SimpleXMLObject
     */
    public function leseXML($dir = null)
    {
        return self::$helper->getXML($dir ?? self::$cTemplate);
    }

    /**
     * get registered plugin resources (js/css)
     *
     * @return array
     */
    public function getPluginResources(): array
    {
        $resourcesc = Shop::Container()->getDB()->query(
            "SELECT * FROM tplugin_resources
                JOIN tplugin
                    ON tplugin.kPlugin = tplugin_resources.kPlugin
                WHERE tplugin.nStatus = 2
                ORDER BY tplugin_resources.priority DESC",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $grouped    = \Functional\group($resourcesc, function ($e) {
            return $e->type;
        });
        if (isset($grouped['js'])) {
            $grouped['js'] = \Functional\group($grouped['js'], function ($e) {
                return $e->position;
            });
        }

        return [
            'css'     => $this->getPluginResourcesPath($grouped['css'] ?? []),
            'js_head' => $this->getPluginResourcesPath($grouped['js']['head'] ?? []),
            'js_body' => $this->getPluginResourcesPath($grouped['js']['body'] ?? [])
        ];
    }

    /**
     * get resource path for single plugins
     *
     * @param stdClass[] $items
     * @return array
     */
    private function getPluginResourcesPath(array $items): array
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
    private function checkCondition($node): bool
    {
        $settingsGroup = constant((string)$node->attributes()->DependsOnSettingGroup);
        $settingValue  = (string)$node->attributes()->DependsOnSettingValue;
        $comparator    = (string)$node->attributes()->DependsOnSettingComparison;
        $setting       = (string)$node->attributes()->DependsOnSetting;
        $conf          = Shop::getSettings([$settingsGroup]);
        $hierarchy     = explode('.', $setting);
        $iterations    = count($hierarchy);
        $i             = 0;
        if (empty($comparator)) {
            $comparator = '==';
        }
        foreach ($hierarchy as $_h) {
            $conf = $conf[$_h] ?? null;
            if ($conf === null) {
                return false;
            }
            if (++$i === $iterations) {
                switch ($comparator) {
                    case '==':
                        return $conf == $settingValue;
                    case '===':
                        return $conf === $settingValue;
                    case '>=':
                        return $conf >= $settingValue;
                    case '<=':
                        return $conf <= $settingValue;
                    case '>':
                        return $conf > $settingValue;
                    case '<':
                        return $conf < $settingValue;
                    default:
                        return false;
                }
            }
        }

        return false;
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
        $folders    = [];
        $res        = [];
        $parentHash = '';
        if (self::$parent !== null) {
            $parentHash = self::$parent;
            $folders[]  = self::$parent;
        }
        $folders[] = $cOrdner;
        $cacheID   = 'tpl_mnfy_dt_' . $cOrdner . $parentHash;
        if (($tplGroups_arr = Shop::Cache()->get($cacheID)) === false) {
            $tplGroups_arr = [
                'plugin_css'     => [],
                'plugin_js_head' => [],
                'plugin_js_body' => []
            ];
            foreach ($folders as $cOrdner) {
                $oXML = self::$helper->getXML($cOrdner);
                if ($oXML === null) {
                    continue;
                }
                $cssSource = $oXML->Minify->CSS ?? [];
                $jsSource  = $oXML->Minify->JS ?? [];
                /** @var SimpleXMLElement $oCSS */
                foreach ($cssSource as $oCSS) {
                    $name = (string)$oCSS->attributes()->Name;
                    if (!isset($tplGroups_arr[$name])) {
                        $tplGroups_arr[$name] = [];
                    }
                    /** @var SimpleXMLElement $oFile */
                    foreach ($oCSS->File as $oFile) {
                        $cFile     = (string)$oFile->attributes()->Path;
                        $cFilePath = self::$isAdmin === false
                            ? PFAD_ROOT . PFAD_TEMPLATES . $oXML->Ordner . '/' . $cFile
                            : PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES . $oXML->Ordner . '/' . $cFile;
                        if (file_exists($cFilePath)
                            && (empty($oFile->attributes()->DependsOnSetting) || $this->checkCondition($oFile) === true)
                        ) {
                            $_file           = PFAD_TEMPLATES . $cOrdner . '/' . (string)$oFile->attributes()->Path;
                            $cCustomFilePath = str_replace('.css', '_custom.css', $cFilePath);
                            if (file_exists($cCustomFilePath)) { //add _custom file if existing
                                $_file                  = str_replace(
                                    '.css',
                                    '_custom.css',
                                    PFAD_TEMPLATES . $cOrdner . '/' . (string)$oFile->attributes()->Path
                                );
                                $tplGroups_arr[$name][] = [
                                    'idx' => str_replace('.css', '_custom.css', (string)$oFile->attributes()->Path),
                                    'abs' => realpath(PFAD_ROOT . $_file),
                                    'rel' => $_file
                                ];
                            } else { //otherwise add normal file
                                $tplGroups_arr[$name][] = [
                                    'idx' => $cFile,
                                    'abs' => realpath(PFAD_ROOT . $_file),
                                    'rel' => $_file
                                ];
                            }
                        }
                    }
                }
                /** @var SimpleXMLElement $oJS */
                foreach ($jsSource as $oJS) {
                    $name = (string)$oJS->attributes()->Name;
                    if (!isset($tplGroups_arr[$name])) {
                        $tplGroups_arr[$name] = [];
                    }
                    foreach ($oJS->File as $oFile) {
                        if (!empty($oFile->attributes()->DependsOnSetting) && $this->checkCondition($oFile) !== true) {
                            continue;
                        }
                        $_file    = PFAD_TEMPLATES . $cOrdner . '/' . (string)$oFile->attributes()->Path;
                        $newEntry = [
                            'idx' => (string)$oFile->attributes()->Path,
                            'abs' => PFAD_ROOT . $_file,
                            'rel' => $_file
                        ];
                        $found    = false;
                        if (!empty($oFile->attributes()->override)
                            && (string)$oFile->attributes()->override === 'true'
                        ) {
                            $idxToOverride = (string)$oFile->attributes()->Path;
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

                $pluginRes = $this->getPluginResources();
                foreach ($pluginRes['css'] as $_cssRes) {
                    $cCustomFilePath = str_replace('.css', '_custom.css', $_cssRes->abs);
                    if (file_exists($cCustomFilePath)) {
                        $tplGroups_arr['plugin_css'][] = [
                            'idx' => $_cssRes->cName,
                            'abs' => $cCustomFilePath,
                            'rel' => str_replace('.css', '_custom.css', $_cssRes->rel)
                        ];
                    } else {
                        $tplGroups_arr['plugin_css'][] = [
                            'idx' => $_cssRes->cName,
                            'abs' => $_cssRes->abs,
                            'rel' => $_cssRes->rel
                        ];
                    }
                }
                foreach ($pluginRes['js_head'] as $_jshRes) {
                    $tplGroups_arr['plugin_js_head'][] = [
                        'idx' => $_jshRes->cName,
                        'abs' => $_jshRes->abs,
                        'rel' => $_jshRes->rel
                    ];
                }
                foreach ($pluginRes['js_body'] as $_jsbRes) {
                    $tplGroups_arr['plugin_js_body'][] = [
                        'idx' => $_jsbRes->cName,
                        'abs' => $_jsbRes->abs,
                        'rel' => $_jsbRes->rel
                    ];
                }
            }
            $cacheTags = [CACHING_GROUP_OPTION, CACHING_GROUP_TEMPLATE, CACHING_GROUP_PLUGIN];
            executeHook(HOOK_CSS_JS_LIST, [
                'groups'     => &$tplGroups_arr,
                'cache_tags' => &$cacheTags
            ]);
            Shop::Cache()->set($cacheID, $tplGroups_arr, $cacheTags);
        }
        foreach ($tplGroups_arr as $name => $_tplGroup) {
            $res[$name] = [];
            foreach ($_tplGroup as $_file) {
                $res[$name][] = $absolute === true ? $_file['abs'] : $_file['rel'];
            }
        }

        return $res;
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    private function getMobileTemplate(): bool
    {
        return false;
    }

    /**
     * @deprecated since 5.0.0
     * @return bool
     */
    public function hasMobileTemplate(): bool
    {
        return false;
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function isMobileTemplateActive(): bool
    {
        return false;
    }

    /**
     * get current template's active skin
     *
     * @return string|null
     */
    public function getSkin()
    {
        $cSkin = Shop::Container()->getDB()->select(
            'ttemplateeinstellungen',
            ['cName', 'cSektion', 'cTemplate'],
            [
                'theme_default',
                'theme',
                self::$cTemplate
            ]
        );

        return $cSkin->cWert ?? null;
    }

    /**
     * @return $this
     */
    public function setzeKundenTemplate(): self
    {
        unset($_SESSION['template'], $_SESSION['cTemplate']);
        $this->init();

        return $this;
    }

    /**
     * @param string      $folder - the current template's dir name
     * @param string|null $parent
     * @return array
     */
    public function leseEinstellungenXML($folder, $parent = null): array
    {
        self::$cTemplate = $folder;
        $oDBSettings     = $this->getConfig();
        $folders         = [$folder];
        if ($parent !== null) {
            $folders[] = $parent;
        }
        $oSection_arr    = [];
        $ignoredSettings = []; //list of settings that are overridden by child
        foreach ($folders as $cOrdner) {
            $oXML = self::$helper->getXML($cOrdner);
            if (!$oXML || !isset($oXML->Settings, $oXML->Settings->Section)) {
                continue;
            }
            /** @var SimpleXMLElement $oXMLSection */
            foreach ($oXML->Settings->Section as $oXMLSection) {
                $oSection  = null;
                $sectionID = (string)$oXMLSection->attributes()->Key;
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
                    $oSection->cName         = (string)$oXMLSection->attributes()->Name;
                    $oSection->cKey          = $sectionID;
                    $oSection->oSettings_arr = [];
                }
                /** @var SimpleXMLElement $XMLSetting */
                foreach ($oXMLSection->Setting as $XMLSetting) {
                    $key                     = (string)$XMLSetting->attributes()->Key;
                    $oSetting                = new stdClass();
                    $oSetting->rawAttributes = [];
                    $settingExists           = false;
                    $atts                    = $XMLSetting->attributes();
                    if (in_array($key, $ignoredSettings, true)) {
                        continue;
                    }
                    foreach ($atts as $_k => $_attr) {
                        $oSetting->rawAttributes[$_k] = (string)$_attr;
                    }
                    if ((string)$XMLSetting->attributes()->override === 'true') {
                        $ignoredSettings[] = $key;
                    }
                    $oSetting->cName        = (string)$XMLSetting->attributes()->Description;
                    $oSetting->cKey         = $key;
                    $oSetting->cType        = (string)$XMLSetting->attributes()->Type;
                    $oSetting->cValue       = (string)$XMLSetting->attributes()->Value;
                    $oSetting->bEditable    = (string)$XMLSetting->attributes()->Editable;
                    $oSetting->cPlaceholder = (string)$XMLSetting->attributes()->Placeholder;
                    // negative values for the 'toggle'-attributes of textarea(resizable), check-boxes and radio-buttons
                    $vToggleValues = ['0', 'no', 'none', 'off', 'false'];
                    // special handling for textarea-type settings
                    if ('textarea' === $oSetting->cType) {
                        // inject the tag-attributes of the TextAreaValue in our oSetting
                        $oSetting->vTextAreaAttr_arr = [];
                        // get the SimpleXMLElement-array
                        $attr = $XMLSetting->TextAreaValue->attributes();
                        // we insert our default "no resizable"
                        $oSetting->vTextAreaAttr_arr['Resizable'] = 'none';
                        foreach ($attr as $_key => $_val) {
                            $_val                               = (string)$_val; // cast the value(!)
                            $oSetting->vTextAreaAttr_arr[$_key] = $_val;
                            // multiple values of 'disable resizing' are allowed,
                            // but only vertical is ok, if 'resizable' is required
                            if ('Resizable' === (string)$_key) {
                                in_array($_val, $vToggleValues, true)
                                    ? $oSetting->vTextAreaAttr_arr[$_key] = 'none'
                                    : $oSetting->vTextAreaAttr_arr[$_key] = 'vertical';
                                // only vertical, because horizontal breaks the layout
                            } else {
                                $oSetting->vTextAreaAttr_arr[$_key] = $_val;
                            }
                        }
                        // get the tag-content of "TextAreaValue"; trim leading and trailing spaces
                        $vszTextLines = mb_split("\n", (string)$XMLSetting->TextAreaValue);
                        array_walk($vszTextLines, function (&$szLine) {
                            $szLine = trim($szLine);
                        });
                        $oSetting->cTextAreaValue = implode("\n", $vszTextLines);
                    }
                    foreach ($oSection->oSettings_arr as $_setting) {
                        if ($_setting->cKey === $oSetting->cKey) {
                            $settingExists = true;
                            $oSetting      = $_setting;
                            break;
                        }
                    }
                    $oSetting->bEditable = strlen($oSetting->bEditable) === 0
                        ? true
                        : (boolean)(int)$oSetting->bEditable;
                    if ($oSetting->bEditable && isset($oDBSettings[$oSection->cKey][$oSetting->cKey])) {
                        $oSetting->cValue = $oDBSettings[$oSection->cKey][$oSetting->cKey];
                    }
                    if (isset($XMLSetting->Option)) {
                        if (!isset($oSetting->oOptions_arr)) {
                            $oSetting->oOptions_arr = [];
                        }
                        /** @var SimpleXMLElement $XMLOption */
                        foreach ($XMLSetting->Option as $XMLOption) {
                            $oOption          = new stdClass();
                            $oOption->cName   = (string)$XMLOption;
                            $oOption->cValue  = (string)$XMLOption->attributes()->Value;
                            $oOption->cOrdner = $cOrdner; //add current folder to option - useful for theme previews
                            if ('' === (string)$XMLOption && '' !== (string)$XMLOption->attributes()->Name) {
                                // overwrite the cName (which defaults to the tag-content),
                                // if it's empty, with the Option-attribute "Name", if we got that
                                $oOption->cName = (string)$XMLOption->attributes()->Name;
                            }
                            $oSetting->oOptions_arr[] = $oOption;
                        }
                    }
                    if (isset($XMLSetting->Optgroup)) {
                        if (!isset($oSetting->oOptgroup_arr)) {
                            $oSetting->oOptgroup_arr = [];
                        }
                        /** @var SimpleXMLElement $XMLOptgroup */
                        foreach ($XMLSetting->Optgroup as $XMLOptgroup) {
                            $oOptgroup              = new stdClass();
                            $oOptgroup->cName       = (string)$XMLOptgroup->attributes()->label;
                            $oOptgroup->oValues_arr = [];
                            /** @var SimpleXMLElement $XMLOptgroupOption */
                            foreach ($XMLOptgroup->Option as $XMLOptgroupOption) {
                                $oOptgroupValues          = new stdClass();
                                $oOptgroupValues->cName   = (string)$XMLOptgroupOption;
                                $oOptgroupValues->cValue  = (string)$XMLOptgroupOption->attributes()->Value;
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

        return $oSection_arr;
    }

    /**
     * @param string|null $cOrdner
     * @return array
     */
    public function getBoxLayoutXML($cOrdner = null): array
    {
        $oItem_arr     = [];
        $cOrdner_arr   = self::$parent !== null ? [self::$parent] : [];
        $cOrdner_arr[] = $cOrdner ?? self::$cTemplate;

        foreach ($cOrdner_arr as $dir) {
            $oXML = self::$helper->getXML($dir);
            if (isset($oXML->Boxes) && count($oXML->Boxes) === 1) {
                $oXMLBoxes_arr = $oXML->Boxes[0];
                /** @var SimpleXMLElement $oXMLContainer */
                foreach ($oXMLBoxes_arr as $oXMLContainer) {
                    $cPosition             = (string)$oXMLContainer->attributes()->Position;
                    $bAvailable            = (boolean)(int)$oXMLContainer->attributes()->Available;
                    $oItem_arr[$cPosition] = $bAvailable;
                }
            }
        }

        return $oItem_arr;
    }

    /**
     * @param string $cOrdner
     * @return array
     * @todo: self::$parent
     */
    public function leseLessXML($cOrdner): array
    {
        $oXML           = self::$helper->getXML($cOrdner);
        $oLessFiles_arr = [];
        if (!$oXML || !isset($oXML->Lessfiles)) {
            return $oLessFiles_arr;
        }
        /** @var SimpleXMLElement $oXMLTheme */
        foreach ($oXML->Lessfiles->THEME as $oXMLTheme) {
            $oTheme             = new stdClass();
            $oTheme->cName      = (string)$oXMLTheme->attributes()->Name;
            $oTheme->oFiles_arr = [];
            foreach ($oXMLTheme->File as $cFile) {
                $oThemeFiles          = new stdClass();
                $oThemeFiles->cPath   = (string)$cFile->attributes()->Path;
                $oTheme->oFiles_arr[] = $oThemeFiles;
            }
            $oLessFiles_arr[$oTheme->cName] = $oTheme;
        }

        return $oLessFiles_arr;
    }

    /**
     * set new frontend template
     *
     * @param string $cOrdner
     * @param string $eTyp
     * @return bool
     */
    public function setTemplate($cOrdner, $eTyp = 'standard'): bool
    {
        Shop::Container()->getDB()->delete('ttemplate', 'eTyp', $eTyp);
        Shop::Container()->getDB()->delete('ttemplate', 'cTemplate', $cOrdner);
        $tplConfig = self::$helper->getXML($cOrdner);
        if (!empty($tplConfig->Parent)) {
            if (!is_dir(PFAD_ROOT . PFAD_TEMPLATES . $tplConfig->Parent)) {
                return false;
            }
            self::$parent = $tplConfig->Parent;
            $parentConfig = self::$helper->getXML(self::$parent);
        } else {
            $parentConfig = false;
        }

        $tplObject              = new stdClass();
        $tplObject->cTemplate   = $cOrdner;
        $tplObject->eTyp        = $eTyp;
        $tplObject->parent      = !empty($tplConfig->Parent)
            ? (string)$tplConfig->Parent
            : '_DBNULL_';
        $tplObject->name        = (string)$tplConfig->Name;
        $tplObject->author      = (string)$tplConfig->Author;
        $tplObject->url         = (string)$tplConfig->URL;
        $tplObject->version     = empty($tplConfig->Version) && $parentConfig
            ? (float)$parentConfig->Version
            : (float)$tplConfig->Version;
        $tplObject->shopversion = empty($tplConfig->ShopVersion) && $parentConfig
            ? (int)$parentConfig->ShopVersion
            : (int)$tplConfig->ShopVersion;
        $tplObject->preview     = (string)$tplConfig->Preview;
        $inserted               = Shop::Container()->getDB()->insert('ttemplate', $tplObject);
        if ($inserted > 0) {
            if (!$dh = opendir(PFAD_ROOT . PFAD_COMPILEDIR)) {
                return false;
            }
            while (($obj = readdir($dh)) !== false) {
                if ($obj{0} === '.') {
                    continue;
                }
                if (!is_dir(PFAD_ROOT . PFAD_COMPILEDIR . $obj)) {
                    unlink(PFAD_ROOT . PFAD_COMPILEDIR . $obj);
                }
            }
        }
        Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_TEMPLATE]);

        return $inserted > 0;
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
    public function setConfig($cOrdner, $cSektion, $cName, $cWert): self
    {
        $oSetting = Shop::Container()->getDB()->select(
            'ttemplateeinstellungen',
            'cTemplate', $cOrdner,
            'cSektion', $cSektion,
            'cName', $cName
        );
        if ($oSetting !== null && isset($oSetting->cTemplate)) {
            Shop::Container()->getDB()->update(
                'ttemplateeinstellungen',
                ['cTemplate', 'cSektion', 'cName'],
                [$cOrdner, $cSektion, $cName],
                (object)['cWert' => $cWert]
            );
        } else {
            $_ins            = new stdClass();
            $_ins->cTemplate = $cOrdner;
            $_ins->cSektion  = $cSektion;
            $_ins->cName     = $cName;
            $_ins->cWert     = $cWert;
            Shop::Container()->getDB()->insert('ttemplateeinstellungen', $_ins);
        }
        Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_TEMPLATE]);

        return $this;
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function IsMobile(): bool
    {
        return false;
    }

    /**
     * @param bool $absolute
     * @return string
     */
    public function getDir($absolute = false): string
    {
        return $absolute ? (PFAD_ROOT . PFAD_TEMPLATES . self::$cTemplate) : self::$cTemplate;
    }

    /**
     * @return string|null
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
    public function getVersion(): float
    {
        return (float)$this->version;
    }

    /**
     * @return string|null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string|null
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * @return TemplateHelper
     */
    public function getHelper(): TemplateHelper
    {
        return self::$helper;
    }

    /**
     * @return string|null
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * @return int|null
     */
    public function getShopVersion()
    {
        return $this->shopVersion;
    }

    /**
     * @param bool $bRedirect
     * @deprecated since 5.0.0
     */
    public function check($bRedirect = true)
    {
    }
}
