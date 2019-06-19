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
            foreach ($redirects as $id => $item) {
                $redirect = new Redirect($id);
                if ($redirect->kRedirect > 0 && $redirect->cToUrl !== $item['cToUrl']) {
                    if (Redirect::checkAvailability($item['cToUrl'])) {
                        $redirect->cToUrl     = $item['cToUrl'];
                        $redirect->cAvailable = 'y';
                        Shop::Container()->getDB()->update('tredirect', 'kRedirect', $redirect->kRedirect, $redirect);
                    } else {
                        $alertHelper->addAlert(
                            Alert::TYPE_ERROR,
                            sprintf(__('errorURLNotReachable'), $item['cToUrl']),
                            'errorURLNotReachable'
                        );
                    }
                }
            }
            break;
        case 'delete':
            foreach ($redirects as $id => $item) {
                if (isset($item['enabled']) && (int)$item['enabled'] === 1) {
                    Redirect::deleteRedirect($id);
                }
            }
            break;
        case 'delete_all':
            Redirect::deleteUnassigned();
            break;
        case 'new':
            $redirect = new Redirect();
            if ($redirect->saveExt(
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
            $redirect = new Redirect();
            if (is_uploaded_file($_FILES['cFile']['tmp_name'])) {
                $file = PFAD_ROOT . PFAD_EXPORT . md5($_FILES['cFile']['name'] . time());
                if (move_uploaded_file($_FILES['cFile']['tmp_name'], $file)) {
                    $errors = $redirect->doImport($file);
                    if (count($errors) === 0) {
                        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successImport'), 'successImport');
                    } else {
                        @unlink($file);
                        $alertHelper->addAlert(
                            Alert::TYPE_ERROR,
                            __('errorImport') . '<br><br>' . implode('<br>', $errors),
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
$filter->addTextfield('URL', 'cFromUrl', Operation::CONTAINS);
$filter->addTextfield('Ziel-URL', 'cToUrl', Operation::CONTAINS);
$select = $filter->addSelectfield('Umleitung', 'cToUrl');
$select->addSelectOption('alle', '');
$select->addSelectOption('vorhanden', '', Operation::NOT_EQUAL);
$select->addSelectOption('fehlend', '', Operation::EQUALS);
$filter->addTextfield('Aufrufe', 'nCount', Operation::CUSTOM, DataType::NUMBER);
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

$list = Redirect::getRedirects(
    $filter->getWhereSQL(),
    $pagination->getOrderSQL(),
    $pagination->getLimitSQL()
);

handleCsvExportAction(
    'redirects',
    'redirects.csv',
    function () use ($filter, $pagination, $redirectCount) {
        $db    = Shop::Container()->getDB();
        $where = $filter->getWhereSQL();
        $order = $pagination->getOrderSQL();

        for ($i = 0; $i < $redirectCount; $i += 1000) {
            $oRedirectIter = $db->query(
                'SELECT cFromUrl, cToUrl
                    FROM tredirect' .
                    ($where !== '' ? ' WHERE ' . $where : '') .
                    ($order !== '' ? ' ORDER BY ' . $order : '') .
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
       ->assign('pagination', $pagination)
       ->assign('oRedirect_arr', $list)
       ->assign('nTotalRedirectCount', Redirect::getRedirectCount())
       ->display('redirect.tpl');
