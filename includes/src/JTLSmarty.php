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
     * @var \Cache\JTLCacheInterface
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
    public function __construct(bool $fast_init = false, bool $isAdmin = false, bool $tplCache = true, string $context = 'frontend')
    {
        parent::__construct();
        Smarty::$_CHARSET = JTL_CHARSET;
        if (defined('SMARTY_USE_SUB_DIRS') && is_bool(SMARTY_USE_SUB_DIRS)) {
            $this->setUseSubDirs(SMARTY_USE_SUB_DIRS);
        }
        $this->setErrorReporting(SMARTY_LOG_LEVEL)
             ->setForceCompile(SMARTY_FORCE_COMPILE ? true : false)
             ->setDebugging(SMARTY_DEBUG_CONSOLE ? true : false);

        $this->config = Shop::getSettings([CONF_TEMPLATE, CONF_CACHING, CONF_GLOBAL]);
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
    public function setCachingParams(array $config = null)
    {
        // instantiate new cache - we use different options here
        if ($config === null) {
            $config = Shop::getSettings([CONF_CACHING]);
        }

        return $this->setCaching(self::CACHING_OFF)
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
        if ((isset($hookList[HOOK_SMARTY_OUTPUTFILTER])
                && is_array($hookList[HOOK_SMARTY_OUTPUTFILTER])
                && count($hookList[HOOK_SMARTY_OUTPUTFILTER]) > 0)
                || count(EventDispatcher::getInstance()->getListeners('shop.hook.' . HOOK_SMARTY_OUTPUTFILTER)) > 0
        ) {
            $this->unregisterFilter('output', [$this, 'outputFilter']);
            $doc = phpQuery::newDocumentHTML($tplOutput, JTL_CHARSET);
            executeHook(HOOK_SMARTY_OUTPUTFILTER);
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
     * @param null|string $template
     * @param null|string $cache_id
     * @param null|string $compile_id
     * @param null $parent
     * @return bool
     */
    public function isCached($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        return false;
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
    private function minify_html(string $html, bool $minifyCSS = false, bool $minifyJS = false)
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
    public function truncate($string, int $length = 80, $etc = '...', bool $break_words = false, bool $middle = false)
    {
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
        }

        return parent::display($this->getResourceName($template), $cache_id, $compile_id, $parent);
    }

    /**
     * generates a unique cache id for every given resource
     *
     * @param string      $resource_name
     * @param array       $conditions
     * @param string|null $cache_id
     * @return null|string
     */
    public function getCacheID($resource_name, $conditions, $cache_id = null)
    {
        return null;
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
