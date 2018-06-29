<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param array $cPostAssoc_arr
 * @return bool
 */
function speicherTrennzeichen($cPostAssoc_arr)
{
    foreach ([JTL_SEPARATOR_WEIGHT, JTL_SEPARATOR_AMOUNT, JTL_SEPARATOR_LENGTH] as $nEinheit) {
        if (isset($cPostAssoc_arr['nDezimal_' . $nEinheit],
            $cPostAssoc_arr['cDezZeichen_' . $nEinheit],
            $cPostAssoc_arr['cTausenderZeichen_' . $nEinheit])
        ) {
            $oTrennzeichen = new Trennzeichen();
            $oTrennzeichen->setSprache($_SESSION['kSprache'])
                          ->setEinheit($nEinheit)
                          ->setDezimalstellen($cPostAssoc_arr['nDezimal_' . $nEinheit])
                          ->setDezimalZeichen($cPostAssoc_arr['cDezZeichen_' . $nEinheit])
                          ->setTausenderZeichen($cPostAssoc_arr['cTausenderZeichen_' . $nEinheit]);
            // Update
            $idx = 'kTrennzeichen_' . $nEinheit;
            if (isset($cPostAssoc_arr[$idx])) {
                $oTrennzeichen->setTrennzeichen($cPostAssoc_arr[$idx])
                              ->update();
            } elseif (!$oTrennzeichen->save()) {
                return false;
            }
        }
    }

    Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_CATEGORY, CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);

    return true;
}
