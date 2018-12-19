<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Renderer;

use \Boxes\Items\BoxInterface;

/**
 * Interface RendererInterface
 * @package Boxes\Renderer
 */
interface RendererInterface
{
    /**
     * RendererInterface constructor.
     * @param \Smarty\JTLSmartyTemplateClass $smarty
     * @param BoxInterface|null              $box
     */
    public function __construct($smarty, BoxInterface $box = null);

    /**
     * @return BoxInterface
     */
    public function getBox(): BoxInterface;

    /**
     * @param BoxInterface $box
     */
    public function setBox(BoxInterface $box): void;

    /**
     * @param int $pageType
     * @param int $pageID
     * @return string
     */
    public function render(int $pageType = 0, int $pageID = 0): string;
}
