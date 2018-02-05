<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

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
     * @param mixed $arguments
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
     * @return JTLCache
     */
    protected function getObjectCache()
    {
        return JTLCache::getInstance()->setJtlCacheConfig();
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
        $flags = Jtllog::getSytemlogFlag(false);

        return (object)[
            'error'  => Jtllog::isBitFlagSet(JTLLOG_LEVEL_ERROR, $flags) > 0,
            'notice' => Jtllog::isBitFlagSet(JTLLOG_LEVEL_NOTICE, $flags) > 0,
            'debug'  => Jtllog::isBitFlagSet(JTLLOG_LEVEL_DEBUG, $flags) > 0
        ];
    }

    /**
     * checks the db-structure against 'admin/includes/shopmd5files/dbstruct_[shop-version].json'
     * (the 'shop-Version' is here needed without points)
     *
     * @return bool  true='no errors', false='something is wrong'
     */
    protected function validDatabaseStruct()
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
    protected function validFileStruct()
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
    protected function validFolderPermissions()
    {
        $permissionStat = (new Systemcheck_Platform_Filesystem(PFAD_ROOT))->getFolderStats();

        return $permissionStat->nCountInValid === 0;
    }

    /**
     * @return array
     */
    protected function getPluginSharedHooks()
    {
        $sharedPlugins = [];
        $sharedHookIds = Shop::DB()->executeQuery(
            "SELECT nHook
                FROM tpluginhook
                GROUP BY nHook
                HAVING COUNT(DISTINCT kPlugin) > 1", 2
        );
        foreach ($sharedHookIds as $hookData) {
            $hookId                 = (int)$hookData->nHook;
            $sharedPlugins[$hookId] = [];
            $plugins                = Shop::DB()->query(
                "SELECT DISTINCT tpluginhook.kPlugin, tplugin.cName, tplugin.cPluginID
                    FROM tpluginhook
                    INNER JOIN tplugin
                        ON tpluginhook.kPlugin = tplugin.kPlugin
                    WHERE tpluginhook.nHook = " . $hookId . "
                        AND tplugin.nStatus = 2", 2
            );
            foreach ($plugins as $plugin) {
                $sharedPlugins[$hookId][$plugin->cPluginID] = $plugin;
            }
        }

        return $sharedPlugins;
    }

    /**
     * @return bool
     */
    protected function hasPendingUpdates()
    {
        return (new Updater())->hasPendingUpdates();
    }

    /**
     * @return bool
     */
    protected function hasActiveProfiler()
    {
        return Profiler::getIsActive() !== 0;
    }

    /**
     * @return bool
     */
    protected function hasInstallDir()
    {
        return is_dir(PFAD_ROOT . 'install');
    }

    /**
     * @return bool
     */
    protected function hasDifferentTemplateVersion()
    {
        return JTL_VERSION != Template::getInstance()->getShopVersion();
    }

    /**
     * @return bool
     */
    protected function hasMobileTemplateIssue()
    {
        $oTemplate = Shop::DB()->select('ttemplate', 'eTyp', 'standard');
        if ($oTemplate !== null && isset($oTemplate->cTemplate)) {
            $oTplData = TemplateHelper::getInstance()->getData($oTemplate->cTemplate);
            if ($oTplData->bResponsive) {
                $oMobileTpl = Shop::DB()->select('ttemplate', 'eTyp', 'mobil');
                if ($oMobileTpl !== null) {
                    $cXMLFile = PFAD_ROOT . PFAD_TEMPLATES . $oMobileTpl->cTemplate .
                        DIRECTORY_SEPARATOR . TEMPLATE_XML;
                    if (file_exists($cXMLFile)) {
                        return true;
                    }
                    // Wenn ein Template aktiviert aber physisch nicht vorhanden ist,
                    // ist der DB-Eintrag falsch und wird gelöscht
                    Shop::DB()->delete('ttemplate', 'eTyp', 'mobil');
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function hasStandardTemplateIssue()
    {
        return Shop::DB()->select('ttemplate', 'eTyp', 'standard') === null;
    }

    /**
     * @return bool
     */
    protected function hasValidEnvironment()
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
    protected function getMySQLStats()
    {
        $stats = Shop::DB()->stats();
        $info  = Shop::DB()->info();
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
    protected function getPaymentMethodsWithError()
    {
        $incorrectPaymentMethods = [];
        $paymentMethods          = Shop::DB()->selectAll(
            'tzahlungsart',
            'nActive',
            1,
            '*',
            'cAnbieter, cName, nSort, kZahlungsart'
        );
        foreach ($paymentMethods as $method) {
            if (($logCount = ZahlungsLog::count($method->cModulId, JTLLOG_LEVEL_ERROR)) > 0) {
                $method->logCount = $logCount;
                $incorrectPaymentMethods[] = $method;
            }
        }

        return $incorrectPaymentMethods;
    }

    /**
     * @return bool
     */
    protected function hasInvalidPollCoupons()
    {
        $aPollCoupons        = Shop::DB()->selectAll('tumfrage', 'nAktiv', 1);
        $invalidCouponsFound = false;
        foreach ($aPollCoupons as $Kupon) {
            if ($Kupon->kKupon > 0) {
                $kKupon = Shop::DB()->select(
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
     * @return bool
     */
    protected function usesDeprecatedPriceImages()
    {
        $grafikPreise = Shop::DB()->selectAll('teinstellungen', 'kEinstellungenSektion', 118);

        foreach ($grafikPreise as $preis) {
            if ($preis->cWert === 'Y') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param bool $has
     * @return array|bool
     */
    protected function getOrphanedCategories($has = true)
    {
        $categories = Shop::DB()->query(
            "SELECT kKategorie, cName
                FROM tkategorie
                WHERE kOberkategorie > 0
                    AND kOberkategorie NOT IN (SELECT DISTINCT kKategorie FROM tkategorie)", 2
        );

        return $has === true
            ? count($categories) === 0
            : $categories;
    }

    /**
     * @return bool
     */
    protected function hasFullTextIndexError()
    {
        $conf = Shop::getSettings([CONF_ARTIKELUEBERSICHT]);

        return isset($conf['artikeluebersicht']['suche_fulltext'])
            && $conf['artikeluebersicht']['suche_fulltext'] === 'Y'
            && (!Shop::DB()->query("SHOW INDEX FROM tartikel WHERE KEY_NAME = 'idx_tartikel_fulltext'", 1)
                || !Shop::DB()->query("SHOW INDEX FROM tartikelsprache WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'", 1));
    }

    /**
     * @return bool
     */
    protected function hasNewPluginVersions()
    {
        $fNewVersions = false;
        // get installed plugins from DB
        $oPluginsDB = Shop::DB()->query('SELECT `cVerzeichnis`, `nVersion` FROM `tplugin`', 2);
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
    public function hasInvalidPasswordResetMailTemplate()
    {
        $translations = Shop::DB()->query("
            SELECT lang.cContentText, lang.cContentHtml
            FROM temailvorlagesprache lang
            JOIN temailvorlage ON lang.kEmailvorlage = temailvorlage.kEmailvorlage
            WHERE temailvorlage.cName = 'Passwort vergessen'
        ", NiceDB::RET_ARRAY_OF_OBJECTS);
        foreach($translations as $t) {
            $old = '{$neues_passwort}';
            if(strpos($t->cContentHtml, $old) !== false || strpos($t->cContentText, $old) !== false) {
                return true;
            }
        }
        return false;
    }
}
