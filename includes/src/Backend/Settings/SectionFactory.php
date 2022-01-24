<?php declare(strict_types=1);

namespace JTL\Backend\Settings;

use JTL\Backend\Settings\Sections\Base;
use JTL\Backend\Settings\Sections\Cache;
use JTL\Backend\Settings\Sections\Checkout;
use JTL\Backend\Settings\Sections\Comparelist;
use JTL\Backend\Settings\Sections\PaymentMethod;

/**
 * Class SectionFactory
 * @package Backend\Settings
 */
class SectionFactory
{
    public function getSection(int $sectionID, Manager $manager)
    {
        switch ($sectionID) {
            case \CONF_KAUFABWICKLUNG:
                return new Checkout($manager, $sectionID);
            case \CONF_CACHING:
                return new Cache($manager, $sectionID);
            case \CONF_ZAHLUNGSARTEN:
                return new PaymentMethod($manager, $sectionID);
            case \CONF_VERGLEICHSLISTE:
                return new Comparelist($manager, $sectionID);
            default:
                return new Base($manager, $sectionID);
        }
    }
}
