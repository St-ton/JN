<?php declare(strict_types=1);

namespace JTL\Smarty;

use JTL\Backend\AdminTemplate;
use JTL\Backend\Notification;
use JTL\DB\DbInterface;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Profiler;
use JTL\Router\Route;
use JTL\Shop;
use scc\DefaultComponentRegistrator;
use sccbs3\Bs3sccRenderer;

/**
 * Class BackendSmarty
 * @package \JTL\Smarty
 */
class BackendSmarty extends JTLSmarty
{
    /**
     * @var string
     */
    protected string $templateDir = 'bootstrap';

    /**
     * @param DbInterface $db
     */
    public function __construct(private DbInterface $db)
    {
        parent::__construct(false, ContextType::BACKEND);
    }

    /**
     * @inheritdoc
     */
    protected function initTemplate(): ?string
    {
        $compileDir = \PFAD_ROOT . \PFAD_ADMIN . \PFAD_COMPILEDIR;
        if (!\is_dir($compileDir) && !\mkdir($compileDir) && !\is_dir($compileDir)) {
            throw new \RuntimeException(\sprintf('Directory "%s" could not be created', $compileDir));
        }
        $this->setCaching(self::CACHING_OFF)
            ->setDebugging(\SMARTY_DEBUG_CONSOLE)
            ->setTemplateDir([$this->context => \PFAD_ROOT . \PFAD_ADMIN . \PFAD_TEMPLATES . $this->templateDir])
            ->setCompileDir($compileDir)
            ->setConfigDir(\PFAD_ROOT . \PFAD_ADMIN . \PFAD_TEMPLATES . $this->templateDir . '/lang/')
            ->setPluginsDir(\SMARTY_PLUGINS_DIR);

        return null;
    }

    /**
     * @inheritdoc
     */
    protected function init(?string $parent = null): void
    {
        $plugins = new BackendPlugins($this->db);
        $scc     = new DefaultComponentRegistrator(new Bs3sccRenderer($this));
        $scc->registerComponents();
        $pluginCollection = new PluginCollection($this->config, LanguageHelper::getInstance());
        $this->registerPlugin(self::PLUGIN_FUNCTION, 'lang', [$pluginCollection, 'translate'])
            ->registerPlugin(self::PLUGIN_MODIFIER, 'replace_delim', [$pluginCollection, 'replaceDelimiters'])
            ->registerPlugin(self::PLUGIN_MODIFIER, 'count_characters', [$pluginCollection, 'countCharacters'])
            ->registerPlugin(self::PLUGIN_MODIFIER, 'string_format', [$pluginCollection, 'stringFormat'])
            ->registerPlugin(self::PLUGIN_MODIFIER, 'string_date_format', [$pluginCollection, 'dateFormat'])
            ->registerPlugin(self::PLUGIN_MODIFIERCOMPILER, 'default', [$pluginCollection, 'compilerModifierDefault'])
            ->registerPlugin(self::PLUGIN_MODIFIER, 'truncate', [$pluginCollection, 'truncate'])
            ->registerPlugin(self::PLUGIN_BLOCK, 'inline_script', [$pluginCollection, 'inlineScript'])
            ->registerPlugin(
                self::PLUGIN_FUNCTION,
                'getCurrencyConversionSmarty',
                [$plugins, 'getCurrencyConversionSmarty']
            )
            ->registerPlugin(
                self::PLUGIN_FUNCTION,
                'getCurrencyConversionTooltipButton',
                [$plugins, 'getCurrencyConversionTooltipButton']
            )
            ->registerPlugin(self::PLUGIN_FUNCTION, 'getCurrentPage', [$plugins, 'getCurrentPage'])
            ->registerPlugin(self::PLUGIN_FUNCTION, 'SmartyConvertDate', [$plugins, 'convertDate'])
            ->registerPlugin(self::PLUGIN_FUNCTION, 'getHelpDesc', [$plugins, 'getHelpDesc'])
            ->registerPlugin(self::PLUGIN_FUNCTION, 'getExtensionCategory', [$plugins, 'getExtensionCategory'])
            ->registerPlugin(self::PLUGIN_FUNCTION, 'formatVersion', [$plugins, 'formatVersion'])
            ->registerPlugin(self::PLUGIN_MODIFIER, 'formatByteSize', [Text::class, 'formatSize'])
            ->registerPlugin(self::PLUGIN_FUNCTION, 'getAvatar', [$plugins, 'getAvatar'])
            ->registerPlugin(self::PLUGIN_FUNCTION, 'getRevisions', [$plugins, 'getRevisions'])
            ->registerPlugin(self::PLUGIN_FUNCTION, 'captchaMarkup', [$plugins, 'captchaMarkup'])
            ->registerPlugin(self::PLUGIN_MODIFIER, 'permission', [$plugins, 'permission']);

        $template           = AdminTemplate::getInstance();
        $shopURL            = Shop::getURL();
        $adminURL           = Shop::getAdminURL();
        $currentTemplateDir = $this->getTemplateUrlPath();
        $availableLanguages = LanguageHelper::getInstance()->gibInstallierteSprachen();
        $resourcePaths      = $template->getResources(false);
        $gettext            = Shop::Container()->getGetText();
        $langTag            = $_SESSION['AdminAccount']->language ?? $gettext->getLanguage();
        $faviconUrl         = $adminURL . (\file_exists(\PFAD_ROOT . \PFAD_ADMIN . 'favicon.ico')
                ? '/favicon.ico'
                : '/favicon-default.ico');
        $this->assignDeprecated('URL_SHOP', $shopURL, '5.2.0')
            ->assignDeprecated('PFAD_ADMIN', PFAD_ADMIN, '5.0.0')
            ->assignDeprecated('JTL_CHARSET', JTL_CHARSET, '5.0.0')
            ->assignDeprecated('session_name', \session_name(), '5.2.0')
            ->assignDeprecated('session_id', \session_id(), '5.2.0')
            ->assignDeprecated('PFAD_CODEMIRROR', $shopURL . '/' . PFAD_CODEMIRROR, '5.2.0')
            ->assignDeprecated('lang', 'german', '5.2.0')
            ->assignDeprecated('Einstellungen', $this->config, '5.2.0')
            ->assign('jtl_token', Form::getTokenInput())
            ->assign('shopURL', $shopURL)
            ->assign('adminURL', $adminURL)
            ->assign('adminTplVersion', $template->version)
            ->assign('currentTemplateDir', $currentTemplateDir)
            ->assign('templateBaseURL', $adminURL . '/' . $currentTemplateDir)
            ->assign('admin_css', $resourcePaths['css'])
            ->assign('admin_js', $resourcePaths['js'])
            ->assign('config', $this->config)
            ->assign('notifications', Notification::getInstance($this->db))
            ->assign('alertList', Shop::Container()->getAlertService())
            ->assign('language', $langTag)
            ->assign('sprachen', $availableLanguages)
            ->assign('availableLanguages', $availableLanguages)
            ->assign('languageName', \Locale::getDisplayLanguage($langTag, $langTag))
            ->assign('languages', $gettext->getAdminLanguages())
            ->assign('faviconAdminURL', $faviconUrl)
            ->assign('cTab', Text::filterXSS(Request::verifyGPDataString('tab')))
            ->assign(
                'wizardDone',
                (($conf['global']['global_wizard_done'] ?? 'Y') === 'Y'
                    || !\str_contains($_SERVER['REQUEST_URI'], Route::WIZARD))
                && !Request::getVar('fromWizard')
            );
    }

    /**
     * @inheritDoc
     */
    public function display($template = null, $cacheID = null, $compileID = null, $parent = null): void
    {
        parent::display($this->getResourceName($template), $cacheID, $compileID, $parent);
        Profiler::finalize();
    }
}
