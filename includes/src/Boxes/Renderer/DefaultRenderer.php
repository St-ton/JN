<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Renderer;

use Boxes\Items\BoxInterface;


/**
 * Class BoxRenderer
 *
 * @package Boxes
 */
class DefaultRenderer implements RendererInterface
{
    /**
     * @var \JTLSmarty
     */
    protected $smarty;

    /**
     * @var BoxInterface
     */
    protected $box;

    /**
     * @inheritdoc
     */
    public function __construct($smarty, BoxInterface $box = null)
    {
        $this->smarty = $smarty;
        $this->box    = $box;
    }

    /**
     * @inheritdoc
     */
    public function setBox(BoxInterface $box)
    {
        $this->box = $box;
    }

    /**
     * @inheritdoc
     */
    public function getBox(): BoxInterface
    {
        return $this->box;
    }

    /**
     * @inheritdoc
     */
    public function render(int $pageType = 0, int $pageID = 0): string
    {
        $this->smarty->assign('oBox', $this->box);
        try {
            $html = $this->box->getTemplateFile() !== '' && $this->box->isBoxVisible($pageType, $pageID)
                ? $this->smarty->fetch($this->box->getTemplateFile())
                : '';
        } catch (\SmartyException $e) {
            $html = $e->getMessage();
        } catch (\Exception $e) {
            $html = $e->getMessage();
        }
        $this->smarty->clearAssign('oBox');

        return $html;
    }
}
