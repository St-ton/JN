<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Widgets;

/**
 * Class WidgetHelp
 */
class Help extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $this->oSmarty->assign('JTLURL_GET_SHOPHELP', \JTLURL_GET_SHOPHELP);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/help.tpl');
    }
}
