<?php declare(strict_types=1);

namespace JTL\Boxes\Renderer;

use JTL\Boxes\Items\BoxInterface;
use Smarty_Internal_TemplateBase;

/**
 * Interface RendererInterface
 * @package JTL\Boxes\Renderer
 */
interface RendererInterface
{
    /**
     * RendererInterface constructor.
     * @param Smarty_Internal_TemplateBase $smarty
     * @param BoxInterface|null            $box
     */
    public function __construct(Smarty_Internal_TemplateBase $smarty, BoxInterface $box = null);

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
