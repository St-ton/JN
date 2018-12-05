<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Widgets;

/**
 * Class Patch
 *
 * @package Widgets
 */
class Patch extends WidgetBase
{
    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->assign('version', getJTLVersionDB())->fetch('tpl_inc/widgets/patch.tpl');
    }
}
