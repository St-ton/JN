<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

/**
 * Class Author
 * @package Plugin\Admin\Validation\Items
 */
class Author extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $baseNode = $this->getBaseNode();

        return isset($baseNode['Author']) ? InstallCode::OK : InstallCode::INVALID_AUTHOR;
    }
}
