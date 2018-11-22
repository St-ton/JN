<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation\Items;

use Plugin\InstallCode;

/**
 * Class Portlets
 * @package Plugin\Admin\Installation\Items
 */
class Portlets extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): ?array
    {
        return isset($this->baseNode['Install'][0]['Blueprints'][0]['Blueprint'])
        && \is_array($this->baseNode['Install'][0]['Blueprints'][0]['Blueprint'])
            ? $this->baseNode['Install'][0]['Blueprints'][0]['Blueprint']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        foreach ($this->getNode() as $u => $portlet) {
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            $oPortlet = (object)[
                'kPlugin' => $this->plugin->kPlugin,
                'cTitle'  => $portlet['Title'],
                'cClass'  => $portlet['Class'],
                'cGroup'  => $portlet['Group'],
                'bActive' => (int)$portlet['Active'],
            ];
            if (!$this->db->insert('topcportlet', $oPortlet)) {
                return InstallCode::SQL_CANNOT_SAVE_PORTLET;
            }
        }

        return InstallCode::OK;
    }
}
