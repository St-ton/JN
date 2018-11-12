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
use Plugin\Admin\Validation\Shop4ValidationFactory;
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
            return InstallCode::SHOP_VERSION_COMPATIBILITY;
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

        $cVersionsnummer = $this->getVersion($installNode, $this->dir);
        if (!\is_string($cVersionsnummer)) {
            return $cVersionsnummer;
        }
        $validation = new Shop4ValidationFactory();
        $checks     = $validation->getValidations($baseNode, $this->dir, $cVersionsnummer, $baseNode['PluginID']);
        foreach ($checks as $check) {
            $res = $check->validate();
            if ($res !== InstallCode::OK) {
                return $res;
            }
        }
        if ($nXMLVersion > 100) {
            return $isShop4Compatible ? InstallCode::OK : InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE;
        }

        return $isShop4Compatible ? InstallCode::OK : InstallCode::OK_BUT_NOT_SHOP4_COMPATIBLE;
    }

    /**
     * @param array  $node
     * @param string $dir
     * @return int|string
     */
    private function getVersion($node, $dir)
    {
        if (!isset($node['Version'])
            || !\is_array($node['Version'])
            || !\count($node['Version']) === 0
        ) {
            return InstallCode::INVALID_XML_VERSION_NUMBER;
        }
        if ((int)$node['Version']['0 attr']['nr'] !== 100) {
            return InstallCode::INVALID_XML_VERSION_NUMBER;
        }
        $version = '';
        foreach ($node['Version'] as $i => $Version) {
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
                    && !\file_exists($dir . '/' . \PFAD_PLUGIN_VERSION . $version . '/' .
                        \PFAD_PLUGIN_SQL . $Version['SQL'])
                ) {
                    return InstallCode::MISSING_SQL_FILE;
                }
                if (!\is_dir($dir . '/' . \PFAD_PLUGIN_VERSION . $version)) {
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
}
