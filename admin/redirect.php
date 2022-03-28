<?php declare(strict_types=1);

use JTL\CSV\Export;
use JTL\CSV\Import;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\DataType;
use JTL\Pagination\Filter;
use JTL\Pagination\Operation;
use JTL\Pagination\Pagination;
use JTL\Redirect;
use JTL\Shop;

/**
 * @global \JTL\Smarty\JTLSmarty     $smarty
 * @global \JTL\Backend\AdminAccount $oAccount
 */
require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('REDIRECT_VIEW', true, true);

$alertHelper = Shop::Container()->getAlertService();
$errors      = [];
$action      = Request::verifyGPDataString('action');
if (Request::verifyGPDataString('importcsv') === 'redirects') {
    $action = 'csvImport';
}
$redirects = $_POST['redirects'] ?? [];
$db        = Shop::Container()->getDB();
$filter    = new Filter();
$filter->addTextfield(__('url'), 'cFromUrl', Operation::CONTAINS);
$filter->addTextfield(__('redirectTo'), 'cToUrl', Operation::CONTAINS);
$select = $filter->addSelectfield(__('redirection'), 'cToUrl');
$select->addSelectOption(__('all'), '');
$select->addSelectOption(__('available'), '', Operation::NOT_EQUAL);
$select->addSelectOption(__('missing'), '', Operation::EQUALS);
$filter->addTextfield(__('calls'), 'nCount', Operation::CUSTOM, DataType::NUMBER);
$filter->assemble();
$pagination = new Pagination();
$pagination->setSortByOptions([
    ['cFromUrl', __('url')],
    ['cToUrl', __('redirectTo')],
    ['nCount', __('calls')]
]);
if (Form::validateToken()) {
    switch ($action) {
        case 'csvImport':
            $importer = new Import($db);
            $importer->import('redirects', 'tredirect', [], null, Request::verifyGPCDataInt('importType'));
            $errorCount = $importer->getErrorCount();
            if ($errorCount > 0) {
                $alertHelper->addError(
                    __('errorImport') . '<br><br>' . implode('<br>', $importer->getErrors()),
                    'errorImport'
                );
            } else {
                $alertHelper->addSuccess(__('successImport'), 'successImport');
            }
            break;
        case 'csvExport':
            $redirectCount = Redirect::getRedirectCount($filter->getWhereSQL());
            $pagination->setItemCount($redirectCount)->assemble();
            $export = new Export();
            $export->export(
                'redirects',
                'redirects.csv',
                static function () use ($filter, $pagination, $redirectCount, $db) {
                    $where = $filter->getWhereSQL();
                    $order = $pagination->getOrderSQL();
                    for ($i = 0; $i < $redirectCount; $i += 1000) {
                        $iter = $db->getPDOStatement(
                            'SELECT cFromUrl, cToUrl
                                FROM tredirect' .
                            ($where !== '' ? ' WHERE ' . $where : '') .
                            ($order !== '' ? ' ORDER BY ' . $order : '') .
                            ' LIMIT ' . $i . ', 1000'
                        );

                        foreach ($iter as $oRedirect) {
                            yield (object)$oRedirect;
                        }
                    }
                }
            );
            break;
        case 'save':
            foreach ($redirects as $id => $item) {
                $redirect = new Redirect((int)$id);
                if ($redirect->kRedirect > 0 && $redirect->cToUrl !== $item['cToUrl']) {
                    if (Redirect::checkAvailability($item['cToUrl'])) {
                        $redirect->cToUrl     = $item['cToUrl'];
                        $redirect->cAvailable = 'y';
                        $db->update('tredirect', 'kRedirect', $redirect->kRedirect, $redirect);
                    } else {
                        $alertHelper->addError(
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
                    Redirect::deleteRedirect((int)$id);
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
                $alertHelper->addSuccess(__('successRedirectSave'), 'successRedirectSave');
            } else {
                $alertHelper->addError(__('errorCheckInput'), 'errorCheckInput');
                $smarty->assign('cTab', 'new_redirect')
                    ->assign('cFromUrl', Text::filterXSS(Request::verifyGPDataString('cFromUrl')))
                    ->assign('cToUrl', Text::filterXSS(Request::verifyGPDataString('cToUrl')));
            }
            break;
        default:
            break;
    }
}
$redirectCount = Redirect::getRedirectCount($filter->getWhereSQL());
$pagination->setItemCount($redirectCount)->assemble();

$list = Redirect::getRedirects(
    $filter->getWhereSQL(),
    $pagination->getOrderSQL(),
    $pagination->getLimitSQL()
);

$smarty->assign('oFilter', $filter)
    ->assign('pagination', $pagination)
    ->assign('oRedirect_arr', $list)
    ->assign('nTotalRedirectCount', Redirect::getRedirectCount())
    ->display('redirect.tpl');
