<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Widgets;

use JTL\Nice;

/**
 * Class ExtensionViewer
 * @package JTL\Widgets
 */
class ExtensionViewer extends AbstractWidget
{
    /**
     *
     */
    public function init()
    {
        $nice    = Nice::getInstance();
        $modules = $nice->gibAlleMoeglichenModule();
        foreach ($modules as $module) {
            $module->bActive = $nice->checkErweiterung($module->kModulId);
        }
        $this->oSmarty->assign('oModul_arr', $modules);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/extension_viewer.tpl');
    }
}
