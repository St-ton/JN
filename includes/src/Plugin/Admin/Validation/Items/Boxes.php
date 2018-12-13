<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

/**
 * Class Boxes
 * @package Plugin\Admin\Validation\Items
 */
class Boxes extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        $dir  = $this->getDir();
        if (!isset($node['Boxes']) || !\is_array($node['Boxes'])) {
            return InstallCode::OK;
        }
        if (!isset($node['Boxes'][0]['Box'])
            || !\is_array($node['Boxes'][0]['Box'])
            || \count($node['Boxes'][0]['Box']) === 0
        ) {
            return InstallCode::MISSING_BOX;
        }
        $base = $dir . \PFAD_PLUGIN_FRONTEND . \PFAD_PLUGIN_BOXEN;
        foreach ($node['Boxes'][0]['Box'] as $i => $box) {
            $i = (string)$i;
            \preg_match('/[0-9]+/', $i, $hits3);
            if (\strlen($hits3[0]) !== \strlen($i)) {
                continue;
            }
            if (empty($box['Name'])) {
                return InstallCode::INVALID_BOX_NAME;
            }
            if (empty($box['TemplateFile'])) {
                return InstallCode::INVALID_BOX_TEMPLATE;
            }
            $new = $dir . \PFAD_PLUGIN_FRONTEND . 'boxes/' . $box['TemplateFile'];
            $old = $base . $box['TemplateFile'];
            if (!\file_exists($new) && !\file_exists($old)) {
                return InstallCode::MISSING_BOX_TEMPLATE_FILE;
            }
        }

        return InstallCode::OK;
    }
}
