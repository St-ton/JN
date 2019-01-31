<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation;

use DB\DbInterface;
use DB\ReturnType;
use JTL\XMLParser;
use JTLShop\SemVer\Version;
use Plugin\AbstractExtension;
use Plugin\Admin\Validation\ValidatorInterface;
use Plugin\ExtensionLoader;
use Plugin\Helper;
use Plugin\InstallCode;
use Plugin\PluginLoader;
use Plugin\State;

/**
 * Class Installer
 * @package Plugin\Admin
 */
final class Installer
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var string
     */
    private $dir;

    /**
     * @var Uninstaller
     */
    private $uninstaller;

    /**
     * @var ValidatorInterface
     */
    private $pluginValidator;

    /**
     * @var ValidatorInterface
     */
    private $extensionValidator;

    /**
     * @var AbstractExtension|null
     */
    private $plugin;

    /**
     * @var bool
     */
    private $isExtension = false;

    /**
     * Installer constructor.
     * @param DbInterface        $db
     * @param Uninstaller        $uninstaller
     * @param ValidatorInterface $validator
     * @param ValidatorInterface $modernValidator
     */
    public function __construct(
        DbInterface $db,
        Uninstaller $uninstaller,
        ValidatorInterface $validator,
        ValidatorInterface $modernValidator
    ) {
        $this->db                 = $db;
        $this->uninstaller        = $uninstaller;
        $this->pluginValidator    = $validator;
        $this->extensionValidator = $modernValidator;
    }

    /**
     * @return string
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @param string $dir
     */
    public function setDir(string $dir): void
    {
        $this->dir = $dir;
    }

    /**
     * @return AbstractExtension|null
     */
    public function getPlugin(): ?AbstractExtension
    {
        return $this->plugin;
    }

    /**
     * @param AbstractExtension|null $plugin
     */
    public function setPlugin(AbstractExtension $plugin): void
    {
        $this->plugin = $plugin;
    }

    /**
     * @return int
     * @former installierePluginVorbereitung()
     */
    public function prepare(): int
    {
        if (empty($this->dir)) {
            return InstallCode::WRONG_PARAM;
        }
        $validator = $this->pluginValidator;
        $baseDir   = \PFAD_ROOT . \PFAD_PLUGIN . \basename($this->dir);
        if (!\file_exists($baseDir . '/' . \PLUGIN_INFO_FILE)) {
            $baseDir           = \PFAD_ROOT . \PFAD_EXTENSIONS . \basename($this->dir);
            $validator         = $this->extensionValidator;
            $this->isExtension = true;
            if (!\file_exists($baseDir . '/' . \PLUGIN_INFO_FILE)) {
                return InstallCode::INFO_XML_MISSING;
            }
        }
        $validator->setDir($baseDir);
        $parser = new XMLParser();
        $xml    = $parser->parse($baseDir . '/' . \PLUGIN_INFO_FILE);
        $code   = $validator->pluginPlausiIntern($xml, $this->plugin !== null);
        if ($code === InstallCode::DUPLICATE_PLUGIN_ID && $this->plugin !== null && $this->plugin->getID() > 0) {
            $code = InstallCode::OK;
        }
        if ($code === InstallCode::OK || $code === InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE) {
            $code = $this->install($xml);
        }

        return $code;
    }

    /**
     * Installiert ein Plugin
     *
     * @param array $xml
     * @return int
     * @former installierePlugin()
     */
    public function install(array $xml): int
    {
        $baseNode         = $this->getBaseNode($xml);
        $versionNode      = $baseNode['Install'][0]['Version'] ?? null;
        $xmlVersion       = (int)$baseNode['XMLVersion'];
        $licenceClass     = '';
        $licenceClassName = '';
        $state            = State::ACTIVATED;
        $tagsToFlush      = [];
        $basePath         = \PFAD_ROOT . \PFAD_PLUGIN . $this->dir . \DIRECTORY_SEPARATOR;
        $lastVersionKey   = null;
        $modern           = false;
        $plugin           = new \stdClass();
        $p                = null;
        // @todo:
        if (\is_array($versionNode)) {
            $lastVersionKey = \count($versionNode) / 2 - 1;
            $version        = (int)$versionNode[$lastVersionKey . ' attr']['nr'];
            $versionedDir   = $basePath . \PFAD_PLUGIN_VERSION . $version . \DIRECTORY_SEPARATOR;
            $loader         = new PluginLoader($this->db, \Shop::Container()->getCache());
        } else {
            $version      = $baseNode['Version'];
            $basePath     = \PFAD_ROOT . \PFAD_EXTENSIONS . $this->dir . \DIRECTORY_SEPARATOR;
            $versionedDir = $basePath;
            $versionNode  = [];
            $modern       = true;
            $loader       = new ExtensionLoader($this->db, \Shop::Container()->getCache());
        }
        $tags = empty($baseNode['Install'][0]['FlushTags'])
            ? []
            : \explode(',', $baseNode['Install'][0]['FlushTags']);
        if (isset($baseNode['LicenceClass'], $baseNode['LicenceClassFile'])
            && \strlen($baseNode['LicenceClass']) > 0
            && \strlen($baseNode['LicenceClassFile']) > 0
        ) {
            $licenceClass     = $baseNode['LicenceClass'];
            $licenceClassName = $baseNode['LicenceClassFile'];
            $state            = State::LICENSE_KEY_MISSING;
        }
        $plugin->bExtension           = (int)$modern;
        $plugin->cName                = $baseNode['Name'];
        $plugin->cBeschreibung        = $baseNode['Description'];
        $plugin->cAutor               = $baseNode['Author'];
        $plugin->cURL                 = $baseNode['URL'];
        $plugin->cIcon                = $baseNode['Icon'] ?? null;
        $plugin->cVerzeichnis         = $this->dir;
        $plugin->cPluginID            = $baseNode['PluginID'];
        $plugin->cStoreID             = $baseNode['StoreID'];
        $plugin->cFehler              = '';
        $plugin->cLizenz              = '';
        $plugin->cLizenzKlasse        = $licenceClass;
        $plugin->cLizenzKlasseName    = $licenceClassName;
        $plugin->nStatus              = $state;
        $plugin->nVersion             = $version;
        $plugin->nXMLVersion          = $xmlVersion;
        $plugin->nPrio                = 0;
        $plugin->dZuletztAktualisiert = 'NOW()';
        $plugin->dErstellt            = $lastVersionKey !== null
            ? $versionNode[$lastVersionKey]['CreateDate']
            : $baseNode['CreateDate'];
        $plugin->bBootstrap           = \is_file($versionedDir . 'bootstrap.php') ? 1 : 0;
        foreach ($tags as $_tag) {
            if (\defined(\trim($_tag))) {
                $tagsToFlush[] = \constant(\trim($_tag));
            }
        }
        if (\count($tagsToFlush) > 0) {
            \Shop::Container()->getCache()->flushTags($tagsToFlush);
        }
        $licenceClassFile = $versionedDir . \PFAD_PLUGIN_LICENCE . $plugin->cLizenzKlasseName;
        if ($this->plugin !== null
            && \is_file($licenceClassFile)
            && $this->plugin->getState() > 0
            && $this->plugin->getLicense()->hasLicense()
        ) {
            require_once $licenceClassFile;
            $pluginLicence = new $plugin->cLizenzKlasse();
            $licenceMethod = \PLUGIN_LICENCE_METHODE;
            if ($pluginLicence->$licenceMethod($this->plugin->getLicense()->getKey())) {
                $plugin->cLizenz = $this->plugin->getLicense()->getKey();
                $plugin->nStatus = $this->plugin->getState();
            }
        }
        $plugin->dInstalliert = ($this->plugin !== null && $this->plugin->getID() > 0)
            ? $this->plugin->getMeta()->getDateInstalled()->format('Y-m-d H:i:s')
            : 'NOW()';
        $kPlugin              = $this->db->insert('tplugin', $plugin);
        $plugin->kPlugin      = $kPlugin;
        if ($kPlugin <= 0) {
            return InstallCode::WRONG_PARAM;
        }

        $factory = $this->isExtension
            ? new PluginInstallerFactory($this->db, $xml, $plugin)
            : new ExtensionInstallerFactory($this->db, $xml, $plugin);
        $res     = $factory->install();
        if ($res !== InstallCode::OK) {
            $this->uninstaller->uninstall($kPlugin);

            return $res;
        }
        $hasSQLError = false;
        $code        = InstallCode::OK;
        foreach ($versionNode as $i => $versionData) {
            if ($version > 0
                && $this->plugin !== null
                && isset($versionData['nr'])
                && $this->plugin->getMeta()->getVersion() >= (int)$versionData['nr']
            ) {
                continue;
            }
            $i = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);

            if (!isset($hits1[0]) || \strlen($hits1[0]) !== \strlen($i)) {
                continue;
            }
            $nVersionTMP = (int)$versionData['nr'];
            $xy          = \trim(\str_replace('attr', '', $i));
            $sqlFile     = $versionNode[$xy]['SQL'] ?? '';
            if ($sqlFile === '') {
                continue;
            }
            $code = $this->validateSQL($sqlFile, $nVersionTMP, $plugin);
            if ($code !== InstallCode::OK) {
                $hasSQLError = true;
                break;
            }
        }
        if ($modern === true) {
            $this->updateByMigration($plugin, $versionedDir, Version::parse($version));
        }
        // Ist ein SQL Fehler aufgetreten? Wenn ja, deinstalliere wieder alles
        if ($hasSQLError) {
            $this->uninstaller->uninstall($plugin->kPlugin);
        }
        if ($code === InstallCode::OK
            && $this->plugin === null
            && ($p = Helper::bootstrap($plugin->kPlugin, $loader)) !== null
        ) {
            $p->installed();
        }
        if ($this->plugin !== null
            && ($code === InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE || $code === InstallCode::OK)
        ) {
            $code = $this->syncPluginUpdate($plugin->kPlugin);
            if (($p = Helper::bootstrap($this->plugin->getID(), $loader)) !== null) {
                $p->updated($this->plugin->getMeta()->getVersion(), $version);
            }
        }

        return $code;
    }

    /**
     * @param array $xml
     * @return array
     */
    private function getBaseNode(array $xml): array
    {
        return $xml['jtlshopplugin'][0] ?? $xml['jtlshop3plugin'][0];
    }

    /**
     * Geht die angegebene SQL durch und formatiert diese. Immer 1 SQL pro Zeile.
     *
     * @param string $sqlFile
     * @param string $pluginName
     * @param int    $pluginVersion
     * @return array
     */
    private function parseSQLFile(string $sqlFile, string $pluginName, $pluginVersion): array
    {
        $file = \PFAD_ROOT . \PFAD_PLUGIN . $pluginName . '/' .
            \PFAD_PLUGIN_VERSION . $pluginVersion . '/' .
            \PFAD_PLUGIN_SQL . $sqlFile;

        if (!\file_exists($file)) {
            return [];// SQL Datei existiert nicht
        }
        $handle   = \fopen($file, 'r');
        $sqlLines = [];
        $line     = '';
        while (($data = \fgets($handle)) !== false) {
            $data = \trim($data);
            if ($data !== '' && \strpos($data, '--') !== 0) {
                if (\strpos($data, 'CREATE TABLE') !== false) {
                    $line .= \trim($data);
                } elseif (\strpos($data, 'INSERT') !== false) {
                    $line .= \trim($data);
                } else {
                    $line .= \trim($data);
                }

                if (\substr($data, \strlen($data) - 1, 1) === ';') {
                    $sqlLines[] = $line;
                    $line       = '';
                }
            }
        }
        \fclose($handle);

        return $sqlLines;
    }

    /**
     * @param \stdClass $plugin
     * @param string    $pluginPath
     * @param Version   $targetVersion
     * @return array|Version
     * @throws \Exception
     */
    private function updateByMigration(\stdClass $plugin, string $pluginPath, Version $targetVersion)
    {
        $path              = $pluginPath . \DIRECTORY_SEPARATOR . \PFAD_PLUGIN_MIGRATIONS;
        $manager           = new MigrationManager($this->db, $path, $plugin->cPluginID, $targetVersion);
        $pendingMigrations = $manager->getPendingMigrations();
        if (\count($pendingMigrations) === 0) {
            return $targetVersion;
        }

        return $manager->migrate(null);
    }

    /**
     * @param string $sqlFile
     * @param int    $version
     * @return int
     * @throws \Exceptions\CircularReferenceException
     * @throws \Exceptions\ServiceNotFoundException
     * @former logikSQLDatei()
     */
    private function validateSQL(string $sqlFile, $version, \stdClass $plugin): int
    {
        if (empty($sqlFile)
            || (int)$version < 100
            || (int)$plugin->kPlugin <= 0
            || empty($plugin->cPluginID)
        ) {
            return InstallCode::SQL_MISSING_DATA;
        }
        $lines = $this->parseSQLFile($sqlFile, $plugin->cVerzeichnis, $version);
        if (\count($lines) === 0) {
            return InstallCode::SQL_INVALID_FILE_CONTENT;
        }
        $sqlRegEx = '/xplugin[_]{1}' . $plugin->cPluginID . '[_]{1}[a-zA-Z0-9_]+/';
        foreach ($lines as $sql) {
            $sql = \StringHandler::removeNumerousWhitespaces($sql);
            if (\stripos($sql, 'create table') !== false) {
                // when using "create table if not exists" statement, the table name is at index 5, otherwise at 2
                $index = (\stripos($sql, 'create table if not exists') !== false) ? 5 : 2;
                $tmp   = \explode(' ', $sql);
                $table = \str_replace(["'", '`'], '', $tmp[$index]);
                \preg_match($sqlRegEx, $table, $hits);
                if (!isset($hits[0]) || \strlen($hits[0]) !== \strlen($table)) {
                    return InstallCode::SQL_WRONG_TABLE_NAME_CREATE;
                }
                $exists = $this->db->select('tplugincustomtabelle', 'cTabelle', $table);
                if (!isset($exists->kPluginCustomTabelle) || !$exists->kPluginCustomTabelle) {
                    $customTable           = new \stdClass();
                    $customTable->kPlugin  = $plugin->kPlugin;
                    $customTable->cTabelle = $table;

                    $this->db->insert('tplugincustomtabelle', $customTable);
                }
            } elseif (\stripos($sql, 'drop table') !== false) {
                // SQL versucht eine Tabelle zu löschen => prüfen ob es sich um eine Plugintabelle handelt
                // when using "drop table if exists" statement, the table name is at index 5, otherwise at 2
                $index = (\stripos($sql, 'drop table if exists') !== false) ? 4 : 2;
                $tmp   = \explode(' ', \StringHandler::removeNumerousWhitespaces($sql));
                $table = \str_replace(["'", '`'], '', $tmp[$index]);
                \preg_match($sqlRegEx, $table, $hits);
                if (\strlen($hits[0]) !== \strlen($table)) {
                    return InstallCode::SQL_WRONG_TABLE_NAME_DELETE;
                }
            }

            $this->db->query($sql, ReturnType::DEFAULT);
            $errNo = $this->db->getErrorCode();
            if ($errNo) {
                \Shop::Container()->getLogService()->withName('kPlugin')->error(
                    'SQL Fehler beim Installieren des Plugins (' . $plugin->cName . '): ' .
                    \str_replace("'", '', $this->db->getErrorMessage()),
                    [$plugin->kPlugin]
                );

                return InstallCode::SQL_ERROR;
            }
        }

        return InstallCode::OK;
    }

    /**
     * Wenn ein Update erfolgreich mit neuer kPlugin in der Datenbank ist
     * wird der alte kPlugin auf die neue Version übertragen und
     * die alte Plugin-Version deinstalliert.
     *
     * @param int $pluginID
     * @return int
     * 1 = Alles O.K.
     * 2 = Übergabeparameter nicht korrekt
     * 3 = Update konnte nicht installiert werden
     */
    public function syncPluginUpdate(int $pluginID): int
    {
        $oldPluginID = $this->plugin->getID();
        $res         = $this->uninstaller->uninstall($oldPluginID, true, $pluginID);

        if ($res === InstallCode::OK) {
            $upd = (object)['kPlugin' => $oldPluginID];
            $this->db->update('tplugin', 'kPlugin', $pluginID, $upd);
            $this->db->update('tpluginhook', 'kPlugin', $pluginID, $upd);
            $this->db->update('tpluginadminmenu', 'kPlugin', $pluginID, $upd);
            $this->db->update('tpluginsprachvariable', 'kPlugin', $pluginID, $upd);
            $this->db->update('tadminwidgets', 'kPlugin', $pluginID, $upd);
            $this->db->update('tpluginsprachvariablecustomsprache', 'kPlugin', $pluginID, $upd);
            $this->db->update('tplugin_resources', 'kPlugin', $pluginID, $upd);
            $this->db->update('tplugincustomtabelle', 'kPlugin', $pluginID, $upd);
            $this->db->update('tplugintemplate', 'kPlugin', $pluginID, $upd);
            $this->db->update('tpluginlinkdatei', 'kPlugin', $pluginID, $upd);
            $this->db->update('tpluginemailvorlage', 'kPlugin', $pluginID, $upd);
            $this->db->update('texportformat', 'kPlugin', $pluginID, $upd);
            $this->db->update('topcportlet', 'kPlugin', $pluginID, $upd);
            $this->db->update('topcblueprint', 'kPlugin', $pluginID, $upd);
            $pluginConf = $this->db->query(
                'SELECT *
                    FROM tplugineinstellungen
                    WHERE kPlugin IN (' . $oldPluginID . ', ' . $pluginID . ')
                    ORDER BY kPlugin',
                ReturnType::ARRAY_OF_OBJECTS
            );
            if (\count($pluginConf) > 0) {
                $confData = [];
                foreach ($pluginConf as $conf) {
                    $name = \str_replace(
                        ['kPlugin_' . $oldPluginID . '_', 'kPlugin_' . $pluginID . '_'],
                        '',
                        $conf->cName
                    );
                    if (!isset($confData[$name])) {
                        $confData[$name]          = new \stdClass();
                        $confData[$name]->kPlugin = $oldPluginID;
                        $confData[$name]->cName   = \str_replace(
                            'kPlugin_' . $pluginID . '_',
                            'kPlugin_' . $oldPluginID . '_',
                            $conf->cName
                        );
                        $confData[$name]->cWert   = $conf->cWert;
                    }
                }
                $this->db->query(
                    'DELETE FROM tplugineinstellungen
                        WHERE kPlugin IN (' . $oldPluginID . ', ' . $pluginID . ')',
                    ReturnType::AFFECTED_ROWS
                );

                foreach ($confData as $value) {
                    $this->db->insert('tplugineinstellungen', $value);
                }
            }
            $this->db->query(
                'UPDATE tplugineinstellungen
                    SET kPlugin = ' . $oldPluginID . ",
                        cName = REPLACE(cName, 'kPlugin_" . $pluginID . "_', 'kPlugin_" . $oldPluginID . "_')
                    WHERE kPlugin = " . $pluginID,
                ReturnType::AFFECTED_ROWS
            );
            $this->db->query(
                'UPDATE tplugineinstellungenconf
                    SET kPlugin = ' . $oldPluginID . ",
                        cWertName = REPLACE(cWertName, 'kPlugin_" . $pluginID . "_', 'kPlugin_" . $oldPluginID . "_')
                    WHERE kPlugin = " . $pluginID,
                ReturnType::AFFECTED_ROWS
            );
            $this->db->update(
                'tboxvorlage',
                ['kCustomID', 'eTyp'],
                [$pluginID, 'plugin'],
                (object)['kCustomID' => $oldPluginID]
            );
            $this->db->query(
                'UPDATE tpluginzahlungsartklasse
                    SET kPlugin = ' . $oldPluginID . ",
                        cModulId = REPLACE(cModulId, 'kPlugin_" . $pluginID . "_', 'kPlugin_" . $oldPluginID . "_')
                    WHERE kPlugin = " . $pluginID,
                ReturnType::AFFECTED_ROWS
            );
            $oldMailTpl = $this->db->select('tpluginemailvorlage', 'kPlugin', $oldPluginID);
            $newMailTpl = $this->db->select('tpluginemailvorlage', 'kPlugin', $pluginID);
            if (isset($newMailTpl->kEmailvorlage, $oldMailTpl->kEmailvorlage)) {
                $this->db->update(
                    'tpluginemailvorlageeinstellungen',
                    'kEmailvorlage',
                    $oldMailTpl->kEmailvorlage,
                    (object)['kEmailvorlage' => $newMailTpl->kEmailvorlage]
                );
            }
            $kEmailvorlageNeu = 0;
            $kEmailvorlageAlt = 0;
            foreach ($this->plugin->getMailTemplates()->getTemplatesAssoc() as $cModulId => $oldMailTpl) {
                $newMailTpl = $this->db->select(
                    'tpluginemailvorlage',
                    'kPlugin',
                    $oldPluginID,
                    'cModulId',
                    $cModulId,
                    null,
                    null,
                    false,
                    'kEmailvorlage'
                );
                if (isset($newMailTpl->kEmailvorlage) && $newMailTpl->kEmailvorlage > 0) {
                    if ($kEmailvorlageNeu === 0 || $kEmailvorlageAlt === 0) {
                        $kEmailvorlageNeu = (int)$newMailTpl->kEmailvorlage;
                        $kEmailvorlageAlt = (int)$oldMailTpl->kEmailvorlage;
                    }
                    $this->db->update(
                        'tpluginemailvorlagesprache',
                        'kEmailvorlage',
                        $oldMailTpl->kEmailvorlage,
                        (object)['kEmailvorlage' => $newMailTpl->kEmailvorlage]
                    );
                }
            }
            $upd = (object)['kEmailvorlage' => $kEmailvorlageNeu];
            $this->db->update('tpluginemailvorlageeinstellungen', 'kEmailvorlage', $kEmailvorlageAlt, $upd);
            $this->db->update('tlink', 'kPlugin', $pluginID, (object)['kPlugin' => $oldPluginID]);
            // tboxen
            // Ausnahme: Gibt es noch eine Boxenvorlage in der Pluginversion?
            // Falls nein -> lösche tboxen mit dem entsprechenden kPlugin
            $oObj = $this->db->select('tboxvorlage', 'kCustomID', $oldPluginID, 'eTyp', 'plugin');
            if (isset($oObj->kBoxvorlage) && (int)$oObj->kBoxvorlage > 0) {
                $upd              = new \stdClass();
                $upd->kBoxvorlage = $oObj->kBoxvorlage;
                $this->db->update('tboxen', 'kCustomID', $oldPluginID, $upd);
            } else {
                $this->db->delete('tboxen', 'kCustomID', $oldPluginID);
            }
            $upd = (object)['kPlugin' => $oldPluginID];
            $this->db->update('tcheckboxfunktion', 'kPlugin', $pluginID, $upd);
            $this->db->update('tspezialseite', 'kPlugin', $pluginID, $upd);
            $oldPaymentMethods = $this->db->queryPrepared(
                'SELECT kZahlungsart, cModulId
                    FROM tzahlungsart
                    WHERE cModulId LIKE :newID',
                ['newID' => 'kPlugin_' . $oldPluginID . '_%'],
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($oldPaymentMethods as $method) {
                $oldModuleID      = \str_replace(
                    'kPlugin_' . $oldPluginID . '_',
                    'kPlugin_' . $pluginID . '_',
                    $method->cModulId
                );
                $newPaymentMethod = $this->db->queryPrepared(
                    'SELECT kZahlungsart
                        FROM tzahlungsart
                        WHERE cModulId LIKE :oldID',
                    ['oldID' => $oldModuleID],
                    ReturnType::SINGLE_OBJECT
                );
                $setSQL           = '';
                if (isset($method->kZahlungsart, $newPaymentMethod->kZahlungsart)) {
                    $this->db->query(
                        'DELETE tzahlungsart, tzahlungsartsprache
                            FROM tzahlungsart
                            JOIN tzahlungsartsprache
                                ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                            WHERE tzahlungsart.kZahlungsart = ' . $method->kZahlungsart,
                        ReturnType::AFFECTED_ROWS
                    );

                    $setSQL = ' , kZahlungsart = ' . $method->kZahlungsart;
                    $upd    = (object)['kZahlungsart' => $method->kZahlungsart];
                    $this->db->update('tzahlungsartsprache', 'kZahlungsart', $newPaymentMethod->kZahlungsart, $upd);
                }

                $this->db->queryPrepared(
                    'UPDATE tzahlungsart
                        SET cModulId = :newID ' . $setSQL . '
                        WHERE cModulId LIKE :oldID',
                    ['oldID' => $oldModuleID, 'newID' => $method->cModulId],
                    ReturnType::AFFECTED_ROWS
                );
            }

            return InstallCode::OK;
        }
        $this->uninstaller->uninstall($pluginID);

        return InstallCode::SQL_ERROR;
    }
}
