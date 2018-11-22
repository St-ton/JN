<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation\Items;

use Plugin\InstallCode;

/**
 * Class Widgets
 * @package Plugin\Admin\Installation\Items
 */
class Widgets extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): ?array
    {
        return isset($this->baseNode['Install'][0]['AdminWidget'][0]['Widget'])
        && \is_array($this->baseNode['Install'][0]['AdminWidget'][0]['Widget'])
            ? $this->baseNode['Install'][0]['AdminWidget'][0]['Widget']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        foreach ($this->getNode() as $u => $widgetData) {
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            $widget               = new \stdClass();
            $widget->kPlugin      = $this->plugin->kPlugin;
            $widget->cTitle       = $widgetData['Title'];
            $widget->cClass       = $this->plugin->bExtension === 1 // @todo
                ? $widgetData['Class']
                : $widgetData['Class'] . '_' . $this->plugin->cPluginID;
            $widget->eContainer   = $widgetData['Container'];
            $widget->cDescription = $widgetData['Description'];
            if (\is_array($widget->cDescription)) {
                //@todo: when description is empty, this becomes an array with indices [0] => '' and [0 attr] => ''
                $widget->cDescription = $widget->cDescription[0];
            }
            $widget->nPos      = $widgetData['Pos'];
            $widget->bExpanded = $widgetData['Expanded'];
            $widget->bActive   = $widgetData['Active'];
            if (!$this->db->insert('tadminwidgets', $widget)) {
                return InstallCode::SQL_CANNOT_SAVE_WIDGET;
            }
        }

        return InstallCode::OK;
    }
}
