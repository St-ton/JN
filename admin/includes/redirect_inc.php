<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Redirect;
use JTL\Shop;

/**
 * Update and return the availability of a redirect
 *
 * @param int $redirectID
 * @return bool
 */
function updateRedirectState(int $redirectID): bool
{
    $url       = Shop::Container()->getDB()->select('tredirect', 'kRedirect', $redirectID)->cToUrl;
    $available = $url !== '' && Redirect::checkAvailability($url) ? 'y' : 'n';

    Shop::Container()->getDB()->update('tredirect', 'kRedirect', $redirectID, (object)['cAvailable' => $available]);

    return $available === 'y';
}
