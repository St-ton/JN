<?php declare(strict_types=1);

namespace JTL\Widgets;

/**
 * Class Patch
 * @package JTL\Widgets
 */
class Patch extends AbstractWidget
{
    /**
     *
     */
    public function init()
    {
        $this->setPermission('DIAGNOSTIC_VIEW');
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->assign('version', $this->getDBVersion())->fetch('tpl_inc/widgets/patch.tpl');
    }


    /**
     * @return string
     */
    private function getDBVersion(): string
    {
        return $this->getDB()->getSingleObject('SELECT nVersion FROM tversion')->nVersion ?? '0.0.0';
    }
}
