<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

/**
 * Class Blueprints
 * @package Plugin\Admin\Validation\Items
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
        foreach ($node['Blueprints'][0]['Blueprint'] as $u => $blueprint) {
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
}
