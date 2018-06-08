<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Renderer;

/**
 * Class ContainerRenderer
 *
 * @package Boxes
 */
class ContainerRenderer extends DefaultRenderer
{
    /**
     * @inheritdoc
     */
    public function render(int $pageType = 0, int $pageID = 0): string
    {
        $html        = '';
        $boxRenderer = new DefaultRenderer($this->smarty);
        foreach ($this->box->getChildren() as $child) {
            $boxRenderer->setBox($child);
            $rendererClass = $child->getRenderer();
            if (get_class($boxRenderer) !== $rendererClass) {
                $boxRenderer = new $rendererClass($this->smarty);
            }
            $html .= trim($boxRenderer->render($pageType, $pageID));
        }
        $this->box->setHTML($html);

        return parent::render($pageType, $pageID);
    }
}