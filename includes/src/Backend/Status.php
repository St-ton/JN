<?php declare(strict_types=1);

namespace JTL\Backend;

use Exception;
use JTL\Cache\JTLCacheInterface;
use JTL\Checkout\ZahlungsLog;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\License\Manager;
use JTL\License\Mapper;
use JTL\License\Struct\ExsLicense;
use JTL\Media\Image\Product;
use JTL\Media\Image\StatsItem;
use JTL\Plugin\Helper;
use JTL\Plugin\State;
use JTL\Profiler;
use JTL\Shop;
use JTL\Update\Updater;
use stdClass;
use Systemcheck\Environment;
use Systemcheck\Platform\Filesystem;
use Systemcheck\Platform\Hosting;
use function Functional\some;

/**
 * Class Status
 * @package JTL\Backend
 */
class Status
{
    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @var DbInterface
     */
    protected $db;


    private static $instance;

    /**
     * Status constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db       = $db;
        self::$instance = $this;
    }

    /**
     * @param DbInterface $db
     * @return Status
     */
    public static function getInstance(DbInterface $db): self
    {
        return static::$instance ?? new self($db);
    }

    /**
     * @param string $name
     * @param mixed  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (!isset($this->cache[$name])) {
            $this->cache[$name] = \call_user_func_array([&$this, $name], $arguments);
        }

        return $this->cache[$name];
    }

    /**
     * @return JTLCacheInterface
     */
    public function getObjectCache(): JTLCacheInterface
    {
        return Shop::Container()->getCache()->setJtlCacheConfig(
            $this->db->selectAll('teinstellungen', 'kEinstellungenSektion', \CONF_CACHING)
        );
    }

    /**
     * @return StatsItem
     * @throws Exception
     */
    public function getImageCache(): StatsItem
    {
        return (new Product($this->db))->getStats();
    }

    /**
     * @return stdClass
     */
    public function getSystemLogInfo(): stdClass
    {
        $conf = Shop::getConfigValue(\CONF_GLOBAL, 'systemlog_flag');

        return (object)[
            'error'  => $conf >= \JTLLOG_LEVEL_ERROR,
            'notice' => $conf >= \JTLLOG_LEVEL_NOTICE,
            'debug'  => $conf >= \JTLLOG_LEVEL_NOTICE
        ];
    }

    /**
     * checks the db-structure against 'admin/includes/shopmd5files/dbstruct_[shop-version].json'
     *
     * @return bool  true='no errors', false='something is wrong'
     */
    public function validDatabaseStruct(): bool
    {
        require_once \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . 'dbcheck_inc.php';

        $current  = \getDBStruct(true);
        $original = \getDBFileStruct();

        return \is_array($current) && \is_array($original) && \count(\compareDBStruct($original, $current)) === 0;
    }

    /**
     * checks the shop-filesystem-structure against 'admin/includes/shopmd5files/[shop-version].csv'
     *
     * @return bool  true='no errors', false='something is wrong'
     */
    public function validModifiedFileStruct(): bool
    {
        $check   = new FileCheck();
        $files   = [];
        $stats   = 0;
        $md5file = \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_SHOPMD5 . $check->getVersionString() . '.csv';

        return $check->validateCsvFile($md5file, $files, $stats) === FileCheck::OK
            ? $stats === 0
            : false;
    }

    /**
     * checks the shop-filesystem-structure against 'admin/includes/shopmd5files/deleted_files_[shop-version].csv'
     *
     * @return bool  true='no errors', false='something is wrong'
     */
    public function validOrphanedFilesStruct(): bool
    {
        $check             = new FileCheck();
        $files             = [];
        $stats             = 0;
        $orphanedFilesFile = \PFAD_ROOT . \PFAD_ADMIN .
            \PFAD_INCLUDES . \PFAD_SHOPMD5
            . 'deleted_files_' . $check->getVersionString() . '.csv';

        return $check->validateCsvFile($orphanedFilesFile, $files, $stats) === FileCheck::OK
            ? $stats === 0
            : false;
    }

    /**
     * @return bool
     */
    public function validFolderPermissions(): bool
    {
        $permissionStat = (new Filesystem(\PFAD_ROOT))->getFolderStats();

        return $permissionStat->nCountInValid === 0;
    }

    /**
     * @return array
     */
    public function getPluginSharedHooks(): array
    {
        $sharedPlugins = [];
        $sharedHookIds = $this->db->query(
            'SELECT nHook
                FROM tpluginhook
                GROUP BY nHook
                HAVING COUNT(DISTINCT kPlugin) > 1',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($sharedHookIds as $hookData) {
            $hookID                 = (int)$hookData->nHook;
            $sharedPlugins[$hookID] = [];
            $plugins                = $this->db->queryPrepared(
                'SELECT DISTINCT tpluginhook.kPlugin, tplugin.cName, tplugin.cPluginID
                    FROM tpluginhook
                    INNER JOIN tplugin
                        ON tpluginhook.kPlugin = tplugin.kPlugin
                    WHERE tpluginhook.nHook = :hook
                        AND tplugin.nStatus = :state',
                [
                    'hook'  => $hookID,
                    'state' => State::ACTIVATED
                ],
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($plugins as $plugin) {
                $sharedPlugins[$hookID][$plugin->cPluginID] = $plugin;
            }
        }

        return $sharedPlugins;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function hasPendingUpdates(): bool
    {
        return (new Updater($this->db))->hasPendingUpdates();
    }

    /**
     * @return bool
     */
    public function hasActiveProfiler(): bool
    {
        return Profiler::getIsActive() !== 0;
    }

    /**
     * @return bool
     */
    public function hasInstallDir(): bool
    {
        return \is_dir(\PFAD_ROOT . \PFAD_INSTALL);
    }

    /**
     * @return bool
     */
    public function hasDifferentTemplateVersion(): bool
    {
        try {
            $template = Shop::Container()->getTemplateService()->getActiveTemplate();
        } catch (Exception $e) {
            return false;
        }
        return $template->getVersion() !== \APPLICATION_VERSION;
    }

    /**
     * @return bool
     */
    public function hasMobileTemplateIssue(): bool
    {
        try {
            $template = Shop::Container()->getTemplateService()->getActiveTemplate();
        } catch (Exception $e) {
            return false;
        }
        if ($template->isResponsive()) {
            $mobileTpl = $this->db->select('ttemplate', 'eTyp', 'mobil');
            if ($mobileTpl !== null) {
                $xmlFile = \PFAD_ROOT . \PFAD_TEMPLATES . $mobileTpl->cTemplate .
                    \DIRECTORY_SEPARATOR . \TEMPLATE_XML;
                if (\file_exists($xmlFile)) {
                    return true;
                }
                // Wenn ein Template aktiviert aber physisch nicht vorhanden ist,
                // ist der DB-Eintrag falsch und wird gelöscht
                $this->db->delete('ttemplate', 'eTyp', 'mobil');
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasStandardTemplateIssue(): bool
    {
        return $this->db->select('ttemplate', 'eTyp', 'standard') === null
            || Shop::Container()->getTemplateService()->getActiveTemplate()->getTemplate() === null;
    }

    /**
     * @return bool
     */
    public function hasValidEnvironment(): bool
    {
        $systemcheck = new Environment();
        $systemcheck->executeTestGroup('Shop5');

        return $systemcheck->getIsPassed();
    }

    /**
     * @return array
     */
    public function getEnvironmentTests(): array
    {
        return (new Environment())->executeTestGroup('Shop5');
    }

    /**
     * @return Hosting
     */
    public function getPlatform(): Hosting
    {
        return new Hosting();
    }

    /**
     * @return array
     */
    public function getMySQLStats(): array
    {
        $stats = $this->db->stats();
        $info  = $this->db->info();
        $lines = \explode('  ', $stats);
        $lines = \array_map(static function ($v) {
            [$key, $value] = \explode(':', $v, 2);

            return ['key' => \trim($key), 'value' => \trim($value)];
        }, $lines);

        return \array_merge([['key' => 'Version', 'value' => $info]], $lines);
    }

    /**
     * @return array
     */
    public function getPaymentMethodsWithError(): array
    {
        $incorrectPaymentMethods = [];
        $paymentMethods          = $this->db->selectAll(
            'tzahlungsart',
            'nActive',
            1,
            '*',
            'cAnbieter, cName, nSort, kZahlungsart'
        );
        foreach ($paymentMethods as $method) {
            if (($logCount = ZahlungsLog::count($method->cModulId, \JTLLOG_LEVEL_ERROR)) > 0) {
                $method->logCount          = $logCount;
                $incorrectPaymentMethods[] = $method;
            }
        }

        return $incorrectPaymentMethods;
    }

    /**
     * @param bool $has
     * @return array|bool
     */
    public function getOrphanedCategories(bool $has = true)
    {
        $categories = $this->db->query(
            'SELECT kKategorie, cName
                FROM tkategorie
                WHERE kOberkategorie > 0
                    AND kOberkategorie NOT IN (SELECT DISTINCT kKategorie FROM tkategorie)',
            ReturnType::ARRAY_OF_OBJECTS
        );

        return $has === true
            ? \count($categories) === 0
            : $categories;
    }

    /**
     * @return bool
     */
    public function hasFullTextIndexError(): bool
    {
        $conf = Shop::getSettings([\CONF_ARTIKELUEBERSICHT])['artikeluebersicht'];

        return isset($conf['suche_fulltext'])
            && $conf['suche_fulltext'] !== 'N'
            && (!$this->db->query(
                "SHOW INDEX
                    FROM tartikel
                    WHERE KEY_NAME = 'idx_tartikel_fulltext'",
                ReturnType::SINGLE_OBJECT
            )
                || !$this->db->query(
                    "SHOW INDEX
                    FROM tartikelsprache
                    WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'",
                    ReturnType::SINGLE_OBJECT
                ));
    }

    /**
     * @return bool
     */
    public function hasLicenseExpirations(): bool
    {
        $manager    = new Manager($this->db, Shop::Container()->getCache());
        $mapper     = new Mapper($manager);
        $collection = $mapper->getCollection();

        return $collection->contains(static function (ExsLicense $item) {
            $license = $item->getLicense();
            if ($license->getValidUntil() !== null && $license->getDaysRemaining() < 28) {
                return true;
            }
            $subscription = $license->getSubscription();

            return $subscription->getValidUntil() !== null && $subscription->getDaysRemaining() < 28;
        });
    }

    /**
     * @return bool
     */
    public function hasNewPluginVersions(): bool
    {
        if (\SAFE_MODE === true) {
            return false;
        }

        $data = $this->db->query(
            'SELECT `kPlugin`, `nVersion`, `bExtension`
                FROM `tplugin`',
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($data) || 1 > \count($data)) {
            return false; // there are no plugins installed
        }

        foreach ($data as $item) {
            try {
                $plugin = Helper::getLoader((int)$item->bExtension === 1)->init((int)$item->kPlugin);
            } catch (Exception $e) {
                continue;
            }
            if ($plugin->getCurrentVersion()->greaterThan($item->nVersion)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks, whether the password reset mail template contains the old variable $neues_passwort.
     *
     * @return bool
     */
    public function hasInvalidPasswordResetMailTemplate(): bool
    {
        $translations = $this->db->query(
            "SELECT lang.cContentText, lang.cContentHtml
                FROM temailvorlagesprache lang
                JOIN temailvorlage
                ON lang.kEmailvorlage = temailvorlage.kEmailvorlage
                WHERE temailvorlage.cName = 'Passwort vergessen'",
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($translations as $t) {
            $old = '{$neues_passwort}';
            if (\mb_strpos($t->cContentHtml, $old) !== false || \mb_strpos($t->cContentText, $old) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks, whether SMTP is configured for sending mails but no encryption method is chosen for E-Mail-Server
     * communication
     *
     * @return bool
     */
    public function hasInsecureMailConfig(): bool
    {
        $conf = Shop::getConfig([\CONF_EMAILS])['emails'];

        return $conf['email_methode'] === 'smtp' && empty(\trim($conf['email_smtp_verschluesselung']));
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function needPasswordRehash2FA(): bool
    {
        $passwordService = Shop::Container()->getPasswordService();
        $hashes          = $this->db->query(
            'SELECT *
                FROM tadmin2facodes
                GROUP BY kAdminlogin',
            ReturnType::ARRAY_OF_OBJECTS
        );

        return some($hashes, static function ($hash) use ($passwordService) {
            return $passwordService->needsRehash($hash->cEmergencyCode);
        });
    }

    /**
     * @return array
     */
    public function getDuplicateLinkGroupTemplateNames(): array
    {
        return $this->db->query(
            'SELECT * FROM tlinkgruppe
                GROUP BY cTemplatename
                HAVING COUNT(*) > 1',
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @return int
     */
    public function getExportFormatErrorCount(): int
    {
        if (!isset($_SESSION['exportSyntaxErrorCount'])) {
            $_SESSION['exportSyntaxErrorCount'] = (int)$this->db->query(
                'SELECT COUNT(*) AS cnt FROM texportformat WHERE nFehlerhaft = 1',
                ReturnType::SINGLE_OBJECT
            )->cnt;
        }

        return $_SESSION['exportSyntaxErrorCount'];
    }

    /**
     * @return int
     */
    public function getEmailTemplateSyntaxErrorCount(): int
    {
        if (!isset($_SESSION['emailSyntaxErrorCount'])) {
            $_SESSION['emailSyntaxErrorCount'] = (int)$this->db->query(
                'SELECT COUNT(*) AS cnt FROM temailvorlage WHERE nFehlerhaft = 1',
                ReturnType::SINGLE_OBJECT
            )->cnt;
        }

        return $_SESSION['emailSyntaxErrorCount'];
    }

    /**
     * @return bool
     */
    public function hasExtensionSOAP(): bool
    {
        return \extension_loaded('soap');
    }
}
