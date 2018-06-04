<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Renderer;

use Boxes\BoxInterface;

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
     * @param \JTLSmarty $smarty
     * @param BoxInterface $box
     */
    public function __construct(\JTLSmarty $smarty, BoxInterface $box = null);

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