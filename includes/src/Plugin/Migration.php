<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Plugin\Admin\MigrationHelper;

/**
 * Class Migration
 * @package Plugin
 */
class Migration extends \Migration
{
    /**
     * @return string|null
     */
    public function getId()
    {
        return MigrationHelper::mapClassNameToId($this->getName());
    }
}
