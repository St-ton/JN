<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Renderer;


use \Boxes\Items\BoxInterface;

/**
 * Interface BoxRendererInterface
 *
 * @package Boxes
 */
interface RendererInterface
{
    /**
     * BoxRendererInterface constructor.
     *
     * @param \JTLSmarty|\JTLSmartyTemplateClass $smarty
     * @param BoxInterface $box
     */
    public function __construct($smarty, BoxInterface $box = null);

    /**
     * @return BoxInterface
     */
    public function getBox(): BoxInterface;

    /**
     * @param BoxInterface $box
     */
    public function setBox(BoxInterface $box);

    /**
     * @param int        $pageType
     * @param int        $pageID
     * @return string
     */
    public function render(int $pageType = 0, int $pageID = 0): string;
}
