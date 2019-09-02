<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Installation;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Language\LanguageHelper;
use JTL\Plugin\Helper;
use JTL\Plugin\InstallCode;
use JTL\Plugin\LegacyPluginLoader;
use JTL\Plugin\PluginInterface;
use JTL\Plugin\PluginLoader;

/**
 * Class Uninstaller
 * @package JTL\Plugin\Admin\Installation
 */
final class Uninstaller
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * Uninstaller constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db    = $db;
        $this->cache = $cache;
    }

    /**
     * Versucht, ein ausgewähltes Plugin zu deinstallieren
     *
     * @param int      $pluginID
     * @param bool     $update
     * @param int|null $newPluginID
     * @return int
     * 1 = Alles O.K.
     * 2 = $kPlugin wurde nicht übergeben
     * 3 = SQL-Fehler
     */
    public function uninstall(int $pluginID, bool $update = false, int $newPluginID = null): int
    {
        if ($pluginID <= 0) {
            return InstallCode::WRONG_PARAM;
        }
        $data = $this->db->select('tplugin', 'kPlugin', $pluginID);
        $loader = (int)$data->bExtension === 1
            ? new PluginLoader($this->db, $this->cache)
            : new LegacyPluginLoader($this->db, $this->cache);
        $plugin = $loader->init($pluginID);
        if ($plugin === null) {
            return InstallCode::NO_PLUGIN_FOUND;
        }
        if (!$update) {
            // Plugin wird vollständig deinstalliert
            if (($p = Helper::bootstrap($pluginID, $loader)) !== null) {
                $p->uninstalled();
            }
            $this->executeMigrations($plugin);
            $uninstaller = $plugin->getPaths()->getUninstaller();
            if ($uninstaller !== null && \file_exists($uninstaller)) {
                try {
                    include $plugin->getPaths()->getUninstaller();
                } catch (\Exception $exc) {
                }
            }
            $customTables = $this->db->selectAll('tplugincustomtabelle', 'kPlugin', $pluginID);
            foreach ($customTables as $table) {
                $this->db->query('DROP TABLE IF EXISTS ' . $table->cTabelle, ReturnType::DEFAULT);
            }
            $this->doSQLDelete($pluginID, $update, $newPluginID);
        } else {
            // Plugin wird nur teilweise deinstalliert, weil es danach ein Update gibt
            $this->doSQLDelete($pluginID, $update, $newPluginID);
        }
        $this->cache->flushAll();

        return InstallCode::OK;
    }

    /**
     * @param PluginInterface $plugin
     * @return array
     * @throws \Exception
     */
    private function executeMigrations($plugin): array
    {
        $manager = new MigrationManager(
            $this->db,
            $plugin->getPaths()->getBasePath() . \PFAD_PLUGIN_MIGRATIONS,
            $plugin->getPluginID()
        );

        return $manager->migrate(0);
    }

    /**
     * @param int $pluginID
     */
    private function fullDelete(int $pluginID): void
    {
        $this->db->query(
            'DELETE tpluginsprachvariablesprache, tpluginsprachvariablecustomsprache, tpluginsprachvariable
                FROM tpluginsprachvariable
                LEFT JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                LEFT JOIN tpluginsprachvariablecustomsprache
                    ON tpluginsprachvariablecustomsprache.cSprachvariable = tpluginsprachvariable.cName
                    AND tpluginsprachvariablecustomsprache.kPlugin = tpluginsprachvariable.kPlugin
                WHERE tpluginsprachvariable.kPlugin = ' . $pluginID,
            ReturnType::DEFAULT
        );
        $this->db->delete('tplugineinstellungen', 'kPlugin', $pluginID);
        $this->db->delete('tplugincustomtabelle', 'kPlugin', $pluginID);
        $this->db->delete('tpluginlinkdatei', 'kPlugin', $pluginID);
        $this->db->queryPrepared(
            'DELETE tzahlungsartsprache, tzahlungsart
                FROM tzahlungsart
                LEFT JOIN tzahlungsartsprache
                    ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                WHERE tzahlungsart.cModulId LIKE :pid',
            ['pid' => 'kPlugin_' . $pluginID . '_%'],
            ReturnType::DEFAULT
        );
        $this->db->queryPrepared(
            "DELETE tboxen, tboxvorlage
                FROM tboxvorlage
                LEFT JOIN tboxen 
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE tboxvorlage.kCustomID = :pid
                    AND (tboxvorlage.eTyp = 'plugin' OR tboxvorlage.eTyp = 'extension')",
            ['pid' => $pluginID],
            ReturnType::DEFAULT
        );
        $this->db->query(
            'DELETE tpluginemailvorlageeinstellungen, temailvorlagespracheoriginal,
                temailvorlage, temailvorlagesprache
                FROM temailvorlage
                LEFT JOIN temailvorlagespracheoriginal
                    ON temailvorlagespracheoriginal.kEmailvorlage = temailvorlage.kEmailvorlage
                LEFT JOIN tpluginemailvorlageeinstellungen
                    ON tpluginemailvorlageeinstellungen.kEmailvorlage = temailvorlage.kEmailvorlage
                LEFT JOIN temailvorlagesprache
                    ON temailvorlagesprache.kEmailvorlage = temailvorlage.kEmailvorlage
                WHERE temailvorlage.kPlugin = ' . $pluginID,
            ReturnType::DEFAULT
        );
    }

    /**
     * @param int $pluginID
     */
    private function partialDelete(int $pluginID): void
    {
        $this->db->query(
            'DELETE tpluginsprachvariablesprache, tpluginsprachvariable
                FROM tpluginsprachvariable
                LEFT JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                WHERE tpluginsprachvariable.kPlugin = ' . $pluginID,
            ReturnType::DEFAULT
        );
        $this->db->delete('tboxvorlage', ['kCustomID', 'eTyp'], [$pluginID, 'plugin']);
        $this->db->delete('tpluginlinkdatei', 'kPlugin', $pluginID);
        $this->db->query(
            'DELETE temailvorlage, temailvorlagespracheoriginal
                FROM temailvorlage
                LEFT JOIN temailvorlagespracheoriginal
                    ON temailvorlagespracheoriginal.kEmailvorlage = temailvorlage.kEmailvorlage
                WHERE temailvorlage.kPlugin = ' . $pluginID,
            ReturnType::DEFAULT
        );
    }

    /**
     * @param int      $pluginID
     * @param bool     $update
     * @param null|int $newPluginID
     */
    private function doSQLDelete(int $pluginID, bool $update, int $newPluginID = null): void
    {
        if (!$update) {
            $this->fullDelete($pluginID);
        } else {
            $this->partialDelete($pluginID);
        }
        $this->db->query(
            'DELETE tpluginsqlfehler, tpluginhook
                FROM tpluginhook
                LEFT JOIN tpluginsqlfehler
                    ON tpluginsqlfehler.kPluginHook = tpluginhook.kPluginHook
                WHERE tpluginhook.kPlugin = ' . $pluginID,
            ReturnType::DEFAULT
        );
        $this->db->delete('tpluginadminmenu', 'kPlugin', $pluginID);
        $this->db->query(
            'DELETE tplugineinstellungenconfwerte, tplugineinstellungenconf
                FROM tplugineinstellungenconf
                LEFT JOIN tplugineinstellungenconfwerte
                    ON tplugineinstellungenconfwerte.kPluginEinstellungenConf = 
                    tplugineinstellungenconf.kPluginEinstellungenConf
                WHERE tplugineinstellungenconf.kPlugin = ' . $pluginID,
            ReturnType::DEFAULT
        );

        $this->db->delete('tpluginuninstall', 'kPlugin', $pluginID);
        $this->db->delete('tplugin_resources', 'kPlugin', $pluginID);
        $links = [];
        if ($newPluginID !== null && $newPluginID > 0) {
            $newPluginID = (int)$newPluginID;
            $links       = $this->db->query(
                'SELECT kLink
                    FROM tlink
                    WHERE kPlugin IN (' . $pluginID . ', ' . $newPluginID . ')
                        ORDER BY kLink',
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        if (\count($links) === 2) {
            $oldLocalization = $this->db->selectAll('tlinksprache', 'kLink', $links[0]->kLink);
            $languages       = LanguageHelper::getAllLanguages(2);
            foreach ($oldLocalization as $item) {
                $this->db->update(
                    'tlinksprache',
                    ['kLink', 'cISOSprache'],
                    [$links[1]->kLink, $item->cISOSprache],
                    (object)['cSeo' => $item->cSeo]
                );
                $languageID = $languages[$item->cISOSprache]->kSprache;
                $this->db->delete(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kLink', $links[0]->kLink, $languageID]
                );
                $this->db->update(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kLink', $links[1]->kLink, $languageID],
                    (object)['cSeo' => $item->cSeo]
                );
            }
        }
        $this->db->query(
            "DELETE tlinksprache, tseo, tlink
                FROM tlink
                LEFT JOIN tlinksprache
                    ON tlinksprache.kLink = tlink.kLink
                LEFT JOIN tseo
                    ON tseo.cKey = 'kLink'
                    AND tseo.kKey = tlink.kLink
                WHERE tlink.kPlugin = " . $pluginID,
            ReturnType::DEFAULT
        );
        $this->db->delete('tpluginzahlungsartklasse', 'kPlugin', $pluginID);
        $this->db->delete('tplugintemplate', 'kPlugin', $pluginID);
        $this->db->delete('tcheckboxfunktion', 'kPlugin', $pluginID);
        $this->db->delete('tadminwidgets', 'kPlugin', $pluginID);
        $this->db->delete('topcportlet', 'kPlugin', $pluginID);
        $this->db->delete('topcblueprint', 'kPlugin', $pluginID);
        $this->db->query(
            'DELETE texportformateinstellungen, texportformatqueuebearbeitet, texportformat
                FROM texportformat
                LEFT JOIN texportformateinstellungen
                    ON texportformateinstellungen.kExportformat = texportformat.kExportformat
                LEFT JOIN texportformatqueuebearbeitet
                    ON texportformatqueuebearbeitet.kExportformat = texportformat.kExportformat
                WHERE texportformat.kPlugin = ' . $pluginID,
            ReturnType::DEFAULT
        );
        $this->db->delete('tplugin', 'kPlugin', $pluginID);
    }
}
