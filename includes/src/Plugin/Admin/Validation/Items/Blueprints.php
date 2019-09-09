<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Helpers\GeneralObject;
use JTL\Plugin\InstallCode;
use JTL\Shop;

/**
 * Class Blueprints
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class Blueprints extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node   = $this->getInstallNode();
        $dir    = $this->getDir();
        $bpNode = $node['Blueprints'][0] ?? null;
        $base   = $dir . \PFAD_PLUGIN_BLUEPRINTS;
        if ($bpNode === null) {
            return InstallCode::OK;
        }
        if (!GeneralObject::hasCount('Blueprint', $bpNode)) {
            return InstallCode::MISSING_BLUEPRINTS;
        }
        foreach ($bpNode['Blueprint'] as $i => $blueprint) {
            $i = (string)$i;
            if (!isset($blueprint['Name'])) {
                return InstallCode::INVALID_BLUEPRINT_NAME;
            }
            if (!isset($blueprint['JSONFile'])) {
                return InstallCode::INVALID_BLUEPRINT_FILE;
            }
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
