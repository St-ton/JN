<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation;

use JTLShop\SemVer\Version;
use Plugin\InstallCode;

/**
 * Class ExtensionValidator
 * @package Plugin\Admin\Validation
 */
final class ExtensionValidator extends AbstractValidator
{
    protected const BASE_DIR = \PFAD_ROOT . \PFAD_EXTENSIONS;

    /**
     * @inheritdoc
     */
    public function pluginPlausiIntern($xml, bool $forUpdate): int
    {
        $baseNode    = $xml['jtlshopplugin'][0] ?? null;
        $appVersion  = Version::parse(\APPLICATION_VERSION);
        $shopVersion = null;
        if (!isset($baseNode['XMLVersion'])) {
            return InstallCode::INVALID_XML_VERSION;
        }
        \preg_match('/[0-9]{3}/', $baseNode['XMLVersion'], $hits);
        if (\count($hits) === 0
            || (\mb_strlen($hits[0]) !== \mb_strlen($baseNode['XMLVersion']) && (int)$baseNode['XMLVersion'] >= 100)
        ) {
            return InstallCode::INVALID_XML_VERSION;
        }
        if (empty($baseNode['ShopVersion']) && empty($baseNode['Shop4Version'])) {
            return InstallCode::INVALID_SHOP_VERSION;
        }
        if ($forUpdate === false) {
            $check = $this->db->select('tplugin', 'cPluginID', $baseNode['PluginID']);
            if (isset($check->kPlugin) && $check->kPlugin > 0) {
                return InstallCode::DUPLICATE_PLUGIN_ID;
            }
        }
        if (isset($baseNode['ShopVersion'])) {
            $shopVersion = Version::parse($baseNode['ShopVersion']);
        }
        if (empty($appVersion) || empty($shopVersion) || $shopVersion->greaterThan($appVersion)) {
            return InstallCode::SHOP_VERSION_COMPATIBILITY;
        }

        $cVersionsnummer = $this->getVersion($baseNode);
        if (!\is_string($cVersionsnummer)) {
            return $cVersionsnummer;
        }
        $validation = new ExtensionValidationFactory();
        $checks     = $validation->getValidations($baseNode, $this->dir, $cVersionsnummer, $baseNode['PluginID']);
        foreach ($checks as $check) {
            $check->setDir($this->dir . \DIRECTORY_SEPARATOR); // override versioned dir from base validator
            $res = $check->validate();
            if ($res !== InstallCode::OK) {
                return $res;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $node
     * @return int|string
     */
    private function getVersion($node)
    {
        return !isset($node['Version']) || \is_array($node['Version'])
            ? InstallCode::INVALID_VERSION_NUMBER
            : $node['Version'];
    }
}
