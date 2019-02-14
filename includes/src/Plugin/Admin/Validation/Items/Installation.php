<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class Installation
 * @package JTL\Plugin\Admin\Validation\Items
 */
class Installation extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $baseNode = $this->getBaseNode();

        return !isset($baseNode['Install']) || !\is_array($baseNode['Install'])
            ? InstallCode::INSTALL_NODE_MISSING
            : InstallCode::OK;
    }
}
