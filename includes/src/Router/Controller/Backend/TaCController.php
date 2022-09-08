<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Recommendation\Manager;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class TaCController
 * @package JTL\Router\Controller\Backend
 */
class TaCController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::ORDER_AGB_WRB_VIEW);
        $this->getText->loadAdminLocale('pages/agbwrb');
        $this->step = 'agbwrb_uebersicht';
        $this->setLanguage();
        $this->assignScrollPosition();

        if (Request::verifyGPCDataInt('agbwrb') === 1 && Form::validateToken()) {
            // Editieren
            if (Request::verifyGPCDataInt('agbwrb_edit') === 1) {
                $this->actionEdit(Request::verifyGPCDataInt('kKundengruppe'));
            } elseif (Request::verifyGPCDataInt('agbwrb_editieren_speichern') === 1) {
                $this->actionSave(
                    Request::verifyGPCDataInt('kKundengruppe'),
                    $_POST,
                    Request::verifyGPCDataInt('kText')
                );
                if (Request::postVar('saveAndContinue')) {
                    $this->actionEdit(Request::verifyGPCDataInt('kKundengruppe'));
                }
            }
        }

        if ($this->step === 'agbwrb_uebersicht') {
            $this->assignOverview();
        }

        return $this->smarty->assign('step', $this->step)
            ->assign('languageID', $this->currentLanguageID)
            ->assign('route', $this->route)
            ->assign('recommendations', new Manager($this->alertService, Manager::SCOPE_BACKEND_LEGAL_TEXTS))
            ->getResponse('agbwrb.tpl');
    }

    /**
     * @param int $customerGroupID
     * @return void
     */
    private function actionEdit(int $customerGroupID): void
    {
        if ($customerGroupID > 0) {
            $this->step = 'agbwrb_editieren';
            $data       = $this->db->select(
                'ttext',
                'kSprache',
                $this->currentLanguageID,
                'kKundengruppe',
                $customerGroupID
            );
            $this->smarty->assign('kKundengruppe', $customerGroupID)
                ->assign('oAGBWRB', $data);
        } else {
            $this->alertService->addError(\__('errorInvalidCustomerGroup'), 'errorInvalidCustomerGroup');
        }
    }

    private function assignOverview(): void
    {
        $agbWrb = [];
        $data   = $this->db->selectAll('ttext', 'kSprache', $this->currentLanguageID);
        foreach ($data as $item) {
            $item->kKundengruppe          = (int)$item->kKundengruppe;
            $item->kText                  = (int)$item->kText;
            $item->kSprache               = (int)$item->kSprache;
            $item->nStandard              = (int)$item->nStandard;
            $agbWrb[$item->kKundengruppe] = $item;
        }
        $this->smarty->assign('customerGroups', CustomerGroup::getGroups())
            ->assign('oAGBWRB_arr', $agbWrb);
    }

    /**
     * @param int   $customerGroupID
     * @param array $post
     * @param int   $textID
     * @return void
     * @former speicherAGBWRB()
     */
    private function actionSave(int $customerGroupID, array $post, int $textID = 0): void
    {
        if ($customerGroupID <= 0 || $this->currentLanguageID <= 0) {
            $this->alertService->addError(\__('errorSave'), 'agbWrbErrorSave');
            return;
        }
        $item = new stdClass();
        if ($textID > 0) {
            $this->db->delete('ttext', 'kText', $textID);
            $item->kText = $textID;
        }
        $item->kSprache            = $this->currentLanguageID;
        $item->kKundengruppe       = $customerGroupID;
        $item->cAGBContentText     = $post['cAGBContentText'];
        $item->cAGBContentHtml     = $post['cAGBContentHtml'];
        $item->cWRBContentText     = $post['cWRBContentText'];
        $item->cWRBContentHtml     = $post['cWRBContentHtml'];
        $item->cDSEContentText     = $post['cDSEContentText'];
        $item->cDSEContentHtml     = $post['cDSEContentHtml'];
        $item->cWRBFormContentText = $post['cWRBFormContentText'];
        $item->cWRBFormContentHtml = $post['cWRBFormContentHtml'];
        /* deprecated */
        $item->nStandard = 0;

        $this->db->insert('ttext', $item);
        $this->alertService->addSuccess(\__('successSave'), 'agbWrbSuccessSave');
    }
}
