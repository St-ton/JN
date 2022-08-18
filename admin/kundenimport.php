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
        && Request::postVar('action') === 'import-customers'
        && \mb_strlen($_FILES['csv']['tmp_name']) > 0
    ) {
        $alertService = Shop::Container()->getAlertService();
        $importer     = new Import(Shop::Container()->getDB());
        $importer->setCustomerGroupID(Request::postInt('kKundengruppe'));
        $importer->setLanguageID(Request::postInt('kSprache'));

        if ($importer->processFile($_FILES['csv']['tmp_name']) === false) {
            $alertService->addAlert(Alert::TYPE_ERROR, \implode('<br>', $importer->getErrors()), 'importError');
        }

        if ($importer->getImportedRowsCount() > 0) {
            $alertService->addAlert(
                Alert::TYPE_SUCCESS,
                \sprintf(\__('successImportCustomerCsv'), $importer->getImportedRowsCount()),
                'importSuccess',
                ['dismissable' => true, 'fadeOut' => 0]
            );

            $smarty->assign('noPasswordCustomerIds', $importer->getNoPasswordCustomerIds());
        }
    }
}

$smarty->assign('kundengruppen', Shop::Container()->getDB()->getObjects(
    'SELECT * FROM tkundengruppe ORDER BY cName'
))
    ->assign('step', $step ?? null)
    ->display('kundenimport.tpl');
