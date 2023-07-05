<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use function Functional\map;
use function Functional\reindex;

/**
 * Class ContactFormsController
 * @package JTL\Router\Controller\Backend
 */
class ContactFormsController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->getText->loadAdminLocale('pages/kontaktformular');
        $this->checkPermissions(Permissions::SETTINGS_CONTACTFORM_VIEW);

        $this->step = 'uebersicht';
        $tab        = 'config';
        if (Request::getInt('del') > 0 && Form::validateToken()) {
            $this->actionDeleteItem(Request::getInt('del'));
        }
        if (Request::postInt('content') === 1 && Form::validateToken()) {
            $this->actionCreateItem();
            $tab = 'content';
        }
        if (Request::postInt('betreff') === 1 && Form::validateToken()) {
            $postData = Text::filterXSS($_POST);
            $this->actionCreateSubject($postData);
            $tab = 'subjects';
        }
        if (Request::postInt('einstellungen') === 1) {
            $this->saveAdminSectionSettings(\CONF_KONTAKTFORMULAR, $_POST);
            $tab = 'config';
        }
        if ((Request::getInt('kKontaktBetreff') > 0 || Request::getInt('neu') === 1) && Form::validateToken()) {
            $this->step = 'betreff';
        }
        if ($this->step === 'uebersicht') {
            $this->assignOverview();
        } elseif ($this->step === 'betreff') {
            $this->assignCreateSubject();
        }

        return $smarty->assign('step', $this->step)
            ->assign('cTab', $tab)
            ->assign('route', $this->route)
            ->getResponse('kontaktformular.tpl');
    }

    /**
     * @param stdClass|null $link
     * @return array
     * @former getGesetzteKundengruppen()
     */
    private function getActiveCustomerGroups(?stdClass $link): array
    {
        $ret = [];
        if ($link === null || !isset($link->cKundengruppen) || !$link->cKundengruppen) {
            $ret[0] = true;

            return $ret;
        }
        foreach (\array_filter(\explode(';', $link->cKundengruppen)) as $customerGroupID) {
            $ret[$customerGroupID] = true;
        }

        return $ret;
    }

    /**
     * @param int $id
     * @return array
     */
    private function getNames(int $id): array
    {
        $data = $this->db->selectAll('tkontaktbetreffsprache', 'kKontaktBetreff', $id);

        return map(reindex($data, static function ($e) {
            return $e->cISOSprache;
        }), static function ($e) {
            return $e->cName;
        });
    }

    /**
     * @param int $id
     * @return void
     */
    private function actionDeleteItem(int $id): void
    {
        $this->db->delete('tkontaktbetreff', 'kKontaktBetreff', $id);
        $this->db->delete('tkontaktbetreffsprache', 'kKontaktBetreff', $id);
        $this->alertService->addSuccess(\__('successSubjectDelete'), 'successSubjectDelete');
    }

    private function actionCreateItem(): void
    {
        $this->db->delete('tspezialcontentsprache', 'nSpezialContent', \SC_KONTAKTFORMULAR);
        foreach (LanguageHelper::getAllLanguages(0, true, true) as $language) {
            $code                             = $language->getIso();
            $spezialContent1                  = new stdClass();
            $spezialContent2                  = new stdClass();
            $spezialContent3                  = new stdClass();
            $spezialContent1->nSpezialContent = \SC_KONTAKTFORMULAR;
            $spezialContent2->nSpezialContent = \SC_KONTAKTFORMULAR;
            $spezialContent3->nSpezialContent = \SC_KONTAKTFORMULAR;
            $spezialContent1->cISOSprache     = $code;
            $spezialContent2->cISOSprache     = $code;
            $spezialContent3->cISOSprache     = $code;
            $spezialContent1->cTyp            = 'oben';
            $spezialContent2->cTyp            = 'unten';
            $spezialContent3->cTyp            = 'titel';
            $spezialContent1->cContent        = $_POST['cContentTop_' . $code];
            $spezialContent2->cContent        = $_POST['cContentBottom_' . $code];
            $spezialContent3->cContent        = \htmlspecialchars(
                $_POST['cTitle_' . $code],
                \ENT_COMPAT | \ENT_HTML401,
                \JTL_CHARSET
            );

            $this->db->insert('tspezialcontentsprache', $spezialContent1);
            $this->db->insert('tspezialcontentsprache', $spezialContent2);
            $this->db->insert('tspezialcontentsprache', $spezialContent3);
            unset($spezialContent1, $spezialContent2, $spezialContent3);
        }
        $this->alertService->addSuccess(\__('successContentSave'), 'successContentSave');
    }

    /**
     * @param array $postData
     * @return void
     */
    private function actionCreateSubject(array $postData): void
    {
        if (empty($postData['cName']) || empty($postData['cMail'])) {
            $this->alertService->addError(\__('errorSubjectSave'), 'errorSubjectSave');
            $this->step = 'betreff';

            return;
        }
        $subject        = new stdClass();
        $subject->cName = \htmlspecialchars($postData['cName'], \ENT_COMPAT | \ENT_HTML401, \JTL_CHARSET);
        $subject->cMail = $postData['cMail'];
        if (\is_array($postData['cKundengruppen'])) {
            $postData['cKundengruppen'] = \array_map('\intval', $postData['cKundengruppen']);
            $subject->cKundengruppen    = \implode(';', $postData['cKundengruppen']) . ';';
            if (\in_array(0, $postData['cKundengruppen'], true)) {
                $subject->cKundengruppen = 0;
            }
        }
        $subject->nSort = Request::postInt('nSort');
        if (Request::postInt('kKontaktBetreff') === 0) {
            $subjectID = $this->db->insert('tkontaktbetreff', $subject);
            $this->alertService->addSuccess(\__('successSubjectCreate'), 'successSubjectCreate');
        } else {
            $subjectID = Request::postInt('kKontaktBetreff');
            $this->db->update('tkontaktbetreff', 'kKontaktBetreff', $subjectID, $subject);
            $this->alertService->addSuccess(
                \sprintf(\__('successSubjectSave'), $subject->cName),
                'successSubjectSave'
            );
        }
        $localized                  = new stdClass();
        $localized->kKontaktBetreff = $subjectID;
        foreach (LanguageHelper::getAllLanguages(0, true, true) as $language) {
            $code                   = $language->getIso();
            $localized->cISOSprache = $code;
            $localized->cName       = $subject->cName;
            if ($postData['cName_' . $code]) {
                $localized->cName = \htmlspecialchars(
                    $postData['cName_' . $code],
                    \ENT_COMPAT | \ENT_HTML401,
                    \JTL_CHARSET
                );
            }
            $this->db->delete(
                'tkontaktbetreffsprache',
                ['kKontaktBetreff', 'cISOSprache'],
                [$subjectID, $code]
            );
            $this->db->insert('tkontaktbetreffsprache', $localized);
        }
    }

    private function assignCreateSubject(): void
    {
        $subject = null;
        if (Request::getInt('kKontaktBetreff') > 0) {
            $subject = $this->db->select(
                'tkontaktbetreff',
                'kKontaktBetreff',
                Request::getInt('kKontaktBetreff')
            );
        }

        $this->smarty->assign('Betreff', $subject)
            ->assign('kundengruppen', $this->db->getObjects('SELECT * FROM tkundengruppe ORDER BY cName'))
            ->assign('gesetzteKundengruppen', $this->getActiveCustomerGroups($subject))
            ->assign('Betreffname', ($subject !== null) ? $this->getNames((int)$subject->kKontaktBetreff) : null);
    }

    private function assignOverview(): void
    {
        $subjects = $this->db->getObjects('SELECT * FROM tkontaktbetreff ORDER BY nSort');
        foreach ($subjects as $subject) {
            $groups = '';
            if (!$subject->cKundengruppen) {
                $groups = \__('allCustomerGroups');
            } else {
                foreach (\explode(';', $subject->cKundengruppen) as $customerGroupID) {
                    if (!\is_numeric($customerGroupID)) {
                        continue;
                    }
                    $kndgrp  = new CustomerGroup((int)$customerGroupID, $this->db);
                    $groups .= ' ' . ($kndgrp->getName() ?? '');
                }
            }
            $subject->Kundengruppen = $groups;
        }
        $specialContent = $this->db->selectAll(
            'tspezialcontentsprache',
            'nSpezialContent',
            \SC_KONTAKTFORMULAR,
            '*',
            'cTyp'
        );
        $content        = [];
        foreach ($specialContent as $item) {
            $content[$item->cISOSprache . '_' . $item->cTyp] = $item->cContent;
        }
        $this->getAdminSectionSettings(\CONF_KONTAKTFORMULAR);
        $this->smarty->assign('Betreffs', $subjects)
            ->assign('Content', $content);
    }
}
