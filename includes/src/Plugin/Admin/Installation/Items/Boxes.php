<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation\Items;

use Plugin\InstallCode;

/**
 * Class Boxes
 * @package Plugin\Admin\Installation\Items
 */
class Boxes extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): ?array
    {
        return isset($this->baseNode['Install'][0]['Boxes'])
        && \is_array($this->baseNode['Install'][0]['Boxes'])
            ? $this->baseNode['Install'][0]['Boxes'][0]['Box']
            : [];
    }
    
    /**
     * @inheritdoc
     */
    public function install(): int
    {
        foreach ($this->getNode() as $h => $box) {
            \preg_match('/[0-9]+/', $h, $hits3);
            if (\strlen($hits3[0]) !== \strlen($h)) {
                continue;
            }
            $boxTpl              = new \stdClass();
            $boxTpl->kCustomID   = $this->plugin->kPlugin;
            $boxTpl->eTyp        = $this->plugin->bExtension === 1 ? 'extension' : 'plugin'; // @todo
            $boxTpl->cName       = $box['Name'];
            $boxTpl->cVerfuegbar = $box['Available'];
            $boxTpl->cTemplate   = $box['TemplateFile'];
            if (!$this->db->insert('tboxvorlage', $boxTpl)) {
                return InstallCode::SQL_CANNOT_SAVE_BOX_TEMPLATE;
            }
        }

        return InstallCode::OK;
    }
}
