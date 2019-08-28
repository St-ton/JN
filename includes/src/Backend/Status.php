<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Backend;

use JTL\Cache\JTLCacheInterface;
use JTL\Checkout\ZahlungsLog;
use JTL\DB\ReturnType;
use JTL\Helpers\Template as TemplateHelper;
use JTL\Media\Image;
use JTL\Media\MediaImage;
use JTL\Plugin\State;
use JTL\Profiler;
use JTL\Shop;
use JTL\SingletonTrait;
use JTL\Template;
use JTL\Update\Updater;
use stdClass;
use function Functional\some;

/**
 * Class Status
 * @package JTL\Backend
 */
class Status
{
    use SingletonTrait;

    /**
     * @var array
     */
    protected $cache = [];

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
    protected function getObjectCache(): JTLCacheInterface
    {
        return Shop::Container()->getCache()->setJtlCacheConfig(
            Shop::Container()->getDB()->selectAll('teinstellungen', 'kEinstellungenSektion', \CONF_CACHING)
        );
    }

    /**
     * @return stdClass
     * @throws \Exception
     */
    protected function getImageCache(): stdClass
    {
        return MediaImage::getStats(Image::TYPE_PRODUCT);
    }

    /**
     * @return stdClass
     */
    protected function getSystemLogInfo(): stdClass
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
    protected function validDatabaseStruct(): bool
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
    protected function validModifiedFileStruct(): bool
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
    protected function validOrphanedFilesStruct(): bool
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
    protected function validFolderPermissions(): bool
    {
        $permissionStat = (new \Systemcheck_Platform_Filesystem(\PFAD_ROOT))->getFolderStats();

        return $permissionStat->nCountInValid === 0;
    }

    /**
     * @return array
     */
    protected function getPluginSharedHooks(): array
    {
        $sharedPlugins = [];
        $sharedHookIds = Shop::Container()->getDB()->query(
            'SELECT nHook
                FROM tpluginhook
                GROUP BY nHook
                HAVING COUNT(DISTINCT kPlugin) > 1',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($sharedHookIds as $hookData) {
            $hookID                 = (int)$hookData->nHook;
            $sharedPlugins[$hookID] = [];
            $plugins                = Shop::Container()->getDB()->queryPrepared(
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
     * @throws \Exception
     */
    protected function hasPendingUpdates(): bool
    {
        return (new Updater(Shop::Container()->getDB()))->hasPendingUpdates();
    }

    /**
     * @return bool
     */
    protected function hasActiveProfiler(): bool
    {
        return Profiler::getIsActive() !== 0;
    }

    /**
     * @return bool
     */
    protected function hasInstallDir(): bool
    {
        return \is_dir(\PFAD_ROOT . \PFAD_INSTALL);
    }

    /**
     * @return bool
     */
    protected function hasDifferentTemplateVersion(): bool
    {
        return Template::getInstance()->getVersion() !== \APPLICATION_VERSION;
    }

    /**
     * @return bool
     */
    protected function hasMobileTemplateIssue(): bool
    {
        $template = Shop::Container()->getDB()->select('ttemplate', 'eTyp', 'standard');
        if ($template !== null && isset($template->cTemplate)) {
            $tplData = TemplateHelper::getInstance()->getData($template->cTemplate);
            if ($tplData->bResponsive) {
                $mobileTpl = Shop::Container()->getDB()->select('ttemplate', 'eTyp', 'mobil');
                if ($mobileTpl !== null) {
                    $xmlFile = \PFAD_ROOT . \PFAD_TEMPLATES . $mobileTpl->cTemplate .
                        \DIRECTORY_SEPARATOR . \TEMPLATE_XML;
                    if (\file_exists($xmlFile)) {
                        return true;
                    }
                    // Wenn ein Template aktiviert aber physisch nicht vorhanden ist,
                    // ist der DB-Eintrag falsch und wird gelöscht
                    Shop::Container()->getDB()->delete('ttemplate', 'eTyp', 'mobil');
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function hasStandardTemplateIssue(): bool
    {
        return Shop::Container()->getDB()->select('ttemplate', 'eTyp', 'standard') === null;
    }

    /**
     * @return bool
     */
    protected function hasValidEnvironment(): bool
    {
        $systemcheck = new \Systemcheck_Environment();
        $systemcheck->executeTestGroup('Shop4');

        return $systemcheck->getIsPassed();
    }

    /**
     * @return array
     */
    protected function getEnvironmentTests(): array
    {
        return (new \Systemcheck_Environment())->executeTestGroup('Shop4');
    }

    /**
     * @return \Systemcheck_Platform_Hosting
     */
    protected function getPlatform(): \Systemcheck_Platform_Hosting
    {
        return new \Systemcheck_Platform_Hosting();
    }

    /**
     * @return array
     */
    protected function getMySQLStats(): array
    {
        $stats = Shop::Container()->getDB()->stats();
        $info  = Shop::Container()->getDB()->info();
        $lines = \explode('  ', $stats);
        $lines = \array_map(function ($v) {
            [$key, $value] = \explode(':', $v, 2);

            return ['key' => \trim($key), 'value' => \trim($value)];
        }, $lines);

        return \array_merge([['key' => 'Version', 'value' => $info]], $lines);
    }

    /**
     * @return array
     */
    protected function getPaymentMethodsWithError(): array
    {
        $incorrectPaymentMethods = [];
        $paymentMethods          = Shop::Container()->getDB()->selectAll(
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
     * @return bool
     */
    protected function hasInvalidPollCoupons(): bool
    {
        $pollCoupons         = Shop::Container()->getDB()->selectAll('tumfrage', 'nAktiv', 1);
        $invalidCouponsFound = false;
        foreach ($pollCoupons as $coupon) {
            if ($coupon->kKupon > 0) {
                $couponID = Shop::Container()->getDB()->select(
                    'tkupon',
                    'kKupon',
                    $coupon->kKupon,
                    'cAktiv',
                    'Y',
                    null,
                    null,
                    false,
                    'kKupon'
                );

                $invalidCouponsFound = empty($couponID);
            }
        }

        return $invalidCouponsFound;
    }

    /**
     * @param bool $has
     * @return array|bool
     */
    protected function getOrphanedCategories(bool $has = true)
    {
        $categories = Shop::Container()->getDB()->query(
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
    protected function hasFullTextIndexError(): bool
    {
        $conf = Shop::getSettings([\CONF_ARTIKELUEBERSICHT])['artikeluebersicht'];

        return isset($conf['suche_fulltext'])
            && $conf['suche_fulltext'] !== 'N'
            && (!Shop::Container()->getDB()->query(
                "SHOW INDEX 
                    FROM tartikel 
                    WHERE KEY_NAME = 'idx_tartikel_fulltext'",
                ReturnType::SINGLE_OBJECT
            )
                || !Shop::Container()->getDB()->query(
                    "SHOW INDEX 
                    FROM tartikelsprache 
                    WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'",
                    ReturnType::SINGLE_OBJECT
                ));
    }

    /**
     * @return bool
     */
    protected function hasNewPluginVersions(): bool
    {
        $newVersions = false;
        $data        = Shop::Container()->getDB()->query(
            'SELECT `cVerzeichnis`, `nVersion` 
                FROM `tplugin`',
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($data) || 1 > \count($data)) {
            return false; // there are no plugins installed
        }
        $pluginsDB = [];
        foreach ($data as $item) {
            $pluginsDB[$item->cVerzeichnis] = $item->nVersion;
        }
        // check against plugins, found in file-system
        foreach ($pluginsDB as $dir => $version) {
            $info = \PFAD_ROOT . \PFAD_PLUGIN . $dir . '/' . \PLUGIN_INFO_FILE;
            $xml  = null;
            if (\file_exists($info)) {
                $xml = \simplexml_load_file($info);
                // read all pluginversions from 'info.xml'
                $pluginXmlVersions = [0];
                foreach ($xml->Install->Version as $item) {
                    $pluginXmlVersions[] = (int)$item['nr'];
                }
                // check for the highest and set marker, if it's different from installed db-version
                if (\max($pluginXmlVersions) !== (int)$version) {
                    $newVersions = true;
                }
            }
        }

        return $newVersions;
    }

    /**
     * Checks, whether the password reset mail template contains the old variable $neues_passwort.
     *
     * @return bool
     */
    public function hasInvalidPasswordResetMailTemplate(): bool
    {
        $translations = Shop::Container()->getDB()->query(
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
     * @throws \Exception
     */
    protected function needPasswordRehash2FA(): bool
    {
        $passwordService = Shop::Container()->getPasswordService();
        $hashes          = Shop::Container()->getDB()->query(
            'SELECT *
                FROM tadmin2facodes
                GROUP BY kAdminlogin',
            ReturnType::ARRAY_OF_OBJECTS
        );

        return some($hashes, function ($hash) use ($passwordService) {
            return $passwordService->needsRehash($hash->cEmergencyCode);
        });
    }

    /**
     * @return array
     */
    public function getDuplicateLinkGroupTemplateNames(): array
    {
        return Shop::Container()->getDB()->query(
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
            $_SESSION['exportSyntaxErrorCount'] = (int)Shop::Container()->getDB()->query(
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
            $_SESSION['emailSyntaxErrorCount'] = (int)Shop::Container()->getDB()->query(
                'SELECT COUNT(*) AS cnt FROM temailvorlage WHERE nFehlerhaft = 1',
                ReturnType::SINGLE_OBJECT
            )->cnt;
        }

        return $_SESSION['emailSyntaxErrorCount'];
    }
}
