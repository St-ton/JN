<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation\Items;

use Plugin\InstallCode;

/**
 * Class Templates
 * @package Plugin\Admin\Installation\Items
 */
class Templates extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): ?array
    {
        return isset($this->baseNode['Install'][0]['ExtendedTemplates'])
        && \is_array($this->baseNode['Install'][0]['ExtendedTemplates'])
            ? (array)$this->baseNode['Install'][0]['ExtendedTemplates'][0]['Template']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        foreach ($this->getNode() as $template) {
            \preg_match("/[a-zA-Z0-9\/_\-]+\.tpl/", $template, $hits3);
            if (\strlen($hits3[0]) !== \strlen($template)) {
                continue;
            }
            $plgnTpl            = new \stdClass();
            $plgnTpl->kPlugin   = $this->plugin->kPlugin;
            $plgnTpl->cTemplate = $template;
            if (!$this->db->insert('tplugintemplate', $plgnTpl)) {
                return InstallCode::SQL_CANNOT_SAVE_TEMPLATE;
            }
        }

        return InstallCode::OK;
    }
}
