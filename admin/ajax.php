<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
/** @global JTLSmarty $smarty */
$smarty->setForceCompile(true);
require PFAD_ROOT . PFAD_ADMIN . PFAD_CLASSES . 'class.JTL-Shopadmin.JSONAPI.php';

$oAccount->permission('BOXES_VIEW', true, true);

$jsonAPI = new JSONAPI();

if (isset($_GET['query']) && isset($_GET['type']) && validateToken()) {
    switch ($_GET['type']) {
        case 'product' :
            die($jsonAPI->getProducts());
        case 'category':
            die($jsonAPI->getCategories());
        case 'page':
            die($jsonAPI->getPages());
        case 'manufacturer':
            die($jsonAPI->getManufacturers());
        case 'TwoFA':
            $oTwoFA = new TwoFA();
            $oTwoFA->setUserByName($_GET['userName']);

            $oUserData = new stdClass();
            $oUserData->szSecret = $oTwoFA->createNewSecret()->getSecret();
            $oUserData->szQRcode = $oTwoFA->getQRcode();
            $szJSONuserData = json_encode($oUserData);

            die($szJSONuserData);
        default :
            die();
    }
}

