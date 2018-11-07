<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class SettingSectionWarenkorbKaufabwicklung
 */
class SettingSectionWarenkorbKaufabwicklung extends SettingSection
{
    /**
     * SettingSectionWarenkorbKaufabwicklung constructor.
     */
    public function __construct()
    {
        $this->hasSectionMarkup = true;
    }

    /**
     * @return string
     * @throws SmartyException
     */
    public function getSectionMarkup(): string
    {
        return Shop::Smarty()->fetch('tpl_inc/settingsection_warenkorb.tpl');
    }
}
