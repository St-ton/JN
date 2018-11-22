<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation\Items;

use Plugin\InstallCode;

/**
 * Class Checkboxes
 * @package Plugin\Admin\Installation\Items
 */
class Checkboxes extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['CheckBoxFunction'][0]['Function'])
        && \is_array($this->baseNode['Install'][0]['CheckBoxFunction'][0]['Function'])
        && \count($this->baseNode['Install'][0]['CheckBoxFunction'][0]['Function']) > 0
            ? $this->baseNode['Install'][0]['CheckBoxFunction'][0]['Function']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        foreach ($this->getNode() as $t => $function) {
            \preg_match('/[0-9]+/', $t, $hits2);
            if (\strlen($hits2[0]) !== \strlen($t)) {
                continue;
            }
            $cbFunction          = new \stdClass();
            $cbFunction->kPlugin = $this->plugin->kPlugin;
            $cbFunction->cName   = $function['Name'];
            $cbFunction->cID     = $this->plugin->cPluginID . '_' . $function['ID'];
            $this->db->insert('tcheckboxfunktion', $cbFunction);
        }

        return InstallCode::OK;
    }
}
