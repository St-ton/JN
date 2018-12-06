<?php declare(strict_types=1);
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
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['Portlets'][0]['Portlet'])
        && \is_array($this->baseNode['Install'][0]['Portlets'][0]['Portlet'])
            ? $this->baseNode['Install'][0]['Portlets'][0]['Portlet']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        foreach ($this->getNode() as $i => $portlet) {
            $i = (string)$i;
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\strlen($hits2[0]) !== \strlen($i)) {
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
