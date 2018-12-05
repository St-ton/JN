<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Widgets;

/**
 * Class WidgetDuk
 */
class Duk extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $this->oSmarty->assign('JTLURL_GET_DUK', JTLURL_GET_DUK);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/duk.tpl');
    }
}
