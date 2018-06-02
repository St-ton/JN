<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class AdminTemplate
 */
class AdminTemplate
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
     * @var AdminTemplate
     */
    private static $instance;

    /**
     * @var bool
     */
    private static $isAdmin = true;

    /**
     * @var TemplateHelper
     */
    private static $helper;

    /**
     * @var object
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
     * @var int
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
        self::$helper  = TemplateHelper::getInstance(true);
        $this->xmlData = self::$helper->getData(self::$cTemplate);
        $this->init();
        self::$instance = $this;
    }

    /**
     * @return $this
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self();
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
     * @param bool $absolute
     * @return string
     */
    public function getDir(bool $absolute = false): string
    {
        return $absolute
            ? (PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES . self::$cTemplate)
            : self::$cTemplate;
    }

    /**
     * @return $this
     */
    public function init(): self
    {
        $cacheID = 'current_template__admin';
        if (($oTemplate = Shop::Cache()->get($cacheID)) !== false) {
            self::$cTemplate = $oTemplate->cTemplate;
        } else {
            $oTemplate = Shop::Container()->getDB()->select('ttemplate', 'eTyp', 'admin');
            if ($oTemplate) {
                self::$cTemplate = $oTemplate->cTemplate;
                Shop::Cache()->set($cacheID, $oTemplate, [CACHING_GROUP_TEMPLATE]);

                return $this;
            }
            // fall back to admin template "default"
            self::$cTemplate = 'default';
        }

        return $this;
    }

    /**
     * get array of static resources in minify compatible format
     *
     * @param bool $absolute
     * @return array
     */
    public function getMinifyArray(bool $absolute = false): array
    {
        $cOrdner   = $this->getDir();
        $folders   = [];
        $folders[] = $cOrdner;
        $cacheID   = 'template_minify_data_adm_' . $cOrdner . (($absolute === true) ? '_a' : '');
        if (($tplGroups_arr = Shop::Cache()->get($cacheID)) === false) {
            $tplGroups_arr = [
                'admin_css' => [],
                'admin_js'  => []
            ];
            foreach ($folders as $cOrdner) {
                $oXML = self::$helper->getXML($cOrdner, true);
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
                    foreach ($oCSS->File as $oFile) {
                        $cFile     = (string) $oFile->attributes()->Path;
                        $cFilePath = self::$isAdmin === false
                            ? PFAD_ROOT . PFAD_TEMPLATES . $oXML->Ordner . '/' . $cFile
                            : PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES . $oXML->Ordner . '/' . $cFile;
                        if (file_exists($cFilePath)) {
                            $tplGroups_arr[$name][] = ($absolute === true ? PFAD_ROOT : '') .
                                (self::$isAdmin === true ? PFAD_ADMIN : '') .
                                PFAD_TEMPLATES . $cOrdner . '/' . (string)$oFile->attributes()->Path;
                            $cCustomFilePath = str_replace('.css', '_custom.css', $cFilePath);
                            if (file_exists($cCustomFilePath)) {
                                $tplGroups_arr[$name][] = str_replace('.css', '_custom.css',
                                    ($absolute === true ? PFAD_ROOT : '') .
                                    (self::$isAdmin === true ? PFAD_ADMIN : '') .
                                    PFAD_TEMPLATES . $cOrdner . '/' . (string)$oFile->attributes()->Path);
                            }
                        }
                    }
                    // assign custom.css
                    $cCustomFilePath = PFAD_ROOT . 'templates/' . $oXML->Ordner . '/themes/custom.css';
                    if (file_exists($cCustomFilePath)) {
                        $tplGroups_arr[$name][] = (($absolute === true) ? PFAD_ROOT : '') .
                            (self::$isAdmin === true ? PFAD_ADMIN : '') .
                            PFAD_TEMPLATES . $cOrdner . '/' . 'themes/custom.css';
                    }
                }
                foreach ($jsSource as $oJS) {
                    $name = (string)$oJS->attributes()->Name;
                    if (!isset($tplGroups_arr[$name])) {
                        $tplGroups_arr[$name] = [];
                    }
                    foreach ($oJS->File as $oFile) {
                        $tplGroups_arr[$name][] = ($absolute === true ? PFAD_ROOT : '') .
                            (self::$isAdmin === true ? PFAD_ADMIN : '') .
                            PFAD_TEMPLATES . $cOrdner . '/' . (string)$oFile->attributes()->Path;
                    }
                }
            }
            $cacheTags = [CACHING_GROUP_OPTION, CACHING_GROUP_TEMPLATE, CACHING_GROUP_PLUGIN];
            if (!self::$isAdmin) {
                executeHook(HOOK_CSS_JS_LIST, ['groups' => &$tplGroups_arr, 'cache_tags' => &$cacheTags]);
            }
            Shop::Cache()->set($cacheID, $tplGroups_arr, $cacheTags);
        }

        return $tplGroups_arr;
    }

    /**
     * build string to serve minified files or direct head includes
     *
     * @param bool $minify - generates absolute links for minify when true
     * @return array - list of js/css resources
     */
    public function getResources(bool $minify = true): array
    {
        self::$isAdmin = true;
        $outputCSS     = '';
        $outputJS      = '';
        $baseURL       = Shop::getURL();
        $version       = Shop::getVersion();
        $files         = $this->getMinifyArray($minify);
        if ($minify === false) {
            $fileSuffix = '?v=' . $version;
            foreach ($files['admin_js'] as $_file) {
                $outputJS .= '<script type="text/javascript" src="'
                    . $baseURL . '/'
                    . $_file
                    . $fileSuffix
                    . '"></script>'
                    . "\n";
            }
            foreach ($files['admin_css'] as $_file) {
                $outputCSS .= '<link rel="stylesheet" type="text/css" href="'
                    . $baseURL . '/'
                    . $_file
                    . $fileSuffix
                    . '" media="screen" />'
                    . "\n";
            }
        } else {
            $tplString  = $this->getDir(); // add tpl string to avoid caching
            $fileSuffix = '&v=' . $version;
            $outputCSS  = '<link rel="stylesheet" type="text/css" href="'
                . $baseURL . '/'
                . PFAD_MINIFY . '/index.php?g=admin_css&tpl='
                . $tplString
                . $fileSuffix
                . '" media="screen" />';
            $outputJS   = '<script type="text/javascript" src="'
                . $baseURL . '/'
                . PFAD_MINIFY
                . '/index.php?g=admin_js&tpl='
                . $tplString
                . $fileSuffix
                . '"></script>';
        }

        return ['js' => $outputJS, 'css' => $outputCSS];
    }
}
