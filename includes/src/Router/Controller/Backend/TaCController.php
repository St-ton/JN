<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Recommendation\Manager;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class TaCController
 * @package JTL\Router\Controller\Backend
 */
class TaCController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('ORDER_AGB_WRB_VIEW');
        $this->getText->loadAdminLocale('pages/agbwrb');
        $this->step      = 'agbwrb_uebersicht';
        $recommendations = new Manager($this->alertService, Manager::SCOPE_BACKEND_LEGAL_TEXTS);
        $this->setzeSprache();
        $languageID = (int)$_SESSION['editLanguageID'];
        if (Request::verifyGPCDataInt('agbwrb') === 1 && Form::validateToken()) {
            // Editieren
            if (Request::verifyGPCDataInt('agbwrb_edit') === 1) {
                if (Request::verifyGPCDataInt('kKundengruppe') > 0) {
                    $this->step = 'agbwrb_editieren';
                    $data       = $this->db->select(
                        'ttext',
                        'kSprache',
                        $languageID,
                        'kKundengruppe',
                        Request::verifyGPCDataInt('kKundengruppe')
                    );
                    $this->smarty->assign('kKundengruppe', Request::verifyGPCDataInt('kKundengruppe'))
                        ->assign('oAGBWRB', $data);
                } else {
                    $this->alertService->addError(\__('errorInvalidCustomerGroup'), 'errorInvalidCustomerGroup');
                }
            } elseif (Request::verifyGPCDataInt('agbwrb_editieren_speichern') === 1) {
                if ($this->speicherAGBWRB(
                    Request::verifyGPCDataInt('kKundengruppe'),
                    $languageID,
                    $_POST,
                    Request::verifyGPCDataInt('kText')
                )) {
                    $this->alertService->addSuccess(\__('successSave'), 'agbWrbSuccessSave');
                } else {
                    $this->alertService->addError(\__('errorSave'), 'agbWrbErrorSave');
                }
            }
        }

        if ($this->step === 'agbwrb_uebersicht') {
            $agbWrb = [];
            $data   = $this->db->selectAll('ttext', 'kSprache', $languageID);
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

        return $this->smarty->assign('step', $this->step)
            ->assign('languageID', $languageID)
            ->assign('route', $route->getPath())
            ->assign('recommendations', $recommendations)
            ->getResponse('agbwrb.tpl');
    }

    /**
     * @param int   $customerGroupID
     * @param int   $languageID
     * @param array $post
     * @param int   $textID
     * @return bool
     * @former speicherAGBWRB()
     */
    private function speicherAGBWRB(int $customerGroupID, int $languageID, array $post, int $textID = 0): bool
    {
        if ($customerGroupID <= 0 || $languageID <= 0) {
            return false;
        }
        $item = new stdClass();
        if ($textID > 0) {
            $this->db->delete('ttext', 'kText', $textID);
            $item->kText = $textID;
        }
        $item->kSprache            = $languageID;
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

        return true;
    }
}
