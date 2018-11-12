<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

/**
 * Class Uninstaller
 * @package Plugin\Admin\Validation\Items
 */
class Uninstaller extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getBaseNode();
        $dir  = $this->getDir();
        if (isset($node['Uninstall'])
            && \strlen($node['Uninstall']) > 0
            && !\file_exists($dir . \PFAD_PLUGIN_UNINSTALL . $node['Uninstall'])
        ) {
            return InstallCode::MISSING_UNINSTALL_FILE;
        }

        return InstallCode::OK;
    }
}
