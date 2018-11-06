<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

use DB\DbInterface;
use DB\ReturnType;
use JTL\XMLParser;
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
        $parser = new XMLParser();

        return $this->pluginPlausiIntern($parser->parse($info), $forUpdate);
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
        if (!\is_dir($this->dir)) {
            return InstallCode::DIR_DOES_NOT_EXIST;
        }
        $infoXML = "{$this->dir}/" . \PLUGIN_INFO_FILE;
        if (!\file_exists($infoXML)) {
            return InstallCode::INFO_XML_MISSING;
        }
        $parser = new XMLParser();
        $xml    = $parser->parse($infoXML);

        return $this->pluginPlausiIntern($xml, $forUpdate);
    }

    /**
     * @param      $xml
     * @param bool $forUpdate
     * @return int
     * @former pluginPlausiIntern()
     */
    public function pluginPlausiIntern($xml, bool $forUpdate): int
    {
        $isShop4Compatible    = false;
        $parsedXMLShopVersion = null;
        $parsedVersion        = null;
        $baseNode             = $xml['jtlshop3plugin'][0];
        $oVersion             = $this->db->query('SELECT nVersion FROM tversion LIMIT 1', ReturnType::SINGLE_OBJECT);
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
        $nXMLVersion = (int)$xml['jtlshop3plugin'][0]['XMLVersion'];
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
            return InstallCode::INVALID_SHOP_VERSION;
        }
        if (isset($baseNode['Shop4Version'])) {
            $parsedXMLShopVersion = Version::parse($baseNode['Shop4Version']);
            $isShop4Compatible    = true;
        } else {
            $parsedXMLShopVersion = Version::parse($baseNode['ShopVersion']);
        }
        $installNode = $baseNode['Install'][0];
        if (empty($parsedVersion)
            || empty($parsedXMLShopVersion)
            || $parsedXMLShopVersion->greaterThan($parsedVersion)
        ) {
            return InstallCode::SHOP_VERSION_COMPATIBILITY; //Shop-Version ist zu niedrig
        }
        if (!isset($baseNode['Author'])) {
            return InstallCode::INVALID_AUTHOR;
        }
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
        \preg_match('/[\w_]+/', $baseNode['PluginID'], $hits);
        if (empty($baseNode['PluginID']) || \strlen($hits[0]) !== \strlen($baseNode['PluginID'])) {
            return InstallCode::INVALID_PLUGIN_ID;
        }

        if (!isset($baseNode['Install']) || !\is_array($baseNode['Install'])) {
            return InstallCode::INSTALL_NODE_MISSING;
        }
        $cVersionsnummer = $this->validateVersion($installNode);
        if (!\is_string($cVersionsnummer)) {
            return $cVersionsnummer;
        }
        if (($res = $this->validateBootstrapper($baseNode, $forUpdate)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->validateLicense($baseNode)) !== InstallCode::OK) {
            return $res;
        }
        $versionedDir = $this->dir . '/' . \PFAD_PLUGIN_VERSION . $cVersionsnummer . '/';
        if (($res = $this->validateHooks($installNode, $versionedDir)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->validateMenus($installNode, $versionedDir)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->validateFrontendLinks($installNode, $versionedDir)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->validatePaymentMethods($installNode, $versionedDir)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->validatePortlets($installNode, $versionedDir)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->validateBlueprints($installNode, $versionedDir)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->validateBoxTemplates($installNode, $versionedDir)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->validateMailTemplates($installNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->validateLocalization($installNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->validateCheckboxes($installNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->validateWidgets($installNode, $versionedDir, $baseNode['PluginID'])) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->validateExports($installNode, $versionedDir)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->validateExtendedTemplates($installNode, $versionedDir)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->validateUninstaller($baseNode, $versionedDir)) !== InstallCode::OK) {
            return $res;
        }
        if ($nXMLVersion > 100) {
            return $isShop4Compatible ? InstallCode::OK : InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE;
        }

        return $isShop4Compatible ? InstallCode::OK : InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE;
    }

    /**
     * @param array $baseNode
     * @return int
     */
    private function validateLicense(array $baseNode): int
    {
        $requiresMissingIoncube = false;
        $installNode            = $baseNode['Install'][0];
        if (isset($baseNode['LicenceClassFile']) && !\extension_loaded('ionCube Loader')) {
            // ioncube is not loaded
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
        $nLastVersionKey    = \count($installNode['Version']) / 2 - 1;
        $nLastPluginVersion = (int)$installNode['Version'][$nLastVersionKey . ' attr']['nr'];
        $versionedDir       = $this->dir . '/' . \PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/';
        if (isset($baseNode['LicenceClassFile']) && \strlen($baseNode['LicenceClassFile']) > 0) {
            if (!\file_exists($versionedDir . \PFAD_PLUGIN_LICENCE . $baseNode['LicenceClassFile'])) {
                return InstallCode::MISSING_LICENCE_FILE;
            }
            if (empty($baseNode['LicenceClass'])
                || $baseNode['LicenceClass'] !== $baseNode['PluginID'] . \PLUGIN_LICENCE_CLASS
            ) {
                return InstallCode::INVALID_LICENCE_FILE_NAME;
            }
            if ($requiresMissingIoncube) {
                return InstallCode::IONCUBE_REQUIRED;
            }
            require_once $versionedDir . \PFAD_PLUGIN_LICENCE . $baseNode['LicenceClassFile'];
            if (!\class_exists($baseNode['LicenceClass'])) {
                return InstallCode::MISSING_LICENCE;
            }
            $classMethods = \get_class_methods($baseNode['LicenceClass']);
            $bClassMethod = \is_array($classMethods) && \in_array(\PLUGIN_LICENCE_METHODE, $classMethods, true);
            if (!$bClassMethod) {
                return InstallCode::MISSING_LICENCE_CHECKLICENCE_METHOD;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $baseNode
     * @param bool  $forUpdate
     * @return int
     */
    private function validateBootstrapper(array $baseNode, bool $forUpdate): int
    {
        $installNode        = $baseNode['Install'][0];
        $nLastVersionKey    = \count($installNode['Version']) / 2 - 1;
        $nLastPluginVersion = (int)$installNode['Version'][$nLastVersionKey . ' attr']['nr'];
        $versionedDir       = $this->dir . '/' . \PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/';
        $namespace          = $baseNode['PluginID'];
        $classFile          = $versionedDir . \PLUGIN_BOOTSTRAPPER;
        if ($forUpdate === false && \is_file($classFile)) {
            $class = \sprintf('%s\\%s', $namespace, 'Bootstrap');

            require_once $classFile;

            if (!\class_exists($class)) {
                return InstallCode::MISSING_BOOTSTRAP_CLASS;
            }

            $bootstrapper = new $class((object)['cPluginID' => $namespace]);

            if (!\is_subclass_of($bootstrapper, \AbstractPlugin::class)) {
                return InstallCode::INVALID_BOOTSTRAP_IMPLEMENTATION;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $installNode
     * @return int|string
     */
    private function validateVersion(array $installNode)
    {
        if (!isset($installNode['Version'])
            || !\is_array($installNode['Version'])
            || !\count($installNode['Version']) === 0
        ) {
            return InstallCode::INVALID_XML_VERSION_NUMBER;
        }
        if ((int)$installNode['Version']['0 attr']['nr'] !== 100) {
            return InstallCode::INVALID_XML_VERSION_NUMBER;
        }
        $version = '';
        foreach ($installNode['Version'] as $i => $Version) {
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($i)) {
                $version = $Version['nr'];
                \preg_match('/[0-9]+/', $Version['nr'], $hits);
                if (\strlen($hits[0]) !== \strlen($Version['nr'])) {
                    return InstallCode::INVALID_VERSION_NUMBER;
                }
            } elseif (\strlen($hits2[0]) === \strlen($i)) {
                if (isset($Version['SQL'])
                    && \strlen($Version['SQL']) > 0
                    && !\file_exists($this->dir . '/' . \PFAD_PLUGIN_VERSION . $version . '/' .
                        \PFAD_PLUGIN_SQL . $Version['SQL'])
                ) {
                    return InstallCode::MISSING_SQL_FILE;
                }
                if (!\is_dir($this->dir . '/' . \PFAD_PLUGIN_VERSION . $version)) {
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

        return $version;
    }

    /**
     * @param array  $installNode
     * @param string $versionedDir
     * @return int
     */
    private function validateHooks(array $installNode, string $versionedDir): int
    {
        if (!isset($installNode['Hooks']) || \is_array($installNode['Hooks'])) {
            return InstallCode::OK;
        }
        if (\count($installNode['Hooks'][0]) === 1) {
            foreach ($installNode['Hooks'][0]['Hook'] as $i => $hook) {
                \preg_match('/[0-9]+\sattr/', $i, $hits1);
                \preg_match('/[0-9]+/', $i, $hits2);
                if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($i)) {
                    if (\strlen($hook['id']) === 0) {
                        return InstallCode::INVALID_HOOK;
                    }
                } elseif (isset($hits2[0]) && \strlen($hits2[0]) === \strlen($i)) {
                    if (\strlen($hook) === 0) {
                        return InstallCode::INVALID_HOOK;
                    }
                    if (!\file_exists($versionedDir . \PFAD_PLUGIN_FRONTEND . $hook['Hook'])) {
                        return InstallCode::MISSING_HOOK_FILE;
                    }
                }
            }
        } elseif (\count($installNode['Hooks'][0]) > 1) {
            $hook = $installNode['Hooks'][0];
            if ((int)$hook['Hook attr']['id'] === 0 || \strlen($hook['Hook']) === 0) {
                return InstallCode::INVALID_HOOK;
            }
            if (!\file_exists($versionedDir . \PFAD_PLUGIN_FRONTEND . $hook['Hook'])) {
                return InstallCode::MISSING_HOOK_FILE;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array  $installNode
     * @param string $versionedDir
     * @return int
     */
    private function validateMenus(array $installNode, string $versionedDir): int
    {
        if (!isset($installNode['Adminmenu'][0])) {
            return InstallCode::OK;
        }
        $node = $installNode['Adminmenu'][0];
        if (isset($node['Customlink'])
            && \is_array($node['Customlink'])
            && \count($node['Customlink']) > 0
        ) {
            foreach ($node['Customlink'] as $i => $Customlink_arr) {
                \preg_match('/[0-9]+\sattr/', $i, $hits1);
                \preg_match('/[0-9]+/', $i, $hits2);
                if (\strlen($hits2[0]) === \strlen($i)) {
                    \preg_match(
                        '/[a-zA-Z0-9äÄüÜöÖß' . "\_\- ]+/",
                        $Customlink_arr['Name'],
                        $hits
                    );
                    if (empty($Customlink_arr['Name']) || \strlen($hits[0]) !== \strlen($Customlink_arr['Name'])) {
                        return InstallCode::INVALID_CUSTOM_LINK_NAME;
                    }
                    if (empty($Customlink_arr['Filename'])) {
                        return InstallCode::INVALID_CUSTOM_LINK_FILE_NAME;
                    }
                    if (!\file_exists($versionedDir . \PFAD_PLUGIN_ADMINMENU . $Customlink_arr['Filename'])) {
                        return InstallCode::MISSING_CUSTOM_LINK_FILE;
                    }
                }
            }
        }
        if (!isset($node['Settingslink']) || !\is_array($node['Settingslink'])) {
            return InstallCode::OK;
        }
        foreach ($node['Settingslink'] as $i => $settingsLink) {
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\strlen($hits2[0]) === \strlen($i)) {
                if (empty($settingsLink['Name'])) {
                    return InstallCode::INVALID_CONFIG_LINK_NAME;
                }
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
                        if (\strlen($setting['type']) === 0) {
                            return InstallCode::INVALID_CONFIG_TYPE;
                        }
                        if (\strlen($setting['sort']) === 0) {
                            return InstallCode::INVALID_CONFIG_SORT_VALUE;
                        }
                        if (\strlen($setting['conf']) === 0) {
                            return InstallCode::INVALID_CONF;
                        }
                    } elseif (\strlen($hits4[0]) === \strlen($j)) {
                        if (\strlen($setting['Name']) === 0) {
                            return InstallCode::INVALID_CONFIG_NAME;
                        }
                        if (!isset($setting['ValueName'])
                            || !\is_string($setting['ValueName'])
                            || \strlen($setting['ValueName']) === 0
                        ) {
                            return InstallCode::INVALID_CONF_VALUE_NAME;
                        }
                        if ($type === 'selectbox') {
                            if (isset($setting['OptionsSource'])
                                && \is_array($setting['OptionsSource'])
                                && \count($setting['OptionsSource']) > 0
                            ) {
                                if (empty($setting['OptionsSource'][0]['File'])) {
                                    return InstallCode::INVALID_OPTIONS_SOURE_FILE;
                                }
                                if (!\file_exists($versionedDir .
                                    \PFAD_PLUGIN_ADMINMENU .
                                    $setting['OptionsSource'][0]['File'])
                                ) {
                                    return InstallCode::MISSING_OPTIONS_SOURE_FILE;
                                }
                            } elseif (isset($setting['SelectboxOptions'])
                                && \is_array($setting['SelectboxOptions'])
                                && \count($setting['SelectboxOptions']) > 0
                            ) {
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
                                            if (\strlen($Option_arr) === 0) {
                                                return InstallCode::INVALID_CONFIG_OPTION;
                                            }
                                        }
                                    }
                                } elseif (\count($setting['SelectboxOptions'][0]) === 2) {
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
                            if (isset($setting['OptionsSource'])
                                && \is_array($setting['OptionsSource'])
                                && \count($setting['OptionsSource']) > 0
                            ) {
                                //do nothing for now
                            } elseif (isset($setting['RadioOptions'])
                                && \is_array($setting['RadioOptions'])
                                && \count($setting['RadioOptions']) > 0
                            ) {
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

        return InstallCode::OK;
    }

    /**
     * @param array  $installNode
     * @param string $versionedDir
     * @return int
     */
    private function validateFrontendLinks(array $installNode, string $versionedDir): int
    {
        if (!isset($installNode['FrontendLink'][0])) {
            return InstallCode::OK;
        }
        $node = $installNode['FrontendLink'][0];
        if (!isset($node['Link']) || !\is_array($node['Link']) || \count($node['Link']) === 0) {
            return InstallCode::MISSING_FRONTEND_LINKS;
        }
        foreach ($node['Link'] as $u => $link) {
            \preg_match('/[0-9]+\sattr/', $u, $hits1);
            \preg_match('/[0-9]+/', $u, $hits2);

            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            if (\strlen($link['Filename']) === 0) {
                return InstallCode::INVALID_FRONTEND_LINK_FILENAME;
            }
            \preg_match(
                "/[a-zA-Z0-9äÄöÖüÜß" . "\_\- ]+/",
                $link['Name'],
                $hits1
            );
            if (\strlen($hits1[0]) !== \strlen($link['Name'])) {
                return InstallCode::INVALID_FRONTEND_LINK_NAME;
            }
            // Templatename UND Fullscreen Templatename vorhanden?
            // Es darf nur entweder oder geben
            if (isset($link['Template'], $link['FullscreenTemplate'])
                && \strlen($link['Template']) > 0
                && \strlen($link['FullscreenTemplate']) > 0
            ) {
                return InstallCode::TOO_MANY_FULLSCREEN_TEMPLATE_NAMES;
            }
            if (!isset($link['FullscreenTemplate']) || \strlen($link['FullscreenTemplate']) === 0) {
                if (\strlen($link['Template']) === 0) {
                    return InstallCode::INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE;
                }
                \preg_match("/[a-zA-Z0-9\/_\-.]+.tpl/", $link['Template'], $hits1);
                if (\strlen($hits1[0]) === \strlen($link['Template'])) {
                    if (!\file_exists($versionedDir .
                        \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_TEMPLATE . $link['Template'])
                    ) {
                        return InstallCode::MISSING_FRONTEND_LINK_TEMPLATE;
                    }
                } else {
                    return InstallCode::INVALID_FULLSCREEN_TEMPLATE;
                }
            }
            if (!isset($link['Template']) || \strlen($link['Template']) === 0) {
                if (\strlen($link['FullscreenTemplate']) === 0) {
                    return InstallCode::INVALID_FRONTEND_LINK_TEMPLATE_FULLSCREEN_TEMPLATE;
                }
                \preg_match("/[a-zA-Z0-9\/_\-.]+.tpl/", $link['FullscreenTemplate'], $hits1);
                if (\strlen($hits1[0]) === \strlen($link['FullscreenTemplate'])) {
                    if (!\file_exists($versionedDir .
                        \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_TEMPLATE . $link['FullscreenTemplate'])
                    ) {
                        return InstallCode::MISSING_FULLSCREEN_TEMPLATE_FILE;
                    }
                } else {
                    return InstallCode::INVALID_FULLSCREEN_TEMPLATE_NAME;
                }
            }
            \preg_match("/[NY]{1,1}/", $link['VisibleAfterLogin'], $hits2);
            if (\strlen($hits2[0]) !== \strlen($link['VisibleAfterLogin'])) {
                return InstallCode::INVALID_FRONEND_LINK_VISIBILITY;
            }
            \preg_match("/[NY]{1,1}/", $link['PrintButton'], $hits3);
            if (\strlen($hits3[0]) !== \strlen($link['PrintButton'])) {
                return InstallCode::INVALID_FRONEND_LINK_PRINT;
            }
            if (isset($link['NoFollow'])) {
                \preg_match("/[NY]{1,1}/", $link['NoFollow'], $hits3);
            } else {
                $hits3 = [];
            }
            if (isset($hits3[0]) && \strlen($hits3[0]) !== \strlen($link['NoFollow'])) {
                return InstallCode::INVALID_FRONTEND_LINK_NO_FOLLOW;
            }
            if (!isset($link['LinkLanguage'])
                || !\is_array($link['LinkLanguage'])
                || \count($link['LinkLanguage']) === 0
            ) {
                return InstallCode::INVALID_FRONEND_LINK_ISO;
            }
            foreach ($link['LinkLanguage'] as $l => $localized) {
                \preg_match('/[0-9]+\sattr/', $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($l)) {
                    \preg_match("/[A-Z]{3}/", $localized['iso'], $hits);
                    $len = \strlen($localized['iso']);
                    if ($len === 0 || \strlen($hits[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_ISO;
                    }
                } elseif (\strlen($hits2[0]) === \strlen($l)) {
                    \preg_match("/[a-zA-Z0-9- ]+/", $localized['Seo'], $hits1);
                    $len = \strlen($localized['Seo']);
                    if ($len === 0 || \strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_SEO;
                    }
                    \preg_match(
                        "/[a-zA-Z0-9äÄüÜöÖß" . "\- ]+/",
                        $localized['Name'],
                        $hits1
                    );
                    $len = \strlen($localized['Name']);
                    if ($len === 0 || \strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_NAME;
                    }
                    \preg_match(
                        "/[a-zA-Z0-9äÄüÜöÖß" . "\- ]+/",
                        $localized['Title'],
                        $hits1
                    );
                    $len = \strlen($localized['Title']);
                    if ($len === 0 || \strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_TITLE;
                    }
                    \preg_match(
                        "/[a-zA-Z0-9äÄüÜöÖß" . "\,\.\- ]+/",
                        $localized['MetaTitle'],
                        $hits1
                    );
                    $len = \strlen($localized['MetaTitle']);
                    if ($len === 0 && \strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_META_TITLE;
                    }
                    \preg_match(
                        "/[a-zA-Z0-9äÄüÜöÖß" . "\,\- ]+/",
                        $localized['MetaKeywords'],
                        $hits1
                    );
                    $len = \strlen($localized['MetaKeywords']);
                    if ($len === 0 || \strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_META_KEYWORDS;
                    }
                    \preg_match(
                        "/[a-zA-Z0-9äÄüÜöÖß" . "\,\.\- ]+/",
                        $localized['MetaDescription'],
                        $hits1
                    );
                    $len = \strlen($localized['MetaDescription']);
                    if ($len === 0 || \strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_FRONEND_LINK_META_DESCRIPTION;
                    }
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array  $installNode
     * @param string $versionedDir
     * @return int
     */
    private function validatePaymentMethods(array $installNode, string $versionedDir): int
    {
        if (!isset($installNode['PaymentMethod'][0]['Method'])
            || \is_array($installNode['PaymentMethod'][0]['Method'])
        ) {
            return InstallCode::OK;
        }
        foreach ($installNode['PaymentMethod'][0]['Method'] as $u => $method) {
            \preg_match('/[0-9]+\sattr/', $u, $hits1);
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            \preg_match(
                "/[a-zA-Z0-9äÄöÖüÜß" . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                $method['Name'],
                $hits1
            );
            if (\strlen($hits1[0]) !== \strlen($method['Name'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_NAME;
            }
            \preg_match('/[0-9]+/', $method['Sort'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($method['Sort'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_SORT;
            }
            \preg_match("/[0-1]{1}/", $method['SendMail'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($method['SendMail'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_MAIL;
            }
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
            \preg_match("/[0-1]{1}/", $method['PreOrder'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($method['PreOrder'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_PRE_ORDER;
            }
            \preg_match("/[0-1]{1}/", $method['Soap'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($method['Soap'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_SOAP;
            }
            \preg_match("/[0-1]{1}/", $method['Curl'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($method['Curl'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_CURL;
            }
            \preg_match('/[0-1]{1}/', $method['Sockets'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($method['Sockets'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_SOCKETS;
            }
            if (isset($method['ClassFile'])) {
                \preg_match('/[a-zA-Z0-9\/_\-.]+.php/', $method['ClassFile'], $hits1);
                if (\strlen($hits1[0]) === \strlen($method['ClassFile'])) {
                    if (!\file_exists($versionedDir . \PFAD_PLUGIN_PAYMENTMETHOD . $method['ClassFile'])) {
                        return InstallCode::MISSING_PAYMENT_METHOD_FILE;
                    }
                } else {
                    return InstallCode::INVALID_PAYMENT_METHOD_CLASS_FILE;
                }
            }
            if (isset($method['ClassName'])) {
                \preg_match("/[a-zA-Z0-9\/_\-]+/", $method['ClassName'], $hits1);
                if (\strlen($hits1[0]) !== \strlen($method['ClassName'])) {
                    return InstallCode::INVALID_PAYMENT_METHOD_CLASS_NAME;
                }
            }
            if (isset($method['TemplateFile']) && \strlen($method['TemplateFile']) > 0) {
                \preg_match(
                    '/[a-zA-Z0-9\/_\-.]+.tpl/',
                    $method['TemplateFile'],
                    $hits1
                );
                if (\strlen($hits1[0]) !== \strlen($method['TemplateFile'])) {
                    return InstallCode::INVALID_PAYMENT_METHOD_TEMPLATE;
                }
                if (!\file_exists($versionedDir . \PFAD_PLUGIN_PAYMENTMETHOD . $method['TemplateFile'])) {
                    return InstallCode::MISSING_PAYMENT_METHOD_TEMPLATE;
                }
            }
            if (isset($method['AdditionalTemplateFile']) && \strlen($method['AdditionalTemplateFile']) > 0) {
                \preg_match(
                    '/[a-zA-Z0-9\/_\-.]+.tpl/',
                    $method['AdditionalTemplateFile'],
                    $hits1
                );
                if (\strlen($hits1[0]) !== \strlen($method['AdditionalTemplateFile'])) {
                    return InstallCode::INVALID_PAYMENT_METHOD_ADDITIONAL_STEP_TEMPLATE_FILE;
                }
                if (!\file_exists($versionedDir . \PFAD_PLUGIN_PAYMENTMETHOD . $method['AdditionalTemplateFile'])) {
                    return InstallCode::MISSING_PAYMENT_METHOD_ADDITIONAL_STEP_FILE;
                }
            }
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
                    \preg_match("/[A-Z]{3}/", $MethodLanguage_arr['iso'], $hits);
                    $len = \strlen($MethodLanguage_arr['iso']);
                    if ($len === 0 || \strlen($hits[0]) !== $len) {
                        return InstallCode::INVALID_PAYMENT_METHOD_LANGUAGE_ISO;
                    }
                } elseif (isset($hits2[0]) && \strlen($hits2[0]) === \strlen($l)) {
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
            $type = '';
            if (!isset($method['Setting']) || !\is_array($method['Setting']) || !\count($method['Setting']) === 0) {
                continue;
            }
            foreach ($method['Setting'] as $j => $setting) {
                \preg_match('/[0-9]+\sattr/', $j, $hits3);
                \preg_match('/[0-9]+/', $j, $hits4);
                if (isset($hits3[0]) && \strlen($hits3[0]) === \strlen($j)) {
                    $type = $setting['type'];
                    if (\strlen($setting['type']) === 0) {
                        return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_TYPE;
                    }
                    if (\strlen($setting['sort']) === 0) {
                        return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_SORT;
                    }
                    if (\strlen($setting['conf']) === 0) {
                        return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_CONF;
                    }
                } elseif (isset($hits4[0]) && \strlen($hits4[0]) === \strlen($j)) {
                    if (\strlen($setting['Name']) === 0) {
                        return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_NAME;
                    }
                    if (\strlen($setting['ValueName']) === 0) {
                        return InstallCode::INVALID_PAYMENT_METHOD_VALUE_NAME;
                    }
                    if ($type === 'selectbox') {
                        if (!isset($setting['SelectboxOptions'])
                            || !\is_array($setting['SelectboxOptions'])
                            || \count($setting['SelectboxOptions']) === 0
                        ) {
                            return InstallCode::MISSING_PAYMENT_METHOD_SELECTBOX_OPTIONS;
                        }
                        if (\count($setting['SelectboxOptions'][0]) === 1) {
                            foreach ($setting['SelectboxOptions'][0]['Option'] as $y => $Option_arr) {
                                \preg_match('/[0-9]+\sattr/', $y, $hits6);
                                \preg_match('/[0-9]+/', $y, $hits7);
                                if (isset($hits6[0]) && \strlen($hits6[0]) === \strlen($y)) {
                                    if (\strlen($Option_arr['value']) === 0) {
                                        return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                    if (\strlen($Option_arr['sort']) === 0) {
                                        return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                } elseif (isset($hits7[0]) && \strlen($hits7[0]) === \strlen($y)) {
                                    if (\strlen($Option_arr) === 0) {
                                        return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                }
                            }
                        } elseif (\count($setting['SelectboxOptions'][0]) === 2) {
                            //Es gibt nur 1 Option
                            if (\strlen($setting['SelectboxOptions'][0]['Option attr']['value']) === 0) {
                                return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                            }
                            if (\strlen($setting['SelectboxOptions'][0]['Option attr']['sort']) === 0) {
                                return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                            }
                            if (\strlen($setting['SelectboxOptions'][0]['Option']) === 0) {
                                return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                            }
                        }
                    } elseif ($type === 'radio') {
                        if (!isset($setting['RadioOptions'])
                            || !\is_array($setting['RadioOptions'])
                            || \count($setting['RadioOptions']) === 0
                        ) {
                            return InstallCode::MISSING_PAYMENT_METHOD_SELECTBOX_OPTIONS;
                        }
                        if (\count($setting['RadioOptions'][0]) === 1) {
                            foreach ($setting['RadioOptions'][0]['Option'] as $y => $Option_arr) {
                                \preg_match('/[0-9]+\sattr/', $y, $hits6);
                                \preg_match('/[0-9]+/', $y, $hits7);
                                if (isset($hits6[0]) && \strlen($hits6[0]) === \strlen($y)) {
                                    if (\strlen($Option_arr['value']) === 0) {
                                        return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                    if (\strlen($Option_arr['sort']) === 0) {
                                        return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                } elseif (isset($hits7[0]) && \strlen($hits7[0]) === \strlen($y)) {
                                    if (\strlen($Option_arr) === 0) {
                                        return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                }
                            }
                        } elseif (\count($setting['RadioOptions'][0]) === 2) { //Es gibt nur 1 Option
                            if (\strlen($setting['RadioOptions'][0]['Option attr']['value']) === 0) {
                                return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                            }
                            if (\strlen($setting['RadioOptions'][0]['Option attr']['sort']) === 0) {
                                return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                            }
                            if (\strlen($setting['RadioOptions'][0]['Option']) === 0) {
                                return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                            }
                        }
                    }
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array  $installNode
     * @param string $versionedDir
     * @return int
     */
    private function validatePortlets(array $installNode, string $versionedDir): int
    {
        if (!isset($installNode['Portlets']) || !\is_array($installNode['Portlets'])) {
            return InstallCode::OK;
        }
        if (!isset($installNode['Portlets'][0]['Portlet'])
            || !\is_array($installNode['Portlets'][0]['Portlet'])
            || \count($installNode['Portlets'][0]['Portlet']) === 0
        ) {
            return InstallCode::MISSING_PORTLETS;
        }
        foreach ($installNode['Portlets'][0]['Portlet'] as $u => $portlet) {
            \preg_match('/[0-9]+\sattr/', $u, $hits1);
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) === \strlen($u)) {
                \preg_match(
                    "/[a-zA-Z0-9\/_\-äÄüÜöÖß" . \utf8_decode('äÄüÜöÖß') . "\(\) ]+/",
                    $portlet['Title'],
                    $hits1
                );
                if (\strlen($hits1[0]) !== \strlen($portlet['Title'])) {
                    return InstallCode::INVALID_PORTLET_TITLE;
                }
                \preg_match("/[a-zA-Z0-9\/_\-.]+/", $portlet['Class'], $hits1);
                if (\strlen($hits1[0]) === \strlen($portlet['Class'])) {
                    if (!\file_exists($versionedDir .
                        \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_PORTLETS . $portlet['Class'] . '/' .
                        $portlet['Class'] . '.php')
                    ) {
                        return InstallCode::INVALID_PORTLET_CLASS_FILE;
                    }
                } else {
                    return InstallCode::INVALID_PORTLET_CLASS;
                }
                \preg_match(
                    "/[a-zA-Z0-9\/_\-äÄüÜöÖß" . \utf8_decode('äÄüÜöÖß') . "\(\) ]+/",
                    $portlet['Group'],
                    $hits1
                );
                if (\strlen($hits1[0]) !== \strlen($portlet['Group'])) {
                    return InstallCode::INVALID_PORTLET_GROUP;
                }
                \preg_match("/[0-1]{1}/", $portlet['Active'], $hits1);
                if (\strlen($hits1[0]) !== \strlen($portlet['Active'])) {
                    return InstallCode::INVALID_PORTLET_ACTIVE;
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array  $installNode
     * @param string $versionedDir
     * @return int
     */
    private function validateBlueprints(array $installNode, string $versionedDir): int
    {
        if (!isset($installNode['Blueprints']) || !\is_array($installNode['Blueprints'])) {
            return InstallCode::OK;
        }
        if (!isset($installNode['Blueprints'][0]['Blueprint'])
            || !\is_array($installNode['Blueprints'][0]['Blueprint'])
            || \count($installNode['Blueprints'][0]['Blueprint']) === 0
        ) {
            return InstallCode::MISSING_BLUEPRINTS;
        }
        $base = $versionedDir . \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_BLUEPRINTS;
        foreach ($installNode['Blueprints'][0]['Blueprint'] as $u => $blueprint) {
            \preg_match('/[0-9]+\sattr/', $u, $hits1);
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) === \strlen($u)) {
                \preg_match(
                    "/[a-zA-Z0-9\/_\-\ äÄüÜöÖß" . \utf8_decode('äÄüÜöÖß') . "\(\) ]+/",
                    $blueprint['Name'],
                    $hits1
                );
                if (\strlen($hits1[0]) !== \strlen($blueprint['Name'])) {
                    return InstallCode::INVALID_BLUEPRINT_NAME;
                }
                if (!\is_file($base . $blueprint['JSONFile'])) {
                    return InstallCode::INVALID_BLUEPRINT_FILE;
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array  $installNode
     * @param string $versionedDir
     * @return int
     */
    private function validateBoxTemplates(array $installNode, string $versionedDir): int
    {
        if (!isset($installNode['Boxes']) || !\is_array($installNode['Boxes'])) {
            return InstallCode::OK;
        }
        if (!isset($installNode['Boxes'][0]['Box'])
            || !\is_array($installNode['Boxes'][0]['Box'])
            || \count($installNode['Boxes'][0]['Box']) === 0
        ) {
            return InstallCode::MISSING_BOX;
        }
        $base = $versionedDir . \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_BOXEN;
        foreach ($installNode['Boxes'][0]['Box'] as $h => $box) {
            \preg_match('/[0-9]+/', $h, $hits3);
            if (\strlen($hits3[0]) !== \strlen($h)) {
                continue;
            }
            if (empty($box['Name'])) {
                return InstallCode::INVALID_BOX_NAME;
            }
            if (empty($box['TemplateFile'])) {
                return InstallCode::INVALID_BOX_TEMPLATE;
            }
            if (!\file_exists($base . $box['TemplateFile'])) {
                return InstallCode::MISSING_BOX_TEMPLATE_FILE;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array  $installNode
     * @param string $versionedDir
     * @return int
     */
    private function validateMailTemplates(array $installNode): int
    {
        if (!isset($installNode['Emailtemplate']) || !\is_array($installNode['Emailtemplate'])) {
            return InstallCode::OK;
        }
        if (!isset($installNode['Emailtemplate'][0]['Template'])
            || !\is_array($installNode['Emailtemplate'][0]['Template'])
            || \count($installNode['Emailtemplate'][0]['Template']) === 0
        ) {
            return InstallCode::MISSING_EMAIL_TEMPLATES;
        }
        foreach ($installNode['Emailtemplate'][0]['Template'] as $u => $tpl) {
            \preg_match('/[0-9]+\sattr/', $u, $hits1);
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            \preg_match(
                "/[a-zA-Z0-9\/_\-äÄüÜöÖß" . " ]+/",
                $tpl['Name'],
                $hits1
            );
            if (\strlen($hits1[0]) !== \strlen($tpl['Name'])) {
                return InstallCode::INVALID_TEMPLATE_NAME;
            }
            if ($tpl['Type'] !== 'text/html' && $tpl['Type'] !== 'text') {
                return InstallCode::INVALID_TEMPLATE_TYPE;
            }
            if (\strlen($tpl['ModulId']) === 0) {
                return InstallCode::INVALID_TEMPLATE_MODULE_ID;
            }
            if (\strlen($tpl['Active']) === 0) {
                return InstallCode::INVALID_TEMPLATE_ACTIVE;
            }
            if (\strlen($tpl['AKZ']) === 0) {
                return InstallCode::INVALID_TEMPLATE_AKZ;
            }
            if (\strlen($tpl['AGB']) === 0) {
                return InstallCode::INVALID_TEMPLATE_AGB;
            }
            if (\strlen($tpl['WRB']) === 0) {
                return InstallCode::INVALID_TEMPLATE_WRB;
            }
            if (!isset($tpl['TemplateLanguage'])
                || !\is_array($tpl['TemplateLanguage'])
                || \count($tpl['TemplateLanguage']) === 0
            ) {
                return InstallCode::MISSING_EMAIL_TEMPLATE_LANGUAGE;
            }
            foreach ($tpl['TemplateLanguage'] as $l => $localized) {
                \preg_match('/[0-9]+\sattr/', $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($l)) {
                    \preg_match("/[A-Z]{3}/", $localized['iso'], $hits);
                    $len = \strlen($localized['iso']);
                    if ($len === 0 || \strlen($hits[0]) !== $len) {
                        return InstallCode::INVALID_EMAIL_TEMPLATE_ISO;
                    }
                } elseif (\strlen($hits2[0]) === \strlen($l)) {
                    \preg_match("/[a-zA-Z0-9\/_\-.#: ]+/", $localized['Subject'], $hits1);
                    $len = \strlen($localized['Subject']);
                    if ($len === 0 || \strlen($hits1[0]) !== $len) {
                        return InstallCode::INVALID_EMAIL_TEMPLATE_SUBJECT;
                    }
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $installNode
     * @return int
     */
    private function validateLocalization(array $installNode): int
    {
        if (!isset($installNode['Locales']) || !\is_array($installNode['Locales'])) {
            return InstallCode::OK;
        }
        if (!isset($installNode['Locales'][0]['Variable'])
            || !\is_array($installNode['Locales'][0]['Variable'])
            || \count($installNode['Locales'][0]['Variable']) === 0
        ) {
            return InstallCode::MISSING_LANG_VARS;
        }
        foreach ($installNode['Locales'][0]['Variable'] as $t => $var) {
            \preg_match('/[0-9]+/', $t, $hits2);
            if (\strlen($hits2[0]) !== \strlen($t)) {
                continue;
            }
            if (\strlen($var['Name']) === 0) {
                return InstallCode::INVALID_LANG_VAR_NAME;
            }
            // Nur eine Sprache vorhanden
            if (isset($var['VariableLocalized attr'])
                && \is_array($var['VariableLocalized attr'])
                && \count($var['VariableLocalized attr']) > 0
            ) {
                if (!isset($var['VariableLocalized attr']['iso'])) {
                    return InstallCode::MISSING_LOCALIZED_LANG_VAR;
                }
                \preg_match("/[A-Z]{3}/", $var['VariableLocalized attr']['iso'], $hits);
                if (\strlen($hits[0]) !== \strlen($var['VariableLocalized attr']['iso'])) {
                    return InstallCode::INVALID_LANG_VAR_ISO;
                }
                if (\strlen($var['VariableLocalized']) === 0) {
                    return InstallCode::INVALID_LOCALIZED_LANG_VAR_NAME;
                }
            } elseif (isset($var['VariableLocalized'])
                && \is_array($var['VariableLocalized'])
                && \count($var['VariableLocalized']) > 0
            ) {
                // Mehr als eine Sprache vorhanden
                foreach ($var['VariableLocalized'] as $i => $localized) {
                    \preg_match('/[0-9]+\sattr/', $i, $hits1);
                    \preg_match('/[0-9]+/', $i, $hits2);
                    if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($i)) {
                        \preg_match("/[A-Z]{3}/", $localized['iso'], $hits);
                        $len = \strlen($localized['iso']);
                        if ($len === 0 || \strlen($hits[0]) !== $len) {
                            return InstallCode::INVALID_LANG_VAR_ISO;
                        }
                    } elseif (isset($hits2[0]) && \strlen($hits2[0]) === \strlen($i)) {
                        if (\strlen($localized) === 0) {
                            return InstallCode::INVALID_LOCALIZED_LANG_VAR_NAME;
                        }
                    }
                }
            } else {
                return InstallCode::MISSING_LOCALIZED_LANG_VAR;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $installNode
     * @return int
     */
    private function validateCheckboxes(array $installNode): int
    {
        if (!isset($installNode['CheckBoxFunction'][0]['Function'])
            || !\is_array($installNode['CheckBoxFunction'][0]['Function'])
            || \count($installNode['CheckBoxFunction'][0]['Function']) === 0
        ) {
            return InstallCode::OK;
        }
        foreach ($installNode['CheckBoxFunction'][0]['Function'] as $t => $cb) {
            \preg_match('/[0-9]+/', $t, $hits2);
            if (\strlen($hits2[0]) === \strlen($t)) {
                if (\strlen($cb['Name']) === 0) {
                    return InstallCode::INVALID_CHECKBOX_FUNCTION_NAME;
                }
                if (\strlen($cb['ID']) === 0) {
                    return InstallCode::INVALID_CHECKBOX_FUNCTION_ID;
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array  $installNode
     * @param string $versionedDir
     * @param string $pluginID
     * @return int
     */
    private function validateWidgets(array $installNode, string $versionedDir, string $pluginID): int
    {
        if (!isset($installNode['AdminWidget']) || !\is_array($installNode['AdminWidget'])) {
            return InstallCode::OK;
        }
        if (!isset($installNode['AdminWidget'][0]['Widget'])
            || !\is_array($installNode['AdminWidget'][0]['Widget'])
            || \count($installNode['AdminWidget'][0]['Widget']) === 0
        ) {
            return InstallCode::MISSING_WIDGETS;
        }
        $base = $versionedDir . \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_WIDGET;
        foreach ($installNode['AdminWidget'][0]['Widget'] as $u => $widget) {
            \preg_match('/[0-9]+\sattr/', $u, $hits1);
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            \preg_match(
                "/[a-zA-Z0-9\/_\-äÄüÜöÖß" . "\(\) ]+/",
                $widget['Title'],
                $hits1
            );
            if (\strlen($hits1[0]) !== \strlen($widget['Title'])) {
                return InstallCode::INVALID_WIDGET_TITLE;
            }
            \preg_match("/[a-zA-Z0-9\/_\-.]+/", $widget['Class'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($widget['Class'])) {
                return InstallCode::INVALID_WIDGET_CLASS;
            }
            if (!\file_exists($base . 'class.Widget' . $widget['Class'] . '_' . $pluginID . '.php')) {
                return InstallCode::MISSING_WIDGET_CLASS_FILE;
            }
            if (!\in_array($widget['Container'], ['center', 'left', 'right'], true)) {
                return InstallCode::INVALID_WIDGET_CONTAINER;
            }
            \preg_match('/[0-9]+/', $widget['Pos'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($widget['Pos'])) {
                return InstallCode::INVALID_WIDGET_POS;
            }
            \preg_match("/[0-1]{1}/", $widget['Expanded'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($widget['Expanded'])) {
                return InstallCode::INVALID_WIDGET_EXPANDED;
            }
            \preg_match("/[0-1]{1}/", $widget['Active'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($widget['Active'])) {
                return InstallCode::INVALID_WIDGET_ACTIVE;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array  $installNode
     * @param string $versionedDir
     * @return int
     */
    private function validateExports(array $installNode, string $versionedDir): int
    {
        if (!isset($installNode['ExportFormat']) || !\is_array($installNode['ExportFormat'])) {
            return InstallCode::OK;
        }
        if (!isset($installNode['ExportFormat'][0]['Format'])
            || !\is_array($installNode['ExportFormat'][0]['Format'])
            || \count($installNode['ExportFormat'][0]['Format']) === 0
        ) {
            return InstallCode::MISSING_FORMATS;
        }
        $base = $versionedDir . \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_EXPORTFORMAT;
        foreach ($installNode['ExportFormat'][0]['Format'] as $h => $export) {
            \preg_match('/[0-9]+\sattr/', $h, $hits1);
            \preg_match('/[0-9]+/', $h, $hits2);
            if (\strlen($hits2[0]) !== \strlen($h)) {
                continue;
            }
            if (\strlen($export['Name']) === 0) {
                return InstallCode::INVALID_FORMAT_NAME;
            }
            if (\strlen($export['FileName']) === 0) {
                return InstallCode::INVALID_FORMAT_FILE_NAME;
            }
            if ((!isset($export['Content']) || \strlen($export['Content']) === 0)
                && (!isset($export['ContentFile']) || \strlen($export['ContentFile']) === 0)
            ) {
                return InstallCode::MISSING_FORMAT_CONTENT;
            }
            if ($export['Encoding'] !== 'ASCII' && $export['Encoding'] !== 'UTF-8') {
                return InstallCode::INVALID_FORMAT_ENCODING;
            }
            if (\strlen($export['ShippingCostsDeliveryCountry']) === 0) {
                return InstallCode::INVALID_FORMAT_SHIPPING_COSTS_DELIVERY_COUNTRY;
            }
            if (\strlen($export['ContentFile']) > 0 && !\file_exists($base . $export['ContentFile'])) {
                return InstallCode::INVALID_FORMAT_CONTENT_FILE;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array  $installNode
     * @param string $versionedDir
     * @return int
     */
    private function validateExtendedTemplates(array $installNode, string $versionedDir): int
    {
        if (!isset($installNode['ExtendedTemplates']) || !\is_array($installNode['ExtendedTemplates'])) {
            return InstallCode::OK;
        }
        if (!isset($installNode['ExtendedTemplates'][0]['Template'])) {
            return InstallCode::MISSING_EXTENDED_TEMPLATE;
        }
        foreach ((array)$installNode['ExtendedTemplates'][0]['Template'] as $template) {
            \preg_match('/[a-zA-Z0-9\/_\-]+\.tpl/', $template, $hits3);
            if (\strlen($hits3[0]) !== \strlen($template)) {
                return InstallCode::INVALID_EXTENDED_TEMPLATE_FILE_NAME;
            }
            if (!\file_exists($versionedDir . \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_TEMPLATE . $template)) {
                return InstallCode::MISSING_EXTENDED_TEMPLATE_FILE;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array  $baseNode
     * @param string $versionedDir
     * @return int
     */
    private function validateUninstaller(array $baseNode, string $versionedDir): int
    {
        if (isset($baseNode['Uninstall'])
            && \strlen($baseNode['Uninstall']) > 0
            && !\file_exists($versionedDir . \PFAD_PLUGIN_UNINSTALL . $baseNode['Uninstall'])
        ) {
            return InstallCode::MISSING_UNINSTALL_FILE;
        }

        return InstallCode::OK;
    }
}
