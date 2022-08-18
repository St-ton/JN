<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Customer\Import;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('IMPORT_CUSTOMER_VIEW', true, true);

if (Form::validateToken()) {
    if (isset($_FILES['csv']['tmp_name'])
        && Request::postInt('kundenimport') === 1
        && mb_strlen($_FILES['csv']['tmp_name']) > 0
    ) {
        $importer = new Import(Shop::Container()->getDB());
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

$smarty->assign('kundengruppen', Shop::Container()->getDB()->getObjects(
    'SELECT * FROM tkundengruppe ORDER BY cName'
))
    ->assign('step', $step ?? null)
    ->display('kundenimport.tpl');
