<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Customer\Import;
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
    public function getResponse(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $this->checkPermissions(Permissions::IMPORT_CUSTOMER_VIEW);
        $this->getText->loadAdminLocale('pages/kundenimport');
        if ($this->tokenIsValid) {
            if (isset($_FILES['csv']['tmp_name'])
                && $this->request->post('action') === 'import-customers'
                && \mb_strlen($_FILES['csv']['tmp_name']) > 0
            ) {
                $importer = new Import($this->db);
                $importer->setCustomerGroupID($this->request->postInt('kKundengruppe'));
                $importer->setLanguageID($this->request->postInt('kSprache'));

                if ($importer->processFile($_FILES['csv']['tmp_name']) === false) {
                    $this->alertService->addError(\implode('<br>', $importer->getErrors()), 'importError');
                }

                if ($importer->getImportedRowsCount() > 0) {
                    $this->alertService->addSuccess(
                        \sprintf(\__('successImportCustomerCsv'), $importer->getImportedRowsCount()),
                        'importSuccess',
                        ['dismissable' => true, 'fadeOut' => 0]
                    );
                    $this->smarty->assign('noPasswordCustomerIds', $importer->getNoPasswordCustomerIds());
                }
            } elseif ($this->request->post('action') === 'notify-customers') {
                $noPasswordCustomerIds = \json_decode($this->request->post('noPasswordCustomerIds', '[]'));
                $importer              = new Import($this->db);
                $importer->notifyCustomers($noPasswordCustomerIds);
            }
        }

        return $this->smarty->assign('kundengruppen', $this->db->getObjects(
            'SELECT * FROM tkundengruppe ORDER BY cName'
        ))
            ->getResponse('kundenimport.tpl');
    }
}
