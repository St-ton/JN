<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Customer\Import;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CustomerImportController
 * @package JTL\Router\Controller\Backend
 */
class CustomerImportController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::IMPORT_CUSTOMER_VIEW);
        $this->getText->loadAdminLocale('pages/kundenimport');

        if (Form::validateToken()) {
            if (isset($_FILES['csv']['tmp_name'])
                && Request::postVar('action') === 'import-customers'
                && \mb_strlen($_FILES['csv']['tmp_name']) > 0
            ) {
                $importer = new Import($this->db);
                $importer->setCustomerGroupID(Request::postInt('kKundengruppe'));
                $importer->setLanguageID(Request::postInt('kSprache'));

                if ($importer->processFile($_FILES['csv']['tmp_name']) === false) {
                    $this->alertService->addError(\implode('<br>', $importer->getErrors()), 'importError');
                }

                if ($importer->getImportedRowsCount() > 0) {
                    $this->alertService->addSuccess(
                        \sprintf(\__('successImportCustomerCsv'), $importer->getImportedRowsCount()),
                        'importSuccess',
                        ['dismissable' => true, 'fadeOut' => 0]
                    );

                    $smarty->assign('noPasswordCustomerIds', $importer->getNoPasswordCustomerIds());
                }
            } elseif (Request::postVar('action') === 'notify-customers') {
                $noPasswordCustomerIds = \json_decode(Request::postVar('noPasswordCustomerIds', '[]'));
                $importer              = new Import($this->db);
                $importer->notifyCustomers($noPasswordCustomerIds);
            }
        }

        return $smarty->assign('kundengruppen', $this->db->getObjects(
            'SELECT * FROM tkundengruppe ORDER BY cName'
        ))
            ->assign('route', $this->route)
            ->getResponse('kundenimport.tpl');
    }
}
