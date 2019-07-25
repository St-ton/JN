<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Customer\Referral;
use JTL\Shop;
use JTL\DB\ReturnType;

/**
 * @param array $post
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeEingabe(array $post)
{
    trigger_error(__FUNCTION__ . ' is deprecated. ' .
        'Use KundenwerbenKunden::checkInputData() instead.', E_USER_DEPRECATED);
    return Referral::checkInputData($post);
}

/**
 * @param array $post
 * @param array $conf
 * @return bool
 * @deprecated since 5.0.0
 */
function setzeKwKinDB(array $post, array $conf)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use KundenwerbenKunden::saveToDB() instead.', E_USER_DEPRECATED);
    return Referral::saveToDB($post, $conf);
}

/**
 * @param int   $customerID
 * @param float $fGuthaben
 * @return bool
 * @deprecated since 5.0.0 - not use in core anymore
 */
function gibBestandskundeGutbaben(int $customerID, $fGuthaben)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if ($customerID > 0) {
        Shop::Container()->getDB()->queryPrepared(
            'UPDATE tkunde 
                SET fGuthaben = fGuthaben + :bal 
                WHERE kKunde = :cid',
            [
                'bal' => (float)$fGuthaben,
                'cid' => $customerID
            ],
            ReturnType::AFFECTED_ROWS
        );

        return true;
    }

    return false;
}
