<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\CheckBox;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Pagination\Pagination;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CheckboxController
 * @package JTL\Router\Controller\Backend
 */
class CheckboxController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::CHECKBOXES_VIEW);
        $this->getText->loadAdminLocale('pages/checkbox');

        $step     = 'uebersicht';
        $checkbox = new CheckBox(0, $this->db);
        $tab      = $step;
        if (\mb_strlen(Request::verifyGPDataString('tab')) > 0) {
            $tab = Request::verifyGPDataString('tab');
        }
        if (isset($_POST['erstellenShowButton'])) {
            $tab = 'erstellen';
        } elseif (Request::verifyGPCDataInt('uebersicht') === 1 && Form::validateToken()) {
            $checkboxIDs = Request::verifyGPDataIntegerArray('kCheckBox');
            if (isset($_POST['checkboxAktivierenSubmit'])) {
                $checkbox->activate($checkboxIDs);
                $this->alertService->addSuccess(\__('successCheckboxActivate'), 'successCheckboxActivate');
            } elseif (isset($_POST['checkboxDeaktivierenSubmit'])) {
                $checkbox->deactivate($checkboxIDs);
                $this->alertService->addSuccess(\__('successCheckboxDeactivate'), 'successCheckboxDeactivate');
            } elseif (isset($_POST['checkboxLoeschenSubmit'])) {
                $checkbox->delete($checkboxIDs);
                $this->alertService->addSuccess(\__('successCheckboxDelete'), 'successCheckboxDelete');
            }
        } elseif (Request::verifyGPCDataInt('edit') > 0) {
            $checkboxID = Request::verifyGPCDataInt('edit');
            $step       = 'erstellen';
            $tab        = $step;
            $smarty->assign('oCheckBox', new CheckBox($checkboxID, $this->db));
        } elseif (Request::verifyGPCDataInt('erstellen') === 1 && Form::validateToken()) {
            $post       = Text::filterXSS($_POST);
            $step       = 'erstellen';
            $checkboxID = Request::verifyGPCDataInt('kCheckBox');
            $languages  = LanguageHelper::getAllLanguages(0, true, true);
            $checks     = $this->validate($post, $languages);
            if (\count($checks) === 0) {
                $checkbox = $this->save($post, $languages);
                $step     = 'uebersicht';
                $this->alertService->addSuccess(\__('successCheckboxCreate'), 'successCheckboxCreate');
            } else {
                $this->alertService->addError(\__('errorFillRequired'), 'errorFillRequired');
                $smarty->assign('cPost_arr', $post)
                    ->assign('cPlausi_arr', $checks);
                if ($checkboxID > 0) {
                    $smarty->assign('kCheckBox', $checkboxID);
                }
            }
            $tab = $step;
        }

        $pagination = (new Pagination())
            ->setItemCount($checkbox->getTotalCount())
            ->assemble();

        return $smarty->assign('oCheckBox_arr', $checkbox->getAll('LIMIT ' . $pagination->getLimitSQL()))
            ->assign('pagination', $pagination)
            ->assign('cAnzeigeOrt_arr', CheckBox::gibCheckBoxAnzeigeOrte())
            ->assign('customerGroups', CustomerGroup::getGroups())
            ->assign('oLink_arr', $this->db->getObjects(
                'SELECT * 
                     FROM tlink 
                     ORDER BY cName'
            ))
            ->assign('oCheckBoxFunktion_arr', $checkbox->getCheckBoxFunctions())
            ->assign('step', $step)
            ->assign('cTab', $tab)
            ->assign('route', $this->route)
            ->getResponse('checkbox.tpl');
    }

    /**
     * @param array           $post
     * @param LanguageModel[] $languages
     * @return array
     * @former plausiCheckBox()
     */
    private function validate(array $post, array $languages): array
    {
        $checks = [];
        if (\count($languages) === 0) {
            $checks['oSprache_arr'] = 1;

            return $checks;
        }
        if (!isset($post['cName']) || \mb_strlen($post['cName']) === 0) {
            $checks['cName'] = 1;
        }
        $text = false;
        $link = true;
        foreach ($languages as $language) {
            if (\mb_strlen($post['cText_' . $language->getIso()]) > 0) {
                $text = true;
                break;
            }
        }
        if (!$text) {
            $checks['cText'] = 1;
        }
        if ((int)$post['nLink'] === 1) {
            $link = isset($post['kLink']) && (int)$post['kLink'] > 0;
        }
        if (!$link) {
            $checks['kLink'] = 1;
        }
        if (!\is_array($post['cAnzeigeOrt']) || \count($post['cAnzeigeOrt']) === 0) {
            $checks['cAnzeigeOrt'] = 1;
        } else {
            foreach ($post['cAnzeigeOrt'] as $cAnzeigeOrt) {
                if ((int)$cAnzeigeOrt === 3 && (int)$post['kCheckBoxFunktion'] === 1) {
                    $checks['cAnzeigeOrt'] = 2;
                }
            }
        }
        if (!isset($post['nPflicht']) || \mb_strlen($post['nPflicht']) === 0) {
            $checks['nPflicht'] = 1;
        }
        if (!isset($post['nAktiv']) || \mb_strlen($post['nAktiv']) === 0) {
            $checks['nAktiv'] = 1;
        }
        if (!isset($post['nLogging']) || \mb_strlen($post['nLogging']) === 0) {
            $checks['nLogging'] = 1;
        }
        if (!isset($post['nSort']) || (int)$post['nSort'] === 0) {
            $checks['nSort'] = 1;
        }
        if (!\is_array($post['kKundengruppe']) || \count($post['kKundengruppe']) === 0) {
            $checks['kKundengruppe'] = 1;
        }

        return $checks;
    }

    /**
     * @param array $post - pre-filtered post data
     * @param LanguageModel[] $languages
     * @return CheckBox
     * @former speicherCheckBox()
     */
    private function save(array $post, array $languages): CheckBox
    {
        if (isset($post['kCheckBox']) && (int)$post['kCheckBox'] > 0) {
            $checkBox = new CheckBox((int)$post['kCheckBox'], $this->db);
            $checkBox->delete([(int)$post['kCheckBox']]);
        } else {
            $checkBox = new CheckBox(0, $this->db);
        }
        $checkBox->kLink = 0;
        if ((int)$post['nLink'] === 1) {
            $checkBox->kLink = (int)$post['kLink'];
        }
        $checkBox->kCheckBoxFunktion = (int)$post['kCheckBoxFunktion'];
        $checkBox->cName             = \htmlspecialchars($post['cName'], \ENT_COMPAT | \ENT_HTML401, \JTL_CHARSET);
        $checkBox->cKundengruppe     = Text::createSSK($post['kKundengruppe']);
        $checkBox->cAnzeigeOrt       = Text::createSSK($post['cAnzeigeOrt']);
        $checkBox->nAktiv            = 0;
        if ($post['nAktiv'] === 'Y') {
            $checkBox->nAktiv = 1;
        }
        $checkBox->nPflicht = 0;
        $checkBox->nLogging = 0;
        if ($post['nLogging'] === 'Y') {
            $checkBox->nLogging = 1;
        }
        if ($post['nPflicht'] === 'Y') {
            $checkBox->nPflicht = 1;
        }
        $checkBox->nSort     = (int)$post['nSort'];
        $checkBox->dErstellt = 'NOW()';
        $texts               = [];
        $descr               = [];
        foreach ($languages as $language) {
            $code         = $language->getIso();
            $texts[$code] = \str_replace('"', '&quot;', $post['cText_' . $code]);
            $descr[$code] = \str_replace('"', '&quot;', $post['cBeschreibung_' . $code]);
        }

        $checkBox->insertDB($texts, $descr);
        $this->cache->flushTags(['checkbox']);

        return $checkBox;
    }
}
