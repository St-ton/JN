<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
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
        default :
            die();
    }
}
