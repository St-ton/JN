<?php declare(strict_types=1);

namespace JTL\Smarty;

use JSMin\JSMin;
use JSMin\UnterminatedStringException;
use JTL\Events\Dispatcher;
use JTL\Helpers\GeneralObject;
use JTL\Language\LanguageHelper;
use JTL\phpQuery\phpQuery;
use JTL\Plugin\Helper;
use JTL\Profiler;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Template\BootChecker;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Class JTLSmarty
 * @package \JTL\Smarty
 */
class JTLSmarty extends BC
{
    /**
     * @var array
     */
    public array $config;

    /**
     * @var array
     */
    public $_cache_include_info;

    /**
     * @var JTLSmarty[]
     */
    private static array $instance = [];

    /**
     * @var bool
     */
    public static bool $isChildTemplate = false;

    /**
     * @var string
     */
    protected string $templateDir;

    /**
     * @var array
     */
    private array $mapping = [];

    /**
     * modified constructor with custom initialisation
     *
     * @param bool   $fast - set to true when init from backend to avoid setting session data
     * @param string $context
     */
    public function __construct(bool $fast = false, public string $context = ContextType::FRONTEND)
    {
        parent::__construct();
        self::$_CHARSET = \JTL_CHARSET;
        $this->setErrorReporting(\SMARTY_LOG_LEVEL)
            ->setForceCompile(\SMARTY_FORCE_COMPILE)
            ->setDebugging(\SMARTY_DEBUG_CONSOLE)
            ->setUseSubDirs(\SMARTY_USE_SUB_DIRS);
        $this->config = Shopsetting::getInstance()->getAll();
        $parent       = $this->initTemplate();
        if ($fast === false) {
            $this->init($parent);
        }
        self::$instance[$context] = $this;
        if ($fast === false && $context !== ContextType::BACKEND) {
            \executeHook(\HOOK_SMARTY_INC, ['smarty' => $this]);
        }
    }

    /**
     * @return string|null
     */
    protected function initTemplate(): ?string
    {
        $model = Shop::Container()->getTemplateService()->getActiveTemplate();
        if ($model->getTemplate() === null) {
            throw new RuntimeException('Cannot load template ' . ($model->getName() ?? ''));
        }
        $paths      = $model->getPaths();
        $tplDir     = $model->getDir();
        $parent     = $model->getParent();
        $compileDir = $paths->getCompileDir();
        if (!\is_dir($compileDir) && !\mkdir($compileDir) && !\is_dir($compileDir)) {
            throw new RuntimeException(\sprintf('Directory "%s" could not be created', $compileDir));
        }
        $this->template_dir = [];
        $this->setCompileDir($compileDir)
            ->setCacheDir($paths->getCacheDir())
            ->setPluginsDir(\SMARTY_PLUGINS_DIR)
            ->assign('tplDir', $paths->getBaseDir())
            ->assign('parentTemplateDir', null);
        if ($parent !== null) {
            self::$isChildTemplate = true;
            $this->assign('tplDir', $paths->getParentDir())
                ->assign('parent_template_path', $paths->getParentDir())
                ->assign('parentTemplateDir', $paths->getParentRelDir())
                ->addTemplateDir($paths->getParentDir(), $parent);
        }
        $this->addTemplateDir($paths->getBaseDir(), $this->context);
        foreach (Helper::getTemplatePaths() as $moduleId => $path) {
            $templateKey = 'plugin_' . $moduleId;
            $this->addTemplateDir($path, $templateKey);
        }
        if (($bootstrapper = BootChecker::bootstrap($tplDir) ?? BootChecker::bootstrap($parent)) !== null) {
            $bootstrapper->setSmarty($this);
            $bootstrapper->setTemplate($model);
            $bootstrapper->boot();
        }
        $this->templateDir = $tplDir;

        return $parent;
    }

    /**
     * @param string|null $parent
     * @throws \SmartyException
     */
    protected function init(?string $parent = null): void
    {
        $pluginCollection = new PluginCollection($this->config, LanguageHelper::getInstance());
        $this->registerPlugin(self::PLUGIN_FUNCTION, 'lang', [$pluginCollection, 'translate'])
            ->registerPlugin(self::PLUGIN_MODIFIER, 'replace_delim', [$pluginCollection, 'replaceDelimiters'])
            ->registerPlugin(self::PLUGIN_MODIFIER, 'count_characters', [$pluginCollection, 'countCharacters'])
            ->registerPlugin(self::PLUGIN_MODIFIER, 'string_format', [$pluginCollection, 'stringFormat'])
            ->registerPlugin(self::PLUGIN_MODIFIER, 'string_date_format', [$pluginCollection, 'dateFormat'])
            ->registerPlugin(self::PLUGIN_MODIFIERCOMPILER, 'default', [$pluginCollection, 'compilerModifierDefault'])
            ->registerPlugin(self::PLUGIN_MODIFIER, 'truncate', [$pluginCollection, 'truncate'])
            ->registerPlugin(self::PLUGIN_BLOCK, 'inline_script', [$pluginCollection, 'inlineScript']);

        $this->cache_lifetime = 86400;
        $this->template_class = \SHOW_TEMPLATE_HINTS > 0
            ? JTLSmartyTemplateHints::class
            : JTLSmartyTemplateClass::class;
        $this->setCachingParams($this->config);
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
        $config = $config ?? Shop::getSettings([\CONF_CACHING]);

        return $this->setCaching(self::CACHING_OFF)
            ->setCompileCheck((int)(($config['caching']['compile_check'] ?? 'Y') === 'Y'));
    }

    /**
     * @param bool   $fast
     * @param string $context
     * @return JTLSmarty
     */
    public static function getInstance(bool $fast = false, string $context = ContextType::FRONTEND): self
    {
        return self::$instance[$context] ?? new self($fast, $context);
    }

    /**
     * @return string
     */
    public function getTemplateUrlPath(): string
    {
        return \PFAD_TEMPLATES . $this->templateDir . '/';
    }

    /**
     * phpquery output filter
     *
     * @param string $tplOutput
     * @return string
     */
    public function outputFilter(string $tplOutput): string
    {
        $hookList = Helper::getHookList();
        if (GeneralObject::hasCount(\HOOK_SMARTY_OUTPUTFILTER, $hookList)
            || \count(Dispatcher::getInstance()->getListeners('shop.hook.' . \HOOK_SMARTY_OUTPUTFILTER)) > 0
        ) {
            $this->unregisterFilter('output', [$this, 'outputFilter']);
            $doc = phpQuery::newDocumentHTML($tplOutput, \JTL_CHARSET);
            \executeHook(\HOOK_SMARTY_OUTPUTFILTER, ['smarty' => $this, 'document' => $doc]);
            $tplOutput = $doc->htmlOuter();
        }

        return ($this->config['template']['general']['minify_html'] ?? 'N') === 'Y'
            ? $this->minifyHTML(
                $tplOutput,
                ($this->config['template']['general']['minify_html_css'] ?? 'N') === 'Y',
                ($this->config['template']['general']['minify_html_js'] ?? 'N') === 'Y'
            )
            : $tplOutput;
    }

    /**
     * @inheritDoc
     */
    public function isCached($template = null, $cacheID = null, $compileID = null, $parent = null): bool
    {
        return false;
    }

    /**
     * @param int $mode
     * @return $this
     */
    public function setCaching($mode): self
    {
        $this->caching = (int)$mode;

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
            $options['cssMinifier'] = [\Minify_CSSmin::class, 'minify'];
        }
        if ($minifyJS === true) {
            $options['jsMinifier'] = [JSMin::class, 'minify'];
        }
        try {
            $res = (new \Minify_HTML($html, $options))->process();
        } catch (UnterminatedStringException) {
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
        $file   = \basename($filename, '.tpl');
        $dir    = \dirname($filename);
        $custom = !\str_contains($dir, \PFAD_ROOT)
            ? $this->getTemplateDir($this->context) . (($dir === '.')
                ? ''
                : ($dir . '/')) . $file . '_custom.tpl'
            : ($dir . '/' . $file . '_custom.tpl');

        return \file_exists($custom) ? $custom : $filename;
    }

    /**
     * fetches a rendered Smarty template
     *
     * @param string|null $template the resource handle of the template file or template object
     * @param mixed|null  $cacheID cache id to be used with this template
     * @param mixed|null  $compileID compile id to be used with this template
     * @param object|null $parent next higher level of Smarty variables
     * @return string rendered template output
     * @throws \SmartyException
     * @throws \Exception
     */
    public function fetch($template = null, $cacheID = null, $compileID = null, $parent = null): string
    {
        $debug = $this->_debug->template_data ?? null;
        $res   = parent::fetch($this->getResourceName($template), $cacheID, $compileID, $parent);
        if ($debug !== null) {
            // fetch overwrites the old debug data so we have to merge it with our previously saved data
            $this->_debug->template_data = \array_merge($debug, $this->_debug->template_data);
        }

        return $res;
    }

    /**
     * @inheritDoc
     */
    public function display($template = null, $cacheID = null, $compileID = null, $parent = null): void
    {
        if ($this->context === ContextType::FRONTEND) {
            $this->registerFilter('output', [$this, 'outputFilter']);
        }
        parent::display($this->getResourceName($template), $cacheID, $compileID, $parent);
    }

    /**
     * @param string $template
     * @return ResponseInterface
     */
    public function getResponse(string $template): ResponseInterface
    {
        if ($this->context === ContextType::FRONTEND) {
            $this->registerFilter('output', [$this, 'outputFilter']);
            $template = $this->createTemplate($this->getResourceName($template), null, null, $this, false);
            /** @var JTLSmartyTemplateClass $template */
            $template->noOutputFilter = false;
        }

        $res = parent::fetch($template);
        Profiler::finalize();
        $response = new Response();
        $response->getBody()->write($res);

        return $response;
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
        if (\str_starts_with($resourceName, 'string:') || \str_contains($resourceName, '[')) {
            return $resourceName;
        }
        if (\str_starts_with($resourceName, 'file:')) {
            $resourceName = \str_replace('file:', '', $resourceName);
            $transform    = true;
        }
        $mapped = $this->mapping[$resourceName] ?? null;
        if ($mapped !== null) {
            return $mapped;
        }
        $resource_custom_name = $this->getCustomFile($resourceName);
        $resource_cfb_name    = $resource_custom_name;

        if ($this->context === ContextType::FRONTEND) {
            \executeHook(\HOOK_SMARTY_FETCH_TEMPLATE, [
                'original'  => &$resourceName,
                'custom'    => &$resource_custom_name,
                'fallback'  => &$resource_custom_name,
                'out'       => &$resource_cfb_name,
                'transform' => $transform
            ]);
            if ($resourceName === $resource_cfb_name) {
                $extends = [];
                foreach ($this->getTemplateDir() as $module => $templateDir) {
                    if (\str_starts_with($module, 'plugin_')) {
                        $pluginID    = \mb_substr($module, 7);
                        $templateVar = 'oPlugin_' . $pluginID;
                        if ($this->getTemplateVars($templateVar) === null) {
                            $plugin = Helper::getPluginById($pluginID);
                            $this->assign($templateVar, $plugin);
                        }
                    }
                    if (\file_exists($templateDir . $resource_cfb_name)) {
                        $extends[] = \sprintf('[%s]%s', $module, $resource_cfb_name);
                    }
                }
                if (\count($extends) > 1) {
                    $transform         = false;
                    $resource_cfb_name = \sprintf(
                        'extends:%s',
                        \implode('|', $extends)
                    );
                }
            }
        }
        $res                          = $transform ? ('file:' . $resource_cfb_name) : $resource_cfb_name;
        $this->mapping[$resourceName] = $res;

        return $res;
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
     * @param int $check
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

    /**
     * @param string $name
     * @param        $value
     * @param string $version
     * @return JTLSmarty
     */
    public function assignDeprecated(string $name, $value, string $version): self
    {
        $this->tpl_vars[$name] = new DeprecatedVariable($value, $name, $version);

        return $this;
    }
}
