<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\CSV\Export;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Filter;
use JTL\Pagination\Operation;
use JTL\Pagination\Pagination;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class LanguageController
 * @package JTL\Router\Controller\Backend
 */
class LanguageController extends AbstractBackendController
{
    /**
     * @var LanguageHelper
     */
    private LanguageHelper $helper;

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::LANGUAGE_VIEW);
        $this->getText->loadAdminLocale('pages/sprache');
        $this->setLanguage();

        $this->step   = 'overview';
        $this->helper = LanguageHelper::getInstance($this->db, $this->cache);
        $langActive   = false;
        if (isset($_FILES['csvfile']['tmp_name'])
            && Form::validateToken()
            && Request::verifyGPDataString('importcsv') === 'langvars'
        ) {
            $this->import($_FILES['csvfile']['tmp_name'], $this->currentLanguageCode);
        }
        $installedLanguages = $this->helper->getInstalled();
        $availableLanguages = $this->helper->getAvailable();
        if (\count($installedLanguages) !== \count($availableLanguages)) {
            $this->alertService->addNotice(\__('newLangAvailable'), 'newLangAvailable');
        }
        foreach ($installedLanguages as $language) {
            if ($language->getIso() === $this->currentLanguageCode) {
                $langActive = true;
                break;
            }
        }
        if (isset($_REQUEST['action']) && Form::validateToken()) {
            $this->handleAction($_REQUEST['action']);
        }

        if ($this->step === 'newvar') {
            $smarty->assign('oSektion_arr', $this->helper->getSections());
        } elseif ($this->step === 'overview') {
            $this->getOverview($this->currentLanguageCode, $langActive);
        }

        return $smarty->assign('tab', $_REQUEST['tab'] ?? 'variables')
            ->assign('availableLanguages', $availableLanguages)
            ->assign('step', $this->step)
            ->assign('route', $this->route)
            ->getResponse('sprache.tpl');
    }

    /**
     * @param string $langCode
     * @param bool   $langActive
     * @return void
     */
    private function getOverview(string $langCode, bool $langActive): void
    {
        $langIsoID                   = $this->helper->getLangIDFromIso($langCode)->kSprachISO ?? 0;
        $filter                      = new Filter('langvars');
        $selectField                 = $filter->addSelectfield(\__('section'), 'sw.kSprachsektion');
        $selectField->reloadOnChange = true;
        $selectField->addSelectOption('(' . \__('all') . ')', '');
        foreach ($this->helper->getSections() as $section) {
            $selectField->addSelectOption($section->cName, $section->kSprachsektion, Operation::EQUALS);
        }
        $filter->addTextfield(
            [\__('search'), \__('searchInContentAndVarName')],
            ['sw.cName', 'sw.cWert'],
            Operation::CONTAINS
        );
        $selectField = $filter->addSelectfield(\__('systemOwn'), 'bSystem');
        $selectField->addSelectOption(\__('both'), '');
        $selectField->addSelectOption(\__('system'), '1', Operation::EQUALS);
        $selectField->addSelectOption(\__('own'), '0', Operation::EQUALS);
        $filter->assemble();
        $filterSQL = $filter->getWhereSQL();

        $values = $this->db->getObjects(
            'SELECT sw.cName, sw.cWert, sw.cStandard, sw.bSystem, ss.kSprachsektion, ss.cName AS cSektionName
                FROM tsprachwerte AS sw
                JOIN tsprachsektion AS ss
                    ON ss.kSprachsektion = sw.kSprachsektion
                WHERE sw.kSprachISO = :liso ' . ($filterSQL !== '' ? 'AND ' . $filterSQL : ''),
            ['liso' => $langIsoID]
        );
        if (Form::validateToken() && Request::verifyGPDataString('exportcsv') === 'langvars') {
            $export = new Export();
            $export->export(
                'langvars',
                $langCode . '_' . \date('YmdHis') . '.slf',
                $values,
                ['cSektionName', 'cName', 'cWert', 'bSystem'],
                [],
                ';',
                false
            );
        }

        $pagination = (new Pagination('langvars'))
            ->setRange(4)
            ->setItemArray($values)
            ->assemble();

        $notFound = $this->db->getObjects(
            'SELECT sl.*, ss.kSprachsektion
                FROM tsprachlog AS sl
                LEFT JOIN tsprachsektion AS ss
                    ON ss.cName = sl.cSektion
                WHERE kSprachISO = :lid',
            ['lid' => $langIsoID]
        );

        $this->smarty->assign('oFilter', $filter)
            ->assign('pagination', $pagination)
            ->assign('oWert_arr', $pagination->getPageItems())
            ->assign('bSpracheAktiv', $langActive)
            ->assign('oNotFound_arr', $notFound);
    }

    private function actionSave(): void
    {
        $variable                 = new stdClass();
        $variable->kSprachsektion = (int)$_REQUEST['kSprachsektion'];
        $variable->cName          = $_REQUEST['cName'];
        $variable->cWert_arr      = $_REQUEST['cWert_arr'];
        $variable->cWertAlt_arr   = [];
        $variable->bOverwrite_arr = $_REQUEST['bOverwrite_arr'] ?? [];
        $errors                   = [];
        $variable->cSprachsektion = $this->db
            ->select(
                'tsprachsektion',
                'kSprachsektion',
                $variable->kSprachsektion
            )
            ->cName;

        $_SESSION['newVarSectionName'] = $variable->cSprachsektion;

        $data = $this->db->getObjects(
            'SELECT s.cNameDeutsch AS cSpracheName, sw.cWert, si.cISO
                FROM tsprachwerte AS sw
                    JOIN tsprachiso AS si
                        ON si.kSprachISO = sw.kSprachISO
                    JOIN tsprache AS s
                        ON s.cISO = si.cISO 
                WHERE sw.cName = :cName
                    AND sw.kSprachsektion = :sid',
            ['cName' => $variable->cName, 'sid' => $variable->kSprachsektion]
        );
        foreach ($data as $item) {
            $variable->cWertAlt_arr[$item->cISO] = $item->cWert;
        }
        if (!\preg_match('/(\w+)/', $variable->cName)) {
            $errors[] = \__('errorVarFormat');
        }
        if (\count($variable->bOverwrite_arr) !== \count($data)) {
            $errors[] = \sprintf(
                \__('errorVarExistsForLang'),
                \implode(
                    ', ',
                    \array_map(static function ($item) {
                        return $item->cSpracheName;
                    }, $data)
                )
            );
        }

        if (\count($errors) > 0) {
            $this->alertService->addError(\implode('<br>', $errors), 'newVar');
            $this->step = 'newvar';
            $this->smarty->assign('oVariable', $variable);

            return;
        }
        foreach ($variable->cWert_arr as $cISO => $cWert) {
            if (isset($variable->cWertAlt_arr[$cISO])) {
                // alter Wert vorhanden
                if ((int)$variable->bOverwrite_arr[$cISO] === 1) {
                    // soll ueberschrieben werden
                    $this->helper->setzeSprache($cISO)
                        ->set($variable->kSprachsektion, $variable->cName, $cWert);
                }
            } else {
                // kein alter Wert vorhanden
                $this->helper->fuegeEin($cISO, $variable->kSprachsektion, $variable->cName, $cWert);
            }
        }

        $this->db->delete(
            'tsprachlog',
            ['cSektion', 'cName'],
            [$variable->cSprachsektion, $variable->cName]
        );
        $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
        $this->smarty->assign('oVariable', $variable);
    }

    /**
     * @param string $langCode
     * @return void
     */
    private function actionSaveAll(string $langCode): void
    {
        $modified = [];
        foreach ($_REQUEST['cWert_arr'] as $kSektion => $sectionValues) {
            foreach ($sectionValues as $name => $cWert) {
                if ((int)$_REQUEST['bChanged_arr'][$kSektion][$name] === 1) {
                    $this->helper->setzeSprache($langCode)
                        ->set((int)$kSektion, $name, $cWert);
                    $modified[] = $name;
                }
            }
        }

        $this->cache->flushTags([\CACHING_GROUP_CORE]);
        $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');

        $this->alertService->addSuccess(
            \count($modified) > 0
                ? \__('successVarChange') . \implode(', ', $modified)
                : \__('errorVarChangeNone'),
            'varChangeMessage'
        );
    }

    /**
     * @param string $action
     * @return void
     */
    private function handleAction(string $action): void
    {
        switch ($action) {
            case 'newvar':
                // neue Variable erstellen
                $sectionName              = $_SESSION['newVarSectionName'] ?? 'custom';
                $customSectionId          = $this->db->getSingleInt(
                    'SELECT kSprachsektion FROM tsprachsektion WHERE cName = :sectionName',
                    'kSprachsektion',
                    ['sectionName' => $sectionName]
                );
                $this->step               = 'newvar';
                $variable                 = new stdClass();
                $variable->kSprachsektion = (int)($_REQUEST['kSprachsektion'] ?? $customSectionId);
                $variable->cName          = $_REQUEST['cName'] ?? '';
                $variable->cWert_arr      = [];
                $this->smarty->assign('oVariable', $variable);
                break;
            case 'delvar':
                // Variable loeschen
                $name = Request::getVar('cName');
                $this->helper->loesche(Request::getInt('kSprachsektion'), $name);
                $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
                $this->alertService->addSuccess(\sprintf(\__('successVarRemove'), $name), 'successVarRemove');
                break;
            case 'savevar':
                $this->actionSave();

                break;
            case 'saveall':
                $this->actionSaveAll($this->currentLanguageCode);

                break;
            case 'clearlog':
                $this->helper->setzeSprache($this->currentLanguageCode)
                    ->clearLog();
                $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
                $this->alertService->addSuccess(\__('successListReset'), 'successListReset');
                break;
            default:
                break;
        }
        $this->cache->flushTags([\CACHING_GROUP_LANGUAGE]);
    }

    /**
     * @param string $file
     * @param string $langCode
     * @return void
     */
    private function import(string $file, string $langCode): void
    {
        $res = $this->helper->import($file, $langCode, Request::verifyGPCDataInt('importType'));
        if ($res !== false) {
            $this->alertService->addSuccess(\sprintf(\__('successImport'), $res), 'successImport');
        } else {
            $this->alertService->addError(\__('errorImport'), 'errorImport');
        }
        $this->cache->flushTags([\CACHING_GROUP_CORE, \CACHING_GROUP_LANGUAGE]);
        $this->helper = new LanguageHelper($this->db, $this->cache);
    }
}
