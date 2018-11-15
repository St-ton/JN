<?php declare(strict_types=1);
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
     * @return int|null
     */
    public function getId()
    {
        return MigrationHelper::mapClassNameToId($this->getName());
    }
}
