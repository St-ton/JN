<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Extensions\SelectionWizard\Group;
use JTL\Extensions\SelectionWizard\Question;
use JTL\Extensions\SelectionWizard\Wizard;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Nice;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SelectionWizardController
 * @package JTL\Router\Controller\Backend
 */
class SelectionWizardController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::EXTENSION_SELECTIONWIZARD_VIEW);
        $step     = '';
        $nice     = Nice::getInstance();
        $tab      = 'uebersicht';
        $postData = Text::filterXSS($_POST);
        $this->getText->loadAdminLocale('pages/auswahlassistent');
        $this->getText->loadConfigLocales();
        $this->setLanguage();
        if ($nice->checkErweiterung(SHOP_ERWEITERUNG_AUSWAHLASSISTENT)) {
            $group    = new Group();
            $question = new Question();
            $step     = 'uebersicht';
            $csrfOK   = Form::validateToken();
            if (Request::verifyGPDataString('tab') !== '') {
                $tab = Request::verifyGPDataString('tab');
            }
            if (isset($postData['a']) && $csrfOK) {
                if ($postData['a'] === 'newGrp') {
                    $step = 'edit-group';
                } elseif ($postData['a'] === 'newQuest') {
                    $step = 'edit-question';
                } elseif ($postData['a'] === 'addQuest') {
                    $question->cFrage                  = \htmlspecialchars(
                        $postData['cFrage'],
                        \ENT_COMPAT | \ENT_HTML401,
                        \JTL_CHARSET
                    );
                    $question->kMerkmal                = Request::postInt('kMerkmal');
                    $question->kAuswahlAssistentGruppe = Request::postInt('kAuswahlAssistentGruppe');
                    $question->nSort                   = Request::postInt('nSort');
                    $question->nAktiv                  = Request::postInt('nAktiv');

                    if (Request::postInt('kAuswahlAssistentFrage') > 0) {
                        $question->kAuswahlAssistentFrage = Request::postInt('kAuswahlAssistentFrage');
                        $checks                           = $question->updateQuestion();
                    } else {
                        $checks = $question->saveQuestion();
                    }
                    if ((!\is_array($checks) && $checks) || \count($checks) === 0) {
                        $this->cache->flushTags([\CACHING_GROUP_CORE]);
                        $this->alertService->addSuccess(\__('successQuestionSaved'), 'successQuestionSaved');
                        $tab = 'uebersicht';
                    } elseif (\is_array($checks) && \count($checks) > 0) {
                        $step = 'edit-question';
                        $this->alertService->addError(\__('errorFillRequired'), 'errorFillRequired');
                        $this->smarty->assign('cPost_arr', $postData)
                            ->assign('cPlausi_arr', $checks)
                            ->assign('kAuswahlAssistentFrage', (int)($postData['kAuswahlAssistentFrage'] ?? 0));
                    }
                }
            } elseif ($csrfOK && Request::getVar('a') === 'delQuest' && Request::getInt('q') > 0) {
                if ($question->deleteQuestion([Request::getInt('q')])) {
                    $this->alertService->addSuccess(\__('successQuestionDeleted'), 'successQuestionDeleted');
                    $this->cache->flushTags([\CACHING_GROUP_CORE]);
                } else {
                    $this->alertService->addError(\__('errorQuestionDeleted'), 'errorQuestionDeleted');
                }
            } elseif ($csrfOK && Request::getVar('a') === 'editQuest' && Request::getInt('q') > 0) {
                $step = 'edit-question';
                $this->smarty->assign('oFrage', new Question(Request::getInt('q'), false));
            }

            if (isset($postData['a']) && $csrfOK) {
                if ($postData['a'] === 'addGrp') {
                    $group->kSprache      = $this->currentLanguageID;
                    $group->cName         = \htmlspecialchars(
                        $postData['cName'],
                        \ENT_COMPAT | \ENT_HTML401,
                        \JTL_CHARSET
                    );
                    $group->cBeschreibung = $postData['cBeschreibung'];
                    $group->nAktiv        = Request::postInt('nAktiv');

                    if (Request::postInt('kAuswahlAssistentGruppe') > 0) {
                        $group->kAuswahlAssistentGruppe = Request::postInt('kAuswahlAssistentGruppe');
                        $checks                         = $group->updateGroup($postData);
                    } else {
                        $checks = $group->saveGroup($postData);
                    }
                    if ((!\is_array($checks) && $checks) || \count($checks) === 0) {
                        $step = 'uebersicht';
                        $tab  = 'uebersicht';
                        $this->cache->flushTags([\CACHING_GROUP_CORE]);
                        $this->alertService->addSuccess(\__('successGroupSaved'), 'successGroupSaved');
                    } elseif (\is_array($checks) && \count($checks) > 0) {
                        $step = 'edit-group';
                        $this->alertService->addError(\__('errorFillRequired'), 'errorFillRequired');
                        $this->smarty->assign('cPost_arr', $postData)
                            ->assign('cPlausi_arr', $checks)
                            ->assign('kAuswahlAssistentGruppe', Request::postInt('kAuswahlAssistentGruppe'));
                    }
                } elseif ($postData['a'] === 'delGrp') {
                    if ($group->deleteGroup($postData['kAuswahlAssistentGruppe_arr'] ?? [])) {
                        $this->cache->flushTags([\CACHING_GROUP_CORE]);
                        $this->alertService->addSuccess(\__('successGroupDeleted'), 'successGroupDeleted');
                    } else {
                        $this->alertService->addError(\__('errorGroupDeleted'), 'errorGroupDeleted');
                    }
                } elseif ($postData['a'] === 'saveSettings') {
                    $step = 'uebersicht';
                    $this->saveAdminSectionSettings(\CONF_AUSWAHLASSISTENT, $postData);
                    $this->cache->flushTags([\CACHING_GROUP_CORE]);
                }
            } elseif ($csrfOK && Request::getVar('a') === 'editGrp' && Request::getInt('g') > 0) {
                $step = 'edit-group';
                $this->smarty->assign('oGruppe', new Group(Request::getInt('g'), false, false, true));
            }
            if ($step === 'uebersicht') {
                $this->smarty->assign(
                    'oAuswahlAssistentGruppe_arr',
                    $group->getGroups($this->currentLanguageID, false, false, true)
                );
            } elseif ($step === 'edit-group') {
                $this->smarty->assign('oLink_arr', Wizard::getLinks());
            } elseif ($step === 'edit-question') {
                $defaultLanguage = $this->db->select('tsprache', 'cShopStandard', 'Y');
                $select          = 'tmerkmal.*';
                $join            = '';
                if ($defaultLanguage !== null && (int)$defaultLanguage->kSprache !== $this->currentLanguageID) {
                    $select = 'tmerkmalsprache.*';
                    $join   = ' JOIN tmerkmalsprache ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                            AND tmerkmalsprache.kSprache = ' . $this->currentLanguageID;
                }
                $attributes = $this->db->getObjects(
                    'SELECT ' . $select . '
                    FROM tmerkmal
                    ' . $join . '
                    ORDER BY tmerkmal.nSort'
                );
                $this->smarty->assign('oMerkmal_arr', $attributes)
                    ->assign(
                        'oAuswahlAssistentGruppe_arr',
                        $group->getGroups($this->currentLanguageID, false, false, true)
                    );
            }
        } else {
            $this->smarty->assign('noModule', true);
        }
        $this->getAdminSectionSettings(\CONF_AUSWAHLASSISTENT);

        return $this->smarty->assign('step', $step)
            ->assign('cTab', $tab)
            ->assign('languageID', $this->currentLanguageID)
            ->assign('route', $this->route)
            ->getResponse('auswahlassistent.tpl');
    }
}
