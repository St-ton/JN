<?php declare(strict_types=1);

namespace JTL\Services;

use JTL\Backend\AdminAccount;
use JTL\Backend\AdminLoginConfig;
use JTL\Boxes\Factory as BoxFactory;
use JTL\Boxes\FactoryInterface as BoxFactoryInterface;
use JTL\Boxes\Renderer\DefaultRenderer;
use JTL\Cache\JTLCache;
use JTL\Cache\JTLCacheInterface;
use JTL\Consent\Manager;
use JTL\Consent\ManagerInterface;
use JTL\Cron\Admin\Controller as CronController;
use JTL\DB\DbInterface;
use JTL\DB\NiceDB;
use JTL\DB\Services\GcService;
use JTL\DB\Services\GcServiceInterface;
use JTL\Debug\JTLDebugBar;
use JTL\Filesystem\AdapterFactory;
use JTL\Filesystem\Filesystem;
use JTL\Filesystem\LocalFilesystem;
use JTL\L10n\GetText;
use JTL\Mail\Hydrator\DefaultsHydrator;
use JTL\Mail\Mailer;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Validator\MailValidator;
use JTL\Mapper\AdminLoginStatusMessageMapper;
use JTL\Mapper\AdminLoginStatusToLogLevel;
use JTL\Network\JTLApi;
use JTL\Nice;
use JTL\OPC\DB;
use JTL\OPC\Locker;
use JTL\OPC\PageDB;
use JTL\OPC\PageService;
use JTL\OPC\Service as OPCService;
use JTL\ProcessingHandler\NiceDBHandler;
use JTL\Services\JTL\AlertService;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Services\JTL\BoxService;
use JTL\Services\JTL\BoxServiceInterface;
use JTL\Services\JTL\CaptchaService;
use JTL\Services\JTL\CaptchaServiceInterface;
use JTL\Services\JTL\CountryService;
use JTL\Services\JTL\CountryServiceInterface;
use JTL\Services\JTL\CryptoService;
use JTL\Services\JTL\CryptoServiceInterface;
use JTL\Services\JTL\LinkService;
use JTL\Services\JTL\LinkServiceInterface;
use JTL\Services\JTL\NewsService;
use JTL\Services\JTL\NewsServiceInterface;
use JTL\Services\JTL\PasswordService;
use JTL\Services\JTL\PasswordServiceInterface;
use JTL\Services\JTL\SimpleCaptchaService;
use JTL\Services\JTL\Validation\RuleSet;
use JTL\Services\JTL\Validation\ValidationService;
use JTL\Services\JTL\Validation\ValidationServiceInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\MailSmarty;
use JTL\Template\TemplateService;
use JTL\Template\TemplateServiceInterface;
use League\Flysystem\Config as FlysystemConfig;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Visibility;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;

/**
 * Class Factory
 * @package JTL\Services
 */
class Factory
{
    /**
     * @return DefaultServicesInterface
     */
    public function createContainers(): DefaultServicesInterface
    {
        $container = new Container();
        $container->singleton(DbInterface::class, static function () {
            return new NiceDB(\DB_HOST, \DB_USER, \DB_PASS, \DB_NAME);
        });
        $container->singleton(JTLCacheInterface::class, JTLCache::class);
        $container->singleton(LinkServiceInterface::class, LinkService::class);
        $container->singleton(AlertServiceInterface::class, AlertService::class);
        $container->singleton(NewsServiceInterface::class, NewsService::class);
        $container->singleton(CryptoServiceInterface::class, CryptoService::class);
        $container->singleton(PasswordServiceInterface::class, PasswordService::class);
        $container->singleton(CountryServiceInterface::class, CountryService::class);
        $container->singleton(JTLDebugBar::class, static function (Container $container) {
            return new JTLDebugBar($container->getDB()->getPDO(), Shopsetting::getInstance()->getAll());
        });
        $container->singleton('BackendAuthLogger', static function (Container $container) {
            $loggingConf = Shop::getSettingValue(\CONF_GLOBAL, 'admin_login_logger_mode');
            $handlers    = [];
            foreach ($loggingConf as $value) {
                if ($value === AdminLoginConfig::CONFIG_DB) {
                    $handlers[] = (new NiceDBHandler($container->getDB(), Logger::INFO))
                        ->setFormatter(new LineFormatter('%message%', null, true, true));
                } elseif ($value === AdminLoginConfig::CONFIG_FILE) {
                    $handlers[] = (new StreamHandler(\PFAD_LOGFILES . 'auth.log', Logger::INFO))
                        ->setFormatter(new LineFormatter(null, null, true, true));
                }
            }

            return new Logger('auth', $handlers, [new PsrLogMessageProcessor()]);
        });
        $container->singleton(LoggerInterface::class, static function (Container $container) {
            $handler = (new NiceDBHandler(
                $container->getDB(),
                (int)Shop::getSettingValue(\CONF_GLOBAL, 'systemlog_flag')
            ))->setFormatter(new LineFormatter('%message%', null, true, true));

            return new Logger('jtllog', [$handler], [new PsrLogMessageProcessor()]);
        });
        $container->alias(LoggerInterface::class, 'Logger');
        $container->singleton(ValidationServiceInterface::class, static function () {
            $vs = new ValidationService($_GET, $_POST, $_COOKIE);
            $vs->setRuleSet('identity', (new RuleSet())->integer()->gt(0));

            return $vs;
        });
        $container->bind(JTLApi::class, static function () {
            // return new JTLApi($_SESSION, $container->make(Nice::class));
            return new JTLApi($_SESSION, Nice::getInstance());
        });
        $container->singleton(GcServiceInterface::class, GcService::class);
        $container->singleton(GetText::class);
        $container->singleton(OPCService::class);
        $container->singleton(PageService::class);
        $container->singleton(DB::class);
        $container->singleton(PageDB::class);
        $container->singleton(Locker::class);
        $container->bind(BoxFactoryInterface::class, static function () {
            return new BoxFactory(Shopsetting::getInstance()->getAll());
        });
        $container->singleton(BoxServiceInterface::class, static function (Container $container) {
            $smarty = Shop::Smarty();

            return new BoxService(
                Shopsetting::getInstance()->getAll(),
                $container->getBoxFactory(),
                $container->getDB(),
                $container->getCache(),
                $smarty,
                new DefaultRenderer($smarty)
            );
        });
        $container->singleton(CaptchaServiceInterface::class, static function () {
            return new CaptchaService(new SimpleCaptchaService(
                !(Frontend::get('bAnti_spam_already_checked', false) || Frontend::getCustomer()->isLoggedIn())
            ));
        });
        $container->singleton(AdminAccount::class, static function (Container $container) {
            return new AdminAccount(
                $container->getDB(),
                $container->getBackendLogService(),
                new AdminLoginStatusMessageMapper(),
                new AdminLoginStatusToLogLevel(),
                $container->getGetText(),
                $container->getAlertService()
            );
        });
        $container->singleton(Filesystem::class, static function () {
            $factory = new AdapterFactory(Shop::getSettingSection(\CONF_FS));

            return new Filesystem(
                $factory->getAdapter(),
                [FlysystemConfig::OPTION_DIRECTORY_VISIBILITY => Visibility::PUBLIC]
            );
        });
        $container->singleton(LocalFilesystem::class, static function () {
            return new Filesystem(
                new LocalFilesystemAdapter(\PFAD_ROOT),
                [FlysystemConfig::OPTION_DIRECTORY_VISIBILITY => Visibility::PUBLIC]
            );
        });
        $container->bind(Mailer::class, static function (Container $container) {
            $db        = $container->getDB();
            $settings  = Shopsetting::getInstance();
            $smarty    = new SmartyRenderer(new MailSmarty($db));
            $hydrator  = new DefaultsHydrator($smarty->getSmarty(), $db, $settings);
            $validator = new MailValidator($db, $settings->getAll());

            return new Mailer($hydrator, $smarty, $settings, $validator);
        });
        $container->singleton(ManagerInterface::class, static function (Container $container) {
            return new Manager($container->getDB(), $container->getCache());
        });
        $container->singleton(TemplateServiceInterface::class, static function (Container $container) {
            return new TemplateService($container->getDB(), $container->getCache());
        });
        $container->bind(CronController::class);

        return $container;
    }
}
