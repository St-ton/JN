<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation\Items;

use Plugin\InstallCode;

/**
 * Class Uninstall
 * @package Plugin\Admin\Installation\Items
 */
class Uninstall extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): ?array
    {
        return !empty($base['Uninstall'])
            ? $base['Uninstall']
            : null;
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        if (($node = $this->getNode()) === null) {
            return InstallCode::OK;
        }
        $uninstall             = new \stdClass();
        $uninstall->kPlugin    = $this->plugin->kPlugin;
        $uninstall->cDateiname = $node;
        if (!$this->db->insert('tpluginuninstall', $uninstall)) {
            return InstallCode::SQL_CANNOT_SAVE_UNINSTALL;
        }

        return InstallCode::OK;
    }
}
