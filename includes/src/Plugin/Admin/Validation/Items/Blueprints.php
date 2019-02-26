<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class Blueprints
 * @package JTL\Plugin\Admin\Validation\Items
 */
class Blueprints extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        $dir  = $this->getDir();
        if (!isset($node['Blueprints']) || !\is_array($node['Blueprints'])) {
            return InstallCode::OK;
        }
        if (!isset($node['Blueprints'][0]['Blueprint'])
            || !\is_array($node['Blueprints'][0]['Blueprint'])
            || \count($node['Blueprints'][0]['Blueprint']) === 0
        ) {
            return InstallCode::MISSING_BLUEPRINTS;
        }
        $base = $dir . \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_BLUEPRINTS;
        foreach ($node['Blueprints'][0]['Blueprint'] as $i => $blueprint) {
            $i = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) === \mb_strlen($i)) {
                \preg_match(
                    '/[a-zA-Z0-9\/_\-\ äÄüÜöÖß' . \utf8_decode('äÄüÜöÖß') . '\(\) ]+/',
                    $blueprint['Name'],
                    $hits1
                );
                if (\mb_strlen($hits1[0]) !== \mb_strlen($blueprint['Name'])) {
                    return InstallCode::INVALID_BLUEPRINT_NAME;
                }
                if (!\is_file($base . $blueprint['JSONFile'])) {
                    return InstallCode::INVALID_BLUEPRINT_FILE;
                }
            }
        }

        return InstallCode::OK;
    }
}
