<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin;

/**
 * Class Migration
 * @package JTL\Plugin
 */
class Migration extends \JTL\Update\Migration
{
    /**
     * @return int|null
     */
    public function getId()
    {
        return MigrationHelper::mapClassNameToId($this->getName());
    }
}
