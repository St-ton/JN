<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('REDIRECT_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_exporter_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_importer_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'redirect_inc.php';

handleCsvImportAction('redirects', 'tredirect');

$aData     = isset($_POST['aData']) ? $_POST['aData'] : null;
$oRedirect = new Redirect();
$urls      = [];
$cHinweis  = '';
$cFehler   = '';
$shopURL   = Shop::getURL();

if (isset($aData['action']) && validateToken()) {
    switch ($aData['action']) {
        case 'check_url':
            exit($aData['url'] !== '' && Redirect::checkAvailability($aData['url']) ? '1' : '0');
            break;
        case 'save':
            foreach ($aData['redirect'] as $kRedirect => $redirectEntry) {
                $cToUrl = $redirectEntry['url'];
                $oItem  = new Redirect($kRedirect);
                if (!empty($cToUrl)) {
                    $urls[$oItem->kRedirect] = $cToUrl;
                }
                if ($oItem->kRedirect > 0 && $oItem->cToUrl !== $cToUrl) {
                    $oItem->cToUrl = $cToUrl;
                    if (Redirect::checkAvailability($cToUrl)) {
                        $oItem->bAvailable = $cToUrl !== '' ? '1' : '0';
                        Shop::DB()->update('tredirect', 'kRedirect', $oItem->kRedirect, $oItem);
                    } else {
                        $cFehler .= "&Auml;nderungen konnten nicht gespeichert werden, da die weiterzuleitende URL {$cToUrl} nicht erreichbar ist.<br />";
                    }
                }
            }
            if ($cFehler === '') {
                $cHinweis = 'Daten wurden erfolgreich aktualisiert.';
            }
            break;
        case 'delete':
            foreach ($aData['redirect'] as $kRedirect => $redirectEntry) {
                if (isset($redirectEntry['active']) && (int)$redirectEntry['active'] === 1) {
                    $oRedirect->delete((int)$kRedirect);
                }
            }
            break;
        case 'delete_all':
            $oRedirect->deleteAll();
            break;
        case 'new':
            if ($oRedirect->saveExt($_POST['cSource'], $_POST['cToUrl'])) {
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
                        $cFehler = 'Fehler: Der Import konnte nicht durchgef&uuml;hrt werden. Bitte pr&uuml;fen Sie die CSV-Datei<br /><br />' .
                            implode('<br />', $cError_arr);
                    }
                }
            }
            break;
        default:
            $cFehler = 'Fehler: Es wurde eine ung&uuml;ltige Aktion ausgel&ouml;st';
            break;
    }
}

$oFilter = new Filter();
$oFilter->addTextfield('URL', 'cFromUrl', 1);
$oFilter->addTextfield('Ziel-URL', 'cToUrl', 1);
$oSelect = $oFilter->addSelectfield('Umleitung', 'cToUrl');
$oSelect->addSelectOption('alle', '', 0);
$oSelect->addSelectOption('vorhanden', '', 9);
$oSelect->addSelectOption('fehlend', '', 4);
$oFilter->assemble();

$nRedirectCount = Redirect::getRedirectCount($oFilter->getWhereSQL());

$oPagination = (new Pagination())
    ->setItemCount($nRedirectCount)
    ->setItemsPerPageOptions([10, 20, 50, 100])
    ->setSortByOptions([['cFromUrl', 'URL'],
                        ['cToUrl', 'Weiterleitung nach'],
                        ['nCount', 'Aufrufe']])
    ->assemble();

$oRedirect_arr = Redirect::getRedirects($oFilter->getWhereSQL(), $oPagination->getOrderSQL(), $oPagination->getLimitSQL());

handleCsvExportAction('redirects', 'redirects.csv', function () use ($oFilter, $oPagination) {
        return Redirect::getRedirects($oFilter->getWhereSQL(), $oPagination->getOrderSQL());
    }, ['cFromUrl', 'cToUrl']);

if (!empty($oRedirect_arr) && !empty($urls)) {
    foreach ($oRedirect_arr as &$oRedirect) {
        if (array_key_exists($oRedirect->kRedirect, $urls)) {
            $oRedirect->cToUrl = $urls[$oRedirect->kRedirect];
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
