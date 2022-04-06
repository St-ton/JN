<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

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
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RedirectController
 * @package JTL\Router\Controller\Backend
 */
class RedirectController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions('REDIRECT_VIEW');
        $this->getText->loadAdminLocale('pages/redirect');

        $action = Request::verifyGPDataString('action');
        if (Request::verifyGPDataString('importcsv') === 'redirects') {
            $action = 'csvImport';
        }
        $redirects = $_POST['redirects'] ?? [];
        $filter    = new Filter();
        $filter->addTextfield(\__('url'), 'cFromUrl', Operation::CONTAINS);
        $filter->addTextfield(\__('redirectTo'), 'cToUrl', Operation::CONTAINS);
        $select = $filter->addSelectfield(\__('redirection'), 'cToUrl');
        $select->addSelectOption(\__('all'), '');
        $select->addSelectOption(\__('available'), '', Operation::NOT_EQUAL);
        $select->addSelectOption(\__('missing'), '', Operation::EQUALS);
        $filter->addTextfield(\__('calls'), 'nCount', Operation::CUSTOM, DataType::NUMBER);
        $filter->assemble();
        $pagination = new Pagination();
        $pagination->setSortByOptions([
            ['cFromUrl', \__('url')],
            ['cToUrl', \__('redirectTo')],
            ['nCount', \__('calls')]
        ]);
        if (Form::validateToken()) {
            switch ($action) {
                case 'csvImport':
                    $importer = new Import($this->db);
                    $importer->import('redirects', 'tredirect', [], null, Request::verifyGPCDataInt('importType'));
                    $errorCount = $importer->getErrorCount();
                    if ($errorCount > 0) {
                        $this->alertService->addError(
                            \__('errorImport') . '<br><br>' . \implode('<br>', $importer->getErrors()),
                            'errorImport'
                        );
                    } else {
                        $this->alertService->addSuccess(\__('successImport'), 'successImport');
                    }
                    break;
                case 'csvExport':
                    $redirectCount = Redirect::getRedirectCount($filter->getWhereSQL());
                    $pagination->setItemCount($redirectCount)->assemble();
                    $export = new Export();
                    $export->export(
                        'redirects',
                        'redirects.csv',
                        function () use ($filter, $pagination, $redirectCount) {
                            $where = $filter->getWhereSQL();
                            $order = $pagination->getOrderSQL();
                            for ($i = 0; $i < $redirectCount; $i += 1000) {
                                $iter = $this->db->getPDOStatement(
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
                                $this->db->update('tredirect', 'kRedirect', $redirect->kRedirect, $redirect);
                            } else {
                                $this->alertService->addError(
                                    \sprintf(\__('errorURLNotReachable'), $item['cToUrl']),
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
                        $this->alertService->addSuccess(\__('successRedirectSave'), 'successRedirectSave');
                    } else {
                        $this->alertService->addError(\__('errorCheckInput'), 'errorCheckInput');
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

        return $smarty->assign('oFilter', $filter)
            ->assign('pagination', $pagination)
            ->assign('route', $this->route)
            ->assign('oRedirect_arr', $list)
            ->assign('nTotalRedirectCount', Redirect::getRedirectCount())
            ->getResponse('redirect.tpl');
    }
}
