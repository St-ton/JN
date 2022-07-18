<?php declare(strict_types=1);

namespace JTL\Template\Snippets;

use JTL\Shop;
use Smarty\JTLSmarty;

abstract class Snippet
{
    protected JTLSmarty $smarty;
    protected string $html;

    public function __construct()
    {
        $this->smarty = Shop::Smarty();
    }

    /**
     * @throws \SmartyException
     */
    public function render(): void
    {
        $this->setHtml();
        $this->smarty->display('string:' . $this->html);
    }

    abstract protected function setHtml(): void;
}
