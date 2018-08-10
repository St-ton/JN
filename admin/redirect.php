<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global JTLSmarty $smarty
 * @global AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('REDIRECT_VIEW', true, true);
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_exporter_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_importer_inc.php';

handleCsvImportAction('redirects', 'tredirect');

$cHinweis  = '';
$cFehler   = '';
$redirects = $_POST['redirects'] ?? [];

if (FormHelper::validateToken()) {
    switch (RequestHelper::verifyGPDataString('action')) {
        case 'save':
            foreach ($redirects as $kRedirect => $redirect) {
                $oRedirect = new Redirect($kRedirect);
                if ($oRedirect->kRedirect > 0 && $oRedirect->cToUrl !== $redirect['cToUrl']) {
                    if (Redirect::checkAvailability($redirect['cToUrl'])) {
                        $oRedirect->cToUrl     = $redirect['cToUrl'];
                        $oRedirect->cAvailable = 'y';
                        Shop::Container()->getDB()->update('tredirect', 'kRedirect', $oRedirect->kRedirect, $oRedirect);
                    } else {
                        $cFehler .=
                            'Änderungen konnten nicht gespeichert werden, da die weiterzuleitende URL "' .
                            $redirect['cToUrl'] . '" nicht erreichbar ist.<br>';
                    }
                }
            }
            break;
        case 'delete':
            foreach ($redirects as $kRedirect => $redirect) {
                if (isset($redirect['enabled']) && (int)$redirect['enabled'] === 1) {
                    Redirect::deleteRedirect($kRedirect);
                }
            }
            break;
        case 'delete_all':
            Redirect::deleteUnassigned();
            break;
        case 'new':
            $oRedirect = new Redirect();
            if ($oRedirect->saveExt(RequestHelper::verifyGPDataString('cFromUrl'), RequestHelper::verifyGPDataString('cToUrl'))) {
                $cHinweis = 'Ihre Weiterleitung wurde erfolgreich gespeichert';
            } else {
                $cFehler = 'Fehler: Bitte prüfen Sie Ihre Eingaben';
                $smarty
                    ->assign('cTab', 'new_redirect')
                    ->assign('cFromUrl', RequestHelper::verifyGPDataString('cFromUrl'))
                    ->assign('cToUrl', RequestHelper::verifyGPDataString('cToUrl'));
            }
            break;
        case 'csvimport':
            $oRedirect = new Redirect();
            if (is_uploaded_file($_FILES['cFile']['tmp_name'])) {
                $cFile = PFAD_ROOT . PFAD_EXPORT . md5($_FILES['cFile']['name'] . time());
                if (move_uploaded_file($_FILES['cFile']['tmp_name'], $cFile)) {
                    $cError_arr = $oRedirect->doImport($cFile);
                    if (count($cError_arr) === 0) {
                        $cHinweis = 'Der Import wurde erfolgreich durchgeführt';
                    } else {
                        @unlink($cFile);
                        $cFehler = 'Fehler: Der Import konnte nicht durchgeführt werden.' .
                            'Bitte prüfen Sie die CSV-Datei<br><br>' . implode('<br>', $cError_arr);
                    }
                }
            }
            break;
        default:
            break;
    }
}

$oFilter = new Filter();
$oFilter->addTextfield('URL', 'cFromUrl', 1);
$oFilter->addTextfield('Ziel-URL', 'cToUrl', 1);
$oSelect = $oFilter->addSelectfield('Umleitung', 'cToUrl');
$oSelect->addSelectOption('alle', '');
$oSelect->addSelectOption('vorhanden', '', 9);
$oSelect->addSelectOption('fehlend', '', 4);
$oFilter->addTextfield('Aufrufe', 'nCount', 0, 1);
$oFilter->assemble();

$nRedirectCount = Redirect::getRedirectCount($oFilter->getWhereSQL());

$oPagination = new Pagination();
$oPagination
    ->setItemCount($nRedirectCount)
    ->setSortByOptions([
        ['cFromUrl', 'URL'],
        ['cToUrl', 'Ziel-URL'],
        ['nCount', 'Aufrufe']
    ])
    ->assemble();

$oRedirect_arr = Redirect::getRedirects(
    $oFilter->getWhereSQL(), $oPagination->getOrderSQL(), $oPagination->getLimitSQL()
);

handleCsvExportAction(
    'redirects', 'redirects.csv',
    function () use ($oFilter, $oPagination, $nRedirectCount)
    {
        $cWhereSQL = $oFilter->getWhereSQL();
        $cOrderSQL = $oPagination->getOrderSQL();

        for ($i = 0; $i < $nRedirectCount; $i += 1000) {
            $oRedirectIter = Shop::Container()->getDB()->query(
                'SELECT cFromUrl, cToUrl
                    FROM tredirect' .
                    ($cWhereSQL !== '' ? ' WHERE ' . $cWhereSQL : '') .
                    ($cOrderSQL !== '' ? ' ORDER BY ' . $cOrderSQL : '') .
                    " LIMIT $i, 1000",
                \DB\ReturnType::QUERYSINGLE
            );

            foreach ($oRedirectIter as $oRedirect) {
                yield (object)$oRedirect;
            }
        }
    }
);

$smarty
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->assign('oFilter', $oFilter)
    ->assign('oPagination', $oPagination)
    ->assign('oRedirect_arr', $oRedirect_arr)
    ->assign('nTotalRedirectCount', Redirect::getRedirectCount())
    ->display('redirect.tpl');
