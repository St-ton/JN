<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

use DB\DbInterface;
use DB\ReturnType;
use Plugin\InstallCode;

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
     * @param int  $kPlugin
     * @param bool $bUpdate
     * @param int|null $kPluginNew
     * @return int
     * 1 = Alles O.K.
     * 2 = $kPlugin wurde nicht übergeben
     * 3 = SQL-Fehler
     */
    public function uninstall(int $kPlugin, bool $bUpdate = false, int $kPluginNew = null): int
    {
        if ($kPlugin <= 0) {
            return InstallCode::WRONG_PARAM; // $kPlugin wurde nicht übergeben
        }
        $oPlugin = new \Plugin($kPlugin, false, true); // suppress reload = true um Endlosrekursion zu verhindern
        if (empty($oPlugin->kPlugin)) {
            return InstallCode::NO_PLUGIN_FOUND;
        }
        if (!$bUpdate) {
            // Plugin wird vollständig deinstalliert
            if (isset($oPlugin->oPluginUninstall->kPluginUninstall)
                && (int)$oPlugin->oPluginUninstall->kPluginUninstall > 0
            ) {
                try {
                    include $oPlugin->cPluginUninstallPfad;
                } catch (\Exception $exc) {
                }
            }
            // Custom Tables löschen
            $customTables = $this->db->selectAll('tplugincustomtabelle', 'kPlugin', $kPlugin);
            foreach ($customTables as $table) {
                $this->db->query('DROP TABLE IF EXISTS ' . $table->cTabelle, ReturnType::DEFAULT);
            }
            $this->doSQLDelete($kPlugin, $bUpdate, $kPluginNew);
        } else {
            // Plugin wird nur teilweise deinstalliert, weil es danach ein Update gibt
            $this->doSQLDelete($kPlugin, $bUpdate, $kPluginNew);
        }
        \Shop::Cache()->flushAll();
        if (($p = \Plugin::bootstrapper($kPlugin)) !== null) {
            $p->uninstalled();
        }

        return InstallCode::OK;
    }

    /**
     * @param int      $kPlugin
     * @param bool     $bUpdate
     * @param null|int $kPluginNew
     */
    private function doSQLDelete(int $kPlugin, bool $bUpdate, int $kPluginNew = null): void
    {
        // Kein Update => alles deinstallieren
        if (!$bUpdate) {
            $this->db->query(
                'DELETE tpluginsprachvariablesprache, tpluginsprachvariablecustomsprache, tpluginsprachvariable
                FROM tpluginsprachvariable
                LEFT JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                LEFT JOIN tpluginsprachvariablecustomsprache
                    ON tpluginsprachvariablecustomsprache.cSprachvariable = tpluginsprachvariable.cName
                    AND tpluginsprachvariablecustomsprache.kPlugin = tpluginsprachvariable.kPlugin
                WHERE tpluginsprachvariable.kPlugin = ' . $kPlugin,
                ReturnType::AFFECTED_ROWS
            );

            $this->db->delete('tplugineinstellungen', 'kPlugin', $kPlugin);
            $this->db->delete('tplugincustomtabelle', 'kPlugin', $kPlugin);
            $this->db->delete('tpluginlinkdatei', 'kPlugin', $kPlugin);
            $this->db->query(
                "DELETE tzahlungsartsprache, tzahlungsart
                FROM tzahlungsart
                LEFT JOIN tzahlungsartsprache
                    ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                WHERE tzahlungsart.cModulId LIKE 'kPlugin_" . $kPlugin . "_%'",
                ReturnType::AFFECTED_ROWS
            );

            $this->db->query(
                "DELETE tboxen, tboxvorlage
                FROM tboxvorlage
                LEFT JOIN tboxen 
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE tboxvorlage.kCustomID = " . $kPlugin . "
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
                WHERE tpluginemailvorlage.kPlugin = ' . $kPlugin,
                ReturnType::AFFECTED_ROWS
            );
        } else { // Update => nur teilweise deinstallieren
            $this->db->query(
                'DELETE tpluginsprachvariablesprache, tpluginsprachvariable
                FROM tpluginsprachvariable
                LEFT JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                WHERE tpluginsprachvariable.kPlugin = ' . $kPlugin,
                ReturnType::AFFECTED_ROWS
            );

            $this->db->delete('tboxvorlage', ['kCustomID', 'eTyp'], [$kPlugin, 'plugin']);
            $this->db->delete('tpluginlinkdatei', 'kPlugin', $kPlugin);
            $this->db->query(
                'DELETE tpluginemailvorlage, tpluginemailvorlagespracheoriginal
                FROM tpluginemailvorlage
                LEFT JOIN tpluginemailvorlagespracheoriginal
                    ON tpluginemailvorlagespracheoriginal.kEmailvorlage = tpluginemailvorlage.kEmailvorlage
                WHERE tpluginemailvorlage.kPlugin = ' . $kPlugin,
                ReturnType::AFFECTED_ROWS
            );
        }
        $this->db->query(
            'DELETE tpluginsqlfehler, tpluginhook
            FROM tpluginhook
            LEFT JOIN tpluginsqlfehler
                ON tpluginsqlfehler.kPluginHook = tpluginhook.kPluginHook
            WHERE tpluginhook.kPlugin = ' . $kPlugin,
            ReturnType::AFFECTED_ROWS
        );
        $this->db->delete('tpluginadminmenu', 'kPlugin', $kPlugin);
        $this->db->query(
            'DELETE tplugineinstellungenconfwerte, tplugineinstellungenconf
                FROM tplugineinstellungenconf
                LEFT JOIN tplugineinstellungenconfwerte
                    ON tplugineinstellungenconfwerte.kPluginEinstellungenConf = 
                    tplugineinstellungenconf.kPluginEinstellungenConf
                WHERE tplugineinstellungenconf.kPlugin = ' . $kPlugin,
            ReturnType::AFFECTED_ROWS
        );

        $this->db->delete('tpluginuninstall', 'kPlugin', $kPlugin);
        $this->db->delete('tplugin_resources', 'kPlugin', $kPlugin);
        $oObj_arr = [];
        if ($kPluginNew !== null && $kPluginNew > 0) {
            $kPluginNew = (int)$kPluginNew;
            $oObj_arr   = $this->db->query(
                "SELECT kLink
                    FROM tlink
                    WHERE kPlugin IN ({$kPlugin}, {$kPluginNew})
                        ORDER BY kLink",
                ReturnType::ARRAY_OF_OBJECTS
            );
        }
        if (\is_array($oObj_arr) && \count($oObj_arr) === 2) {
            $oldLocalization = $this->db->selectAll('tlinksprache', 'kLink', $oObj_arr[0]->kLink);
            if (\is_array($oldLocalization) && \count($oldLocalization) > 0) {
                $oSprachAssoc_arr = \Sprache::getAllLanguages(2);

                foreach ($oldLocalization as $item) {
                    $upd       = new \stdClass();
                    $upd->cSeo = $item->cSeo;
                    $this->db->update(
                        'tlinksprache',
                        ['kLink', 'cISOSprache'],
                        [$oObj_arr[1]->kLink, $item->cISOSprache],
                        $upd
                    );
                    $kSprache = $oSprachAssoc_arr[$item->cISOSprache]->kSprache;
                    $this->db->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kLink', $oObj_arr[0]->kLink, $kSprache]
                    );
                    $upd       = new \stdClass();
                    $upd->cSeo = $item->cSeo;
                    $this->db->update(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kLink', $oObj_arr[1]->kLink, $kSprache],
                        $upd
                    );
                }
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
                WHERE tlink.kPlugin = " . $kPlugin,
            ReturnType::AFFECTED_ROWS
        );
        $this->db->delete('tpluginzahlungsartklasse', 'kPlugin', $kPlugin);
        $this->db->delete('tplugintemplate', 'kPlugin', $kPlugin);
        $this->db->delete('tcheckboxfunktion', 'kPlugin', $kPlugin);
        $this->db->delete('tadminwidgets', 'kPlugin', $kPlugin);
        $this->db->delete('topcportlet', 'kPlugin', $kPlugin);
        $this->db->delete('topcblueprint', 'kPlugin', $kPlugin);
        $this->db->query(
            'DELETE texportformateinstellungen, texportformatqueuebearbeitet, texportformat
                FROM texportformat
                LEFT JOIN texportformateinstellungen
                    ON texportformateinstellungen.kExportformat = texportformat.kExportformat
                LEFT JOIN texportformatqueuebearbeitet
                    ON texportformatqueuebearbeitet.kExportformat = texportformat.kExportformat
                WHERE texportformat.kPlugin = ' . $kPlugin,
            ReturnType::AFFECTED_ROWS
        );
        $this->db->delete('tplugin', 'kPlugin', $kPlugin);
    }
}
