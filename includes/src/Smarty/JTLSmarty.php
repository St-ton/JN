<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Smarty;

use Events\Dispatcher;

/**
 * Class JTLSmarty
 * @package Smarty
 * @method JTLSmarty assign(string $variable, mixed $value)
 */
class JTLSmarty extends \SmartyBC
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
     * @var \Template
     */
    public $template;

    /**
     * @var JTLSmarty|null
     */
    public static $instance;

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
     * @param bool   $fast - set to true when init from backend to avoid setting session data
     * @param string $context
     */
    public function __construct(bool $fast = false, string $context = ContextType::FRONTEND)
    {
        parent::__construct();
        \Smarty::$_CHARSET = \JTL_CHARSET;
        $this->setErrorReporting(\SMARTY_LOG_LEVEL)
             ->setForceCompile(\SMARTY_FORCE_COMPILE)
             ->setDebugging(\SMARTY_DEBUG_CONSOLE)
             ->setUseSubDirs(\SMARTY_USE_SUB_DIRS);
        $this->context = $context;
        $this->config  = \Shop::getSettings([\CONF_TEMPLATE, \CONF_CACHING, \CONF_GLOBAL]);

        $parent = $this->initTemplate();
        if ($fast === false) {
            $this->init($parent);
        }
        if ($context === ContextType::FRONTEND || $context === ContextType::BACKEND) {
            self::$instance = $this;
        }
        if ($fast === false && $context !== ContextType::BACKEND) {
            \executeHook(\HOOK_SMARTY_INC, ['smarty' => $this]);
        }
    }

    /**
     * @return string|null
     */
    private function initTemplate(): ?string
    {
        $parent         = null;
        $this->template = $this->context === ContextType::BACKEND
            ? \AdminTemplate::getInstance()
            : \Template::getInstance();
        $tplDir         = $this->template->getDir();
        if ($this->context !== ContextType::BACKEND) {
            $parent     = $this->template->getParent();
            $compileDir = \PFAD_ROOT . \PFAD_COMPILEDIR . $tplDir . '/';
            if (!\file_exists($compileDir)) {
                \mkdir($compileDir);
            }
            $templatePaths[$this->context] = \PFAD_ROOT . \PFAD_TEMPLATES . $tplDir . '/';
            foreach (\Plugin\Helper::getTemplatePaths() as $moduleId => $path) {
                $templateKey                 = 'plugin_' . $moduleId;
                $templatePaths[$templateKey] = $path;
            }
            $this->setTemplateDir($templatePaths)
                 ->setCompileDir($compileDir)
                 ->setCacheDir(\PFAD_ROOT . \PFAD_COMPILEDIR . $tplDir . '/' . 'page_cache/')
                 ->setPluginsDir(\SMARTY_PLUGINS_DIR);

            if ($parent !== null) {
                self::$isChildTemplate = true;
                $this->addTemplateDir(\PFAD_ROOT . \PFAD_TEMPLATES . $parent, $parent . '/')
                     ->assign('parent_template_path', \PFAD_ROOT . \PFAD_TEMPLATES . $parent . '/')
                     ->assign('parentTemplateDir', \PFAD_TEMPLATES . $parent . '/');
            }
        } else {
            $compileDir = \PFAD_ROOT . \PFAD_ADMIN . \PFAD_COMPILEDIR;
            if (!\file_exists($compileDir)) {
                \mkdir($compileDir);
            }
            $this->setCaching(false)
                 ->setDebugging(\SMARTY_DEBUG_CONSOLE)
                 ->setTemplateDir([$this->context => \PFAD_ROOT . \PFAD_ADMIN . \PFAD_TEMPLATES . $tplDir])
                 ->setCompileDir($compileDir)
                 ->setConfigDir(\PFAD_ROOT . \PFAD_ADMIN . \PFAD_TEMPLATES . $tplDir . '/lang/')
                 ->setPluginsDir(\SMARTY_PLUGINS_DIR)
                 ->configLoad('german.conf', 'global');
        }

        return $parent;
    }

    /**
     * @param null $parent
     * @throws \SmartyException
     */
    private function init($parent = null): void
    {
        $pluginCollection = new PluginCollection($this->config, \Sprache::getInstance());
        $this->registerPlugin('function', 'lang', [$pluginCollection, 'translate'])
             ->registerPlugin('modifier', 'replace_delim', [$pluginCollection, 'replaceDelimiters'])
             ->registerPlugin('modifier', 'count_characters', [$pluginCollection, 'countCharacters'])
             ->registerPlugin('modifier', 'string_format', [$pluginCollection, 'stringFormat'])
             ->registerPlugin('modifier', 'string_date_format', [$pluginCollection, 'dateFormat'])
             ->registerPlugin('modifiercompiler', 'default', [$pluginCollection, 'compilerModifierDefault'])
             ->registerPlugin('modifier', 'truncate', [$pluginCollection, 'truncate']);

        if ($this->context !== ContextType::BACKEND) {
            $this->cache_lifetime = 86400;
            $this->template_class = \SHOW_TEMPLATE_HINTS > 0
                ? JTLSmartyTemplateHints::class
                : JTLSmartyTemplateClass::class;
            $this->setCachingParams($this->config);
        }
        $tplDir = $this->getTemplateDir($this->context);
        global $smarty;
        $smarty = $this;
        if (\file_exists($tplDir . 'php/functions_custom.php')) {
            require_once $tplDir . 'php/functions_custom.php';
        } elseif (\file_exists($tplDir . 'php/functions.php')) {
            require_once $tplDir . 'php/functions.php';
        } elseif ($parent !== null && \file_exists(\PFAD_ROOT . \PFAD_TEMPLATES . $parent . '/php/functions.php')) {
            require_once \PFAD_ROOT . \PFAD_TEMPLATES . $parent . '/php/functions.php';
        }
    }

    /**
     * set options
     *
     * @param array|null $config
     * @return $this
     */
    public function setCachingParams(array $config = null): self
    {
        // instantiate new cache - we use different options here
        if ($config === null) {
            $config = \Shop::getSettings([\CONF_CACHING]);
        }

        return $this->setCaching(self::CACHING_OFF)
                    ->setCompileCheck(!(isset($config['caching']['compile_check'])
                        && $config['caching']['compile_check'] === 'N'));
    }

    /**
     * @param bool   $fast
     * @param string $context
     * @return JTLSmarty
     */
    public static function getInstance(bool $fast = false, string $context = ContextType::FRONTEND): self
    {
        return self::$instance ?? new self($fast, $context);
    }

    /**
     * Backslashes on Windows systems should be replaced by forward slashes in paths.
     *
     * @inheritdoc
     */
    public function getTemplateDir($index = null, $isConfig = false)
    {
        return \str_replace('\\', '/', parent::getTemplateDir($index, $isConfig));
    }

    /**
     * phpquery output filter
     *
     * @param string $tplOutput
     * @return string
     */
    public function outputFilter(string $tplOutput): string
    {
        $hookList = \Plugin\Helper::getHookList();
        if ((isset($hookList[\HOOK_SMARTY_OUTPUTFILTER])
                && \is_array($hookList[\HOOK_SMARTY_OUTPUTFILTER])
                && \count($hookList[\HOOK_SMARTY_OUTPUTFILTER]) > 0)
                || \count(Dispatcher::getInstance()->getListeners('shop.hook.' . \HOOK_SMARTY_OUTPUTFILTER)) > 0
        ) {
            require_once \PFAD_ROOT . \PFAD_PHPQUERY . 'phpquery.class.php';
            $this->unregisterFilter('output', [$this, 'outputFilter']);
            $doc = \phpQuery::newDocumentHTML($tplOutput, \JTL_CHARSET);
            \executeHook(\HOOK_SMARTY_OUTPUTFILTER, ['smarty' => $this, 'document' => $doc]);
            $tplOutput = $doc->htmlOuter();
        }

        return isset($this->config['template']['general']['minify_html'])
        && $this->config['template']['general']['minify_html'] === 'Y'
            ? $this->minifyHTML(
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
     * @param null|string $cacheID
     * @param null|string $compileID
     * @param null $parent
     * @return bool
     */
    public function isCached($template = null, $cacheID = null, $compileID = null, $parent = null): bool
    {
        return false;
    }

    /**
     * @param bool $mode
     * @return $this
     */
    public function setCaching($mode): self
    {
        $this->caching = $mode;

        return $this;
    }

    /**
     * @param bool $mode
     * @return $this
     */
    public function setDebugging($mode): self
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
    private function minifyHTML(string $html, bool $minifyCSS = false, bool $minifyJS = false): string
    {
        $options = [];
        if ($minifyCSS === true) {
            $options['cssMinifier'] = [\Minify_CSS::class, 'minify'];
        }
        if ($minifyJS === true) {
            $options['jsMinifier'] = [\JSMin\JSMin::class, 'minify'];
        }
        try {
            $res = (new \Minify_HTML($html, $options))->process();
        } catch (\JSMin\UnterminatedStringException $e) {
            $res = $html;
        }

        return $res;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getCustomFile(string $filename): string
    {
        if (self::$isChildTemplate === true
            || !isset($this->config['template']['general']['use_customtpl'])
            || $this->config['template']['general']['use_customtpl'] !== 'Y'
        ) {
            // disabled on child templates for now
            return $filename;
        }
        $cFile       = \basename($filename, '.tpl');
        $cSubPath    = \dirname($filename);
        $cCustomFile = \strpos($cSubPath, \PFAD_ROOT) === false
            ? $this->getTemplateDir($this->context) . (($cSubPath === '.')
                ? ''
                : ($cSubPath . '/')) . $cFile . '_custom.tpl'
            : ($cSubPath . '/' . $cFile . '_custom.tpl');

        return \file_exists($cCustomFile) ? $cCustomFile : $filename;
    }

    /**
     * @param string $filename
     * @return string
     * @deprecated since 5.0.0
     */
    public function getFallbackFile(string $filename): string
    {
        return $filename;
    }

    /**
     * fetches a rendered Smarty template
     *
     * @param  string $template   the resource handle of the template file or template object
     * @param  mixed  $cacheID   cache id to be used with this template
     * @param  mixed  $compileID compile id to be used with this template
     * @param  object $parent     next higher level of Smarty variables
     *
     * @throws \Exception
     * @throws \SmartyException
     * @return string rendered template output
     */
    public function fetch($template = null, $cacheID = null, $compileID = null, $parent = null): string
    {
        $_debug = !empty($this->_debug->template_data)
            ? $this->_debug->template_data
            : null;
        $res    = parent::fetch($this->getResourceName($template), $cacheID, $compileID, $parent);
        if ($_debug !== null) {
            // fetch overwrites the old debug data so we have to merge it with our previously saved data
            $this->_debug->template_data = \array_merge($_debug, $this->_debug->template_data);
        }

        return $res;
    }

    /**
     * displays a Smarty template
     *
     * @param string $template   the resource handle of the template file or template object
     * @param mixed  $cacheID   cache id to be used with this template
     * @param mixed  $compileID compile id to be used with this template
     * @param object $parent     next higher level of Smarty variables
     * @throws \SmartyException
     */
    public function display($template = null, $cacheID = null, $compileID = null, $parent = null)
    {
        if ($this->context === 'frontend') {
            $this->registerFilter('output', [$this, 'outputFilter']);
        }

        return parent::display($this->getResourceName($template), $cacheID, $compileID, $parent);
    }

    /**
     * generates a unique cache id for every given resource
     *
     * @param string      $resourceName
     * @param array       $conditions
     * @param string|null $cacheID
     * @return null|string
     */
    public function getCacheID($resourceName, $conditions, $cacheID = null)
    {
        return null;
    }

    /**
     * @param string $resourceName
     * @return string
     */
    public function getResourceName(string $resourceName): string
    {
        $transform = false;
        if (\strpos($resourceName, 'string:') === 0) {
            return $resourceName;
        }
        if (\strpos($resourceName, 'file:') === 0) {
            $resourceName = \str_replace('file:', '', $resourceName);
            $transform     = true;
        }
        $resource_custom_name = $this->getCustomFile($resourceName);
        $resource_cfb_name    = $resource_custom_name;

        \executeHook(\HOOK_SMARTY_FETCH_TEMPLATE, [
            'original'  => &$resourceName,
            'custom'    => &$resource_custom_name,
            'fallback'  => &$resource_custom_name,
            'out'       => &$resource_cfb_name,
            'transform' => $transform
        ]);

        if ($this->context === 'frontend'
            && $resourceName === $resource_cfb_name
            && \file_exists($this->getTemplateDir(ContextType::FRONTEND) . $resource_cfb_name)
        ) {
            $pluginTemplateExtends = [];
            foreach (\Plugin\Helper::getTemplatePaths() as $moduleId => $pluginTemplatePath) {
                $templateKey = 'plugin_' . $moduleId;
                $templateVar = 'oPlugin_' . $moduleId;
                if ($this->getTemplateVars($templateVar) === null) {
                    $oPlugin = \Plugin\Helper::getPluginById($moduleId);
                    $this->assign($templateVar, $oPlugin);
                }
                if (\file_exists($this->_realpath($pluginTemplatePath . $resource_cfb_name, true))) {
                    $pluginTemplateExtends[] = \sprintf('[%s]%s', $templateKey, $resource_cfb_name);
                }
            }

            if (\count($pluginTemplateExtends) > 0) {
                $transform         = false;
                $resource_cfb_name = \sprintf(
                    'extends:[frontend]%s|%s',
                    $resource_cfb_name,
                    \implode('|', $pluginTemplateExtends)
                );
            }
        }

        return $transform ? ('file:' . $resource_cfb_name) : $resource_cfb_name;
    }

    /**
     * @param bool $useSubDirs
     * @return $this
     */
    public function setUseSubDirs($useSubDirs): self
    {
        parent::setUseSubDirs($useSubDirs);

        return $this;
    }

    /**
     * @param bool $force
     * @return $this
     */
    public function setForceCompile($force): self
    {
        parent::setForceCompile($force);

        return $this;
    }

    /**
     * @param bool $check
     * @return $this
     */
    public function setCompileCheck($check): self
    {
        parent::setCompileCheck($check);

        return $this;
    }

    /**
     * @param int $reporting
     * @return $this
     */
    public function setErrorReporting($reporting): self
    {
        parent::setErrorReporting($reporting);

        return $this;
    }

    /**
     * @return bool
     */
    public static function getIsChildTemplate(): bool
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
     * @throws \SmartyException
     */
    public function activateBackendSecurityMode(): self
    {
        $sec                = new \Smarty_Security($this);
        $sec->php_handling  = \Smarty::PHP_REMOVE;
        $jtlModifier        = [
            'replace_delim',
            'count_characters',
            'string_format',
            'string_date_format',
            'truncate',
        ];
        $secureFuncs        = $this->getSecurePhpFunctions();
        $sec->php_modifiers = \array_merge(
            $sec->php_modifiers,
            $jtlModifier,
            $secureFuncs
        );
        $sec->php_modifiers = \array_unique($sec->php_modifiers);
        $sec->php_functions = \array_unique(\array_merge($sec->php_functions, $secureFuncs, ['lang']));
        $this->enableSecurity($sec);

        return $this;
    }

    /**
     * Get a list of php functions, that should be save to use in an insecure context.
     *
     * @return string[]
     */
    private function getSecurePhpFunctions(): array
    {
        static $functions;
        if ($functions === null) {
            $functions = \array_map('\trim', \explode(',', \SECURE_PHP_FUNCTIONS));
        }

        return $functions;
    }
}
