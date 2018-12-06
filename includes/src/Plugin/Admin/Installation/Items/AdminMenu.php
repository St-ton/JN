<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation\Items;

use Plugin\InstallCode;

/**
 * Class AdminMenu
 * @package Plugin\Admin\Installation\Items
 */
class AdminMenu extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['Adminmenu'])
        && \is_array($this->baseNode['Install'][0]['Adminmenu'])
            ? $this->baseNode['Install'][0]['Adminmenu']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        $node = $this->getNode();
        if (isset($node[0]['Customlink'])
            && \is_array($node[0]['Customlink'])
            && \count($node[0]['Customlink']) > 0
        ) {
            $sort = 0;
            foreach ($node[0]['Customlink'] as $i => $customLink) {
                $i = (string)$i;
                \preg_match("/[0-9]+\sattr/", $i, $hits1);
                \preg_match('/[0-9]+/', $i, $hits2);
                if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($i)) {
                    $sort = (int)$customLink['sort'];
                } elseif (\strlen($hits2[0]) === \strlen($i)) {
                    $menuItem             = new \stdClass();
                    $menuItem->kPlugin    = $this->plugin->kPlugin;
                    $menuItem->cName      = $customLink['Name'];
                    $menuItem->cDateiname = $customLink['Filename'];
                    $menuItem->nSort      = $sort;
                    $menuItem->nConf      = 0;
                    if (!$this->db->insert('tpluginadminmenu', $menuItem)) {
                        return InstallCode::SQL_CANNOT_SAVE_ADMIN_MENU_ITEM;
                    }
                }
            }
        }

        return InstallCode::OK;
    }
}
