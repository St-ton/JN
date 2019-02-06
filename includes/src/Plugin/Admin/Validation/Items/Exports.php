<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

/**
 * Class Exports
 * @package Plugin\Admin\Validation\Items
 */
class Exports extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        $dir  = $this->getDir();
        if (!isset($node['ExportFormat']) || !\is_array($node['ExportFormat'])) {
            return InstallCode::OK;
        }
        if (!isset($node['ExportFormat'][0]['Format'])
            || !\is_array($node['ExportFormat'][0]['Format'])
            || \count($node['ExportFormat'][0]['Format']) === 0
        ) {
            return InstallCode::MISSING_FORMATS;
        }
        $base = $dir . \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_EXPORTFORMAT;
        foreach ($node['ExportFormat'][0]['Format'] as $i => $export) {
            $i = (string)$i;
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
                continue;
            }
            if (\mb_strlen($export['Name']) === 0) {
                return InstallCode::INVALID_FORMAT_NAME;
            }
            if (\mb_strlen($export['FileName']) === 0) {
                return InstallCode::INVALID_FORMAT_FILE_NAME;
            }
            if ((!isset($export['Content']) || \mb_strlen($export['Content']) === 0)
                && (!isset($export['ContentFile']) || \mb_strlen($export['ContentFile']) === 0)
            ) {
                return InstallCode::MISSING_FORMAT_CONTENT;
            }
            if ($export['Encoding'] !== 'ASCII' && $export['Encoding'] !== 'UTF-8') {
                return InstallCode::INVALID_FORMAT_ENCODING;
            }
            if (\mb_strlen($export['ShippingCostsDeliveryCountry']) === 0) {
                return InstallCode::INVALID_FORMAT_SHIPPING_COSTS_DELIVERY_COUNTRY;
            }
            if (\mb_strlen($export['ContentFile']) > 0 && !\file_exists($base . $export['ContentFile'])) {
                return InstallCode::INVALID_FORMAT_CONTENT_FILE;
            }
        }

        return InstallCode::OK;
    }
}
