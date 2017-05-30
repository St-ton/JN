<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

function updateRedirectState($kRedirect)
{
    $url = Shop::DB()->select('tredirect', 'kRedirect', $kRedirect, null, null, null, null, false, 'cToUrl')->cToUrl;

    $cAvailable = $url !== '' && Redirect::checkAvailability($url) ? 'y' : 'n';

    Shop::DB()->update('tredirect', 'kRedirect', $kRedirect, (object)['cAvailable' => $cAvailable]);

    return $cAvailable;
}
