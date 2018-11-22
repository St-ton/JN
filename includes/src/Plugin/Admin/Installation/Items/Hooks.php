<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation\Items;

use Plugin\InstallCode;

/**
 * Class Hooks
 * @package Plugin\Admin\Installation\Items
 */
class Hooks extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['Hooks'])
        && \is_array($this->baseNode['Install'][0]['Hooks'])
            ? $this->baseNode['Install'][0]['Hooks']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        $node      = $this->getNode();
        $count     = \count($node[0] ?? []);
        $nHookID   = 0;
        $nPriority = 5;
        $hooks     = [];
        if ($count === 1) {
            foreach ($node[0]['Hook'] as $i => $hook) {
                \preg_match("/[0-9]+\sattr/", $i, $hits1);
                \preg_match('/[0-9]+/', $i, $hits2);
                if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($i)) {
                    $nHookID   = (int)$hook['id'];
                    $nPriority = isset($hook['priority']) ? (int)$hook['priority'] : 5;
                } elseif (isset($hits2[0]) && \strlen($hits2[0]) === \strlen($i)) {
                    $plugin             = new \stdClass();
                    $plugin->kPlugin    = $this->plugin->kPlugin;
                    $plugin->nHook      = $nHookID;
                    $plugin->nPriority  = $nPriority;
                    $plugin->cDateiname = $hook;

                    $hooks[] = $plugin;
                }
            }
        } elseif ($count > 1) {
            $hook               = $node[0];
            $plugin             = new \stdClass();
            $plugin->kPlugin    = $this->plugin->kPlugin;
            $plugin->nHook      = (int)$hook['Hook attr']['id'];
            $plugin->nPriority  = isset($hook['Hook attr']['priority'])
                ? (int)$hook['Hook attr']['priority']
                : $nPriority;
            $plugin->cDateiname = $hook['Hook'];

            $hooks[] = $plugin;
        }

        foreach ($hooks as $hook) {
            if (!$this->db->insert('tpluginhook', $hook)) {
                return InstallCode::SQL_CANNOT_SAVE_HOOK;
            }
        }

        return InstallCode::OK;
    }
}
