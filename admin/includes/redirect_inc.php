<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Update and return the availability of a redirect
 *
 * @param int $kRedirect
 * @return bool
 */
function updateRedirectState(int $kRedirect): bool
{
    $url        = Shop::Container()->getDB()->select('tredirect', 'kRedirect', $kRedirect)->cToUrl;
    $cAvailable = $url !== '' && Redirect::checkAvailability($url) ? 'y' : 'n';

    Shop::Container()->getDB()->update('tredirect', 'kRedirect', $kRedirect, (object)['cAvailable' => $cAvailable]);

    return $cAvailable === 'y';
}
