<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Widgets;

/**
 * Class Clock
 * @package JTL\Widgets
 */
class Clock extends AbstractWidget
{
    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/clock.tpl');
    }
}
