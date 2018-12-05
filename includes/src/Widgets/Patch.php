<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Widgets;

/**
 * Class WidgetPatch
 */
class Patch extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $this->oSmarty->assign('version', getJTLVersionDB());
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/patch.tpl');
    }
}
