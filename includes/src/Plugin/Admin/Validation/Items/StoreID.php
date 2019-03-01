<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class StoreID
 * @package Plugin\Admin\Validation\Items
 */
class StoreID extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $baseNode = $this->getBaseNode();
        if (isset($baseNode['StoreID'])) {
            if (preg_match("/\\w+/", $baseNode['StoreID']) !== 1) {
                return InstallCode::INVALID_STORE_ID;
            }
        }

        return InstallCode::OK;
    }
}
