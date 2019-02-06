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
     * @var \Helpers\Template
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
     * @var string
     */
    public $preview;

    /**
     *
     */
    public function __construct()
    {
        self::$helper = \Helpers\Template::getInstance();
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
            self::$cTemplate = $_SESSION['template']->cTemplate;
            self::$parent    = $_SESSION['template']->parent;
            $this->name      = $_SESSION['template']->name;
            $this->author    = $_SESSION['template']->author;
            $this->url       = $_SESSION['template']->url;
            $this->version   = $_SESSION['template']->version;
            $this->preview   = $_SESSION['template']->preview;

            return $this;
        }
        $cacheID = 'current_template_' .
            (self::$isAdmin === true ? '_admin' : '');
        if (($oTemplate = Shop::Container()->getCache()->get($cacheID)) !== false) {
            self::$cTemplate = $oTemplate->cTemplate;
            self::$parent    = $oTemplate->parent;
            $this->name      = $oTemplate->name;
            $this->author    = $oTemplate->author;
            $this->url       = $oTemplate->url;
            $this->version   = $oTemplate->version;
            $this->preview   = $oTemplate->preview;

            return $this;
        }
        $oTemplate = Shop::Container()->getDB()->select('ttemplate', 'eTyp', 'standard');
        if (!empty($oTemplate)) {
            self::$cTemplate = $oTemplate->cTemplate;
            self::$parent    = !empty($oTemplate->parent) ? $oTemplate->parent : null;
            $this->name      = $oTemplate->name;
            $this->author    = $oTemplate->author;
            $this->url       = $oTemplate->url;
            $this->version   = $oTemplate->version;
            $this->preview   = $oTemplate->preview;

            $tplObject             = new stdClass();
            $tplObject->cTemplate  = self::$cTemplate;
            $tplObject->isMobile   = false;
            $tplObject->parent     = self::$parent;
            $tplObject->name       = $this->name;
            $tplObject->version    = $this->version;
            $tplObject->author     = $this->author;
            $tplObject->url        = $this->url;
            $tplObject->preview    = $this->preview;
            $_SESSION['template']  = $tplObject;
            $_SESSION['cTemplate'] = self::$cTemplate;

            Shop::Container()->getCache()->set($cacheID, $oTemplate, [CACHING_GROUP_TEMPLATE]);
        }

        return $this;
    }

    /**
     * returns current template's name
     *
     * @return string|null
     */
    public function getFrontendTemplate(): ?string
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
        $resourcesc = Shop::Container()->getDB()->queryPrepared(
            'SELECT * 
                FROM tplugin_resources AS res
                JOIN tplugin
                    ON tplugin.kPlugin = res.kPlugin
                WHERE tplugin.nStatus = :state
                ORDER BY res.priority DESC',
            ['state' => \Plugin\State::ACTIVATED],
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
        foreach ($items as $item) {
            $frontend = PFAD_PLUGIN_FRONTEND . $item->type . '/' . $item->path;
            if ((int)$item->bExtension === 1) {
                $item->rel = PLUGIN_DIR . $item->cVerzeichnis . '/';
            } else {
                $item->rel = PFAD_PLUGIN . $item->cVerzeichnis . '/';
                $frontend  = PFAD_PLUGIN_VERSION . $item->nVersion . '/' . $frontend;
            }
            $item->rel .= $frontend;
            $item->abs = PFAD_ROOT . $item->rel;
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
        $dir        = $this->getDir();
        $folders    = [];
        $res        = [];
        $parentHash = '';
        if (self::$parent !== null) {
            $parentHash = self::$parent;
            $folders[]  = self::$parent;
        }
        $folders[] = $dir;
        $cacheID   = 'tpl_mnfy_dt_' . $dir . $parentHash;
        if (($tplGroups = Shop::Container()->getCache()->get($cacheID)) === false) {
            $tplGroups = [
                'plugin_css'     => [],
                'plugin_js_head' => [],
                'plugin_js_body' => []
            ];
            foreach ($folders as $dir) {
                $oXML = self::$helper->getXML($dir);
                if ($oXML === null) {
                    continue;
                }
                $cssSource = $oXML->Minify->CSS ?? [];
                $jsSource  = $oXML->Minify->JS ?? [];
                /** @var SimpleXMLElement $oCSS */
                foreach ($cssSource as $oCSS) {
                    $name = (string)$oCSS->attributes()->Name;
                    if (!isset($tplGroups[$name])) {
                        $tplGroups[$name] = [];
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
                            $_file           = PFAD_TEMPLATES . $dir . '/' . (string)$oFile->attributes()->Path;
                            $cCustomFilePath = str_replace('.css', '_custom.css', $cFilePath);
                            if (file_exists($cCustomFilePath)) { //add _custom file if existing
                                $_file              = str_replace(
                                    '.css',
                                    '_custom.css',
                                    PFAD_TEMPLATES . $dir . '/' . (string)$oFile->attributes()->Path
                                );
                                $tplGroups[$name][] = [
                                    'idx' => str_replace('.css', '_custom.css', (string)$oFile->attributes()->Path),
                                    'abs' => realpath(PFAD_ROOT . $_file),
                                    'rel' => $_file
                                ];
                            } else { //otherwise add normal file
                                $tplGroups[$name][] = [
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
                    if (!isset($tplGroups[$name])) {
                        $tplGroups[$name] = [];
                    }
                    foreach ($oJS->File as $oFile) {
                        if (!empty($oFile->attributes()->DependsOnSetting) && $this->checkCondition($oFile) !== true) {
                            continue;
                        }
                        $_file    = PFAD_TEMPLATES . $dir . '/' . (string)$oFile->attributes()->Path;
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
                            $max           = count($tplGroups[$name]);
                            for ($i = 0; $i < $max; $i++) {
                                if ($tplGroups[$name][$i]['idx'] === $idxToOverride) {
                                    $tplGroups[$name][$i] = $newEntry;
                                    $found                = true;
                                    break;
                                }
                            }
                        }
                        if ($found === false) {
                            $tplGroups[$name][] = $newEntry;
                        }
                    }
                }
            }
            $pluginRes = $this->getPluginResources();
            foreach ($pluginRes['css'] as $_cssRes) {
                $cCustomFilePath = str_replace('.css', '_custom.css', $_cssRes->abs);
                if (file_exists($cCustomFilePath)) {
                    $tplGroups['plugin_css'][] = [
                        'idx' => $_cssRes->cName,
                        'abs' => $cCustomFilePath,
                        'rel' => str_replace('.css', '_custom.css', $_cssRes->rel)
                    ];
                } else {
                    $tplGroups['plugin_css'][] = [
                        'idx' => $_cssRes->cName,
                        'abs' => $_cssRes->abs,
                        'rel' => $_cssRes->rel
                    ];
                }
            }
            foreach ($pluginRes['js_head'] as $_jshRes) {
                $tplGroups['plugin_js_head'][] = [
                    'idx' => $_jshRes->cName,
                    'abs' => $_jshRes->abs,
                    'rel' => $_jshRes->rel
                ];
            }
            foreach ($pluginRes['js_body'] as $_jsbRes) {
                $tplGroups['plugin_js_body'][] = [
                    'idx' => $_jsbRes->cName,
                    'abs' => $_jsbRes->abs,
                    'rel' => $_jsbRes->rel
                ];
            }
            $cacheTags = [CACHING_GROUP_OPTION, CACHING_GROUP_TEMPLATE, CACHING_GROUP_PLUGIN];
            executeHook(HOOK_CSS_JS_LIST, [
                'groups'     => &$tplGroups,
                'cache_tags' => &$cacheTags
            ]);
            Shop::Container()->getCache()->set($cacheID, $tplGroups, $cacheTags);
        }
        foreach ($tplGroups as $name => $_tplGroup) {
            $res[$name] = [];
            foreach ($_tplGroup as $_file) {
                $res[$name][] = $absolute === true ? $_file['abs'] : $_file['rel'];
            }
        }

        return $res;
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
    public function getSkin(): ?string
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
        $sections        = [];
        $ignoredSettings = []; //list of settings that are overridden by child
        foreach ($folders as $dir) {
            $oXML = self::$helper->getXML($dir);
            if (!$oXML || !isset($oXML->Settings, $oXML->Settings->Section)) {
                continue;
            }
            /** @var SimpleXMLElement $oXMLSection */
            foreach ($oXML->Settings->Section as $oXMLSection) {
                $oSection  = null;
                $sectionID = (string)$oXMLSection->attributes()->Key;
                $exists    = false;
                foreach ($sections as &$_section) {
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
                    $key                    = (string)$XMLSetting->attributes()->Key;
                    $setting                = new stdClass();
                    $setting->rawAttributes = [];
                    $settingExists          = false;
                    $atts                   = $XMLSetting->attributes();
                    if (in_array($key, $ignoredSettings, true)) {
                        continue;
                    }
                    foreach ($atts as $_k => $_attr) {
                        $setting->rawAttributes[$_k] = (string)$_attr;
                    }
                    if ((string)$XMLSetting->attributes()->override === 'true') {
                        $ignoredSettings[] = $key;
                    }
                    $setting->cName        = (string)$XMLSetting->attributes()->Description;
                    $setting->cKey         = $key;
                    $setting->cType        = (string)$XMLSetting->attributes()->Type;
                    $setting->cValue       = (string)$XMLSetting->attributes()->Value;
                    $setting->bEditable    = (string)$XMLSetting->attributes()->Editable;
                    $setting->cPlaceholder = (string)$XMLSetting->attributes()->Placeholder;
                    // negative values for the 'toggle'-attributes of textarea(resizable), check-boxes and radio-buttons
                    $vToggleValues = ['0', 'no', 'none', 'off', 'false'];
                    // special handling for textarea-type settings
                    if ('textarea' === $setting->cType) {
                        // inject the tag-attributes of the TextAreaValue in our oSetting
                        $setting->vTextAreaAttr_arr = [];
                        // get the SimpleXMLElement-array
                        $attr = $XMLSetting->TextAreaValue->attributes();
                        // we insert our default "no resizable"
                        $setting->vTextAreaAttr_arr['Resizable'] = 'none';
                        foreach ($attr as $_key => $_val) {
                            $_val                               = (string)$_val; // cast the value(!)
                            $setting->vTextAreaAttr_arr[$_key] = $_val;
                            // multiple values of 'disable resizing' are allowed,
                            // but only vertical is ok, if 'resizable' is required
                            if ('Resizable' === (string)$_key) {
                                in_array($_val, $vToggleValues, true)
                                    ? $setting->vTextAreaAttr_arr[$_key] = 'none'
                                    : $setting->vTextAreaAttr_arr[$_key] = 'vertical';
                                // only vertical, because horizontal breaks the layout
                            } else {
                                $setting->vTextAreaAttr_arr[$_key] = $_val;
                            }
                        }
                        // get the tag-content of "TextAreaValue"; trim leading and trailing spaces
                        $textLines = mb_split("\n", (string)$XMLSetting->TextAreaValue);
                        array_walk($textLines, function (&$szLine) {
                            $szLine = trim($szLine);
                        });
                        $setting->cTextAreaValue = implode("\n", $textLines);
                    }
                    foreach ($oSection->oSettings_arr as $_setting) {
                        if ($_setting->cKey === $setting->cKey) {
                            $settingExists = true;
                            $setting      = $_setting;
                            break;
                        }
                    }
                    $setting->bEditable = mb_strlen($setting->bEditable) === 0
                        ? true
                        : (bool)(int)$setting->bEditable;
                    if ($setting->bEditable && isset($oDBSettings[$oSection->cKey][$setting->cKey])) {
                        $setting->cValue = $oDBSettings[$oSection->cKey][$setting->cKey];
                    }
                    if (isset($XMLSetting->Option)) {
                        if (!isset($setting->oOptions_arr)) {
                            $setting->oOptions_arr = [];
                        }
                        /** @var SimpleXMLElement $XMLOption */
                        foreach ($XMLSetting->Option as $XMLOption) {
                            $oOption          = new stdClass();
                            $oOption->cName   = (string)$XMLOption;
                            $oOption->cValue  = (string)$XMLOption->attributes()->Value;
                            $oOption->cOrdner = $dir; //add current folder to option - useful for theme previews
                            if ('' === (string)$XMLOption && '' !== (string)$XMLOption->attributes()->Name) {
                                // overwrite the cName (which defaults to the tag-content),
                                // if it's empty, with the Option-attribute "Name", if we got that
                                $oOption->cName = (string)$XMLOption->attributes()->Name;
                            }
                            $setting->oOptions_arr[] = $oOption;
                        }
                    }
                    if (isset($XMLSetting->Optgroup)) {
                        if (!isset($setting->oOptgroup_arr)) {
                            $setting->oOptgroup_arr = [];
                        }
                        /** @var SimpleXMLElement $XMLOptgroup */
                        foreach ($XMLSetting->Optgroup as $XMLOptgroup) {
                            $optgroup              = new stdClass();
                            $optgroup->cName       = (string)$XMLOptgroup->attributes()->label;
                            $optgroup->oValues_arr = [];
                            /** @var SimpleXMLElement $XMLOptgroupOption */
                            foreach ($XMLOptgroup->Option as $XMLOptgroupOption) {
                                $oOptgroupValues          = new stdClass();
                                $oOptgroupValues->cName   = (string)$XMLOptgroupOption;
                                $oOptgroupValues->cValue  = (string)$XMLOptgroupOption->attributes()->Value;
                                $optgroup->oValues_arr[] = $oOptgroupValues;
                            }
                            $setting->oOptgroup_arr[] = $optgroup;
                        }
                    }
                    if (!$settingExists) {
                        $oSection->oSettings_arr[] = $setting;
                    }
                }
                if (!$exists) {
                    $sections[] = $oSection;
                }
            }
        }

        return $sections;
    }

    /**
     * @param string|null $dirName
     * @return array
     */
    public function getBoxLayoutXML($dirName = null): array
    {
        $items  = [];
        $dirs   = self::$parent !== null ? [self::$parent] : [];
        $dirs[] = $dirName ?? self::$cTemplate;

        foreach ($dirs as $dir) {
            $oXML = self::$helper->getXML($dir);
            if (isset($oXML->Boxes) && count($oXML->Boxes) === 1) {
                $oXMLBoxes_arr = $oXML->Boxes[0];
                /** @var SimpleXMLElement $oXMLContainer */
                foreach ($oXMLBoxes_arr as $oXMLContainer) {
                    $cPosition         = (string)$oXMLContainer->attributes()->Position;
                    $bAvailable        = (bool)(int)$oXMLContainer->attributes()->Available;
                    $items[$cPosition] = $bAvailable;
                }
            }
        }

        return $items;
    }

    /**
     * @param string $dir
     * @return array
     * @todo: self::$parent
     */
    public function leseLessXML($dir): array
    {
        $xml       = self::$helper->getXML($dir);
        $lessFiles = [];
        if (!$xml || !isset($xml->Lessfiles)) {
            return $lessFiles;
        }
        /** @var SimpleXMLElement $oXMLTheme */
        foreach ($xml->Lessfiles->THEME as $oXMLTheme) {
            $theme             = new stdClass();
            $theme->cName      = (string)$oXMLTheme->attributes()->Name;
            $theme->oFiles_arr = [];
            foreach ($oXMLTheme->File as $cFile) {
                $oThemeFiles          = new stdClass();
                $oThemeFiles->cPath   = (string)$cFile->attributes()->Path;
                $theme->oFiles_arr[] = $oThemeFiles;
            }
            $lessFiles[$theme->cName] = $theme;
        }

        return $lessFiles;
    }

    /**
     * set new frontend template
     *
     * @param string $dir
     * @param string $eTyp
     * @return bool
     */
    public function setTemplate($dir, $eTyp = 'standard'): bool
    {
        Shop::Container()->getDB()->delete('ttemplate', 'eTyp', $eTyp);
        Shop::Container()->getDB()->delete('ttemplate', 'cTemplate', $dir);
        $tplConfig = self::$helper->getXML($dir);
        if (!empty($tplConfig->Parent)) {
            if (!is_dir(PFAD_ROOT . PFAD_TEMPLATES . (string)$tplConfig->Parent)) {
                return false;
            }
            self::$parent = (string)$tplConfig->Parent;
            $parentConfig = self::$helper->getXML(self::$parent);
        } else {
            $parentConfig = false;
        }

        $tplObject            = new stdClass();
        $tplObject->cTemplate = $dir;
        $tplObject->eTyp      = $eTyp;
        $tplObject->parent    = !empty($tplConfig->Parent)
            ? (string)$tplConfig->Parent
            : '_DBNULL_';
        $tplObject->name      = (string)$tplConfig->Name;
        $tplObject->author    = (string)$tplConfig->Author;
        $tplObject->url       = (string)$tplConfig->URL;
        $tplObject->version   = empty($tplConfig->Version) && $parentConfig
            ? $parentConfig->Version
            : $tplConfig->Version;
        $tplObject->preview   = (string)$tplConfig->Preview;
        $inserted             = Shop::Container()->getDB()->insert('ttemplate', $tplObject);
        if ($inserted > 0) {
            if (!$dh = opendir(PFAD_ROOT . PFAD_COMPILEDIR)) {
                return false;
            }
            while (($obj = readdir($dh)) !== false) {
                if (mb_strpos($obj, '.') === 0) {
                    continue;
                }
                if (!is_dir(PFAD_ROOT . PFAD_COMPILEDIR . $obj)) {
                    unlink(PFAD_ROOT . PFAD_COMPILEDIR . $obj);
                }
            }
        }
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_TEMPLATE]);

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
     * @param string $dir
     * @param string $section
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setConfig($dir, $section, $name, $value): self
    {
        $config = Shop::Container()->getDB()->select(
            'ttemplateeinstellungen',
            'cTemplate',
            $dir,
            'cSektion',
            $section,
            'cName',
            $name
        );
        if ($config !== null && isset($config->cTemplate)) {
            Shop::Container()->getDB()->update(
                'ttemplateeinstellungen',
                ['cTemplate', 'cSektion', 'cName'],
                [$dir, $section, $name],
                (object)['cWert' => $value]
            );
        } else {
            $ins            = new stdClass();
            $ins->cTemplate = $dir;
            $ins->cSektion  = $section;
            $ins->cName     = $name;
            $ins->cWert     = $value;
            Shop::Container()->getDB()->insert('ttemplateeinstellungen', $ins);
        }
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_TEMPLATE]);

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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getParent(): ?string
    {
        return self::$parent;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return string|null
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @return string|null
     */
    public function getURL(): ?string
    {
        return $this->url;
    }

    /**
     * @return \Helpers\Template
     */
    public function getHelper(): \Helpers\Template
    {
        return self::$helper;
    }

    /**
     * @return string|null
     */
    public function getPreview(): ?string
    {
        return $this->preview;
    }

    /**
     * @param bool $bRedirect
     * @deprecated since 5.0.0
     */
    public function check($bRedirect = true): void
    {
    }
}
