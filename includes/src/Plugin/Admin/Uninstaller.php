<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

use DB\DbInterface;
use DB\ReturnType;
use Plugin\InstallCode;
use Plugin\Plugin;
use Plugin\Helper;

/**
 * Class Uninstaller
 * @package Plugin\Admin
 */
class Uninstaller
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * Uninstaller constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
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
        $plugin = new Plugin($pluginID, false, true); // suppress reload = true um Endlosrekursion zu verhindern
        if (empty($plugin->kPlugin)) {
            return InstallCode::NO_PLUGIN_FOUND;
        }
        if (!$update) {
            // Plugin wird vollständig deinstalliert
            $this->executeMigrations($plugin);
            if (isset($plugin->oPluginUninstall->kPluginUninstall)
                && (int)$plugin->oPluginUninstall->kPluginUninstall > 0
            ) {
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
        \Shop::Container()->getCache()->flushAll();
        if (($p = Helper::bootstrapper($pluginID)) !== null) {
            $p->uninstalled();
        }

        return InstallCode::OK;
    }

    /**
     * @param Plugin $plugin
     * @return array
     * @throws \Exception
     */
    private function executeMigrations($plugin): array
    {
        $manager           = new MigrationManager(
            $this->db,
            $plugin->cPluginPfad . \PFAD_PLUGIN_MIGRATIONS,
            $plugin->cPluginID
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
            ReturnType::AFFECTED_ROWS
        );
        $this->db->delete('tplugineinstellungen', 'kPlugin', $pluginID);
        $this->db->delete('tplugincustomtabelle', 'kPlugin', $pluginID);
        $this->db->delete('tpluginlinkdatei', 'kPlugin', $pluginID);
        $this->db->query(
            "DELETE tzahlungsartsprache, tzahlungsart
                FROM tzahlungsart
                LEFT JOIN tzahlungsartsprache
                    ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                WHERE tzahlungsart.cModulId LIKE 'kPlugin_" . $pluginID . "_%'",
            ReturnType::AFFECTED_ROWS
        );
        $this->db->query(
            "DELETE tboxen, tboxvorlage
                FROM tboxvorlage
                LEFT JOIN tboxen 
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE tboxvorlage.kCustomID = " . $pluginID . "
                    AND tboxvorlage.eTyp = 'plugin'",
            ReturnType::AFFECTED_ROWS
        );
        $this->db->query(
            'DELETE tpluginemailvorlageeinstellungen, tpluginemailvorlagespracheoriginal,
                tpluginemailvorlage, tpluginemailvorlagesprache
                FROM tpluginemailvorlage
                LEFT JOIN tpluginemailvorlagespracheoriginal
                    ON tpluginemailvorlagespracheoriginal.kEmailvorlage = tpluginemailvorlage.kEmailvorlage
                LEFT JOIN tpluginemailvorlageeinstellungen
                    ON tpluginemailvorlageeinstellungen.kEmailvorlage = tpluginemailvorlage.kEmailvorlage
                LEFT JOIN tpluginemailvorlagesprache
                    ON tpluginemailvorlagesprache.kEmailvorlage = tpluginemailvorlage.kEmailvorlage
                WHERE tpluginemailvorlage.kPlugin = ' . $pluginID,
            ReturnType::AFFECTED_ROWS
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
            ReturnType::AFFECTED_ROWS
        );

        $this->db->delete('tboxvorlage', ['kCustomID', 'eTyp'], [$pluginID, 'plugin']);
        $this->db->delete('tpluginlinkdatei', 'kPlugin', $pluginID);
        $this->db->query(
            'DELETE tpluginemailvorlage, tpluginemailvorlagespracheoriginal
                FROM tpluginemailvorlage
                LEFT JOIN tpluginemailvorlagespracheoriginal
                    ON tpluginemailvorlagespracheoriginal.kEmailvorlage = tpluginemailvorlage.kEmailvorlage
                WHERE tpluginemailvorlage.kPlugin = ' . $pluginID,
            ReturnType::AFFECTED_ROWS
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
            ReturnType::AFFECTED_ROWS
        );
        $this->db->delete('tpluginadminmenu', 'kPlugin', $pluginID);
        $this->db->query(
            'DELETE tplugineinstellungenconfwerte, tplugineinstellungenconf
                FROM tplugineinstellungenconf
                LEFT JOIN tplugineinstellungenconfwerte
                    ON tplugineinstellungenconfwerte.kPluginEinstellungenConf = 
                    tplugineinstellungenconf.kPluginEinstellungenConf
                WHERE tplugineinstellungenconf.kPlugin = ' . $pluginID,
            ReturnType::AFFECTED_ROWS
        );

        $this->db->delete('tpluginuninstall', 'kPlugin', $pluginID);
        $this->db->delete('tplugin_resources', 'kPlugin', $pluginID);
        $links = [];
        if ($newPluginID !== null && $newPluginID > 0) {
            $newPluginID = (int)$newPluginID;
            $links       = $this->db->query(
                "SELECT kLink
                    FROM tlink
                    WHERE kPlugin IN ({$pluginID}, {$newPluginID})
                        ORDER BY kLink",
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        if (\count($links) === 2) {
            $oldLocalization = $this->db->selectAll('tlinksprache', 'kLink', $links[0]->kLink);
            $languages       = \Sprache::getAllLanguages(2);
            foreach ($oldLocalization as $item) {
                $this->db->update(
                    'tlinksprache',
                    ['kLink', 'cISOSprache'],
                    [$links[1]->kLink, $item->cISOSprache],
                    (object)['cSeo' => $item->cSeo]
                );
                $kSprache = $languages[$item->cISOSprache]->kSprache;
                $this->db->delete(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kLink', $links[0]->kLink, $kSprache]
                );
                $this->db->update(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kLink', $links[1]->kLink, $kSprache],
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
            ReturnType::AFFECTED_ROWS
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
            ReturnType::AFFECTED_ROWS
        );
        $this->db->delete('tplugin', 'kPlugin', $pluginID);
    }
}
