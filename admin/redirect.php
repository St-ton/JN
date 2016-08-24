<?php

/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once dirname(__FILE__) . '/includes/admininclude.php';
$oAccount->permission('REDIRECT_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$aData           = (isset($_POST['aData'])) ? $_POST['aData'] : null;
$oRedirect       = new Redirect();
$urls            = array();
$cHinweis        = '';
$cFehler         = '';

switch ($aData['action']) {
    case 'search':
        $ret = array(
            'article'      => getArticleList($aData['search'], array('cLimit' => 10, 'return' => 'object')),
            'category'     => getCategoryList($aData['search'], array('cLimit' => 10, 'return' => 'object')),
            'manufacturer' => getManufacturerList($aData['search'], array('cLimit' => 10, 'return' => 'object')),
        );
        exit(json_encode($ret));
        break;
    case 'check_url':
        $shopURL = Shop::getURL();
        $check   = (($aData['url'] != '' && $oRedirect->isAvailable($shopURL . $aData['url'])) ? '1' : '0');
        exit($check);
        break;
    case 'save' :
        if (validateToken()) {
            $shopURL   = Shop::getURL();
            $kRedirect = array_keys($aData['redirect']);
            for ($i = 0; $i < count($kRedirect); $i++) {
                $cToUrl = $aData['redirect'][$kRedirect[$i]]['url'];
                $oItem  = new Redirect($kRedirect[$i]);
                if (!empty($cToUrl)) {
                    $urls[$oItem->kRedirect] = $cToUrl;
                }
                if ($oItem->kRedirect > 0) {
                    $oItem->cToUrl = $cToUrl;
                    if ($oRedirect->isAvailable($shopURL . $cToUrl)) {
                        Shop::DB()->update('tredirect', 'kRedirect', $oItem->kRedirect, $oItem);
                    } else {
                        $cFehler .= "&Auml;nderungen konnten nicht gespeichert werden, da die weiterzuleitende URL {$cToUrl} nicht erreichbar ist.<br />";
                    }
                }
            }
            $cHinweis = 'Daten wurden erfolgreich aktualisiert.';
        }
        break;
    case 'delete':
        if (validateToken()) {
            foreach($aData['redirect'] as $kRedirect => $redirectEntry) {
                if (isset($redirectEntry['active']) && $redirectEntry['active'] == 1) {
                    $oRedirect->delete((int)$kRedirect);
                }
            }
        }
        break;
    case 'delete_all':
        if (validateToken()) {
            $oRedirect->deleteAll();
        }
        break;
    case 'new':
        if ($oRedirect->saveExt($_POST['cSource'], $_POST['cDestiny'])) {
            $cHinweis = 'Ihre Weiterleitung wurde erfolgreich gespeichert';
        } else {
            $cFehler = 'Fehler: Bitte pr&uuml;fen Sie Ihre Eingaben';
            $smarty->assign('cPost_arr', StringHandler::filterXSS($_POST));
        }
        break;
    case 'csvimport':
        if (is_uploaded_file($_FILES['cFile']['tmp_name'])) {
            $cFile = PFAD_ROOT . PFAD_EXPORT . md5($_FILES['cFile']['name'] . time());
            if (move_uploaded_file($_FILES['cFile']['tmp_name'], $cFile)) {
                $cError_arr = $oRedirect->doImport($cFile);
                if (count($cError_arr) === 0) {
                    $cHinweis = 'Der Import wurde erfolgreich durchgef&uuml;hrt';
                } else {
                    @unlink($cFile);
                    $cFehler = 'Fehler: Der Import konnte nicht durchgef&uuml;hrt werden. Bitte pr&uuml;fen Sie die CSV Datei<br /><br />' . implode('<br />', $cError_arr);
                }
            }
        }
        break;
}

$oFilter = new Filter();
$oFilter->addTextfield('URL', 'cFromUrl', 1);
$oFilter->addTextfield('Ziel-URL', 'cToUrl', 1);
$oSelect = $oFilter->addSelectfield('Umleitung', 'cToUrl');
$oSelect->addSelectOption('alle', '', 0);
$oSelect->addSelectOption('vorhanden', '', 9);
$oSelect->addSelectOption('fehlend', '', 4);
$oFilter->assemble();

$oPagination = (new Pagination())
    ->setItemCount(Redirect::getTotalRedirectCount())
    ->setSortByOptions([['cFromUrl', 'Url'],
                        ['cToUrl', 'Weiterleitung nach'],
                        ['nCount', 'Aufrufe']])
    ->assemble();

$oRedirect_arr = Redirect::getRedirects($oFilter->getWhereSQL(), $oPagination->getOrderSQL(), $oPagination->getLimitSQL());

if (!empty($oRedirect_arr) && !empty($urls)) {
    foreach ($oRedirect_arr as &$kRedirect) {
        if (array_key_exists($kRedirect->kRedirect, $urls)) {
            $kRedirect->cToUrl = $urls[$kRedirect->kRedirect];
        } elseif (array_key_exists('url', $_POST)) {
            //            $kRedirect->cToUrl = '';
        }
    }
    unset($urls);
}

$smarty->assign('aData', $aData)
    ->assign('oPagination', $oPagination)
    ->assign('oFilter', $oFilter)
    ->assign('oRedirect_arr', $oRedirect_arr)
    ->assign('nRedirectCount', Redirect::getTotalRedirectCount())
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->display('redirect.tpl');
