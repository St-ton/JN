<?php

use JTL\Catalog\Product\Preisverlauf;
use JTL\Helpers\Request;
use JTL\Session\Frontend;
use JTL\Shop;

require_once __DIR__ . '/globalinclude.php';

[$_GET['kArtikel'], $_GET['kKundengruppe'], $_GET['kSteuerklasse'], $_GET['fMwSt']] = explode(';', $_GET['cOption']);

if (!isset($_GET['kKundengruppe'])) {
    $_GET['kKundengruppe'] = 1;
}
if (!isset($_GET['kSteuerklasse'])) {
    $_GET['kSteuerklasse'] = 1;
}

/**
 * @param array $data
 * @param int   $max
 * @return mixed
 * @deprecated since 5.0.0
 */
function expandPriceArray($data, $max)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    for ($i = 1; $i <= $max; $i++) {
        if ($i > 1 && !isset($data[$i])) {
            $data[$i] = $data[$i - 1];
        }
    }

    return $data;
}
