<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Shop;
use JTL\Catalog\Trennzeichen;

/**
 * @param array $post
 * @return bool
 */
function speicherTrennzeichen(array $post): bool
{
    foreach ([JTL_SEPARATOR_WEIGHT, JTL_SEPARATOR_AMOUNT, JTL_SEPARATOR_LENGTH] as $nEinheit) {
        if (isset(
            $post['nDezimal_' . $nEinheit],
            $post['cDezZeichen_' . $nEinheit],
            $post['cTausenderZeichen_' . $nEinheit]
        )) {
            $trennzeichen = new Trennzeichen();
            $trennzeichen->setSprache($_SESSION['kSprache'])
                          ->setEinheit($nEinheit)
                          ->setDezimalstellen($post['nDezimal_' . $nEinheit])
                          ->setDezimalZeichen($post['cDezZeichen_' . $nEinheit])
                          ->setTausenderZeichen($post['cTausenderZeichen_' . $nEinheit]);
            $idx = 'kTrennzeichen_' . $nEinheit;
            if (isset($post[$idx])) {
                $trennzeichen->setTrennzeichen($post[$idx])
                              ->update();
            } elseif (!$trennzeichen->save()) {
                return false;
            }
        }
    }

    Shop::Container()->getCache()->flushTags(
        [CACHING_GROUP_CORE, CACHING_GROUP_CATEGORY, CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]
    );

    return true;
}
