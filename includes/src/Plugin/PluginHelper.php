<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

/**
 * Class PluginHelper
 * @package Plugin
 */
class PluginHelper
{
    /**
     * @var array
     */
    private static $hookList;

    /**
     * @var array
     */
    private static $templatePaths;

    /**
     * @var array
     */
    private static $bootstrapper = [];

    /**
     * Holt ein Array mit allen Hooks die von Plugins benutzt werden.
     * Zu jedem Hook in dem Array, gibt es ein weiteres Array mit Plugins die an diesem Hook geladen werden.
     *
     * @return array
     */
    public static function getHookList(): array
    {
        if (self::$hookList !== null) {
            return self::$hookList;
        }
        $cacheID = 'hook_list';
        if (($hooks = \Shop::Container()->getCache()->get($cacheID)) !== false) {
            self::$hookList = $hooks;

            return $hooks;
        }
        $hook     = null;
        $hooks    = [];
        $hookData = \Shop::Container()->getDB()->queryPrepared(
            'SELECT tpluginhook.nHook, tplugin.kPlugin, tplugin.cVerzeichnis, tplugin.nVersion, tpluginhook.cDateiname
                FROM tplugin
                JOIN tpluginhook
                    ON tpluginhook.kPlugin = tplugin.kPlugin
                WHERE tplugin.nStatus = :state
                ORDER BY tpluginhook.nPriority, tplugin.kPlugin',
            ['state' => Plugin::PLUGIN_ACTIVATED],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($hookData as $hook) {
            $plugin             = new \stdClass();
            $plugin->kPlugin    = (int)$hook->kPlugin;
            $plugin->nVersion   = (int)$hook->nVersion;
            $plugin->cDateiname = $hook->cDateiname;

            $hooks[$hook->nHook][$hook->kPlugin] = $plugin;
        }
        // Schauen, ob die Hookliste einen Hook als Frontende Link hat.
        // Falls ja, darf die Liste den Seiten Link Plugin Handler nur einmal ausführen bzw. nur einmal beinhalten
        if (isset($hooks[\HOOK_SEITE_PAGE_IF_LINKART])) {
            $exists = false;
            foreach ($hooks[\HOOK_SEITE_PAGE_IF_LINKART] as $i => $oPluginHookListe) {
                if ($oPluginHookListe->cDateiname === \PLUGIN_SEITENHANDLER) {
                    unset($hooks[\HOOK_SEITE_PAGE_IF_LINKART][$i]);
                    $exists = true;
                }
            }
            // Es war min. einmal der Seiten Link Plugin Handler enthalten um einen Frontend Link anzusteuern
            if ($exists) {
                $plugin                                = new \stdClass();
                $plugin->kPlugin                       = $hook->kPlugin;
                $plugin->nVersion                      = $hook->nVersion;
                $plugin->cDateiname                    = \PLUGIN_SEITENHANDLER;
                $hooks[\HOOK_SEITE_PAGE_IF_LINKART][0] = $plugin;
            }
        }
        \Shop::Container()->getCache()->set($cacheID, $hooks, [\CACHING_GROUP_PLUGIN]);
        self::$hookList = $hooks;

        return $hooks;
    }

    /**
     * @param array $hookList
     * @return bool
     */
    public static function setHookList(array $hookList): bool
    {
        self::$hookList = $hookList;

        return true;
    }

    /**
     * @param string $pluginID
     * @return null|Plugin
     */
    public static function getPluginById(string $pluginID): ?self
    {
        $cacheID = 'plugin_id_list';
        if (($plugins = \Shop::Container()->getCache()->get($cacheID)) === false) {
            $plugins = \Shop::Container()->getDB()->query(
                'SELECT kPlugin, cPluginID 
                    FROM tplugin',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            \Shop::Container()->getCache()->set($cacheID, $plugins, [\CACHING_GROUP_PLUGIN]);
        }
        foreach ($plugins as $plugin) {
            if ($plugin->cPluginID === $pluginID) {
                return new Plugin((int)$plugin->kPlugin);
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public static function getTemplatePaths(): array
    {
        if (self::$templatePaths !== null) {
            return self::$templatePaths;
        }

        $cacheID = 'template_paths';
        if (($templatePaths = \Shop::Container()->getCache()->get($cacheID)) !== false) {
            self::$templatePaths = $templatePaths;

            return $templatePaths;
        }

        $templatePaths = [];
        $plugins       = \Shop::Container()->getDB()->selectAll(
            'tplugin',
            'nStatus',
            Plugin::PLUGIN_ACTIVATED,
            'cPluginID,cVerzeichnis,nVersion',
            'nPrio'
        );

        foreach ($plugins as $plugin) {
            $path = \PFAD_ROOT . \PFAD_PLUGIN . $plugin->cVerzeichnis . '/' .
                \PFAD_PLUGIN_VERSION . $plugin->nVersion . '/' . \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_TEMPLATE;
            if (\is_dir($path)) {
                $templatePaths[$plugin->cPluginID] = $path;
            }
        }
        \Shop::Container()->getCache()->set($cacheID, $templatePaths, [\CACHING_GROUP_PLUGIN]);

        return $templatePaths;
    }

    /**
     * @param Plugin $plugin
     * @param array  $params
     * @return bool
     * @former pluginLizenzpruefung()
     * @since 5.0.0
     */
    public static function licenseCheck(Plugin $plugin, array $params = []): bool
    {
        if (isset($plugin->cLizenzKlasse, $plugin->cLizenzKlasseName)
            && \strlen($plugin->cLizenzKlasse) > 0
            && \strlen($plugin->cLizenzKlasseName) > 0
        ) {
            require_once $plugin->cLicencePfad . $plugin->cLizenzKlasseName;
            $licence       = new $plugin->cLizenzKlasse();
            $licenceMethod = \PLUGIN_LICENCE_METHODE;

            if (!$licence->$licenceMethod($plugin->cLizenz)) {
                $plugin->nStatus = Plugin::PLUGIN_LICENSE_KEY_INVALID;
                $plugin->cFehler = 'Lizenzschl&uuml;ssel ist ung&uuml;ltig';
                $plugin->updateInDB();
                \Shop::Container()->getLogService()->withName('kPlugin')->error(
                    'Plugin Lizenzprüfung: Das Plugin "' . $plugin->cName .
                    '" hat keinen gültigen Lizenzschlüssel und wurde daher deaktiviert!',
                    [$plugin->kPlugin]
                );
                if (isset($params['cModulId']) && \strlen($params['cModulId']) > 0) {
                    self::updatePaymentMethodState($plugin, 0);
                }

                return false;
            }
        }

        return true;
    }
    /**
     * @param int $state
     * @param int $id
     * @return bool
     * @former aenderPluginStatus()
     * @since 5.0.0
     */
    public static function updateStatusByID(int $state, int $id): bool
    {
        return \Shop::Container()->getDB()->update('tplugin', 'kPlugin', $id, (object)['nStatus' => $state]) > 0;
    }

    /**
     * @param Plugin $plugin
     * @param int    $state
     * @former aenderPluginZahlungsartStatus()
     * @since 5.0.0
     */
    public static function updatePaymentMethodState(Plugin $plugin, int $state): void
    {
        foreach (\array_keys($plugin->oPluginZahlungsmethodeAssoc_arr) as $moduleID) {
            \Shop::Container()->getDB()->update(
                'tzahlungsart',
                'cModulId',
                $moduleID,
                (object)['nActive' => $state]
            );
        }
    }

    /**
     * @param int    $id
     * @param string $paymentMethodName
     * @return string
     * @former gibPlugincModulId()
     * @since 5.0.0
     */
    public static function getModuleIDByPluginID(int $id, string $paymentMethodName): string
    {
        return $id > 0 && \strlen($paymentMethodName) > 0
            ? 'kPlugin_' . $id . '_' . \strtolower(\str_replace([' ', '-', '_'], '', $paymentMethodName))
            : '';
    }

    /**
     * @param string $moduleID
     * @return int
     * @former gibkPluginAuscModulId()
     * @since 5.0.0
     */
    public static function getIDByModuleID(string $moduleID): int
    {
        return \preg_match('/^kPlugin_(\d+)_/', $moduleID, $matches)
            ? (int)$matches[1]
            : 0;
    }

    /**
     * @param string $pluginID
     * @return int
     * @former gibkPluginAuscPluginID()
     * @since 5.0.0
     */
    public static function getIDByPluginID(string $pluginID): int
    {
        $plugin = \Shop::Container()->getDB()->select('tplugin', 'cPluginID', $pluginID);

        return isset($plugin->kPlugin) ? (int)$plugin->kPlugin : 0;
    }

    /**
     * @param int    $id
     * @param string $cISO
     * @return array
     * @former gibPluginSprachvariablen()
     * @since 5.0.0
     */
    public static function getLanguageVariablesByID(int $id, $cISO = ''): array
    {
        $return = [];
        $cSQL   = '';
        if (\strlen($cISO) > 0) {
            $cSQL = " AND tpluginsprachvariablesprache.cISO = '" . \strtoupper($cISO) . "'";
        }
        $langVars = \Shop::Container()->getDB()->query(
            'SELECT t.kPluginSprachvariable,
                t.kPlugin,
                t.cName,
                t.cBeschreibung,
                tpluginsprachvariablesprache.cISO,
                IF (c.cName IS NOT NULL, c.cName, tpluginsprachvariablesprache.cName) AS customValue
            FROM tpluginsprachvariable AS t
                LEFT JOIN tpluginsprachvariablesprache
                    ON  t.kPluginSprachvariable = tpluginsprachvariablesprache.kPluginSprachvariable
                LEFT JOIN tpluginsprachvariablecustomsprache AS c
                    ON c.kPlugin = t.kPlugin
                    AND c.kPluginSprachvariable = t.kPluginSprachvariable
                    AND tpluginsprachvariablesprache.cISO = c.cISO
                WHERE t.kPlugin = ' . $id . $cSQL,
            \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        if (!\is_array($langVars) || \count($langVars) < 1) {
            $langVars = \Shop::Container()->getDB()->query(
                "SELECT tpluginsprachvariable.kPluginSprachvariable,
                tpluginsprachvariable.kPlugin,
                tpluginsprachvariable.cName,
                tpluginsprachvariable.cBeschreibung,
                CONCAT('#', tpluginsprachvariable.cName, '#') AS customValue, '" .
                \strtoupper($cISO) . "' AS cISO
                    FROM tpluginsprachvariable
                    WHERE tpluginsprachvariable.kPlugin = " . $id,
                \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
            );
        }
        foreach ($langVars as $_sv) {
            $return[$_sv['cName']] = $_sv['customValue'];
        }

        return $return;
    }

    /**
     * Holt alle PluginSprachvariablen (falls vorhanden)
     *
     * @param int $kPlugin
     * @return array
     * @former gibSprachVariablen()
     */
    public static function getLanguageVariables(int $kPlugin): array
    {
        $langVars = \Shop::Container()->getDB()->queryPrepared(
            'SELECT l.kPluginSprachvariable, l.kPlugin, l.cName, l.cBeschreibung,
            COALESCE(c.cISO, tpluginsprachvariablesprache.cISO)  AS cISO,
            COALESCE(c.cName, tpluginsprachvariablesprache.cName) AS customValue
            FROM tpluginsprachvariable AS l
                LEFT JOIN tpluginsprachvariablecustomsprache AS c
                    ON c.kPluginSprachvariable = l.kPluginSprachvariable
                LEFT JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = l.kPluginSprachvariable
                    AND tpluginsprachvariablesprache.cISO = COALESCE(c.cISO, tpluginsprachvariablesprache.cISO)
            WHERE l.kPlugin = :pid
            ORDER BY l.kPluginSprachvariable',
            ['pid' => $kPlugin],
            \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        if (\count($langVars) === 0) {
            return [];
        }
        $new = [];
        foreach ($langVars as $lv) {
            if (!isset($new[$lv['kPluginSprachvariable']])) {
                $var                                   = new \stdClass();
                $var->kPluginSprachvariable            = $lv['kPluginSprachvariable'];
                $var->kPlugin                          = $lv['kPlugin'];
                $var->cName                            = $lv['cName'];
                $var->cBeschreibung                    = $lv['cBeschreibung'];
                $var->oPluginSprachvariableSprache_arr = [$lv['cISO'] => $lv['customValue']];
                $new[$lv['kPluginSprachvariable']]     = $var;
            } else {
                $new[$lv['kPluginSprachvariable']]->oPluginSprachvariableSprache_arr[$lv['cISO']] = $lv['customValue'];
            }
        }

        return \array_values($new);
    }

    /**
     * @param int $id
     * @return array
     * @former gibPluginEinstellungen()
     * @since 5.0.0
     */
    public static function getConfigByID(int $id): array
    {
        $conf = [];
        $data = \Shop::Container()->getDB()->queryPrepared(
            'SELECT tplugineinstellungen.*, tplugineinstellungenconf.cConf
                FROM tplugin
                JOIN tplugineinstellungen 
                    ON tplugineinstellungen.kPlugin = tplugin.kPlugin
                LEFT JOIN tplugineinstellungenconf 
                    ON tplugineinstellungenconf.kPlugin = tplugin.kPlugin 
                    AND tplugineinstellungen.cName = tplugineinstellungenconf.cWertName
                WHERE tplugin.kPlugin = :pid',
            ['pid' => $id],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($data as $item) {
            $conf[$item->cName] = $item->cConf === 'M' ? \unserialize($item->cWert) : $item->cWert;
        }

        return $conf;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public static function bootstrapper(int $id)
    {
        if (!isset(self::$bootstrapper[$id])) {
            $plugin = new Plugin($id);
            if ($plugin === null || $plugin->bBootstrap === false) {
                return null;
            }
            $file  = $plugin->cPluginPfad . \PLUGIN_BOOTSTRAPPER;
            $class = \sprintf('%s\\%s', $plugin->cPluginID, 'Bootstrap');

            if (!\is_file($file)) {
                return null;
            }

            require_once $file;

            if (!\class_exists($class)) {
                return null;
            }

            $bootstrapper = new $class($plugin);
            if (!\is_subclass_of($bootstrapper, AbstractPlugin::class)) {
                return null;
            }
            self::$bootstrapper[$id] = $bootstrapper;
        }

        return self::$bootstrapper[$id];
    }
}
