<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Redirect;
use JTL\Shop;
use JTL\Pagination\Filter;
use JTL\Pagination\Pagination;
use JTL\DB\ReturnType;
use JTL\Pagination\Operation;
use JTL\Pagination\DataType;
use JTL\Alert\Alert;

/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
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
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successRedirectSave'), 'successRedirectSave');
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
                        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successImport'), 'successImport');
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
$filter->addTextfield(__('url'), 'cFromUrl', Operation::CONTAINS);
$filter->addTextfield(__('redirectTo'), 'cToUrl', Operation::CONTAINS);
$select = $filter->addSelectfield(__('redirection'), 'cToUrl');
$select->addSelectOption(__('all'), '');
$select->addSelectOption(__('available'), '', Operation::NOT_EQUAL);
$select->addSelectOption(__('missing'), '', Operation::EQUALS);
$filter->addTextfield(__('calls'), 'nCount', Operation::CUSTOM, DataType::NUMBER);
$filter->assemble();

$redirectCount = Redirect::getRedirectCount($filter->getWhereSQL());

$pagination = new Pagination();
$pagination
    ->setItemCount($redirectCount)
    ->setSortByOptions([
        ['cFromUrl', __('url')],
        ['cToUrl', __('redirectTo')],
        ['nCount', __('calls')]
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
                ReturnType::QUERYSINGLE
            );

            foreach ($oRedirectIter as $oRedirect) {
                yield (object)$oRedirect;
            }
        }
    }
);

$smarty->assign('oFilter', $filter)
       ->assign('oPagination', $pagination)
       ->assign('oRedirect_arr', $oRedirect_arr)
       ->assign('nTotalRedirectCount', Redirect::getRedirectCount())
       ->display('redirect.tpl');
