<?php declare(strict_types=1);
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
    public function getNode(): array
    {
        return (array)($this->baseNode['Install'][0]['ExtendedTemplates'][0]['Template'] ?? []);
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        foreach ($this->getNode() as $template) {
            \preg_match('/[a-zA-Z0-9\/_\-]+\.tpl/', $template, $hits);
            if (\mb_strlen($hits[0]) !== \mb_strlen($template)) {
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
