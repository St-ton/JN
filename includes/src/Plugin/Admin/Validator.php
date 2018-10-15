<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

use DB\DbInterface;
use DB\ReturnType;
use JTLShop\SemVer\Version;
use Plugin\InstallCode;

/**
 * Class Validator
 * @package Plugin\Admin
 */
final class Validator
{
    private const BASE_DIR = \PFAD_ROOT . \PFAD_PLUGIN;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var string
     */
    private $dir;

    /**
     * Validator constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
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
        $this->dir = \strpos($dir, self::BASE_DIR) === 0
            ? $dir
            : self::BASE_DIR . $dir;
    }

    /**
     * @param int  $kPlugin
     * @param bool $forUpdate
     * @return int
     */
    public function validateByPluginID(int $kPlugin, bool $forUpdate = false): int
    {
        $plugin = $this->db->select('tplugin', 'kPlugin', $kPlugin);
        if (empty($plugin->kPlugin)) {
            return InstallCode::NO_PLUGIN_FOUND;
        }
        $dir = self::BASE_DIR . $plugin->cVerzeichnis;
        $this->setDir($dir);
        if (!\is_dir($dir)) {
            return InstallCode::DIR_DOES_NOT_EXIST;
        }
        $info = $dir . '/' . \PLUGIN_INFO_FILE;
        if (!\file_exists($info)) {
            return InstallCode::INFO_XML_MISSING;
        }
        $xml = \file_get_contents($info);

        return $this->pluginPlausiIntern(\getArrangedArray(\XML_unserialize($xml)), $forUpdate);
    }

    /**
     * @param string $path
     * @param bool   $forUpdate
     * @return int
     */
    public function validateByPath(string $path, bool $forUpdate = false): int
    {
        $this->setDir($path);
        if (empty($this->dir)) {
            return InstallCode::WRONG_PARAM;
        }
        // Plugin wird anhand des Verzeichnisses geprüft
        if (!\is_dir($this->dir)) {
            return InstallCode::DIR_DOES_NOT_EXIST;
        }
        $cInfofile = "{$this->dir}/" . \PLUGIN_INFO_FILE;
        if (!\file_exists($cInfofile)) {
            return InstallCode::INFO_XML_MISSING;
        }
        $xml     = \file_get_contents($cInfofile);
        $xmlData = \XML_unserialize($xml);
        $xmlData = \getArrangedArray($xmlData);

        return $this->pluginPlausiIntern($xmlData, $forUpdate);
    }

    /**
     * @param      $XML_arr
     * @param bool $forUpdate
     * @return int
     * @former pluginPlausiIntern()
     */
    public function pluginPlausiIntern($XML_arr, bool $forUpdate): int
    {
        $cVersionsnummer        = '';
        $isShop4Compatible      = false;
        $requiresMissingIoncube = false;
        $parsedXMLShopVersion   = null;
        $parsedVersion          = null;
        $baseNode               = $XML_arr['jtlshop3plugin'][0];
        $oVersion               = $this->db->query('SELECT nVersion FROM tversion LIMIT 1', ReturnType::SINGLE_OBJECT);

        if ($oVersion->nVersion > 0) {
            $parsedVersion = Version::parse($oVersion->nVersion);
        }
        if (!isset($baseNode['XMLVersion'])) {
            return InstallCode::INVALID_XML_VERSION;
        }
        \preg_match('/[0-9]{3}/', $baseNode['XMLVersion'], $hits);
        if (\count($hits) === 0
            || (\strlen($hits[0]) !== \strlen($baseNode['XMLVersion']) && (int)$baseNode['XMLVersion'] >= 100)
        ) {
            return InstallCode::INVALID_XML_VERSION;
        }
        $nXMLVersion = (int)$XML_arr['jtlshop3plugin'][0]['XMLVersion'];
        if (empty($baseNode['ShopVersion']) && empty($baseNode['Shop4Version'])) {
            return InstallCode::INVALID_SHOP_VERSION;
        }
        if ($forUpdate === false) {
            $oPluginTMP = $this->db->select('tplugin', 'cPluginID', $baseNode['PluginID']);
            if (isset($oPluginTMP->kPlugin) && $oPluginTMP->kPlugin > 0) {
                return InstallCode::DUPLICATE_PLUGIN_ID;
            }
        }
        if ((isset($baseNode['ShopVersion'])
                && \strlen($hits[0]) !== \strlen($baseNode['ShopVersion'])
                && (int)$baseNode['ShopVersion'] >= 300)
            || (isset($baseNode['Shop4Version'])
                && \strlen($hits[0]) !== \strlen($baseNode['Shop4Version'])
                && (int)$baseNode['Shop4Version'] >= 300)
        ) {
            return InstallCode::INVALID_SHOP_VERSION; //Shop-Version entspricht nicht der Konvention
        }
        if (isset($baseNode['Shop4Version'])) {
            $parsedXMLShopVersion = Version::parse($baseNode['Shop4Version']);
            $isShop4Compatible    = true;
        } else {
            $parsedXMLShopVersion = Version::parse($baseNode['ShopVersion']);
        }
        $installNode = $baseNode['Install'][0];
        //check if plugin need ioncube loader but extension is not loaded
        if (isset($baseNode['LicenceClassFile']) && !\extension_loaded('ionCube Loader')) {
            //ioncube is not loaded
            $nLastVersionKey    = \count($installNode['Version']) / 2 - 1;
            $nLastPluginVersion = (int)$installNode['Version'][$nLastVersionKey . ' attr']['nr'];
            if (\file_exists($this->dir . '/' . \PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/' .
                \PFAD_PLUGIN_LICENCE . $baseNode['LicenceClassFile'])
            ) {
                $content = \file_get_contents($this->dir . '/' .
                    \PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/' .
                    \PFAD_PLUGIN_LICENCE . $baseNode['LicenceClassFile']);
                // ioncube encoded files usually have a header that checks loaded extions itself
                // but it can also be in short form, where there are no opening php tags
                $requiresMissingIoncube = ((\strpos($content, 'ionCube') !== false
                        && \strpos($content, 'extension_loaded') !== false)
                    || \strpos($content, '<?php') === false);
            }
        }
        // Shop-Version ausreichend?
        if (empty($parsedVersion)
            || empty($parsedXMLShopVersion)
            || $parsedXMLShopVersion->greaterThan($parsedVersion)
        ) {
            return InstallCode::SHOP_VERSION_COMPATIBILITY; //Shop-Version ist zu niedrig
        }
        if (!isset($baseNode['Author'])) {
            return InstallCode::INVALID_AUTHOR;
        }
        // Prüfe Pluginname
        if (!isset($baseNode['Name'])) {
            return InstallCode::INVALID_NAME;
        }
        \preg_match(
            '/[a-zA-Z0-9äÄüÜöÖß' . '\(\)_ -]+/',
            $baseNode['Name'],
            $hits
        );
        if (!isset($hits[0]) || \strlen($hits[0]) !== \strlen($baseNode['Name'])) {
            return InstallCode::INVALID_NAME;
        }
        // Prüfe PluginID
        \preg_match('/[\w_]+/', $baseNode['PluginID'], $hits);
        if (empty($baseNode['PluginID']) || \strlen($hits[0]) !== \strlen($baseNode['PluginID'])) {
            return InstallCode::INVALID_PLUGIN_ID;
        }
        if (!isset($installNode['Version']) || !\is_array($installNode['Version'])) {
            return InstallCode::INVALID_VERSION_NUMBER;
        }
        $nLastVersionKey    = \count($installNode['Version']) / 2 - 1;
        $nLastPluginVersion = (int)$installNode['Version'][$nLastVersionKey . ' attr']['nr'];
        $currentVersionDir  = $this->dir . '/' . \PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/';
        if (isset($baseNode['LicenceClassFile']) && \strlen($baseNode['LicenceClassFile']) > 0) {
            // Existiert die Lizenzdatei?
            if (!\file_exists($currentVersionDir . \PFAD_PLUGIN_LICENCE . $baseNode['LicenceClassFile'])) {
                return InstallCode::MISSING_LICENCE_FILE;
            }
            // Klassenname gesetzt?
            if (empty($baseNode['LicenceClass'])
                || $baseNode['LicenceClass'] !== $baseNode['PluginID'] . \PLUGIN_LICENCE_CLASS
            ) {
                return InstallCode::INVALID_LICENCE_FILE_NAME;
            }
            if ($requiresMissingIoncube) {
                return InstallCode::IONCUBE_REQUIRED;
            }
            require_once $currentVersionDir . \PFAD_PLUGIN_LICENCE . $baseNode['LicenceClassFile'];
            if (!\class_exists($baseNode['LicenceClass'])) {
                return InstallCode::MISSING_LICENCE;
            }
            $cClassMethod_arr = \get_class_methods($baseNode['LicenceClass']);
            $bClassMethod     = \is_array($cClassMethod_arr)
                && \in_array(\PLUGIN_LICENCE_METHODE, $cClassMethod_arr, true);
            if (!$bClassMethod) {
                return InstallCode::MISSING_LICENCE_CHECKLICENCE_METHOD;
            }
        }
        // Prüfe Bootstrapper
        $cBootstrapNamespace = $baseNode['PluginID'];
        $cBootstrapClassFile = $currentVersionDir . \PLUGIN_BOOTSTRAPPER;
        if ($forUpdate === false && \is_file($cBootstrapClassFile)) {
            $cClass = \sprintf('%s\\%s', $cBootstrapNamespace, 'Bootstrap');

            require_once $cBootstrapClassFile;

            if (!\class_exists($cClass)) {
                return InstallCode::MISSING_BOOTSTRAP_CLASS;
            }

            $bootstrapper = new $cClass((object)['cPluginID' => $cBootstrapNamespace]);

            if (!\is_subclass_of($bootstrapper, 'AbstractPlugin')) {
                return InstallCode::INVALID_BOOTSTRAP_IMPLEMENTATION;
            }
        }
        // Prüfe Install Knoten
        if (!isset($baseNode['Install']) || !\is_array($baseNode['Install'])) {
            return InstallCode::INSTALL_NODE_MISSING;
        }
        // Versionen definiert?
        if (isset($installNode['Version'])
            && \is_array($installNode['Version'])
            && \count($installNode['Version']) > 0
        ) {
            // Ist die 1. Versionsnummer korrekt?
            if ((int)$installNode['Version']['0 attr']['nr'] !== 100) {
                return InstallCode::INVALID_XML_VERSION_NUMBER;
            }
            foreach ($installNode['Version'] as $i => $Version) {
                \preg_match('/[0-9]+\sattr/', $i, $hits1);
                \preg_match('/[0-9]+/', $i, $hits2);
                if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($i)) {
                    $cVersionsnummer = $Version['nr'];
                    // Entpricht die Versionsnummer
                    \preg_match('/[0-9]+/', $Version['nr'], $hits);
                    if (\strlen($hits[0]) !== \strlen($Version['nr'])) {
                        return InstallCode::INVALID_VERSION_NUMBER;
                    }
                } elseif (\strlen($hits2[0]) === \strlen($i)) {
                    // Prüfe SQL und CreateDate
                    if (isset($Version['SQL'])
                        && \strlen($Version['SQL']) > 0
                        && !\file_exists($this->dir . '/' . \PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                            \PFAD_PLUGIN_SQL . $Version['SQL'])
                    ) {
                        return InstallCode::MISSING_SQL_FILE;
                    }
                    // Prüfe Versionsordner
                    if (!\is_dir($this->dir . '/' . \PFAD_PLUGIN_VERSION . $cVersionsnummer)) {
                        return InstallCode::MISSING_VERSION_DIR;
                    }
                    \preg_match(
                        '/[0-9]{4}-[0-1]{1}[0-9]{1}-[0-3]{1}[0-9]{1}/',
                        $Version['CreateDate'],
                        $hits
                    );
                    if (!isset($hits[0]) || \strlen($hits[0]) !== \strlen($Version['CreateDate'])) {
                        return InstallCode::INVALID_DATE;
                    }
                }
            }
        }
        $currentVersionDir = $this->dir . '/' . \PFAD_PLUGIN_VERSION . $cVersionsnummer . '/';
        // Auf Hooks prüfen
        if (isset($installNode['Hooks']) && \is_array($installNode['Hooks'])) {
            if (\count($installNode['Hooks'][0]) === 1) {
                //Es gibt mehr als einen Hook
                foreach ($installNode['Hooks'][0]['Hook'] as $i => $Hook_arr) {
                    \preg_match('/[0-9]+\sattr/', $i, $hits1);
                    \preg_match('/[0-9]+/', $i, $hits2);
                    if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($i)) {
                        if (\strlen($Hook_arr['id']) === 0) {
                            return InstallCode::INVALID_HOOK;
                        }
                    } elseif (isset($hits2[0]) && \strlen($hits2[0]) === \strlen($i)) {
                        if (\strlen($Hook_arr) === 0) {
                            return InstallCode::INVALID_HOOK;
                        }
                        //Hook include Datei vorhanden?
                        if (!\file_exists($this->dir . '/' .
                            \PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                            \PFAD_PLUGIN_FRONTEND . $Hook_arr)
                        ) {
                            return InstallCode::MISSING_HOOK_FILE;
                        }
                    }
                }
            } elseif (\count($installNode['Hooks'][0]) > 1) {
                //Es gibt nur einen Hook
                $Hook_arr = $installNode['Hooks'][0];
                //Hook-Name und ID prüfen
                if ((int)$Hook_arr['Hook attr']['id'] === 0 || \strlen($Hook_arr['Hook']) === 0) {
                    return InstallCode::INVALID_HOOK;
                }
                //Hook include Datei vorhanden?
                if (!\file_exists($currentVersionDir . \PFAD_PLUGIN_FRONTEND . $Hook_arr['Hook'])) {
                    return InstallCode::MISSING_HOOK_FILE;
                }
            }
        }
        // Adminmenü & Einstellungen (falls vorhanden)
        if (isset($installNode['Adminmenu']) && \is_array($installNode['Adminmenu'])) {
            //Adminsmenüs vorhanden?
            if (isset($installNode['Adminmenu'][0]['Customlink'])
                && \is_array($installNode['Adminmenu'][0]['Customlink'])
                && \count($installNode['Adminmenu'][0]['Customlink']) > 0
            ) {
                foreach ($installNode['Adminmenu'][0]['Customlink'] as $i => $Customlink_arr) {
                    \preg_match('/[0-9]+\sattr/', $i, $hits1);
                    \preg_match('/[0-9]+/', $i, $hits2);
                    if (\strlen($hits2[0]) === \strlen($i)) {
                        // Name prüfen
                        \preg_match(
                            '/[a-zA-Z0-9äÄüÜöÖß' . "\_\- ]+/",
                            $Customlink_arr['Name'],
                            $hits
                        );
                        if (\strlen($hits[0]) !== \strlen($Customlink_arr['Name'])
                            || empty($Customlink_arr['Name'])
                        ) {
                            return InstallCode::INVALID_CUSTOM_LINK_NAME;
                        }
                        if (empty($Customlink_arr['Filename'])) {
                            return InstallCode::INVALID_CUSTOM_LINK_FILE_NAME;
                        }
                        if (!\file_exists($currentVersionDir . \PFAD_PLUGIN_ADMINMENU . $Customlink_arr['Filename'])) {
                            return InstallCode::MISSING_CUSTOM_LINK_FILE;
                        }
                    }
                }
            }
            // Einstellungen vorhanden?
            if (isset($installNode['Adminmenu'][0]['Settingslink'])
                && \is_array($installNode['Adminmenu'][0]['Settingslink'])
                && \count($installNode['Adminmenu'][0]['Settingslink']) > 0
            ) {
                foreach ($installNode['Adminmenu'][0]['Settingslink'] as $i => $settingsLink) {
                    \preg_match('/[0-9]+\sattr/', $i, $hits1);
                    \preg_match('/[0-9]+/', $i, $hits2);
                    if (\strlen($hits2[0]) === \strlen($i)) {
                        // EinstellungsLink Name prüfen
                        if (empty($settingsLink['Name'])) {
                            return InstallCode::INVALID_CONFIG_LINK_NAME;
                        }
                        // Einstellungen prüfen
                        $type = '';
                        if (!isset($settingsLink['Setting'])
                            || !\is_array($settingsLink['Setting'])
                            || \count($settingsLink['Setting']) === 0
                        ) {
                            return InstallCode::MISSING_CONFIG;
                        }
                        foreach ($settingsLink['Setting'] as $j => $setting) {
                            \preg_match('/[0-9]+\sattr/', $j, $hits3);
                            \preg_match('/[0-9]+/', $j, $hits4);

                            if (isset($hits3[0]) && \strlen($hits3[0]) === \strlen($j)) {
                                $type = $setting['type'];

                                // Einstellungen type prüfen
                                if (\strlen($setting['type']) === 0) {
                                    return InstallCode::INVALID_CONFIG_TYPE;
                                }
                                // Einstellungen initialValue prüfen
                                //if(\strlen($Setting_arr['initialValue']) == 0)
                                //return 21;  // Einstellungen initialValue entspricht nicht der Konvention

                                // Einstellungen sort prüfen
                                if (\strlen($setting['sort']) === 0) {
                                    return InstallCode::INVALID_CONFIG_SORT_VALUE;
                                }
                                // Einstellungen conf prüfen
                                if (\strlen($setting['conf']) === 0) {
                                    return InstallCode::INVALID_CONF;
                                }
                            } elseif (\strlen($hits4[0]) === \strlen($j)) {
                                // Einstellungen Name prüfen
                                if (\strlen($setting['Name']) === 0) {
                                    return InstallCode::INVALID_CONFIG_NAME;
                                }
                                // Einstellungen ValueName prüfen
                                if (!isset($setting['ValueName'])
                                    || !\is_string($setting['ValueName'])
                                    || \strlen($setting['ValueName']) === 0
                                ) {
                                    return InstallCode::INVALID_CONF_VALUE_NAME;
                                }
                                // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                                if ($type === 'selectbox') {
                                    // SelectboxOptions prüfen
                                    if (isset($setting['OptionsSource'])
                                        && \is_array($setting['OptionsSource'])
                                        && \count($setting['OptionsSource']) > 0
                                    ) {
                                        if (empty($setting['OptionsSource'][0]['File'])) {
                                            return InstallCode::INVALID_OPTIONS_SOURE_FILE;
                                        }
                                        if (!\file_exists($currentVersionDir .
                                            \PFAD_PLUGIN_ADMINMENU .
                                            $setting['OptionsSource'][0]['File'])
                                        ) {
                                            return InstallCode::MISSING_OPTIONS_SOURE_FILE;
                                        }
                                    } elseif (isset($setting['SelectboxOptions'])
                                        && \is_array($setting['SelectboxOptions'])
                                        && \count($setting['SelectboxOptions']) > 0
                                    ) {
                                        // Es gibt mehr als 1 Option
                                        if (\count($setting['SelectboxOptions'][0]) === 1) {
                                            foreach ($setting['SelectboxOptions'][0]['Option'] as $y => $Option_arr) {
                                                \preg_match('/[0-9]+\sattr/', $y, $hits6);
                                                \preg_match('/[0-9]+/', $y, $hits7);

                                                if (isset($hits6[0]) && \strlen($hits6[0]) === \strlen($y)) {
                                                    if (\strlen($Option_arr['value']) === 0) {
                                                        return InstallCode::INVALID_CONFIG_OPTION;
                                                    }
                                                    if (\strlen($Option_arr['sort']) === 0) {
                                                        return InstallCode::INVALID_CONFIG_OPTION;
                                                    }
                                                } elseif (\strlen($hits7[0]) === \strlen($y)) {
                                                    // Name prüfen
                                                    if (\strlen($Option_arr) === 0) {
                                                        return InstallCode::INVALID_CONFIG_OPTION;
                                                    }
                                                }
                                            }
                                        } elseif (\count($setting['SelectboxOptions'][0]) === 2) {
                                            // Es gibt nur 1 Option
                                            if (\strlen($setting['SelectboxOptions'][0]['Option attr']['value']) === 0) {
                                                return InstallCode::INVALID_CONFIG_OPTION;
                                            }
                                            if (\strlen($setting['SelectboxOptions'][0]['Option attr']['sort']) === 0) {
                                                return InstallCode::INVALID_CONFIG_OPTION;
                                            }
                                            if (\strlen($setting['SelectboxOptions'][0]['Option']) === 0) {
                                                return InstallCode::INVALID_CONFIG_OPTION;
                                            }
                                        }
                                    } else {
                                        return InstallCode::MISSING_CONFIG_SELECTBOX_OPTIONS;
                                    }
                                } elseif ($type === 'radio') {
                                    //radioOptions prüfen
                                    if (isset($setting['OptionsSource'])
                                        && \is_array($setting['OptionsSource'])
                                        && \count($setting['OptionsSource']) > 0
                                    ) {
                                        //do nothing for now
                                    } elseif (isset($setting['RadioOptions'])
                                        && \is_array($setting['RadioOptions'])
                                        && \count($setting['RadioOptions']) > 0
                                    ) {
                                        // Es gibt mehr als 1 Option
                                        if (\count($setting['RadioOptions'][0]) === 1) {
                                            foreach ($setting['RadioOptions'][0]['Option'] as $y => $Option_arr) {
                                                \preg_match('/[0-9]+\sattr/', $y, $hits6);
                                                \preg_match('/[0-9]+/', $y, $hits7);
                                                if (isset($hits6[0]) && \strlen($hits6[0]) === \strlen($y)) {
                                                    if (\strlen($Option_arr['value']) === 0) {
                                                        return InstallCode::INVALID_CONFIG_OPTION;
                                                    }
                                                    if (\strlen($Option_arr['sort']) === 0) {
                                                        return InstallCode::INVALID_CONFIG_OPTION;
                                                    }
                                                } elseif (\strlen($hits7[0]) === \strlen($y)) {
                                                    if (\strlen($Option_arr) === 0) {
                                                        return InstallCode::INVALID_CONFIG_OPTION;
                                                    }
                                                }
                                            }
                                        } elseif (\count($setting['RadioOptions'][0]) === 2) {
                                            // Es gibt nur 1 Option
                                            if (\strlen($setting['RadioOptions'][0]['Option attr']['value']) === 0) {
                                                return InstallCode::INVALID_CONFIG_OPTION;
                                            }
                                            if (\strlen($setting['RadioOptions'][0]['Option attr']['sort']) === 0) {
                                                return InstallCode::INVALID_CONFIG_OPTION;
                                            }
                                            if (\strlen($setting['RadioOptions'][0]['Option']) === 0) {
                                                return InstallCode::INVALID_CONFIG_OPTION;
                                            }
                                        }
                                    } else {
                                        return InstallCode::MISSING_CONFIG_SELECTBOX_OPTIONS;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        // FrontendLinks (falls vorhanden)
        if (isset($installNode['FrontendLink']) && \is_array($installNode['FrontendLink'])) {
            // Links prüfen
            if (!isset($installNode['FrontendLink'][0]['Link'])
                || !\is_array($installNode['FrontendLink'][0]['Link'])
                || \count($installNode['FrontendLink'][0]['Link']) === 0
            ) {
                return InstallCode::MISSING_FRONTEND_LINKS;
            }
            foreach ($installNode['FrontendLink'][0]['Link'] as $u => $Link_arr) {
                \preg_match('/[0-9]+\sattr/', $u, $hits1);
                \preg_match('/[0-9]+/', $u, $hits2);

                if (\strlen($hits2[0]) !== \strlen($u)) {
                    continue;
                }
                // Filename prüfen
                if (\strlen($Link_arr['Filename']) === 0) {
                    return InstallCode::INVALID_FRONTEND_LINK_FILENAME;
                }
                // LinkName prüfen
                \preg_match(
                    "/[a-zA-Z0-9äÄöÖüÜß" . "\_\- ]+/",
                    $Link_arr['Name'],
                    $hits1
                );
                if (\strlen($hits1[0]) !== \strlen($Link_arr['Name'])) {
                    return InstallCode::INVALID_FRONTEND_LINK_NAME;
                }
                // Templatename UND Fullscreen Templatename vorhanden?
                // Es darf nur entweder oder geben
                if (isset($Link_arr['Template'], $Link_arr['FullscreenTemplate'])
                    && \strlen($Link_arr['Template']) > 0
                    && \strlen($Link_arr['FullscreenTemplate']) > 0
                ) {
                    return InstallCode::TOO_MANY_FULLSCREEN_TEMPLATE_NAMES;
                }
                // Templatename prüfen
                if (!isset($Link_arr['FullscreenTemplate'])
                    || \strlen($Link_arr['FullscreenTemplate']) === 0
                ) {
                    if (\strlen($Link_arr['Template']) === 0) {
                        return InstallCode::INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE;
                    }
                    \preg_match("/[a-zA-Z0-9\/_\-.]+.tpl/", $Link_arr['Template'], $hits1);
                    if (\strlen($hits1[0]) === \strlen($Link_arr['Template'])) {
                        if (!\file_exists($currentVersionDir .
                            \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_TEMPLATE . $Link_arr['Template'])
                        ) {
                            return InstallCode::MISSING_FRONTEND_LINK_TEMPLATE;
                        }
                    } else {
                        return InstallCode::INVALID_FULLSCREEN_TEMPLATE;
                    }
                }
                // Fullscreen Templatename prüfen
                if (!isset($Link_arr['Template']) || \strlen($Link_arr['Template']) === 0) {
                    if (\strlen($Link_arr['FullscreenTemplate']) === 0) {
                        return InstallCode::INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE;
                    }
                    \preg_match("/[a-zA-Z0-9\/_\-.]+.tpl/", $Link_arr['FullscreenTemplate'], $hits1);
                    if (\strlen($hits1[0]) === \strlen($Link_arr['FullscreenTemplate'])) {
                        if (!\file_exists($currentVersionDir .
                            \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_TEMPLATE . $Link_arr['FullscreenTemplate'])
                        ) {
                            return InstallCode::MISSING_FULLSCREEN_TEMPLATE_FILE;
                        }
                    } else {
                        return InstallCode::INVALID_FULLSCREEN_TEMPLATE_NAME;
                    }
                }
                // Angabe ob erst Sichtbar nach Login prüfen
                \preg_match("/[NY]{1,1}/", $Link_arr['VisibleAfterLogin'], $hits2);
                if (\strlen($hits2[0]) !== \strlen($Link_arr['VisibleAfterLogin'])) {
                    return InstallCode::INVALID_FRONEND_LINK_VISIBILITY;
                }
                // Abgabe ob ein Druckbutton gezeigt werden soll prüfen
                \preg_match("/[NY]{1,1}/", $Link_arr['PrintButton'], $hits3);
                if (\strlen($hits3[0]) !== \strlen($Link_arr['PrintButton'])) {
                    return InstallCode::INVALID_FRONEND_LINK_PRINT;
                }
                // Abgabe ob NoFollow Attribut gezeigt werden soll prüfen
                if (isset($Link_arr['NoFollow'])) {
                    \preg_match("/[NY]{1,1}/", $Link_arr['NoFollow'], $hits3);
                } else {
                    $hits3 = [];
                }
                if (isset($hits3[0]) && \strlen($hits3[0]) !== \strlen($Link_arr['NoFollow'])) {
                    return InstallCode::INVALID_FRONTEND_LINK_NO_FOLLOW;
                }
                // LinkSprachen prüfen
                if (!isset($Link_arr['LinkLanguage'])
                    || !\is_array($Link_arr['LinkLanguage'])
                    || \count($Link_arr['LinkLanguage']) === 0
                ) {
                    return InstallCode::INVALID_FRONEND_LINK_ISO;
                }
                foreach ($Link_arr['LinkLanguage'] as $l => $LinkLanguage_arr) {
                    \preg_match('/[0-9]+\sattr/', $l, $hits1);
                    \preg_match('/[0-9]+/', $l, $hits2);
                    if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($l)) {
                        // ISO prüfen
                        \preg_match("/[A-Z]{3}/", $LinkLanguage_arr['iso'], $hits);
                        if (\strlen($LinkLanguage_arr['iso']) === 0
                            || \strlen($hits[0]) !== \strlen($LinkLanguage_arr['iso'])
                        ) {
                            return InstallCode::INVALID_FRONEND_LINK_ISO;
                        }
                    } elseif (\strlen($hits2[0]) === \strlen($l)) {
                        // Seo prüfen
                        \preg_match("/[a-zA-Z0-9- ]+/", $LinkLanguage_arr['Seo'], $hits1);
                        if (\strlen($LinkLanguage_arr['Seo']) === 0
                            || \strlen($hits1[0]) !== \strlen($LinkLanguage_arr['Seo'])
                        ) {
                            return InstallCode::INVALID_FRONEND_LINK_SEO;
                        }
                        // Name prüfen
                        \preg_match(
                            "/[a-zA-Z0-9äÄüÜöÖß" . "\- ]+/",
                            $LinkLanguage_arr['Name'],
                            $hits1
                        );
                        if (\strlen($LinkLanguage_arr['Name']) === 0
                            || \strlen($hits1[0]) !== \strlen($LinkLanguage_arr['Name'])
                        ) {
                            return InstallCode::INVALID_FRONEND_LINK_NAME;
                        }
                        // Title prüfen
                        \preg_match(
                            "/[a-zA-Z0-9äÄüÜöÖß" . "\- ]+/",
                            $LinkLanguage_arr['Title'],
                            $hits1
                        );
                        if (\strlen($LinkLanguage_arr['Title']) === 0
                            || \strlen($hits1[0]) !== \strlen($LinkLanguage_arr['Title'])
                        ) {
                            return InstallCode::INVALID_FRONEND_LINK_TITLE;
                        }
                        // MetaTitle prüfen
                        \preg_match(
                            "/[a-zA-Z0-9äÄüÜöÖß" . "\,\.\- ]+/",
                            $LinkLanguage_arr['MetaTitle'],
                            $hits1
                        );
                        if (\strlen($LinkLanguage_arr['MetaTitle']) === 0
                            && \strlen($hits1[0]) !== \strlen($LinkLanguage_arr['MetaTitle'])
                        ) {
                            return InstallCode::INVALID_FRONEND_LINK_META_TITLE;
                        }
                        // MetaKeywords prüfen
                        \preg_match(
                            "/[a-zA-Z0-9äÄüÜöÖß" . "\,\- ]+/",
                            $LinkLanguage_arr['MetaKeywords'],
                            $hits1
                        );
                        if (\strlen($LinkLanguage_arr['MetaKeywords']) === 0
                            || \strlen($hits1[0]) !== \strlen($LinkLanguage_arr['MetaKeywords'])
                        ) {
                            return InstallCode::INVALID_FRONEND_LINK_META_KEYWORDS;
                        }
                        // MetaDescription prüfen
                        \preg_match(
                            "/[a-zA-Z0-9äÄüÜöÖß" . "\,\.\- ]+/",
                            $LinkLanguage_arr['MetaDescription'],
                            $hits1
                        );
                        if (\strlen($LinkLanguage_arr['MetaDescription']) === 0
                            || \strlen($hits1[0]) !== \strlen($LinkLanguage_arr['MetaDescription'])
                        ) {
                            return InstallCode::INVALID_FRONEND_LINK_META_DESCRIPTION;
                        }
                    }
                }
            }
        }
        // Zahlungsmethode (PaymentMethod) (falls vorhanden)
        if (isset($installNode['PaymentMethod'][0]['Method'])
            && \is_array($installNode['PaymentMethod'][0]['Method'])
            && \count($installNode['PaymentMethod'][0]['Method']) > 0
        ) {
            foreach ($installNode['PaymentMethod'][0]['Method'] as $u => $method) {
                \preg_match('/[0-9]+\sattr/', $u, $hits1);
                \preg_match('/[0-9]+/', $u, $hits2);
                if (\strlen($hits2[0]) === \strlen($u)) {
                    // Name prüfen
                    \preg_match(
                        "/[a-zA-Z0-9äÄöÖüÜß" . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                        $method['Name'],
                        $hits1
                    );
                    if (\strlen($hits1[0]) !== \strlen($method['Name'])) {
                        return InstallCode::INVALID_PAYMENT_METHOD_NAME;
                    }
                    // Sort prüfen
                    \preg_match('/[0-9]+/', $method['Sort'], $hits1);
                    if (\strlen($hits1[0]) !== \strlen($method['Sort'])) {
                        return InstallCode::INVALID_PAYMENT_METHOD_SORT;
                    }
                    // SendMail prüfen
                    \preg_match("/[0-1]{1}/", $method['SendMail'], $hits1);
                    if (\strlen($hits1[0]) !== \strlen($method['SendMail'])) {
                        return InstallCode::INVALID_PAYMENT_METHOD_MAIL;
                    }
                    // TSCode prüfen
                    \preg_match('/[A-Z_]+/', $method['TSCode'], $hits1);
                    if (\strlen($hits1[0]) === \strlen($method['TSCode'])) {
                        $cTSCode_arr = [
                            'DIRECT_DEBIT',
                            'CREDIT_CARD',
                            'INVOICE',
                            'CASH_ON_DELIVERY',
                            'PREPAYMENT',
                            'CHEQUE',
                            'PAYBOX',
                            'PAYPAL',
                            'CASH_ON_PICKUP',
                            'FINANCING',
                            'LEASING',
                            'T_PAY',
                            'GIROPAY',
                            'GOOGLE_CHECKOUT',
                            'SHOP_CARD',
                            'DIRECT_E_BANKING',
                            'OTHER'
                        ];
                        if (!\in_array($method['TSCode'], $cTSCode_arr, true)) {
                            return InstallCode::INVALID_PAYMENT_METHOD_TSCODE;
                        }
                    } else {
                        return InstallCode::INVALID_PAYMENT_METHOD_TSCODE;
                    }
                    // PreOrder (nWaehrendbestellung) prüfen
                    \preg_match("/[0-1]{1}/", $method['PreOrder'], $hits1);
                    if (\strlen($hits1[0]) !== \strlen($method['PreOrder'])) {
                        return InstallCode::INVALID_PAYMENT_METHOD_PRE_ORDER;
                    }
                    // Soap prüfen
                    \preg_match("/[0-1]{1}/", $method['Soap'], $hits1);
                    if (\strlen($hits1[0]) !== \strlen($method['Soap'])) {
                        return InstallCode::INVALID_PAYMENT_METHOD_SOAP;
                    }
                    // Curl prüfen
                    \preg_match("/[0-1]{1}/", $method['Curl'], $hits1);
                    if (\strlen($hits1[0]) !== \strlen($method['Curl'])) {
                        return InstallCode::INVALID_PAYMENT_METHOD_CURL;
                    }
                    // Sockets prüfen
                    \preg_match('/[0-1]{1}/', $method['Sockets'], $hits1);
                    if (\strlen($hits1[0]) !== \strlen($method['Sockets'])) {
                        return InstallCode::INVALID_PAYMENT_METHOD_SOCKETS;
                    }
                    // ClassFile prüfen
                    if (isset($method['ClassFile'])) {
                        \preg_match('/[a-zA-Z0-9\/_\-.]+.php/', $method['ClassFile'], $hits1);
                        if (\strlen($hits1[0]) === \strlen($method['ClassFile'])) {
                            if (!\file_exists($currentVersionDir . \PFAD_PLUGIN_PAYMENTMETHOD . $method['ClassFile'])) {
                                return InstallCode::MISSING_PAYMENT_METHOD_FILE;
                            }
                        } else {
                            return InstallCode::INVALID_PAYMENT_METHOD_CLASS_FILE;
                        }
                    }
                    // ClassName prüfen
                    if (isset($method['ClassName'])) {
                        \preg_match("/[a-zA-Z0-9\/_\-]+/", $method['ClassName'], $hits1);
                        if (\strlen($hits1[0]) !== \strlen($method['ClassName'])) {
                            return InstallCode::INVALID_PAYMENT_METHOD_CLASS_NAME;
                        }
                    }
                    // TemplateFile prüfen
                    if (isset($method['TemplateFile']) && \strlen($method['TemplateFile']) > 0) {
                        \preg_match(
                            '/[a-zA-Z0-9\/_\-.]+.tpl/',
                            $method['TemplateFile'],
                            $hits1
                        );
                        if (\strlen($hits1[0]) !== \strlen($method['TemplateFile'])) {
                            return InstallCode::INVALID_PAYMENT_METHOD_TEMPLATE;
                        }
                        if (!\file_exists($currentVersionDir . \PFAD_PLUGIN_PAYMENTMETHOD . $method['TemplateFile'])) {
                            return InstallCode::MISSING_PAYMENT_METHOD_TEMPLATE;
                        }
                    }
                    // Zusatzschritt-TemplateFile prüfen
                    if (isset($method['AdditionalTemplateFile'])
                        && \strlen($method['AdditionalTemplateFile']) > 0
                    ) {
                        \preg_match(
                            '/[a-zA-Z0-9\/_\-.]+.tpl/',
                            $method['AdditionalTemplateFile'],
                            $hits1
                        );
                        if (\strlen($hits1[0]) !== \strlen($method['AdditionalTemplateFile'])) {
                            return InstallCode::INVALID_PAYMENT_METHOD_ADDITIONAL_STEP_TEMPLATE_FILE;
                        }
                        if (!\file_exists($currentVersionDir .
                            \PFAD_PLUGIN_PAYMENTMETHOD . $method['AdditionalTemplateFile'])
                        ) {
                            return InstallCode::MISSING_PAYMENT_METHOD_ADDITIONAL_STEP_FILE;
                        }
                    }
                    // ZahlungsmethodeSprachen prüfen
                    if (!isset($method['MethodLanguage'])
                        || !\is_array($method['MethodLanguage'])
                        || \count($method['MethodLanguage']) === 0
                    ) {
                        return InstallCode::MISSING_PAYMENT_METHOD_LANGUAGES;
                    }
                    foreach ($method['MethodLanguage'] as $l => $MethodLanguage_arr) {
                        \preg_match('/[0-9]+\sattr/', $l, $hits1);
                        \preg_match('/[0-9]+/', $l, $hits2);
                        if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($l)) {
                            // ISO prüfen
                            \preg_match("/[A-Z]{3}/", $MethodLanguage_arr['iso'], $hits);
                            if (\strlen($MethodLanguage_arr['iso']) === 0
                                || \strlen($hits[0]) !== \strlen($MethodLanguage_arr['iso'])
                            ) {
                                return InstallCode::INVALID_PAYMENT_METHOD_LANGUAGE_ISO;
                            }
                        } elseif (isset($hits2[0]) && \strlen($hits2[0]) === \strlen($l)) {
                            // Name prüfen
                            if (!isset($MethodLanguage_arr['Name'])) {
                                return InstallCode::INVALID_PAYMENT_METHOD_NAME_LOCALIZED;
                            }
                            \preg_match(
                                "/[a-zA-Z0-9äÄöÖüÜß" . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                                $MethodLanguage_arr['Name'],
                                $hits1
                            );
                            if (\strlen($hits1[0]) !== \strlen($MethodLanguage_arr['Name'])) {
                                return InstallCode::INVALID_PAYMENT_METHOD_NAME_LOCALIZED;
                            }
                            // ChargeName prüfen
                            if (!isset($MethodLanguage_arr['ChargeName'])) {
                                return InstallCode::INVALID_PAYMENT_METHOD_CHARGE_NAME;
                            }
                            \preg_match(
                                "/[a-zA-Z0-9äÄöÖüÜß" . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                                $MethodLanguage_arr['ChargeName'],
                                $hits1
                            );
                            if (\strlen($hits1[0]) !== \strlen($MethodLanguage_arr['ChargeName'])) {
                                return InstallCode::INVALID_PAYMENT_METHOD_CHARGE_NAME;
                            }
                            // InfoText prüfen
                            if (!isset($MethodLanguage_arr['InfoText'])) {
                                return InstallCode::INVALID_PAYMENT_METHOD_INFO_TEXT;
                            }
                            \preg_match(
                                "/[a-zA-Z0-9äÄöÖüÜß" . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                                $MethodLanguage_arr['InfoText'],
                                $hits1
                            );
                            if (isset($hits1[0]) && \strlen($hits1[0]) !== \strlen($MethodLanguage_arr['InfoText'])) {
                                return InstallCode::INVALID_PAYMENT_METHOD_INFO_TEXT;
                            }
                        }
                    }
                    // Zahlungsmethode Einstellungen prüfen
                    $type = '';
                    if (isset($method['Setting']) && \is_array($method['Setting']) && \count($method['Setting']) > 0) {
                        foreach ($method['Setting'] as $j => $setting) {
                            \preg_match('/[0-9]+\sattr/', $j, $hits3);
                            \preg_match('/[0-9]+/', $j, $hits4);
                            if (isset($hits3[0]) && \strlen($hits3[0]) === \strlen($j)) {
                                $type = $setting['type'];
                                // Einstellungen type prüfen
                                if (\strlen($setting['type']) === 0) {
                                    return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_TYPE;
                                }
                                // Einstellungen initialValue prüfen
                                //if(\strlen($Setting_arr['initialValue']) == 0)
                                //return 64;  // Einstellungen initialValue entspricht nicht der Konvention

                                // Einstellungen sort prüfen
                                if (\strlen($setting['sort']) === 0) {
                                    return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_SORT;
                                }
                                // Einstellungen conf prüfen
                                if (\strlen($setting['conf']) === 0) {
                                    return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_CONF;
                                }
                            } elseif (isset($hits4[0]) && \strlen($hits4[0]) === \strlen($j)) {
                                // Einstellungen Name prüfen
                                if (\strlen($setting['Name']) === 0) {
                                    return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_NAME;
                                }
                                // Einstellungen ValueName prüfen
                                if (\strlen($setting['ValueName']) === 0) {
                                    return InstallCode::INVALID_PAYMENT_METHOD_VALUE_NAME;
                                }
                                // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                                if ($type === 'selectbox') {
                                    // SelectboxOptions prüfen
                                    if (!isset($setting['SelectboxOptions'])
                                        || !\is_array($setting['SelectboxOptions'])
                                        || \count($setting['SelectboxOptions']) === 0
                                    ) {
                                        return InstallCode::MISSING_PAYMENT_METHOD_SELECTBOX_OPTIONS;
                                    }
                                    // Es gibt mehr als 1 Option
                                    if (\count($setting['SelectboxOptions'][0]) === 1) {
                                        foreach ($setting['SelectboxOptions'][0]['Option'] as $y => $Option_arr) {
                                            \preg_match('/[0-9]+\sattr/', $y, $hits6);
                                            \preg_match('/[0-9]+/', $y, $hits7);
                                            if (isset($hits6[0]) && \strlen($hits6[0]) === \strlen($y)) {
                                                // Value prüfen
                                                if (\strlen($Option_arr['value']) === 0) {
                                                    return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                                }
                                                // Sort prüfen
                                                if (\strlen($Option_arr['sort']) === 0) {
                                                    return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                                }
                                            } elseif (isset($hits7[0]) && \strlen($hits7[0]) === \strlen($y)) {
                                                // Name prüfen
                                                if (\strlen($Option_arr) === 0) {
                                                    return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                                }
                                            }
                                        }
                                    } elseif (\count($setting['SelectboxOptions'][0]) === 2) {
                                        //Es gibt nur 1 Option
                                        // Value prüfen
                                        if (\strlen($setting['SelectboxOptions'][0]['Option attr']['value']) === 0) {
                                            return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                        }
                                        // Sort prüfen
                                        if (\strlen($setting['SelectboxOptions'][0]['Option attr']['sort']) === 0) {
                                            return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                        }
                                        // Name prüfen
                                        if (\strlen($setting['SelectboxOptions'][0]['Option']) === 0) {
                                            return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                        }
                                    }
                                } elseif ($type === 'radio') {
                                    // SelectboxOptions prüfen
                                    if (!isset($setting['RadioOptions'])
                                        || !\is_array($setting['RadioOptions'])
                                        || \count($setting['RadioOptions']) === 0
                                    ) {
                                        return InstallCode::MISSING_PAYMENT_METHOD_SELECTBOX_OPTIONS;
                                    }
                                    // Es gibt mehr als 1 Option
                                    if (\count($setting['RadioOptions'][0]) === 1) {
                                        foreach ($setting['RadioOptions'][0]['Option'] as $y => $Option_arr) {
                                            \preg_match('/[0-9]+\sattr/', $y, $hits6);
                                            \preg_match('/[0-9]+/', $y, $hits7);
                                            if (isset($hits6[0]) && \strlen($hits6[0]) === \strlen($y)) {
                                                // Value prüfen
                                                if (\strlen($Option_arr['value']) === 0) {
                                                    return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                                }
                                                // Sort prüfen
                                                if (\strlen($Option_arr['sort']) === 0) {
                                                    return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                                }
                                            } elseif (isset($hits7[0]) && \strlen($hits7[0]) === \strlen($y)) {
                                                // Name prüfen
                                                if (\strlen($Option_arr) === 0) {
                                                    return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                                }
                                            }
                                        }
                                    } elseif (\count($setting['RadioOptions'][0]) === 2) { //Es gibt nur 1 Option
                                        // Value prüfen
                                        if (\strlen($setting['RadioOptions'][0]['Option attr']['value']) === 0) {
                                            return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                        }
                                        // Sort prüfen
                                        if (\strlen($setting['RadioOptions'][0]['Option attr']['sort']) === 0) {
                                            return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                        }
                                        // Name prüfen
                                        if (\strlen($setting['RadioOptions'][0]['Option']) === 0) {
                                            return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        // OPC Portlets (falls vorhanden)
        if (isset($installNode['Portlets']) && \is_array($installNode['Portlets'])) {
            if (!isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Portlets'][0]['Portlet'])
                || !\is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Portlets'][0]['Portlet'])
                || \count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Portlets'][0]['Portlet']) === 0
            ) {
                return InstallCode::MISSING_PORTLETS;// Keine Portlets vorhanden
            }
            foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Portlets'][0]['Portlet'] as $u => $Portlet_arr) {
                \preg_match('/[0-9]+\sattr/', $u, $hits1);
                \preg_match('/[0-9]+/', $u, $hits2);
                if (\strlen($hits2[0]) === \strlen($u)) {
                    // Portlet Title prüfen
                    \preg_match(
                        "/[a-zA-Z0-9\/_\-äÄüÜöÖß" . \utf8_decode('äÄüÜöÖß') . "\(\) ]+/",
                        $Portlet_arr['Title'],
                        $hits1
                    );
                    if (\strlen($hits1[0]) !== \strlen($Portlet_arr['Title'])) {
                        // Portlet Title entspricht nicht der Konvention
                        return InstallCode::INVALID_PORTLET_TITLE;
                    }
                    // Portlet Class prüfen
                    \preg_match("/[a-zA-Z0-9\/_\-.]+/", $Portlet_arr['Class'], $hits1);
                    if (\strlen($hits1[0]) === \strlen($Portlet_arr['Class'])) {
                        if (!\file_exists($currentVersionDir .
                            \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_PORTLETS . $Portlet_arr['Class'] . '/' .
                            $Portlet_arr['Class'] . '.php')
                        ) {
                            // Die Datei für die Klasse des Portlets existiert nicht
                            return InstallCode::INVALID_PORTLET_CLASS_FILE;
                        }
                    } else {
                        // Portlet Class entspricht nicht der Konvention
                        return InstallCode::INVALID_PORTLET_CLASS;
                    }
                    // Portlet Group prüfen
                    \preg_match(
                        "/[a-zA-Z0-9\/_\-äÄüÜöÖß" . \utf8_decode('äÄüÜöÖß') . "\(\) ]+/",
                        $Portlet_arr['Group'],
                        $hits1
                    );
                    if (\strlen($hits1[0]) !== \strlen($Portlet_arr['Group'])) {
                        // Portlet Group entspricht nicht der Konvention
                        return InstallCode::INVALID_PORTLET_GROUP;
                    }
                    // Portlet Active prüfen
                    \preg_match("/[0-1]{1}/", $Portlet_arr['Active'], $hits1);
                    if (\strlen($hits1[0]) !== \strlen($Portlet_arr['Active'])) {
                        // Active im Portlet entspricht nicht der Konvention
                        return InstallCode::INVALID_PORTLET_ACTIVE;
                    }
                }
            }
        }
        // OPC Blueprints (falls vorhanden)
        if (isset($installNode['Blueprints']) && \is_array($installNode['Blueprints'])) {
            if (!isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Blueprints'][0]['Blueprint'])
                || !\is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Blueprints'][0]['Blueprint'])
                || \count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Blueprints'][0]['Blueprint']) === 0
            ) {
                return InstallCode::MISSING_BLUEPRINTS;
            }
            foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Blueprints'][0]['Blueprint'] as $u => $blueprint) {
                \preg_match('/[0-9]+\sattr/', $u, $hits1);
                \preg_match('/[0-9]+/', $u, $hits2);
                if (\strlen($hits2[0]) === \strlen($u)) {
                    // Blueprint Name prüfen
                    \preg_match(
                        "/[a-zA-Z0-9\/_\-\ äÄüÜöÖß" . \utf8_decode('äÄüÜöÖß') . "\(\) ]+/",
                        $blueprint['Name'],
                        $hits1
                    );
                    if (\strlen($hits1[0]) !== \strlen($blueprint['Name'])) {
                        // Blueprint Name entspricht nicht der Konvention
                        return InstallCode::INVALID_BLUEPRINT_NAME;
                    }
                    // Blueprint JSON file prüfen
                    if (\is_file($currentVersionDir .
                            \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_BLUEPRINTS . $blueprint['JSONFile']) === false
                    ) {
                        // Blueprint JSON Datei nicht gefunden
                        return InstallCode::INVALID_BLUEPRINT_FILE;
                    }
                }
            }
        }
        // Boxenvorlagen (falls vorhanden)
        if (isset($installNode['Boxes']) && \is_array($installNode['Boxes'])) {
            if (!isset($installNode['Boxes'][0]['Box'])
                || !\is_array($installNode['Boxes'][0]['Box'])
                || \count($installNode['Boxes'][0]['Box']) === 0
            ) {
                return InstallCode::MISSING_BOX;
            }
            foreach ($installNode['Boxes'][0]['Box'] as $h => $box) {
                \preg_match('/[0-9]+/', $h, $hits3);
                if (\strlen($hits3[0]) !== \strlen($h)) {
                    continue;
                }
                // Box Name prüfen
                if (empty($box['Name'])) {
                    return InstallCode::INVALID_BOX_NAME;
                }
                // Box TemplateFile prüfen
                if (empty($box['TemplateFile'])) {
                    return InstallCode::INVALID_BOX_TEMPLATE;
                }
                if (!\file_exists($currentVersionDir .
                    \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_BOXEN . $box['TemplateFile'])
                ) {
                    return InstallCode::MISSING_BOX_TEMPLATE_FILE;
                }
            }
        }
        // Emailvorlagen (falls vorhanden)
        if (isset($installNode['Emailtemplate']) && \is_array($installNode['Emailtemplate'])) {
            // EmailTemplates prüfen
            if (!isset($installNode['Emailtemplate'][0]['Template'])
                || !\is_array($installNode['Emailtemplate'][0]['Template'])
                || \count($installNode['Emailtemplate'][0]['Template']) === 0
            ) {
                return InstallCode::MISSING_EMAIL_TEMPLATES;
            }
            foreach ($installNode['Emailtemplate'][0]['Template'] as $u => $Template_arr) {
                \preg_match('/[0-9]+\sattr/', $u, $hits1);
                \preg_match('/[0-9]+/', $u, $hits2);
                if (\strlen($hits2[0]) !== \strlen($u)) {
                    continue;
                }
                // Template Name prüfen
                \preg_match(
                    "/[a-zA-Z0-9\/_\-äÄüÜöÖß" . " ]+/",
                    $Template_arr['Name'],
                    $hits1
                );
                if (\strlen($hits1[0]) !== \strlen($Template_arr['Name'])) {
                    return InstallCode::INVALID_TEMPLATE_NAME;
                }
                // Template Typ prüfen
                if ($Template_arr['Type'] !== 'text/html' && $Template_arr['Type'] !== 'text') {
                    return InstallCode::INVALID_TEMPLATE_TYPE;
                }
                // Template ModulId prüfen
                if (\strlen($Template_arr['ModulId']) === 0) {
                    return InstallCode::INVALID_TEMPLATE_MODULE_ID;
                }
                // Template Active prüfen
                if (\strlen($Template_arr['Active']) === 0) {
                    return InstallCode::INVALID_TEMPLATE_ACTIVE;
                }
                // Template AKZ prüfen
                if (\strlen($Template_arr['AKZ']) === 0) {
                    return InstallCode::INVALID_TEMPLATE_AKZ;
                }
                // Template AGB prüfen
                if (\strlen($Template_arr['AGB']) === 0) {
                    return InstallCode::INVALID_TEMPLATE_AGB;
                }
                // Template WRB prüfen
                if (\strlen($Template_arr['WRB']) === 0) {
                    return InstallCode::INVALID_TEMPLATE_WRB;
                }
                // Template Sprachen prüfen
                if (!isset($Template_arr['TemplateLanguage'])
                    || !\is_array($Template_arr['TemplateLanguage'])
                    || \count($Template_arr['TemplateLanguage']) === 0
                ) {
                    return InstallCode::MISSING_EMAIL_TEMPLATE_LANGUAGE;
                }
                foreach ($Template_arr['TemplateLanguage'] as $l => $TemplateLanguage_arr) {
                    \preg_match('/[0-9]+\sattr/', $l, $hits1);
                    \preg_match('/[0-9]+/', $l, $hits2);
                    if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($l)) {
                        // ISO prüfen
                        \preg_match("/[A-Z]{3}/", $TemplateLanguage_arr['iso'], $hits);
                        if (\strlen($TemplateLanguage_arr['iso']) === 0
                            || \strlen($hits[0]) !== \strlen($TemplateLanguage_arr['iso'])
                        ) {
                            return InstallCode::INVALID_EMAIL_TEMPLATE_ISO;
                        }
                    } elseif (\strlen($hits2[0]) === \strlen($l)) {
                        // Subject prüfen
                        \preg_match("/[a-zA-Z0-9\/_\-.#: ]+/", $TemplateLanguage_arr['Subject'], $hits1);
                        if (\strlen($TemplateLanguage_arr['Subject']) === 0
                            || \strlen($hits1[0]) !== \strlen($TemplateLanguage_arr['Subject'])
                        ) {
                            return InstallCode::INVALID_EMAIL_TEMPLATE_SUBJECT;
                        }
                    }
                }
            }
        }
        // Locales (falls vorhanden)
        if (isset($installNode['Locales']) && \is_array($installNode['Locales'])) {
            // Variablen prüfen
            if (!isset($installNode['Locales'][0]['Variable'])
                || !\is_array($installNode['Locales'][0]['Variable'])
                || \count($installNode['Locales'][0]['Variable']) === 0
            ) {
                return InstallCode::MISSING_LANG_VARS;
            }
            foreach ($installNode['Locales'][0]['Variable'] as $t => $Variable_arr) {
                \preg_match('/[0-9]+/', $t, $hits2);
                if (\strlen($hits2[0]) !== \strlen($t)) {
                    continue;
                }
                // Variablen Name prüfen
                if (\strlen($Variable_arr['Name']) === 0) {
                    return InstallCode::INVALID_LANG_VAR_NAME;
                }
                // Variable Localized prüfen
                // Nur eine Sprache vorhanden
                if (isset($Variable_arr['VariableLocalized attr'])
                    && \is_array($Variable_arr['VariableLocalized attr'])
                    && \count($Variable_arr['VariableLocalized attr']) > 0
                ) {
                    if (!isset($Variable_arr['VariableLocalized attr']['iso'])) {
                        return InstallCode::MISSING_LOCALIZED_LANG_VAR;
                    }
                    // ISO prüfen
                    \preg_match("/[A-Z]{3}/", $Variable_arr['VariableLocalized attr']['iso'], $hits);
                    if (\strlen($hits[0]) !== \strlen($Variable_arr['VariableLocalized attr']['iso'])) {
                        return InstallCode::INVALID_LANG_VAR_ISO;
                    }
                    // Name prüfen
                    if (\strlen($Variable_arr['VariableLocalized']) === 0) {
                        return InstallCode::INVALID_LOCALIZED_LANG_VAR_NAME;
                    }
                } elseif (isset($Variable_arr['VariableLocalized'])
                    && \is_array($Variable_arr['VariableLocalized'])
                    && \count($Variable_arr['VariableLocalized']) > 0
                ) {
                    // Mehr als eine Sprache vorhanden
                    foreach ($Variable_arr['VariableLocalized'] as $i => $VariableLocalized_arr) {
                        \preg_match('/[0-9]+\sattr/', $i, $hits1);
                        \preg_match('/[0-9]+/', $i, $hits2);
                        if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($i)) {
                            // ISO prüfen
                            \preg_match("/[A-Z]{3}/", $VariableLocalized_arr['iso'], $hits);
                            if (\strlen($VariableLocalized_arr['iso']) === 0 ||
                                \strlen($hits[0]) !== \strlen($VariableLocalized_arr['iso'])
                            ) {
                                return InstallCode::INVALID_LANG_VAR_ISO;
                            }
                        } elseif (isset($hits2[0]) && \strlen($hits2[0]) === \strlen($i)) {
                            // Name prüfen
                            if (\strlen($VariableLocalized_arr) === 0) {
                                return InstallCode::INVALID_LOCALIZED_LANG_VAR_NAME;
                            }
                        }
                    }
                } else {
                    return InstallCode::MISSING_LOCALIZED_LANG_VAR;
                }
            }
        }
        // CheckBoxFunction (falls vorhanden)
        if (isset($installNode['CheckBoxFunction'][0]['Function'])
            && \is_array($installNode['CheckBoxFunction'][0]['Function'])
            && \count($installNode['CheckBoxFunction'][0]['Function']) > 0
        ) {
            foreach ($installNode['CheckBoxFunction'][0]['Function'] as $t => $Function_arr) {
                \preg_match('/[0-9]+/', $t, $hits2);
                if (\strlen($hits2[0]) === \strlen($t)) {
                    // Function Name prüfen
                    if (\strlen($Function_arr['Name']) === 0) {
                        return InstallCode::INVALID_CHECKBOX_FUNCTION_NAME;
                    }
                    // Function ID prüfen
                    if (\strlen($Function_arr['ID']) === 0) {
                        return InstallCode::INVALID_CHECKBOX_FUNCTION_ID;
                    }
                }
            }
        }
        // AdminWidgets (falls vorhanden)
        if (isset($installNode['AdminWidget']) && \is_array($installNode['AdminWidget'])) {
            if (!isset($installNode['AdminWidget'][0]['Widget'])
                || !\is_array($installNode['AdminWidget'][0]['Widget'])
                || \count($installNode['AdminWidget'][0]['Widget']) === 0
            ) {
                return InstallCode::MISSING_WIDGETS;
            }
            foreach ($installNode['AdminWidget'][0]['Widget'] as $u => $Widget_arr) {
                \preg_match('/[0-9]+\sattr/', $u, $hits1);
                \preg_match('/[0-9]+/', $u, $hits2);
                if (\strlen($hits2[0]) !== \strlen($u)) {
                    continue;
                }
                // Widget Title prüfen
                \preg_match(
                    "/[a-zA-Z0-9\/_\-äÄüÜöÖß" . "\(\) ]+/",
                    $Widget_arr['Title'],
                    $hits1
                );
                if (\strlen($hits1[0]) !== \strlen($Widget_arr['Title'])) {
                    return InstallCode::INVALID_WIDGET_TITLE;
                }
                // Widget Class prüfen
                \preg_match("/[a-zA-Z0-9\/_\-.]+/", $Widget_arr['Class'], $hits1);
                if (\strlen($hits1[0]) !== \strlen($Widget_arr['Class'])) {
                    return InstallCode::INVALID_WIDGET_CLASS;
                }
                if (!\file_exists(
                    $currentVersionDir .
                    \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_WIDGET .
                    'class.Widget' . $Widget_arr['Class'] . '_' .
                    $baseNode['PluginID'] . '.php'
                )) {
                    return InstallCode::MISSING_WIDGET_CLASS_FILE;
                }
                // Widget Container prüfen
                if ($Widget_arr['Container'] !== 'center'
                    && $Widget_arr['Container'] !== 'left'
                    && $Widget_arr['Container'] !== 'right'
                ) {
                    return InstallCode::INVALID_WIDGET_CONTAINER;
                }
                // Widget Pos prüfen
                \preg_match('/[0-9]+/', $Widget_arr['Pos'], $hits1);
                if (\strlen($hits1[0]) !== \strlen($Widget_arr['Pos'])) {
                    return InstallCode::INVALID_WIDGET_POS;
                }
                // Widget Expanded prüfen
                \preg_match("/[0-1]{1}/", $Widget_arr['Expanded'], $hits1);
                if (\strlen($hits1[0]) !== \strlen($Widget_arr['Expanded'])) {
                    return InstallCode::INVALID_WIDGET_EXPANDED;
                }
                // Widget Active prüfen
                \preg_match("/[0-1]{1}/", $Widget_arr['Active'], $hits1);
                if (\strlen($hits1[0]) !== \strlen($Widget_arr['Active'])) {
                    return InstallCode::INVALID_WIDGET_ACTIVE;
                }
            }
        }
        // Exportformate (falls vorhanden)
        if (isset($installNode['ExportFormat']) && \is_array($installNode['ExportFormat'])) {
            // Formate prüfen
            if (!isset($installNode['ExportFormat'][0]['Format'])
                || !\is_array($installNode['ExportFormat'][0]['Format'])
                || \count($installNode['ExportFormat'][0]['Format']) === 0
            ) {
                return InstallCode::MISSING_FORMATS;
            }
            foreach ($installNode['ExportFormat'][0]['Format'] as $h => $Format_arr) {
                \preg_match('/[0-9]+\sattr/', $h, $hits1);
                \preg_match('/[0-9]+/', $h, $hits2);
                if (\strlen($hits2[0]) !== \strlen($h)) {
                    continue;
                }
                // Name prüfen
                if (\strlen($Format_arr['Name']) === 0) {
                    return InstallCode::INVALID_FORMAT_NAME;
                }
                // Filename prüfen
                if (\strlen($Format_arr['FileName']) === 0) {
                    return InstallCode::INVALID_FORMAT_FILE_NAME;
                }
                // Content prüfen
                if ((!isset($Format_arr['Content']) || \strlen($Format_arr['Content']) === 0)
                    && (!isset($Format_arr['ContentFile']) || \strlen($Format_arr['ContentFile']) === 0)
                ) {
                    return InstallCode::MISSING_FORMAT_CONTENT;
                }
                // Encoding prüfen
                if (\strlen($Format_arr['Encoding']) === 0
                    || ($Format_arr['Encoding'] !== 'ASCII' && $Format_arr['Encoding'] !== 'UTF-8')
                ) {
                    return InstallCode::INVALID_FORMAT_ENCODING;
                }
                // Encoding prüfen
                if (\strlen($Format_arr['ShippingCostsDeliveryCountry']) === 0) {
                    return InstallCode::INVALID_FORMAT_SHIPPING_COSTS_DELIVERY_COUNTRY;
                }
                // Encoding prüfen
                if (\strlen($Format_arr['ContentFile']) > 0
                    && !\file_exists($currentVersionDir .
                        \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_EXPORTFORMAT . $Format_arr['ContentFile'])
                ) {
                    return InstallCode::INVALID_FORMAT_CONTENT_FILE;
                }
            }
        }
        // ExtendedTemplate (falls vorhanden)
        if (isset($installNode['ExtendedTemplates']) && \is_array($installNode['ExtendedTemplates'])) {
            // Template prüfen
            if (!isset($installNode['ExtendedTemplates'][0]['Template'])) {
                return InstallCode::MISSING_EXTENDED_TEMPLATE;
            }
            foreach ((array)$installNode['ExtendedTemplates'][0]['Template'] as $template) {
                \preg_match('/[a-zA-Z0-9\/_\-]+\.tpl/', $template, $hits3);
                if (\strlen($hits3[0]) !== \strlen($template)) {
                    return InstallCode::INVALID_EXTENDED_TEMPLATE_FILE_NAME;
                }
                if (!\file_exists($currentVersionDir . \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_TEMPLATE . $template)) {
                    return InstallCode::MISSING_EXTENDED_TEMPLATE_FILE;
                }
            }
        }

        // Uninstall (falls vorhanden)
        if (isset($baseNode['Uninstall'])
            && \strlen($baseNode['Uninstall']) > 0
            && !\file_exists($currentVersionDir . \PFAD_PLUGIN_UNINSTALL . $baseNode['Uninstall'])
        ) {
            return InstallCode::MISSING_UNINSTALL_FILE;
        }
        // Interne XML prüfung mit höheren XML Versionen
        if ($nXMLVersion > 100) {
            return $isShop4Compatible ? InstallCode::OK : InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE;
        }

        return $isShop4Compatible ? InstallCode::OK : InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE;
    }
}
