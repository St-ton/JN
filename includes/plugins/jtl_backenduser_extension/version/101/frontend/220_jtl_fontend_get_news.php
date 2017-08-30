<?php
/**
 * HOOK_GET_NEWS
 *
 * Dieses Plugin erweitert Backend Nutzeraccounts um weitere Felder
 * Ausgabe der Felder im News Frontend
 *
 * @package   jtl_backenduser_extension
 * @copyright JTL-Software-GmbH
 *
 * @global array $args_arr
 * @global Plugin $oPlugin
 */

// insert a author-object in each news-array-element
//
$oContenAuthor = ContentAuthor::getInstance();
foreach ($args_arr['oNews_arr'] as $i => $oNews) {
    $oNews->oAuthor = $oContenAuthor->getAuthor('NEWS', $oNews->kNews);
}


