<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Alert\Alert;
use JTL\Backend\Permissions;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Pagination;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class SelectionWizardController
 * @package JTL\Router\Controller\Backend
 */
class PackagingsController extends AbstractBackendController
{
    private string $action = '';

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->getText->loadAdminLocale('pages/zusatzverpackung');
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::ORDER_PACKAGE_VIEW);

        if (Form::validateToken()) {
            if (isset($_POST['action'])) {
                $this->action = $_POST['action'];
            } elseif (Request::getInt('kVerpackung', -1) >= 0) {
                $this->action = 'edit';
            }
        }

        if ($this->action === 'save') {
            $this->actionSave(Text::filterXSS($_POST));
        }
        if ($this->action === 'edit' && Request::verifyGPCDataInt('kVerpackung') > 0) { // Editieren
            $this->actionEdit(Request::verifyGPCDataInt('kVerpackung'));
        } elseif ($this->action === 'delete') {
            $this->actionDelete();
        } elseif ($this->action === 'refresh') {
            $this->actionRefresh();
        }
        $packagingCount = (int)$this->db->getSingleObject(
            'SELECT COUNT(kVerpackung) AS cnt
                FROM tverpackung'
        )->cnt;
        $itemsPerPage   = 10;
        $pagination     = (new Pagination('standard'))
            ->setItemsPerPageOptions([$itemsPerPage, $itemsPerPage * 2, $itemsPerPage * 5])
            ->setItemCount($packagingCount)
            ->assemble();
        $packagings     = $this->db->getObjects(
            'SELECT * FROM tverpackung 
                ORDER BY cName' .
            ($pagination->getLimitSQL() !== '' ? ' LIMIT ' . $pagination->getLimitSQL() : '')
        );

        foreach ($packagings as $packaging) {
            $customerGroup                = $this->getCustomerGroupData($packaging->cKundengruppe);
            $packaging->kKundengruppe_arr = $customerGroup->kKundengruppe_arr;
            $packaging->cKundengruppe_arr = $customerGroup->cKundengruppe_arr;
        }

        return $smarty->assign('customerGroups', CustomerGroup::getGroups())
            ->assign('taxClasses', $this->db->getObjects('SELECT * FROM tsteuerklasse'))
            ->assign('packagings', $packagings)
            ->assign('step', 'zusatzverpackung')
            ->assign('pagination', $pagination)
            ->assign('route', $this->route)
            ->assign('action', $this->action)
            ->getResponse('zusatzverpackung.tpl');
    }

    /**
     * @param array $postData
     * @return void
     */
    private function actionSave(array $postData): void
    {
        $languages                      = LanguageHelper::getAllLanguages(0, true);
        $nameIDX                        = 'cName_' . $languages[0]->getCode();
        $packagingID                    = Request::postInt('kVerpackung');
        $customerGroupIDs               = $postData['kKundengruppe'] ?? null;
        $packaging                      = new stdClass();
        $packaging->fBrutto             = (float)\str_replace(',', '.', $postData['fBrutto'] ?? 0);
        $packaging->fMindestbestellwert = (float)\str_replace(',', '.', $postData['fMindestbestellwert'] ?? 0);
        $packaging->fKostenfrei         = (float)\str_replace(',', '.', $postData['fKostenfrei'] ?? 0);
        $packaging->kSteuerklasse       = Request::postInt('kSteuerklasse');
        $packaging->nAktiv              = Request::postInt('nAktiv');
        $packaging->cName               = \htmlspecialchars(
            \strip_tags(\trim($postData[$nameIDX])),
            \ENT_COMPAT | \ENT_HTML401,
            \JTL_CHARSET
        );
        if ($packaging->kSteuerklasse < 0) {
            $packaging->kSteuerklasse = 0;
        }
        if (!(isset($postData[$nameIDX]) && \mb_strlen($postData[$nameIDX]) > 0)) {
            $this->alertService->addError(\__('errorNameMissing'), 'errorNameMissing');
        }
        if (!(\is_array($customerGroupIDs) && \count($customerGroupIDs) > 0)) {
            $this->alertService->addError(\__('errorCustomerGroupMissing'), 'errorCustomerGroupMissing');
        }

        if ($this->alertService->alertTypeExists(Alert::TYPE_ERROR)) {
            $this->holdInputOnError($packaging, $customerGroupIDs, $packagingID);
            $this->action = 'edit';
            return;
        }
        if ((int)$customerGroupIDs[0] === -1) {
            $packaging->cKundengruppe = '-1';
        } else {
            $packaging->cKundengruppe = ';' . \implode(';', $customerGroupIDs) . ';';
        }
        // Update?
        if ($packagingID > 0) {
            $this->db->queryPrepared(
                'DELETE tverpackung, tverpackungsprache
                FROM tverpackung
                LEFT JOIN tverpackungsprache 
                    ON tverpackungsprache.kVerpackung = tverpackung.kVerpackung
                WHERE tverpackung.kVerpackung = :pid',
                ['pid' => $packagingID]
            );
            $packaging->kVerpackung = $packagingID;
            $this->db->insert('tverpackung', $packaging);
        } else {
            $packagingID = $this->db->insert('tverpackung', $packaging);
        }
        foreach ($languages as $lang) {
            $langCode                 = $lang->getCode();
            $localized                = new stdClass();
            $localized->kVerpackung   = $packagingID;
            $localized->cISOSprache   = $langCode;
            $localized->cName         = !empty($postData['cName_' . $langCode])
                ? \htmlspecialchars($postData['cName_' . $langCode], \ENT_COMPAT | \ENT_HTML401, \JTL_CHARSET)
                : \htmlspecialchars($postData[$nameIDX], \ENT_COMPAT | \ENT_HTML401, \JTL_CHARSET);
            $localized->cBeschreibung = !empty($postData['cBeschreibung_' . $langCode])
                ? \htmlspecialchars($postData['cBeschreibung_' . $langCode], \ENT_COMPAT | \ENT_HTML401, \JTL_CHARSET)
                : \htmlspecialchars(
                    $postData['cBeschreibung_' . $languages[0]->getCode()],
                    \ENT_COMPAT | \ENT_HTML401,
                    \JTL_CHARSET
                );
            $this->db->insert('tverpackungsprache', $localized);
        }
        $this->alertService->addSuccess(
            \sprintf(\__('successPackagingSave'), $postData[$nameIDX]),
            'successPackagingSave'
        );
    }

    /**
     * @param int $packagingID
     * @return void
     */
    private function actionEdit(int $packagingID): void
    {
        $packaging = $this->db->select('tverpackung', 'kVerpackung', $packagingID);
        if ($packaging !== null && $packaging->kVerpackung > 0) {
            $packaging->oSprach_arr = [];
            $localizations          = $this->db->selectAll(
                'tverpackungsprache',
                'kVerpackung',
                $packagingID,
                'cISOSprache, cName, cBeschreibung'
            );
            foreach ($localizations as $localization) {
                $packaging->oSprach_arr[$localization->cISOSprache] = $localization;
            }
            $customerGroup                = $this->getCustomerGroupData($packaging->cKundengruppe);
            $packaging->kKundengruppe_arr = $customerGroup->kKundengruppe_arr;
            $packaging->cKundengruppe_arr = $customerGroup->cKundengruppe_arr;
        }
        $this->smarty->assign('kVerpackung', $packaging->kVerpackung)
            ->assign('oVerpackungEdit', $packaging);
    }

    private function actionRefresh(): void
    {
        if (GeneralObject::hasCount('nAktivTMP', $_POST)) {
            foreach ($_POST['nAktivTMP'] as $packagingID) {
                $upd         = new stdClass();
                $upd->nAktiv = isset($_POST['nAktiv']) && \in_array($packagingID, $_POST['nAktiv'], true) ? 1 : 0;
                $this->db->update('tverpackung', 'kVerpackung', (int)$packagingID, $upd);
            }
            $this->alertService->addSuccess(\__('successPackagingSaveMultiple'), 'successPackagingSaveMultiple');
        }
    }

    private function actionDelete(): void
    {
        if (GeneralObject::hasCount('kVerpackung', $_POST)) {
            foreach ($_POST['kVerpackung'] as $packagingID) {
                $packagingID = (int)$packagingID;
                // tverpackung loeschen
                $this->db->delete('tverpackung', 'kVerpackung', $packagingID);
                $this->db->delete('tverpackungsprache', 'kVerpackung', $packagingID);
            }
            $this->alertService->addSuccess(\__('successPackagingDelete'), 'successPackagingDelete');
        } else {
            $this->alertService->addError(\__('errorAtLeastOnePackaging'), 'errorAtLeastOnePackaging');
        }
    }

    /**
     * @param string $groupString
     * @return stdClass
     * @former gibKundengruppeObj()
     */
    private function getCustomerGroupData(string $groupString): stdClass
    {
        $customerGroup = new stdClass();
        $tmpIDs        = [];
        $tmpNames      = [];

        if (\mb_strlen($groupString) > 0) {
            $data             = $this->db->getObjects('SELECT kKundengruppe, cName FROM tkundengruppe');
            $customerGroupIDs = \array_map('\intval', \array_filter(\explode(';', $groupString)));
            if (!\in_array(-1, $customerGroupIDs, true)) {
                foreach ($customerGroupIDs as $id) {
                    $id       = (int)$id;
                    $tmpIDs[] = $id;
                    foreach ($data as $customerGroup) {
                        if ((int)$customerGroup->kKundengruppe === $id) {
                            $tmpNames[] = $customerGroup->cName;
                            break;
                        }
                    }
                }
            } elseif (\count($data) > 0) {
                foreach ($data as $customerGroup) {
                    $tmpIDs[]   = $customerGroup->kKundengruppe;
                    $tmpNames[] = $customerGroup->cName;
                }
            }
        }
        $customerGroup->kKundengruppe_arr = $tmpIDs;
        $customerGroup->cKundengruppe_arr = $tmpNames;

        return $customerGroup;
    }

    /**
     * @param stdClass   $packaging
     * @param array|null $customerGroupIDs
     * @param int        $packagingID
     * @return void
     * @former holdInputOnError()
     */
    private function holdInputOnError(stdClass $packaging, ?array $customerGroupIDs, int $packagingID): void
    {
        $packaging->oSprach_arr = [];
        $postData               = Text::filterXSS($_POST);
        foreach ($postData as $key => $value) {
            if (!\str_contains($key, 'cName')) {
                continue;
            }
            $iso                                 = \explode('cName_', $key)[1];
            $idx                                 = 'cBeschreibung_' . $iso;
            $packaging->oSprach_arr[$iso]        = new stdClass();
            $packaging->oSprach_arr[$iso]->cName = $value;
            if (isset($postData[$idx])) {
                $packaging->oSprach_arr[$iso]->cBeschreibung = $postData[$idx];
            }
        }

        if (\is_array($customerGroupIDs) && $customerGroupIDs[0] !== '-1') {
            $packaging->cKundengruppe     = ';' . \implode(';', $customerGroupIDs) . ';';
            $customerGroup                = $this->getCustomerGroupData($packaging->cKundengruppe);
            $packaging->kKundengruppe_arr = $customerGroup->kKundengruppe_arr;
            $packaging->cKundengruppe_arr = $customerGroup->cKundengruppe_arr;
        } else {
            $packaging->cKundengruppe = '-1';
        }

        $this->smarty->assign('oVerpackungEdit', $packaging)
            ->assign('kVerpackung', $packagingID);
    }
}
