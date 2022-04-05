<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Alert\Alert;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Pagination;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class SelectionWizardController
 * @package JTL\Router\Controller\Backend
 */
class PackagingsController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->getText->loadAdminLocale('pages/zusatzverpackung');
        $this->smarty = $smarty;
        $this->checkPermissions('ORDER_PACKAGE_VIEW');

        $step      = 'zusatzverpackung';
        $languages = LanguageHelper::getAllLanguages(0, true);
        $action    = '';
        if (Form::validateToken()) {
            if (isset($_POST['action'])) {
                $action = $_POST['action'];
            } elseif (Request::getInt('kVerpackung', -1) >= 0) {
                $action = 'edit';
            }
        }

        if ($action === 'save') {
            $postData                       = Text::filterXSS($_POST);
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
            if (!(isset($postData[$nameIDX]) && mb_strlen($postData[$nameIDX]) > 0)) {
                $this->alertService->addError(\__('errorNameMissing'), 'errorNameMissing');
            }
            if (!(\is_array($customerGroupIDs) && count($customerGroupIDs) > 0)) {
                $this->alertService->addError(\__('errorCustomerGroupMissing'), 'errorCustomerGroupMissing');
            }

            if ($this->alertService->alertTypeExists(Alert::TYPE_ERROR)) {
                $this->holdInputOnError($packaging, $customerGroupIDs, $packagingID);
                $action = 'edit';
            } else {
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
        } elseif ($action === 'edit' && Request::verifyGPCDataInt('kVerpackung') > 0) { // Editieren
            $packagingID = Request::verifyGPCDataInt('kVerpackung');
            $packaging   = $this->db->select('tverpackung', 'kVerpackung', $packagingID);

            if (isset($packaging->kVerpackung) && $packaging->kVerpackung > 0) {
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
                $customerGroup                = $this->gibKundengruppeObj($packaging->cKundengruppe);
                $packaging->kKundengruppe_arr = $customerGroup->kKundengruppe_arr;
                $packaging->cKundengruppe_arr = $customerGroup->cKundengruppe_arr;
            }
            $smarty->assign('kVerpackung', $packaging->kVerpackung)
                ->assign('oVerpackungEdit', $packaging);
        } elseif ($action === 'delete') {
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
        } elseif ($action === 'refresh') {
            if (isset($_POST['nAktivTMP']) && \is_array($_POST['nAktivTMP']) && count($_POST['nAktivTMP']) > 0) {
                foreach ($_POST['nAktivTMP'] as $packagingID) {
                    $upd         = new stdClass();
                    $upd->nAktiv = isset($_POST['nAktiv']) && \in_array($packagingID, $_POST['nAktiv'], true) ? 1 : 0;
                    $this->db->update('tverpackung', 'kVerpackung', (int)$packagingID, $upd);
                }
                $this->alertService->addSuccess(\__('successPackagingSaveMultiple'), 'successPackagingSaveMultiple');
            }
        }
        $taxClasses = $this->db->getObjects('SELECT * FROM tsteuerklasse');

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
            $customerGroup                = $this->gibKundengruppeObj($packaging->cKundengruppe);
            $packaging->kKundengruppe_arr = $customerGroup->kKundengruppe_arr;
            $packaging->cKundengruppe_arr = $customerGroup->cKundengruppe_arr;
        }

        return $smarty->assign('customerGroups', CustomerGroup::getGroups())
            ->assign('taxClasses', $taxClasses)
            ->assign('packagings', $packagings)
            ->assign('step', $step)
            ->assign('pagination', $pagination)
            ->assign('route', $route->getPath())
            ->assign('action', $action)
            ->getResponse('zusatzverpackung.tpl');
    }

    /**
     * @param string $groupString
     * @return stdClass
     * @former gibKundengruppeObj()
     */
    private function gibKundengruppeObj(string $groupString): stdClass
    {
        $customerGroup = new stdClass();
        $tmpIDs        = [];
        $tmpNames      = [];

        if (mb_strlen($groupString) > 0) {
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
            } elseif (count($data) > 0) {
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
            if (mb_strpos($key, 'cName') === false) {
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
            $customerGroup                = $this->gibKundengruppeObj($packaging->cKundengruppe);
            $packaging->kKundengruppe_arr = $customerGroup->kKundengruppe_arr;
            $packaging->cKundengruppe_arr = $customerGroup->cKundengruppe_arr;
        } else {
            $packaging->cKundengruppe = '-1';
        }

        $this->smarty->assign('oVerpackungEdit', $packaging)
            ->assign('kVerpackung', $packagingID);
    }
}
