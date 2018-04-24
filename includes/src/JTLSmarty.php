<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'browsererkennung.php';
require_once PFAD_ROOT . PFAD_PHPQUERY . 'phpquery.class.php';

/**
 * Class JTLSmarty
 * @method JTLSmarty assign(string $variable, mixed $value)
 */
class JTLSmarty extends SmartyBC
{
    /**
     * @var \Cache\JTLCache
     */
    public $jtlCache;

    /**
     * @var array
     */
    public $config;

    /**
     * @var array
     */
    public $_cache_include_info;

    /**
     * @var Template
     */
    public $template;

    /**
     * @var JTLSmarty|null
     */
    public static $_instance;

    /**
     * @var string
     */
    public $context = 'frontend';

    /**
     * @var int
     */
    public $_file_perms = 0664;

    /**
     * @var bool
     */
    public static $isChildTemplate = false;

    /**
     * modified constructor with custom initialisation
     *
     * @param bool   $fast_init - set to true when init from backend to avoid setting session data
     * @param bool   $isAdmin
     * @param bool   $tplCache
     * @param string $context
     */
    public function __construct($fast_init = false, $isAdmin = false, $tplCache = true, $context = 'frontend')
    {
        parent::__construct();
        Smarty::$_CHARSET = JTL_CHARSET;
        if (defined('SMARTY_USE_SUB_DIRS') && is_bool(SMARTY_USE_SUB_DIRS)) {
            $this->setUseSubDirs(SMARTY_USE_SUB_DIRS);
        }
        $this->setErrorReporting(SMARTY_LOG_LEVEL)
             ->setForceCompile(SMARTY_FORCE_COMPILE ? true : false)
             ->setDebugging(SMARTY_DEBUG_CONSOLE ? true : false);

        $this->config = Shopsetting::getInstance()->getAll();
        $template     = $isAdmin ? AdminTemplate::getInstance() : Template::getInstance();
        $cTemplate    = $template->getDir();
        $parent       = null;
        if ($isAdmin === false) {
            $parent      = $template->getParent();
            $_compileDir = PFAD_ROOT . PFAD_COMPILEDIR . $cTemplate . '/';
            if (!file_exists($_compileDir)) {
                mkdir($_compileDir);
            }
            $templatePaths[$this->context] = PFAD_ROOT . PFAD_TEMPLATES . $cTemplate . '/';
            foreach (Plugin::getTemplatePaths() as $moduleId => $path) {
                $templateKey                 = 'plugin_' . $moduleId;
                $templatePaths[$templateKey] = $path;
            }
            $this->setTemplateDir($templatePaths)
                 ->setCompileDir($_compileDir)
                 ->setCacheDir(PFAD_ROOT . PFAD_COMPILEDIR . $cTemplate . '/' . 'page_cache/')
                 ->setPluginsDir(SMARTY_PLUGINS_DIR);

            if ($parent !== null) {
                self::$isChildTemplate = true;
                $this->addTemplateDir(PFAD_ROOT . PFAD_TEMPLATES . $parent, $parent . '/')
                     ->assign('parent_template_path', PFAD_ROOT . PFAD_TEMPLATES . $parent . '/')
                     ->assign('parentTemplateDir', PFAD_TEMPLATES . $parent . '/');
            }
        } else {
            $_compileDir = PFAD_ROOT . PFAD_ADMIN . PFAD_COMPILEDIR;
            if (!file_exists($_compileDir)) {
                mkdir($_compileDir);
            }
            $this->context = 'backend';
            $this->setCaching(false)
                 ->setDebugging(SMARTY_DEBUG_CONSOLE ? true : false)
                 ->setTemplateDir([$this->context => PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES . $cTemplate])
                 ->setCompileDir($_compileDir)
                 ->setConfigDir(PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES . $cTemplate . '/lang/')
                 ->setPluginsDir(SMARTY_PLUGINS_DIR)
                 ->configLoad('german.conf', 'global');
            unset($this->config['caching']['page_cache']);
        }
        $this->template = $template;

        if ($fast_init === false) {
            $this->registerPlugin('function', 'lang', [$this, 'translate'])
                 ->registerPlugin('modifier', 'replace_delim', [$this, 'replaceDelimiters'])
                 ->registerPlugin('modifier', 'count_characters', [$this, 'countCharacters'])
                 ->registerPlugin('modifier', 'string_format', [$this, 'stringFormat'])
                 ->registerPlugin('modifier', 'string_date_format', [$this, 'dateFormat'])
                 ->registerPlugin('modifier', 'truncate', [$this, 'truncate']);

            if ($isAdmin === false) {
                $this->cache_lifetime = 86400;
                $this->template_class = 'JTLSmartyTemplateClass';
            }
            if (!$isAdmin) {
                $this->setCachingParams($this->config);
            }
            $_tplDir = $this->getTemplateDir($this->context);
            global $smarty;
            $smarty = $this;
            if (file_exists($_tplDir . 'php/functions_custom.php')) {
                require_once $_tplDir . 'php/functions_custom.php';
            } elseif (file_exists($_tplDir . 'php/functions.php')) {
                require_once $_tplDir . 'php/functions.php';
            } elseif ($parent !== null && file_exists(PFAD_ROOT . PFAD_TEMPLATES . $parent . '/php/functions.php')) {
                require_once PFAD_ROOT . PFAD_TEMPLATES . $parent . '/php/functions.php';
            }
        }
        if ($context === 'frontend' || $context === 'backend') {
            self::$_instance = $this;
        }
        if ($isAdmin === false && $fast_init === false) {
            executeHook(HOOK_SMARTY_INC);
        }
    }

    /**
     * set options
     *
     * @param array|null $config
     * @return $this
     */
    public function setCachingParams($config = null)
    {
        // instantiate new cache - we use different options here
        if ($config === null) {
            $config = Shop::getSettings([CONF_CACHING]);
        }

        return $this->setCaching(TV_MODE === true ? self::CACHING_LIFETIME_SAVED : self::CACHING_OFF)
                    ->setCompileCheck(!(isset($config['caching']['compile_check'])
                        && $config['caching']['compile_check'] === 'N'));
    }

    /**
     * @param bool $fast_init
     * @param bool $isAdmin
     * @return JTLSmarty|null
     */
    public static function getInstance($fast_init = false, $isAdmin = false)
    {
        return self::$_instance ?? new self($fast_init, $isAdmin);
    }

    /**
     * phpquery output filter
     *
     * @param string $tplOutput
     * @return string
     */
    public function outputFilter($tplOutput)
    {
        $hookList = Plugin::getHookList();
        $isMobile = $this->template->isMobileTemplateActive();
        if ($isMobile
            || ((isset($hookList[HOOK_SMARTY_OUTPUTFILTER])
                && is_array($hookList[HOOK_SMARTY_OUTPUTFILTER])
                && count($hookList[HOOK_SMARTY_OUTPUTFILTER]) > 0)
                || count(EventDispatcher::getInstance()->getListeners('shop.hook.' . HOOK_SMARTY_OUTPUTFILTER)) > 0
            )
        ) {
            $this->unregisterFilter('output', [$this, 'outputFilter']);
            $doc = phpQuery::newDocumentHTML($tplOutput, JTL_CHARSET);
            executeHook($isMobile ? HOOK_SMARTY_OUTPUTFILTER_MOBILE : HOOK_SMARTY_OUTPUTFILTER);
            $tplOutput = $doc->htmlOuter();
        }

        return isset($this->config['template']['general']['minify_html'])
        && $this->config['template']['general']['minify_html'] === 'Y'
            ? $this->minify_html(
                $tplOutput,
                isset($this->config['template']['general']['minify_html_css'])
                && $this->config['template']['general']['minify_html_css'] === 'Y',
                isset($this->config['template']['general']['minify_html_js'])
                && $this->config['template']['general']['minify_html_js'] === 'Y'
            )
            : $tplOutput;
    }

    /**
     * @param bool $mode
     * @return $this
     */
    public function setCaching($mode)
    {
        $this->caching = $mode;

        return $this;
    }

    /**
     * @param bool $mode
     * @return $this
     */
    public function setDebugging($mode)
    {
        $this->debugging = $mode;

        return $this;
    }

    /**
     * html minification
     *
     * @param string $html
     * @param bool   $minifyCSS
     * @param bool   $minifyJS
     * @return string
     */
    private function minify_html($html, $minifyCSS = false, $minifyJS = false)
    {
        $options = [];
        if ($minifyCSS === true) {
            $options['cssMinifier'] = ['Minify_CSS', 'minify'];
        }
        if ($minifyJS === true) {
            $options['jsMinifier'] = ['\JSMin\JSMin', 'minify'];
        }
        try {
            $res = (new Minify_HTML($html, $options))->process();
        } catch (JSMin\UnterminatedStringException $e) {
            $res = $html;
        }

        return $res;

    }

    /**
     * translation
     *
     * @param array                    $params
     * @param Smarty_Internal_Template $template
     * @return void|string
     */
    public function translate($params, Smarty_Internal_Template $template)
    {
        $cValue = '';
        if (!isset($params['section'])) {
            $params['section'] = 'global';
        }
        if (isset($params['section'], $params['key'])) {
            $cValue = Shop::Lang()->get($params['key'], $params['section']);
            // Für vsprintf ein String der :: exploded wird
            if (isset($params['printf']) && strlen($params['printf']) > 0) {
                $cValue = vsprintf($cValue, explode(':::', $params['printf']));
            }
        }
        if (SMARTY_SHOW_LANGKEY) {
            $cValue = '#' . $params['section'] . '.' . $params['key'] . '#';
        }
        if (isset($params['assign'])) {
            $template->assign($params['assign'], $cValue);
        } else {
            return $cValue;
        }
    }

    /**
     * @param string $text
     * @return int
     */
    public function countCharacters($text)
    {
        return strlen($text);
    }

    /**
     * @param string $string
     * @param string $format
     * @return string
     */
    public function stringFormat($string, $format)
    {
        return sprintf($format, $string);
    }

    /**
     * @param string $string
     * @param string $format
     * @param string $default_date
     * @return string
     */
    public function dateFormat($string, $format = '%b %e, %Y', $default_date = '')
    {
        if ($string !== '') {
            $timestamp = smarty_make_timestamp($string);
        } elseif ($default_date !== '') {
            $timestamp = smarty_make_timestamp($default_date);
        } else {
            return $string;
        }
        if (DIRECTORY_SEPARATOR === '\\') {
            $_win_from = ['%D', '%h', '%n', '%r', '%R', '%t', '%T'];
            $_win_to   = ['%m/%d/%y', '%b', "\n", '%I:%M:%S %p', '%H:%M', "\t", '%H:%M:%S'];
            if (strpos($format, '%e') !== false) {
                $_win_from[] = '%e';
                $_win_to[]   = sprintf('%\' 2d', date('j', $timestamp));
            }
            if (strpos($format, '%l') !== false) {
                $_win_from[] = '%l';
                $_win_to[]   = sprintf('%\' 2d', date('h', $timestamp));
            }
            $format = str_replace($_win_from, $_win_to, $format);
        }

        return strftime($format, $timestamp);
    }

    /**
     * @param string $string
     * @param int    $length
     * @param string $etc
     * @param bool   $break_words
     * @param bool   $middle
     * @return mixed|string
     */
    public function truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false)
    {
        $length = (int)$length;
        if ($length === 0) {
            return '';
        }
        if (strlen($string) > $length) {
            $length -= min($length, strlen($etc));
            if (!$break_words && !$middle) {
                $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length + 1));
            }

            return !$middle
                ? substr($string, 0, $length) . $etc
                : substr($string, 0, $length / 2) . $etc . substr($string, -$length / 2);
        }

        return $string;
    }

    /**
     * @param string $cText
     * @return string
     */
    public function replaceDelimiters($cText)
    {
        $cReplace = $this->config['global']['global_dezimaltrennzeichen_sonstigeangaben'];
        if ($cReplace !== ',' || $cReplace !== '.' || $cReplace === '') {
            $cReplace = ',';
        }

        return str_replace('.', $cReplace, $cText);
    }

    /**
     * @param string $cFilename
     * @return string
     */
    public function getCustomFile($cFilename)
    {
        if (self::$isChildTemplate === true
            || !isset($this->config['template']['general']['use_customtpl'])
            || $this->config['template']['general']['use_customtpl'] !== 'Y'
        ) {
            // disabled on child templates for now
            return $cFilename;
        }
        $cFile       = basename($cFilename, '.tpl');
        $cSubPath    = dirname($cFilename);
        $cCustomFile = (strpos($cSubPath, PFAD_ROOT) === false)
            ? $this->getTemplateDir($this->context) . (($cSubPath === '.')
                ? ''
                : ($cSubPath . '/')) . $cFile . '_custom.tpl'
            : ($cSubPath . '/' . $cFile . '_custom.tpl');

        return file_exists($cCustomFile) ? $cCustomFile : $cFilename;
    }

    /**
     * @param string $cFilename
     * @return string
     * @deprecated since 5.0.0
     */
    public function getFallbackFile($cFilename)
    {
        return $cFilename;
    }

    /**
     * fetches a rendered Smarty template
     *
     * @param  string $template   the resource handle of the template file or template object
     * @param  mixed  $cache_id   cache id to be used with this template
     * @param  mixed  $compile_id compile id to be used with this template
     * @param  object $parent     next higher level of Smarty variables
     *
     * @throws Exception
     * @throws SmartyException
     * @return string rendered template output
     */
    public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        $_debug = !empty($this->_debug->template_data)
            ? $this->_debug->template_data
            : null;
        $res    = parent::fetch($this->getResourceName($template), $cache_id, $compile_id, $parent);
        if ($_debug !== null) {
            // fetch overwrites the old debug data so we have to merge it with our previously saved data
            $this->_debug->template_data = array_merge($_debug, $this->_debug->template_data);
        }

        return $res;
    }

    /**
     * displays a Smarty template
     *
     * @param string $template   the resource handle of the template file or template object
     * @param mixed  $cache_id   cache id to be used with this template
     * @param mixed  $compile_id compile id to be used with this template
     * @param object $parent     next higher level of Smarty variables
     */
    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        if ($this->context === 'frontend') {
            $this->registerFilter('output', [$this, 'outputFilter']);
            if ($cache_id === null) {
                $cache_id = $this->getCacheID($template);
            }
        }

        return parent::display($this->getResourceName($template), $cache_id, $compile_id, $parent);
    }

    /**
     * @param null|string $template
     * @param null|string $cache_id
     * @param null|string $compile_id
     * @param null        $parent
     * @return bool
     */
    public function displayCached($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        $params = Shop::getParameters();
        switch (Shop::getPageType()) {
            case PAGE_404:
            case PAGE_DATENSCHUTZ:
            case PAGE_STARTSEITE:
            case PAGE_AGB:
            case PAGE_WRB:
            case PAGE_EIGENE:
            case PAGE_HERSTELLER:
            case PAGE_SITEMAP:
                $template = 'layout/index.tpl';
                break;
            case PAGE_NEWSLETTER:
                $template = 'newsletter/index.tpl';
                break;
            case PAGE_ARTIKEL:
                $template = 'productdetails/index.tpl';
                break;
            case PAGE_KONTAKT:
                $template = 'contact/index.tpl';
                break;
            case PAGE_ARTIKELLISTE:
                $template = 'productlist/index.tpl';
                break;
            default:
                $template = null;
                break;
        }
        if ($template === null) {
            $this->setCaching(self::CACHING_OFF);
            return false;
        }
        if ($cache_id === null) {
            $cache_id = $this->getCacheID($template);
        }
        header('CACHE-ID: ' . $cache_id);
        if (!$this->isCached($template, $cache_id)) {
            header('CACHED: false');

            return false;
        }
        header('CACHED: true ');
        $shopURL               = Shop::getURL();
        $cart                  = Session::Cart();
        $warensumme[0]         = gibPreisStringLocalized(
            $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL],
            true)
        );
        $warensumme[1]         = gibPreisStringLocalized(
            $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], false)
        );
        $gesamtsumme[0]        = gibPreisStringLocalized($cart->gibGesamtsummeWaren(true, true));
        $gesamtsumme[1]        = gibPreisStringLocalized($cart->gibGesamtsummeWaren(false, true));
        $oTemplate             = Template::getInstance();
        $bMobilAktiv           = $oTemplate->isMobileTemplateActive();
        $currentTemplateFolder = $oTemplate->getDir();
        $currentTemplateDir    = PFAD_TEMPLATES . $currentTemplateFolder . '/';
        $themeDir              = empty($this->config['template']['theme']['theme_default'])
            ? 'evo'
            : $this->config['template']['theme']['theme_default'];

        $this
            ->assign('nTemplateVersion', $oTemplate->getVersion())
            ->assign('currentTemplateDir', $currentTemplateDir)
            ->assign('currentTemplateDirFull', $shopURL . '/' . $currentTemplateDir)
            ->assign('currentTemplateDirFullPath', PFAD_ROOT . $currentTemplateDir)
            ->assign('currentThemeDir', $currentTemplateDir . 'themes/' . $themeDir . '/')
            ->assign('currentThemeDirFull', $shopURL . '/' . $currentTemplateDir . 'themes/' . $themeDir . '/')
            ->assign('lang', Shop::getLanguageCode())
            ->assign('ShopURL', $shopURL)
            ->assign('imageBaseURL', Shop::getImageBaseURL())
            ->assign('oSpezialseiten_arr', LinkHelper::getInstance()->getSpecialPages())
            ->assign('ShopURLSSL', Shop::getURL(true))
            ->assign('NettoPreise', Session::CustomerGroup()->getIsMerchant())
            ->assign('PFAD_GFX_BEWERTUNG_STERNE', PFAD_GFX_BEWERTUNG_STERNE)
            ->assign('PFAD_BILDER_BANNER', PFAD_BILDER_BANNER)
            ->assign('WarenkorbArtikelanzahl', $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]))
            ->assign('WarenkorbArtikelPositionenanzahl', $cart->gibAnzahlPositionenExt([C_WARENKORBPOS_TYP_ARTIKEL]))
            ->assign('WarenkorbWarensumme', $warensumme)
            ->assign('WarenkorbGesamtsumme', $gesamtsumme)
            ->assign('WarenkorbGesamtgewicht', $cart->getWeight())
            ->assign('Warenkorbtext', lang_warenkorb_warenkorbEnthaeltXArtikel($cart))
            ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
            ->assign('meta_publisher', $this->config['metaangaben']['global_meta_publisher'])
            ->assign('meta_copyright', $this->config['metaangaben']['global_meta_copyright'])
            ->assign('meta_language', StringHandler::convertISO2ISO639($_SESSION['cISOSprache']))
            ->assign('bAjaxRequest', isAjaxRequest())
            ->assign('jtl_token', getTokenInput())
            ->assign('JTL_CHARSET', JTL_CHARSET)
            ->assign('PFAD_INCLUDES_LIBS', PFAD_INCLUDES_LIBS)
            ->assign('PFAD_FLASHCHART', PFAD_FLASHCHART)
            ->assign('PFAD_MINIFY', PFAD_MINIFY)
            ->assign('PFAD_UPLOADIFY', PFAD_UPLOADIFY)
            ->assign('PFAD_UPLOAD_CALLBACK', PFAD_UPLOAD_CALLBACK)
            ->assign('TS_BUYERPROT_CLASSIC', TS_BUYERPROT_CLASSIC)
            ->assign('TS_BUYERPROT_EXCELLENCE', TS_BUYERPROT_EXCELLENCE)
            ->assign('CHECKBOX_ORT_REGISTRIERUNG', CHECKBOX_ORT_REGISTRIERUNG)
            ->assign('CHECKBOX_ORT_BESTELLABSCHLUSS', CHECKBOX_ORT_BESTELLABSCHLUSS)
            ->assign('CHECKBOX_ORT_NEWSLETTERANMELDUNG', CHECKBOX_ORT_NEWSLETTERANMELDUNG)
            ->assign('CHECKBOX_ORT_KUNDENDATENEDITIEREN', CHECKBOX_ORT_KUNDENDATENEDITIEREN)
            ->assign('CHECKBOX_ORT_KONTAKT', CHECKBOX_ORT_KONTAKT)
            ->assign('nSeitenTyp', Shop::getPageType())
            ->assign('bExclusive', isset($_GET['exclusive_content']))
            ->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
            ->assign('Steuerpositionen', $cart->gibSteuerpositionen())
            ->assign('Einstellungen', $this->config)
            ->assign('deletedPositions', Warenkorb::$deletedPositions)
            ->assign('updatedPositions', Warenkorb::$updatedPositions)
            ->assign('BILD_KEIN_KATEGORIEBILD_VORHANDEN', BILD_KEIN_KATEGORIEBILD_VORHANDEN)
            ->assign('BILD_KEIN_ARTIKELBILD_VORHANDEN', BILD_KEIN_ARTIKELBILD_VORHANDEN)
            ->assign('BILD_KEIN_HERSTELLERBILD_VORHANDEN', BILD_KEIN_HERSTELLERBILD_VORHANDEN)
            ->assign('BILD_KEIN_MERKMALBILD_VORHANDEN', BILD_KEIN_MERKMALBILD_VORHANDEN)
            ->assign('BILD_KEIN_MERKMALWERTBILD_VORHANDEN', BILD_KEIN_MERKMALWERTBILD_VORHANDEN)
            ->assign('showLoginCaptcha', $_SESSION['showLoginCaptcha'] ?? false)
            ->assign('PFAD_SLIDER', $shopURL . '/' . PFAD_BILDER_SLIDER)
            ->assign('ERWDARSTELLUNG_ANSICHT_LISTE', ERWDARSTELLUNG_ANSICHT_LISTE)
            ->assign('ERWDARSTELLUNG_ANSICHT_GALERIE', ERWDARSTELLUNG_ANSICHT_GALERIE)
            ->assign('ERWDARSTELLUNG_ANSICHT_MOSAIK', ERWDARSTELLUNG_ANSICHT_MOSAIK)
            ->assign('Anrede_m', Shop::Lang()->get('salutationM'))
            ->assign('Anrede_w', Shop::Lang()->get('salutationW'));

        parent::display($this->getResourceName($template), $cache_id, $compile_id, $parent);
        require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
        exit();
    }

    /**
     * generates a unique cache id for every given resource
     *
     * @param string $resource_name
     * @return null|string
     */
    public function getCacheID($resource_name)
    {
        $params   = Shop::getParameters();
        $cache_id = 'msc|';
        if ($params['kArtikel']) {
            $cache_id = CACHING_GROUP_ARTICLE . '|id' . $params['kArtikel'];
        } elseif ($params['kLink'] > 0) {
            $cache_id = 'pg|id' . $params['kLink'];
        } elseif ($params['kKategorie'] > 0) {
            $cache_id = CACHING_GROUP_CATEGORY . '|id' . $params['kKategorie'];
        } elseif ($params['kHersteller'] > 0) {
            $cache_id = CACHING_GROUP_MANUFACTURER . '|id' . $params['kHersteller'];
        } elseif ($params['kMerkmalWert'] > 0) {
            $cache_id = 'av|id' . $params['kMerkmalWert'];
        } elseif ($params['kSuchspecial'] > 0) {
            $cache_id = 'ss|id' . $params['kSuchspecial'];
        }

        return $cache_id .
            '|pt' . Shop::getPageType() .
            '|lid' . Shop::getLanguageID() .
            '|cgid' . Session::CustomerGroup()->getID() .
            '|cid' . Session::Currency()->getID() .
            '|tpl' . md5($resource_name) .
            '|' . md5(json_encode($params));
    }

    /**
     * @param string $resource_name
     * @return string
     */
    public function getResourceName($resource_name)
    {
        $transform = false;
        if (strpos($resource_name, 'string:') === 0) {
            return $resource_name;
        }
        if (strpos($resource_name, 'file:') === 0) {
            $resource_name = str_replace('file:', '', $resource_name);
            $transform     = true;
        }
        $resource_custom_name = $this->getCustomFile($resource_name);
        $resource_cfb_name    = $resource_custom_name;

        executeHook(HOOK_SMARTY_FETCH_TEMPLATE, [
            'original'  => &$resource_name,
            'custom'    => &$resource_custom_name,
            'fallback'  => &$resource_custom_name,
            'out'       => &$resource_cfb_name,
            'transform' => $transform
        ]);

        if ($this->context === 'frontend'
            && $resource_name === $resource_cfb_name
            && file_exists($this->getTemplateDir('frontend') . $resource_cfb_name)
        ) {
            $pluginTemplateExtends = [];

            foreach (Plugin::getTemplatePaths() as $moduleId => $pluginTemplatePath) {
                $templateKey = 'plugin_' . $moduleId;
                $templateVar = 'oPlugin_' . $moduleId;

                if ($this->getTemplateVars($templateVar) === null) {
                    $oPlugin = Plugin::getPluginById($moduleId);
                    $this->assign($templateVar, $oPlugin);
                }

                $file = $this->_realpath($pluginTemplatePath . $resource_cfb_name, true);
                if (file_exists($file)) {
                    $pluginTemplateExtends[] = sprintf('[%s]%s', $templateKey, $resource_cfb_name);
                }
            }

            if (count($pluginTemplateExtends) > 0) {
                $transform         = false;
                $resource_cfb_name = sprintf(
                    'extends:[frontend]%s|%s',
                    $resource_cfb_name,
                    implode('|', $pluginTemplateExtends)
                );
            }
        }

        return $transform ? ('file:' . $resource_cfb_name) : $resource_cfb_name;
    }

    /**
     * @param bool $use_sub_dirs
     * @return $this
     */
    public function setUseSubDirs($use_sub_dirs)
    {
        parent::setUseSubDirs($use_sub_dirs);

        return $this;
    }

    /**
     * @param bool $force_compile
     * @return $this
     */
    public function setForceCompile($force_compile)
    {
        parent::setForceCompile($force_compile);

        return $this;
    }

    /**
     * @param bool $compile_check
     * @return $this
     */
    public function setCompileCheck($compile_check)
    {
        parent::setCompileCheck($compile_check);

        return $this;
    }

    /**
     * @param int $error_reporting
     * @return $this
     */
    public function setErrorReporting($error_reporting)
    {
        parent::setErrorReporting($error_reporting);

        return $this;
    }

    /**
     * @return bool
     */
    public static function getIsChildTemplate()
    {
        return self::$isChildTemplate;
    }

    /**
     * When Smarty is used in an insecure context (e.g. when third parties are granted access to shop admin) this
     * function activates a secure mode that:
     *   - deactivates {php}-tags
     *   - removes php code (that could be written to a file an then be executes)
     *   - applies a whitelist for php functions (Smarty modifiers and functions)
     *
     * @return $this
     * @throws SmartyException
     */
    public function activateBackendSecurityMode()
    {
        $sec                = new Smarty_Security($this);
        $sec->php_handling  = Smarty::PHP_REMOVE;
        $sec->allow_php_tag = false;
        $jtlModifier        = [
            'replace_delim',
            'count_characters',
            'string_format',
            'string_date_format',
            'truncate',
        ];
        $secureFuncs        = $this->getSecurePhpFunctions();
        $sec->php_modifiers = array_merge(
            $sec->php_modifiers,
            $jtlModifier,
            $secureFuncs
        );
        $sec->php_modifiers = array_unique($sec->php_modifiers);
        $sec->php_functions = array_unique(array_merge($sec->php_functions, $secureFuncs, ['lang']));
        $this->enableSecurity($sec);

        return $this;
    }

    /**
     * Get a list of php functions, that should be save to use in an insecure context.
     *
     * @return string[]
     */
    private function getSecurePhpFunctions()
    {
        static $functions;
        if ($functions === null) {
            $functions = array_map('trim', explode(',', SECURE_PHP_FUNCTIONS));
        }

        return $functions;
    }
}
