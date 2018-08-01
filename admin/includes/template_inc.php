<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $cOrdner
 * @param string $eTyp
 * @return bool
 */
function __switchTemplate(string $cOrdner, string $eTyp = 'standard')
{
    $cOrdner   = Shop::Container()->getDB()->escape($cOrdner);
    $oTemplate = Template::getInstance();
    $bCheck    = $oTemplate->setTemplate($cOrdner, $eTyp);
    if ($bCheck) {
        unset($_SESSION['cTemplate'], $_SESSION['template']);
    }

    return $bCheck;
}
