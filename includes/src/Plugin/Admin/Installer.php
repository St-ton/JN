<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

use DB\DbInterface;
use Plugin\InstallCode;

/**
 * Class Installer
 * @package Plugin\Admin
 */
final class Installer
{
    private $db;

    private $dir;

    private $uninstaller;

    public function __construct(DbInterface $db, string $dir, Uninstaller $uninstaller)
    {
        $this->db  = $db;
        $this->dir = $dir;
        $this->uninstaller = $uninstaller;
    }

    public function installierePluginVorbereitung($oPluginOld = 0)
    {
        if (empty($this->dir)) {
            return InstallCode::WRONG_PARAM;
        }
        // Plugin wurde schon installiert?
        $oPluginTMP = new \stdClass();
        if (!isset($oPluginOld->kPlugin) || !$oPluginOld->kPlugin) {
            $oPluginTMP = $this->db->select('tplugin', 'cVerzeichnis', $this->dir);
        }
        if (!empty($oPluginTMP->kPlugin)) {
            return 4;// Plugin wurde schon installiert
        }
        $cPfad = \PFAD_ROOT . \PFAD_PLUGIN . \basename($this->dir);
        if (!\file_exists($cPfad . '/' . \PLUGIN_INFO_FILE)) {
            return 3;// info.xml existiert nicht
        }
        $xml     = \file_get_contents($cPfad . '/' . \PLUGIN_INFO_FILE);
        $XML_arr = \XML_unserialize($xml);
        $XML_arr = \getArrangedArray($XML_arr);
        // Interne Plugin Plausi
        $code = \pluginPlausiIntern($XML_arr, $cPfad);
        // Work Around
        if (isset($oPluginOld->kPlugin) && $oPluginOld->kPlugin > 0 && $code === InstallCode::DUPLICATE_PLUGIN_ID) {
            $code = InstallCode::OK;
        }
        // Alles O.K. => installieren
        if ($code === InstallCode::OK || $code === InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE) {
            // Plugin wird installiert
            $code = $this->installierePlugin($XML_arr, $oPluginOld);

            if ($code === InstallCode::OK) {
                return InstallCode::OK;
            }
            $nSQLFehlerCode_arr = [
                2  => 152,
                3  => 153,
                4  => 154,
                5  => 155,
                6  => 156,
                7  => 157,
                8  => 158,
                9  => 159,
                10 => 160,
                11 => 161,
                12 => 162,
                13 => 163,
                14 => 164,
                15 => 165,
                16 => 166,
                22 => 202,
                23 => 203,
                24 => 204,
                25 => 205,
                26 => 206,
                27 => 207,
                28 => 208
            ];

            return $nSQLFehlerCode_arr[$code];
        }

        return $code;
    }

    /**
     * Installiert ein Plugin
     *
     * @param array       $XML_arr
     * @param \Plugin|int $oPluginOld
     * @return int
     */
    public function installierePlugin($XML_arr, $oPluginOld): int
    {
        $baseNode          = $XML_arr['jtlshop3plugin'][0];
        $versionNode       = $baseNode['Install'][0]['Version'];
        $nLastVersionKey   = \count($versionNode) / 2 - 1;
        $nXMLVersion       = (int)$baseNode['XMLVersion'];
        $cLizenzKlasse     = '';
        $cLizenzKlasseName = '';
        $nStatus           = \Plugin::PLUGIN_ACTIVATED;
        $tagsToFlush       = [];
        $basePath          = \PFAD_ROOT . \PFAD_PLUGIN . $this->dir . '/';
        if (isset($baseNode['LicenceClass'], $baseNode['LicenceClassFile'])
            && \strlen($baseNode['LicenceClass']) > 0
            && \strlen($baseNode['LicenceClassFile']) > 0
        ) {
            $cLizenzKlasse     = $baseNode['LicenceClass'];
            $cLizenzKlasseName = $baseNode['LicenceClassFile'];
            $nStatus           = \Plugin::PLUGIN_LICENSE_KEY_MISSING;
        }
        // tplugin füllen
        $oPlugin                       = new \stdClass();
        $oPlugin->cName                = $baseNode['Name'];
        $oPlugin->cBeschreibung        = $baseNode['Description'];
        $oPlugin->cAutor               = $baseNode['Author'];
        $oPlugin->cURL                 = $baseNode['URL'];
        $oPlugin->cIcon                = $baseNode['Icon'] ?? null;
        $oPlugin->cVerzeichnis         = $this->dir;
        $oPlugin->cPluginID            = $baseNode['PluginID'];
        $oPlugin->cFehler              = '';
        $oPlugin->cLizenz              = '';
        $oPlugin->cLizenzKlasse        = $cLizenzKlasse;
        $oPlugin->cLizenzKlasseName    = $cLizenzKlasseName;
        $oPlugin->nStatus              = $nStatus;
        $oPlugin->nVersion             = (int)$versionNode[$nLastVersionKey . ' attr']['nr'];
        $oPlugin->nXMLVersion          = $nXMLVersion;
        $oPlugin->nPrio                = 0;
        $oPlugin->dZuletztAktualisiert = 'NOW()';
        $oPlugin->dErstellt            = $versionNode[$nLastVersionKey]['CreateDate'];
        $oPlugin->bBootstrap           = \is_file($basePath . \PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . 'bootstrap.php')
            ? 1
            : 0;

        $_tags = empty($baseNode['Install'][0]['FlushTags'])
            ? []
            : \explode(',', $baseNode['Install'][0]['FlushTags']);
        foreach ($_tags as $_tag) {
            if (\defined(\trim($_tag))) {
                $tagsToFlush[] = \constant(\trim($_tag));
            }
        }
        if (\count($tagsToFlush) > 0) {
            \Shop::Cache()->flushTags($tagsToFlush);
        }
        $licenceClassFile = $basePath .
            \PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' .
            \PFAD_PLUGIN_LICENCE . $oPlugin->cLizenzKlasseName;
        if (isset($oPluginOld->cLizenz, $oPluginOld->nStatus)
            && (int)$oPluginOld->nStatus > 0
            && \strlen($oPluginOld->cLizenz) > 0
            && \is_file($licenceClassFile)
        ) {
            require_once $licenceClassFile;
            $oPluginLicence = new $oPlugin->cLizenzKlasse();
            $cLicenceMethod = \PLUGIN_LICENCE_METHODE;
            if ($oPluginLicence->$cLicenceMethod($oPluginOld->cLizenz)) {
                $oPlugin->cLizenz = $oPluginOld->cLizenz;
                $oPlugin->nStatus = $oPluginOld->nStatus;
            }
        }
        $oPlugin->dInstalliert = (isset($oPluginOld->kPlugin) && $oPluginOld->kPlugin > 0)
            ? $oPluginOld->dInstalliert
            : 'NOW()';
        $kPlugin               = $this->db->insert('tplugin', $oPlugin);
        $nVersion              = (int)$versionNode[$nLastVersionKey . ' attr']['nr'];
        $oPlugin->kPlugin      = $kPlugin;

        if ($kPlugin <= 0) {
            return InstallCode::WRONG_PARAM;
        }
        $res = \installPluginTables($XML_arr, $oPlugin, $oPluginOld);

        if ($res > 0) {
            $this->uninstaller->uninstall($kPlugin);

            return $res;
        }
        // SQL installieren
        $bSQLFehler = false;
        $code       = 1;
        foreach ($versionNode as $i => $Version_arr) {
            if ($nVersion > 0
                && isset($oPluginOld->kPlugin, $Version_arr['nr'])
                && $oPluginOld->nVersion >= (int)$Version_arr['nr']
            ) {
                continue;
            }
            \preg_match('/[0-9]+\sattr/', $i, $cTreffer1_arr);

            if (!isset($cTreffer1_arr[0]) || \strlen($cTreffer1_arr[0]) !== \strlen($i)) {
                continue;
            }
            $nVersionTMP = (int)$Version_arr['nr'];
            $xy          = \trim(\str_replace('attr', '', $i));
            $cSQLDatei   = $versionNode[$xy]['SQL'] ?? '';
            if ($cSQLDatei === '') {
                continue;
            }
            $code               = \logikSQLDatei($cSQLDatei, $nVersionTMP, $oPlugin);
            $nSQLFehlerCode_arr = [1 => 1, 2 => 22, 3 => 23, 4 => 24, 5 => 25, 6 => 26];
            $code               = $nSQLFehlerCode_arr[$code];

            if ($code !== InstallCode::OK) {
                $bSQLFehler = true;
                break;
            }
        }
        // Ist ein SQL Fehler aufgetreten? Wenn ja, deinstalliere wieder alles
        if ($bSQLFehler) {
            $this->uninstaller->uninstall($oPlugin->kPlugin);
        }
        if ($code === InstallCode::OK && $oPluginOld === 0 && ($p = \Plugin::bootstrapper($oPlugin->kPlugin)) !== null) {
            $p->installed();
        }
        // Installation von höheren XML Versionen
        if ($nXMLVersion > 100
            && ($code === InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE || $code === InstallCode::OK)
        ) {
            $code = InstallCode::OK;
            // Update
            if (isset($oPluginOld->kPlugin) && $oPluginOld->kPlugin > 0 && $code === 1) {
                // Update erfolgreich => sync neue Version auf altes Plugin
                $code               = $this->syncPluginUpdate($oPlugin->kPlugin, $oPluginOld);
                $nSQLFehlerCode_arr = [1 => 1, 2 => 27, 3 => 28];
                $code               = $nSQLFehlerCode_arr[$code];
            }
        } elseif (isset($oPluginOld->kPlugin)
            && $oPluginOld->kPlugin
            && ($code === InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE || $code === InstallCode::OK)
        ) {
            // Update erfolgreich => sync neue Version auf altes Plugin
            $code               = $this->syncPluginUpdate($oPlugin->kPlugin, $oPluginOld);
            $nSQLFehlerCode_arr = [1 => 1, 2 => 27, 3 => 28];
            $code               = $nSQLFehlerCode_arr[$code];
        }

        return $code;
    }

    /**
     * Wenn ein Update erfolgreich mit neuer kPlugin in der Datenbank ist
     * wird der alte kPlugin auf die neue Version übertragen und
     * die alte Plugin-Version deinstalliert.
     *
     * @param int    $kPlugin
     * @param \Plugin $oPluginOld
     * @return int
     * 1 = Alles O.K.
     * 2 = Übergabeparameter nicht korrekt
     * 3 = Update konnte nicht installiert werden
     */
    public function syncPluginUpdate(int $kPlugin, $oPluginOld)
    {
        $kPluginOld = (int)$oPluginOld->kPlugin;
        // Altes Plugin deinstallieren
        $nReturnValue = $this->uninstaller->uninstall($kPluginOld, true, $kPlugin);

        if ($nReturnValue === 1) {
            // tplugin
            $upd          = new stdClass();
            $upd->kPlugin = $kPluginOld;
            Shop::Container()->getDB()->update('tplugin', 'kPlugin', $kPlugin, $upd);
            Shop::Container()->getDB()->update('tpluginhook', 'kPlugin', $kPlugin, $upd);
            Shop::Container()->getDB()->update('tpluginadminmenu', 'kPlugin', $kPlugin, $upd);
            Shop::Container()->getDB()->update('tpluginsprachvariable', 'kPlugin', $kPlugin, $upd);
            Shop::Container()->getDB()->update('tadminwidgets', 'kPlugin', $kPlugin, $upd);
            Shop::Container()->getDB()->update('tpluginsprachvariablecustomsprache', 'kPlugin', $kPlugin, $upd);
            Shop::Container()->getDB()->update('tplugin_resources', 'kPlugin', $kPlugin, $upd);
            Shop::Container()->getDB()->update('tplugincustomtabelle', 'kPlugin', $kPlugin, $upd);
            Shop::Container()->getDB()->update('tplugintemplate', 'kPlugin', $kPlugin, $upd);
            Shop::Container()->getDB()->update('tpluginlinkdatei', 'kPlugin', $kPlugin, $upd);
            Shop::Container()->getDB()->update('tpluginemailvorlage', 'kPlugin', $kPlugin, $upd);
            Shop::Container()->getDB()->update('texportformat', 'kPlugin', $kPlugin, $upd);
            Shop::Container()->getDB()->update('topcportlet', 'kPlugin', $kPlugin, $upd);
            Shop::Container()->getDB()->update('topcblueprint', 'kPlugin', $kPlugin, $upd);
            $pluginConf = Shop::Container()->getDB()->query(
                'SELECT *
                FROM tplugineinstellungen
                WHERE kPlugin IN (' . $kPluginOld . ', ' . $kPlugin . ')
                ORDER BY kPlugin',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            if (is_array($pluginConf) && count($pluginConf) > 0) {
                $oEinstellung_arr = [];
                foreach ($pluginConf as $oPluginEinstellung) {
                    $cName = str_replace(
                        ['kPlugin_' . $kPluginOld . '_', 'kPlugin_' . $kPlugin . '_'],
                        '',
                        $oPluginEinstellung->cName
                    );
                    if (!isset($oEinstellung_arr[$cName])) {
                        $oEinstellung_arr[$cName] = new stdClass();

                        $oEinstellung_arr[$cName]->kPlugin = $kPluginOld;
                        $oEinstellung_arr[$cName]->cName   = str_replace(
                            'kPlugin_' . $kPlugin . '_',
                            'kPlugin_' . $kPluginOld . '_',
                            $oPluginEinstellung->cName
                        );
                        $oEinstellung_arr[$cName]->cWert   = $oPluginEinstellung->cWert;
                    }
                }
                Shop::Container()->getDB()->query(
                    'DELETE FROM tplugineinstellungen
                    WHERE kPlugin IN (' . $kPluginOld . ', ' . $kPlugin . ')',
                    \DB\ReturnType::AFFECTED_ROWS
                );

                foreach ($oEinstellung_arr as $oEinstellung) {
                    Shop::Container()->getDB()->insert('tplugineinstellungen', $oEinstellung);
                }
            }
            Shop::Container()->getDB()->query(
                "UPDATE tplugineinstellungen
                SET kPlugin = " . $kPluginOld . ",
                    cName = REPLACE(cName, 'kPlugin_" . $kPlugin . "_', 'kPlugin_" . $kPluginOld . "_')
                WHERE kPlugin = " . $kPlugin,
                \DB\ReturnType::AFFECTED_ROWS
            );
            // tplugineinstellungenconf
            Shop::Container()->getDB()->query(
                "UPDATE tplugineinstellungenconf
                SET kPlugin = " . $kPluginOld . ",
                    cWertName = REPLACE(cWertName, 'kPlugin_" . $kPlugin . "_', 'kPlugin_" . $kPluginOld . "_')
                WHERE kPlugin = " . $kPlugin,
                \DB\ReturnType::AFFECTED_ROWS
            );
            // tboxvorlage
            $upd = new stdClass();
            $upd->kCustomID = $kPluginOld;
            Shop::Container()->getDB()->update('tboxvorlage', ['kCustomID', 'eTyp'], [$kPlugin, 'plugin'], $upd);
            // tpluginzahlungsartklasse
            Shop::Container()->getDB()->query(
                "UPDATE tpluginzahlungsartklasse
                SET kPlugin = " . $kPluginOld . ",
                    cModulId = REPLACE(cModulId, 'kPlugin_" . $kPlugin . "_', 'kPlugin_" . $kPluginOld . "_')
                WHERE kPlugin = " . $kPlugin,
                \DB\ReturnType::AFFECTED_ROWS
            );
            // tpluginemailvorlageeinstellungen
            //@todo: this part was really messed up - check.
            $oPluginEmailvorlageAlt = Shop::Container()->getDB()->select('tpluginemailvorlage', 'kPlugin', $kPluginOld);
            $oEmailvorlage          = Shop::Container()->getDB()->select('tpluginemailvorlage', 'kPlugin', $kPlugin);
            if (isset($oEmailvorlage->kEmailvorlage, $oPluginEmailvorlageAlt->kEmailvorlage)) {
                $upd = new stdClass();
                $upd->kEmailvorlage = $oEmailvorlage->kEmailvorlage;
                Shop::Container()->getDB()->update('tpluginemailvorlageeinstellungen', 'kEmailvorlage', $oPluginEmailvorlageAlt->kEmailvorlage, $upd);
            }
            // tpluginemailvorlagesprache
            $kEmailvorlageNeu = 0;
            $kEmailvorlageAlt = 0;
            if (isset($oPluginOld->oPluginEmailvorlageAssoc_arr) && count($oPluginOld->oPluginEmailvorlageAssoc_arr) > 0) {
                foreach ($oPluginOld->oPluginEmailvorlageAssoc_arr as $cModulId => $oPluginEmailvorlageAlt) {
                    $oPluginEmailvorlageNeu = Shop::Container()->getDB()->select(
                        'tpluginemailvorlage',
                        'kPlugin',
                        $kPluginOld,
                        'cModulId',
                        $cModulId,
                        null,
                        null,
                        false,
                        'kEmailvorlage'
                    );
                    if (isset($oPluginEmailvorlageNeu->kEmailvorlage) && $oPluginEmailvorlageNeu->kEmailvorlage > 0) {
                        if ($kEmailvorlageNeu == 0 || $kEmailvorlageAlt == 0) {
                            $kEmailvorlageNeu = $oPluginEmailvorlageNeu->kEmailvorlage;
                            $kEmailvorlageAlt = $oPluginEmailvorlageAlt->kEmailvorlage;
                        }
                        $upd = new stdClass();
                        $upd->kEmailvorlage = $oPluginEmailvorlageNeu->kEmailvorlage;
                        Shop::Container()->getDB()->update(
                            'tpluginemailvorlagesprache',
                            'kEmailvorlage',
                            $oPluginEmailvorlageAlt->kEmailvorlage,
                            $upd
                        );
                    }
                }
            }
            // tpluginemailvorlageeinstellungen
            $upd = new stdClass();
            $upd->kEmailvorlage = $kEmailvorlageNeu;
            Shop::Container()->getDB()->update('tpluginemailvorlageeinstellungen', 'kEmailvorlage', $kEmailvorlageAlt, $upd);
            // tlink
            $upd = new stdClass();
            $upd->kPlugin = $kPluginOld;
            Shop::Container()->getDB()->update('tlink', 'kPlugin', $kPlugin, $upd);
            // tboxen
            // Ausnahme: Gibt es noch eine Boxenvorlage in der Pluginversion?
            // Falls nein -> lösche tboxen mit dem entsprechenden kPlugin
            $oObj = Shop::Container()->getDB()->select('tboxvorlage', 'kCustomID', $kPluginOld, 'eTyp', 'plugin');
            if (isset($oObj->kBoxvorlage) && (int)$oObj->kBoxvorlage > 0) {
                // tboxen kCustomID
                $upd = new stdClass();
                $upd->kBoxvorlage = $oObj->kBoxvorlage;
                Shop::Container()->getDB()->update('tboxen', 'kCustomID', $kPluginOld, $upd);
            } else {
                Shop::Container()->getDB()->delete('tboxen', 'kCustomID', $kPluginOld);
            }
            // tcheckboxfunktion
            $upd = new stdClass();
            $upd->kPlugin = $kPluginOld;
            Shop::Container()->getDB()->update('tcheckboxfunktion', 'kPlugin', $kPlugin, $upd);
            // tspezialseite
            Shop::Container()->getDB()->update('tspezialseite', 'kPlugin', $kPlugin, $upd);
            // tzahlungsart
            $oZahlungsartOld_arr = Shop::Container()->getDB()->query("
            SELECT kZahlungsart, cModulId
                FROM tzahlungsart
                WHERE cModulId LIKE 'kPlugin_{$kPluginOld}_%'",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($oZahlungsartOld_arr as $oZahlungsartOld) {
                $cModulIdNew     = str_replace("kPlugin_{$kPluginOld}_", "kPlugin_{$kPlugin}_", $oZahlungsartOld->cModulId);
                $oZahlungsartNew = Shop::Container()->getDB()->query(
                    "SELECT kZahlungsart
                      FROM tzahlungsart
                      WHERE cModulId LIKE '{$cModulIdNew}'",
                    \DB\ReturnType::SINGLE_OBJECT
                );
                $cNewSetSQL      = '';
                if (isset($oZahlungsartOld->kZahlungsart, $oZahlungsartNew->kZahlungsart)) {
                    Shop::Container()->getDB()->query(
                        'DELETE tzahlungsart, tzahlungsartsprache
                        FROM tzahlungsart
                        JOIN tzahlungsartsprache
                            ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                        WHERE tzahlungsart.kZahlungsart = ' . $oZahlungsartOld->kZahlungsart,
                        \DB\ReturnType::AFFECTED_ROWS
                    );

                    $cNewSetSQL = ' , kZahlungsart = ' . $oZahlungsartOld->kZahlungsart;
                    $upd = new stdClass();
                    $upd->kZahlungsart = $oZahlungsartOld->kZahlungsart;
                    Shop::Container()->getDB()->update('tzahlungsartsprache', 'kZahlungsart', $oZahlungsartNew->kZahlungsart, $upd);
                }

                Shop::Container()->getDB()->query(
                    "UPDATE tzahlungsart
                    SET cModulId = '{$oZahlungsartOld->cModulId}'
                    " . $cNewSetSQL . "
                    WHERE cModulId LIKE '{$cModulIdNew}'",
                    \DB\ReturnType::AFFECTED_ROWS
                );
            }

            return \Plugin\InstallCode::OK;
        }
        $this->uninstaller->uninstall($kPlugin);

        return 3;
    }
}
