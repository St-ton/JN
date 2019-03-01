<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Shop;
use JTL\Catalog\Trennzeichen;

/**
 * @param array $cPostAssoc_arr
 * @return bool
 */
function speicherTrennzeichen(array $cPostAssoc_arr): bool
{
    foreach ([JTL_SEPARATOR_WEIGHT, JTL_SEPARATOR_AMOUNT, JTL_SEPARATOR_LENGTH] as $nEinheit) {
        if (isset(
            $cPostAssoc_arr['nDezimal_' . $nEinheit],
            $cPostAssoc_arr['cDezZeichen_' . $nEinheit],
            $cPostAssoc_arr['cTausenderZeichen_' . $nEinheit]
        )) {
            $oTrennzeichen = new Trennzeichen();
            $oTrennzeichen->setSprache($_SESSION['kSprache'])
                          ->setEinheit($nEinheit)
                          ->setDezimalstellen($cPostAssoc_arr['nDezimal_' . $nEinheit])
                          ->setDezimalZeichen($cPostAssoc_arr['cDezZeichen_' . $nEinheit])
                          ->setTausenderZeichen($cPostAssoc_arr['cTausenderZeichen_' . $nEinheit]);
            $idx = 'kTrennzeichen_' . $nEinheit;
            if (isset($cPostAssoc_arr[$idx])) {
                $oTrennzeichen->setTrennzeichen($cPostAssoc_arr[$idx])
                              ->update();
            } elseif (!$oTrennzeichen->save()) {
                return false;
            }
        }
    }

    Shop::Container()->getCache()->flushTags(
        [CACHING_GROUP_CORE, CACHING_GROUP_CATEGORY, CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]
    );

    return true;
}
