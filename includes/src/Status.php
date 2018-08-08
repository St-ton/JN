<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use function Functional\some;

/**
 * Class Status
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
            $this->cache[$name] = call_user_func_array([&$this, $name], $arguments);
        }

        return $this->cache[$name];
    }

    /**
     * @return \Cache\JTLCacheInterface
     */
    protected function getObjectCache()
    {
        return Shop::Container()->getCache()->setJtlCacheConfig();
    }

    /**
     * @return stdClass
     * @throws Exception
     */
    protected function getImageCache()
    {
        return MediaImage::getStats(Image::TYPE_PRODUCT, false);
    }

    /**
     * @return stdClass
     */
    protected function getSystemLogInfo()
    {
        $conf = Shop::getConfigValue(CONF_GLOBAL, 'systemlog_flag');

        return (object)[
            'error'  => $conf >= JTLLOG_LEVEL_ERROR,
            'notice' => $conf >= JTLLOG_LEVEL_NOTICE,
            'debug'  => $conf >= JTLLOG_LEVEL_NOTICE
        ];
    }

    /**
     * checks the db-structure against 'admin/includes/shopmd5files/dbstruct_[shop-version].json'
     * (the 'shop-Version' is here needed without points)
     *
     * @return bool  true='no errors', false='something is wrong'
     */
    protected function validDatabaseStruct(): bool
    {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbcheck_inc.php';

        $current  = getDBStruct(true);
        $original = getDBFileStruct();

        return is_array($current) && is_array($original) && count(compareDBStruct($original, $current)) === 0;
    }

    /**
     * checks the shop-filesystem-structure against 'admin/includes/shopmd5files/[shop-version].csv'
     * (the 'shop-Version' is here needed without points)
     *
     * @return bool  true='no errors', false='something is wrong'
     */
    protected function validFileStruct(): bool
    {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'filecheck_inc.php';

        $files = $stats = [];

        return getAllFiles($files, $stats) === 1
            ? end($stats) === 0
            : false;
    }

    /**
     * @return bool
     */
    protected function validFolderPermissions(): bool
    {
        $permissionStat = (new Systemcheck_Platform_Filesystem(PFAD_ROOT))->getFolderStats();

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
            \DB\ReturnType::ARRAY_OF_OBJECTS
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
                    'state' => Plugin::PLUGIN_ACTIVATED
                ],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($plugins as $plugin) {
                $sharedPlugins[$hookID][$plugin->cPluginID] = $plugin;
            }
        }

        return $sharedPlugins;
    }

    /**
     * @return bool
     */
    protected function hasPendingUpdates(): bool
    {
        return (new Updater())->hasPendingUpdates();
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
        return is_dir(PFAD_ROOT . 'install');
    }

    /**
     * @return bool
     */
    protected function hasDifferentTemplateVersion(): bool
    {
        return JTL_VERSION != Template::getInstance()->getShopVersion();
    }

    /**
     * @return bool
     */
    protected function hasMobileTemplateIssue(): bool
    {
        $oTemplate = Shop::Container()->getDB()->select('ttemplate', 'eTyp', 'standard');
        if ($oTemplate !== null && isset($oTemplate->cTemplate)) {
            $oTplData = TemplateHelper::getInstance()->getData($oTemplate->cTemplate);
            if ($oTplData->bResponsive) {
                $oMobileTpl = Shop::Container()->getDB()->select('ttemplate', 'eTyp', 'mobil');
                if ($oMobileTpl !== null) {
                    $cXMLFile = PFAD_ROOT . PFAD_TEMPLATES . $oMobileTpl->cTemplate .
                        DIRECTORY_SEPARATOR . TEMPLATE_XML;
                    if (file_exists($cXMLFile)) {
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
        $systemcheck = new Systemcheck_Environment();
        $systemcheck->executeTestGroup('Shop4');

        return $systemcheck->getIsPassed();
    }

    /**
     * @return array
     */
    protected function getEnvironmentTests()
    {
        return (new Systemcheck_Environment())->executeTestGroup('Shop4');
    }

    /**
     * @return Systemcheck_Platform_Hosting
     */
    protected function getPlatform()
    {
        return new Systemcheck_Platform_Hosting();
    }

    /**
     * @return array
     */
    protected function getMySQLStats(): array
    {
        $stats = Shop::Container()->getDB()->stats();
        $info  = Shop::Container()->getDB()->info();
        $lines = explode('  ', $stats);

        $lines = array_map(function ($v) {
            list($key, $value) = explode(':', $v, 2);

            return ['key' => trim($key), 'value' => trim($value)];
        }, $lines);

        return array_merge([['key' => 'Version', 'value' => $info]], $lines);
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
            if (($logCount = ZahlungsLog::count($method->cModulId, JTLLOG_LEVEL_ERROR)) > 0) {
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
        $aPollCoupons        = Shop::Container()->getDB()->selectAll('tumfrage', 'nAktiv', 1);
        $invalidCouponsFound = false;
        foreach ($aPollCoupons as $Kupon) {
            if ($Kupon->kKupon > 0) {
                $kKupon = Shop::Container()->getDB()->select(
                    'tkupon',
                    'kKupon',
                    $Kupon->kKupon,
                    'cAktiv',
                    'Y',
                    null,
                    null,
                    false,
                    'kKupon'
                );

                $invalidCouponsFound = empty($kKupon);
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
            "SELECT kKategorie, cName
                FROM tkategorie
                WHERE kOberkategorie > 0
                    AND kOberkategorie NOT IN (SELECT DISTINCT kKategorie FROM tkategorie)",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        return $has === true
            ? count($categories) === 0
            : $categories;
    }

    /**
     * @return bool
     */
    protected function hasFullTextIndexError(): bool
    {
        $conf = Shop::getSettings([CONF_ARTIKELUEBERSICHT])['artikeluebersicht'];

        return isset($conf['suche_fulltext'])
            && $conf['suche_fulltext'] !== 'N'
            && (!Shop::Container()->getDB()->query(
                    "SHOW INDEX 
                    FROM tartikel 
                    WHERE KEY_NAME = 'idx_tartikel_fulltext'",
                    \DB\ReturnType::SINGLE_OBJECT
                )
                || !Shop::Container()->getDB()->query(
                    "SHOW INDEX 
                        FROM tartikelsprache 
                        WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'",
                    \DB\ReturnType::SINGLE_OBJECT
                )
            );
    }

    /**
     * @return bool
     */
    protected function hasNewPluginVersions(): bool
    {
        $fNewVersions = false;
        // get installed plugins from DB
        $oPluginsDB = Shop::Container()->getDB()->query(
            'SELECT `cVerzeichnis`, `nVersion` 
                FROM `tplugin`',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!is_array($oPluginsDB) || 1 > count($oPluginsDB)) {
            return false; // there are no plugins installed
        }
        $vPluginsDB = [];
        foreach ($oPluginsDB as $oElement) {
            $vPluginsDB[$oElement->cVerzeichnis] = $oElement->nVersion;
        }
        // check against plugins, found in file-system
        foreach ($vPluginsDB as $szFolder => $nVersion) {
            $szPluginInfo = PFAD_ROOT . PFAD_PLUGIN . $szFolder . '/info.xml';
            $oXml         = null;
            if (file_exists($szPluginInfo)) {
                $oXml = simplexml_load_file($szPluginInfo);
                // read all pluginversions from 'info.xml'
                $vPluginXmlVersions = [0];
                foreach ($oXml->Install->Version as $oElement) {
                    $vPluginXmlVersions[] = (int)$oElement['nr'];
                }
                // check for the highest and set marker, if it's different from installed db-version
                if (max($vPluginXmlVersions) !== (int)$nVersion) {
                    $fNewVersions = true;
                }
            }
        }

        return $fNewVersions;
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
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($translations as $t) {
            $old = '{$neues_passwort}';
            if (strpos($t->cContentHtml, $old) !== false || strpos($t->cContentText, $old) !== false) {
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
        $emailConf = Shop::getConfig([CONF_EMAILS])['emails'];

        return $emailConf['email_methode'] === 'smtp' && empty(trim($emailConf['email_smtp_verschluesselung']));
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function needPasswordRehash2FA(): bool
    {
        $passwordService = Shop::Container()->getPasswordService();
        $hashes          = Shop::Container()->getDB()->query("
            SELECT *
                FROM tadmin2facodes
                GROUP BY kAdminlogin",
            \DB\ReturnType::ARRAY_OF_OBJECTS
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
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @return bool
     */
    public function hasDuplicateSpecialLinkTypes(): bool
    {
        $linksTMP = [];
        $links    = Shop::Container()->getDB()->query(
            'SELECT * FROM tlink
                LEFT JOIN tspezialseite ON tspezialseite.nLinkart = tlink.nLinkart
                WHERE tspezialseite.nLinkart IS NOT NULL
                ORDER BY tlink.nLinkart',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        foreach ($links as $link) {
            if ($link->cKundengruppen === null || $link->cKundengruppen === 'NULL') {
                $customerGroups = [0];
            } else {
                $customerGroups = StringHandler::parseSSK($link->cKundengruppen);
            }
            if (!isset($linksTMP[$link->nLinkart])) {
                $linksTMP[$link->nLinkart] = [];
            }

            if (!empty(array_intersect($linksTMP[$link->nLinkart], $customerGroups))) {
                return true;
            } else {
                $linksTMP[$link->nLinkart] = array_merge($linksTMP[$link->nLinkart], $customerGroups);
                if (count($linksTMP[$link->nLinkart]) > 1 && in_array(0, $linksTMP[$link->nLinkart], true)) {
                    return true;
                }
            }
        }

        return false;
    }
}
