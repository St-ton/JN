<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

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
        if (!isset($baseNode['StoreID'])) {
            return InstallCode::INVALID_STORE_ID;
        }
        \preg_match('/[\w_]+/', $baseNode['StoreID'], $hits);
        if (empty($baseNode['StoreID']) || \strlen($hits[0]) !== \strlen($baseNode['StoreID'])) {
            return InstallCode::INVALID_STORE_ID;
        }

        return InstallCode::OK;
    }
}
