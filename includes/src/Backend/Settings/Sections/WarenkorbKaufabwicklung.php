<?php declare(strict_types=1);

namespace JTL\Backend\Settings\Sections;

use JTL\Backend\Settings\Manager;

/**
 * Class Kaufabwicklung
 * @package Backend\Settings\Sections
 */
class WarenkorbKaufabwicklung extends Base
{
    /**
     * @inheritdoc
     */
    public function __construct(Manager $manager, int $sectionID)
    {
        parent::__construct($manager, $sectionID);
        $this->hasSectionMarkup = true;
    }

    /**
     * @return string
     * @throws \SmartyException
     */
    public function getSectionMarkup(): string
    {
        return $this->smarty->fetch('tpl_inc/settingsection_warenkorb.tpl');
    }
}
