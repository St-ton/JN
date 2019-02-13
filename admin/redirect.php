<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\Request;
use Pagination\Filter;
use Pagination\Pagination;

/**
 * @global \Smarty\JTLSmarty     $smarty
 * @global \Backend\AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('REDIRECT_VIEW', true, true);
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_exporter_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_importer_inc.php';

handleCsvImportAction('redirects', 'tredirect');

$redirects   = $_POST['redirects'] ?? [];
$alertHelper = Shop::Container()->getAlertService();

if (Form::validateToken()) {
    switch (Request::verifyGPDataString('action')) {
        case 'save':
            foreach ($redirects as $kRedirect => $redirect) {
                $oRedirect = new Redirect($kRedirect);
                if ($oRedirect->kRedirect > 0 && $oRedirect->cToUrl !== $redirect['cToUrl']) {
                    if (Redirect::checkAvailability($redirect['cToUrl'])) {
                        $oRedirect->cToUrl     = $redirect['cToUrl'];
                        $oRedirect->cAvailable = 'y';
                        Shop::Container()->getDB()->update('tredirect', 'kRedirect', $oRedirect->kRedirect, $oRedirect);
                    } else {
                        $alertHelper->addAlert(
                            Alert::TYPE_ERROR,
                            sprintf(__('errorURLNotReachable'), $redirect['cToUrl']),
                            'errorURLNotReachable'
                        );
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
            if ($oRedirect->saveExt(
                Request::verifyGPDataString('cFromUrl'),
                Request::verifyGPDataString('cToUrl')
            )) {
                $alertHelper->addAlert(Alert::TYPE_NOTE, __('successRedirectSave'), 'successRedirectSave');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorCheckInput'), 'errorCheckInput');
                $smarty
                    ->assign('cTab', 'new_redirect')
                    ->assign('cFromUrl', Request::verifyGPDataString('cFromUrl'))
                    ->assign('cToUrl', Request::verifyGPDataString('cToUrl'));
            }
            break;
        case 'csvimport':
            $oRedirect = new Redirect();
            if (is_uploaded_file($_FILES['cFile']['tmp_name'])) {
                $cFile = PFAD_ROOT . PFAD_EXPORT . md5($_FILES['cFile']['name'] . time());
                if (move_uploaded_file($_FILES['cFile']['tmp_name'], $cFile)) {
                    $cError_arr = $oRedirect->doImport($cFile);
                    if (count($cError_arr) === 0) {
                        $alertHelper->addAlert(Alert::TYPE_NOTE, __('successImport'), 'successImport');
                    } else {
                        @unlink($cFile);
                        $alertHelper->addAlert(
                            Alert::TYPE_ERROR,
                            __('errorImport') . '<br><br>' . implode('<br>', $cError_arr),
                            'errorImport'
                        );
                    }
                }
            }
            break;
        default:
            break;
    }
}

$filter = new Filter();
$filter->addTextfield('URL', 'cFromUrl', \Pagination\Operation::CONTAINS);
$filter->addTextfield('Ziel-URL', 'cToUrl', \Pagination\Operation::CONTAINS);
$select = $filter->addSelectfield('Umleitung', 'cToUrl');
$select->addSelectOption('alle', '');
$select->addSelectOption('vorhanden', '', \Pagination\Operation::NOT_EQUAL);
$select->addSelectOption('fehlend', '', \Pagination\Operation::EQUALS);
$filter->addTextfield('Aufrufe', 'nCount', \Pagination\Operation::CUSTOM, \Pagination\DataType::NUMBER);
$filter->assemble();

$redirectCount = Redirect::getRedirectCount($filter->getWhereSQL());

$pagination = new Pagination();
$pagination
    ->setItemCount($redirectCount)
    ->setSortByOptions([
        ['cFromUrl', 'URL'],
        ['cToUrl', 'Ziel-URL'],
        ['nCount', 'Aufrufe']
    ])
    ->assemble();

$oRedirect_arr = Redirect::getRedirects(
    $filter->getWhereSQL(),
    $pagination->getOrderSQL(),
    $pagination->getLimitSQL()
);

handleCsvExportAction(
    'redirects',
    'redirects.csv',
    function () use ($filter, $pagination, $redirectCount) {
        $db        = Shop::Container()->getDB();
        $cWhereSQL = $filter->getWhereSQL();
        $cOrderSQL = $pagination->getOrderSQL();

        for ($i = 0; $i < $redirectCount; $i += 1000) {
            $oRedirectIter = $db->query(
                'SELECT cFromUrl, cToUrl
                    FROM tredirect' .
                    ($cWhereSQL !== '' ? ' WHERE ' . $cWhereSQL : '') .
                    ($cOrderSQL !== '' ? ' ORDER BY ' . $cOrderSQL : '') .
                    ' LIMIT ' . $i . ', 1000',
                \DB\ReturnType::QUERYSINGLE
            );

            foreach ($oRedirectIter as $oRedirect) {
                yield (object)$oRedirect;
            }
        }
    }
);

$smarty
    ->assign('oFilter', $filter)
    ->assign('oPagination', $pagination)
    ->assign('oRedirect_arr', $oRedirect_arr)
    ->assign('nTotalRedirectCount', Redirect::getRedirectCount())
    ->display('redirect.tpl');
