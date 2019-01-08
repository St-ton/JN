<?php declare(strict_types=1);
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
class NoUninstaller extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getBaseNode();

        return isset($node['Uninstall'])
            ? InstallCode::EXT_MUST_NOT_HAVE_UNINSTALLER
            : InstallCode::OK;
    }
}
