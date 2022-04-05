<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\CSV\Export;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Filter;
use JTL\Pagination\Operation;
use JTL\Pagination\Pagination;
use JTL\Smarty\JTLSmarty;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class LanguageController
 * @package JTL\Router\Controller\Backend
 */
class LanguageController extends AbstractBackendController
{
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty,
        Route $route
    ): ResponseInterface {
        $this->smarty = $smarty;
        $this->checkPermissions('LANGUAGE_VIEW');
        $this->getText->loadAdminLocale('pages/sprache');

        $tab  = $_REQUEST['tab'] ?? 'variables';
        $step = 'overview';
        $lang = LanguageHelper::getInstance($this->db, $this->cache);
        $this->setzeSprache();
        $langCode = $_SESSION['editLanguageCode'];
        $variable = null;

        if (isset($_FILES['csvfile']['tmp_name'])
            && Form::validateToken()
            && Request::verifyGPDataString('importcsv') === 'langvars'
        ) {
            $csvFilename = $_FILES['csvfile']['tmp_name'];
            $importType  = Request::verifyGPCDataInt('importType');
            $res         = $lang->import($csvFilename, $langCode, $importType);

            if ($res !== false) {
                $this->alertService->addSuccess(\sprintf(\__('successImport'), $res), 'successImport');
            } else {
                $this->alertService->addError(\__('errorImport'), 'errorImport');
            }
            $this->cache->flushTags([\CACHING_GROUP_CORE, \CACHING_GROUP_LANGUAGE]);
            $lang = new LanguageHelper($this->db, $this->cache);
        }

        $langIsoID          = $lang->getLangIDFromIso($langCode)->kSprachISO ?? 0;
        $installedLanguages = $lang->getInstalled();
        $availableLanguages = $lang->getAvailable();
        $sections           = $lang->getSections();
        $langActive         = false;
        $smarty->assign('availableLanguages', $availableLanguages);

        if (count($installedLanguages) !== count($availableLanguages)) {
            $this->alertService->addNotice(\__('newLangAvailable'), 'newLangAvailable');
        }

        foreach ($installedLanguages as $language) {
            if ($language->getIso() === $langCode) {
                $langActive = true;
                break;
            }
        }
        if (isset($_REQUEST['action']) && Form::validateToken()) {
            switch ($_REQUEST['action']) {
                case 'newvar':
                    // neue Variable erstellen
                    $step                     = 'newvar';
                    $variable                 = new stdClass();
                    $variable->kSprachsektion = (int)($_REQUEST['kSprachsektion'] ?? 1);
                    $variable->cName          = $_REQUEST['cName'] ?? '';
                    $variable->cWert_arr      = [];
                    break;
                case 'delvar':
                    // Variable loeschen
                    $name = Request::getVar('cName');
                    $lang->loesche(Request::getInt('kSprachsektion'), $name);
                    $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
                    $this->alertService->addSuccess(\sprintf(\__('successVarRemove'), $name), 'successVarRemove');
                    break;
                case 'savevar':
                    // neue Variable speichern
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
                            (int)$variable->kSprachsektion
                        )
                        ->cName;

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

                    if (!\preg_match('/([\w\d]+)/', $variable->cName)) {
                        $errors[] = \__('errorVarFormat');
                    }

                    if (count($variable->bOverwrite_arr) !== count($data)) {
                        $errors[] = \sprintf(
                            \__('errorVarExistsForLang'),
                            \implode(
                                ', ',
                                \array_map(static function ($oWertDB) {
                                    return $oWertDB->cSpracheName;
                                }, $data)
                            )
                        );
                    }

                    if (count($errors) > 0) {
                        $this->alertService->addError(\implode('<br>', $errors), 'newVar');
                        $step = 'newvar';
                    } else {
                        foreach ($variable->cWert_arr as $cISO => $cWert) {
                            if (isset($variable->cWertAlt_arr[$cISO])) {
                                // alter Wert vorhanden
                                if ((int)$variable->bOverwrite_arr[$cISO] === 1) {
                                    // soll ueberschrieben werden
                                    $lang
                                        ->setzeSprache($cISO)
                                        ->set($variable->kSprachsektion, $variable->cName, $cWert);
                                }
                            } else {
                                // kein alter Wert vorhanden
                                $lang->fuegeEin($cISO, $variable->kSprachsektion, $variable->cName, $cWert);
                            }
                        }

                        $this->db->delete(
                            'tsprachlog',
                            ['cSektion', 'cName'],
                            [$variable->cSprachsektion, $variable->cName]
                        );
                        $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
                    }

                    break;
                case 'saveall':
                    $modified = [];
                    foreach ($_REQUEST['cWert_arr'] as $kSektion => $sectionValues) {
                        foreach ($sectionValues as $name => $cWert) {
                            if ((int)$_REQUEST['bChanged_arr'][$kSektion][$name] === 1) {
                                $lang->setzeSprache($langCode)
                                    ->set((int)$kSektion, $name, $cWert);
                                $modified[] = $name;
                            }
                        }
                    }

                    $this->cache->flushTags([\CACHING_GROUP_CORE]);
                    $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');

                    $this->alertService->addSuccess(
                        count($modified) > 0
                            ? \__('successVarChange') . \implode(', ', $modified)
                            : \__('errorVarChangeNone'),
                        'varChangeMessage'
                    );

                    break;
                case 'clearlog':
                    $lang->setzeSprache($langCode)
                        ->clearLog();
                    $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
                    $this->alertService->addSuccess(\__('successListReset'), 'successListReset');
                    break;
                default:
                    break;
            }
            $this->cache->flushTags([\CACHING_GROUP_LANGUAGE]);
        }

        if ($step === 'newvar') {
            $smarty->assign('oSektion_arr', $sections)
                ->assign('oVariable', $variable)
                ->assign('oSprache_arr', $availableLanguages);
        } elseif ($step === 'overview') {
            $filter                      = new Filter('langvars');
            $selectField                 = $filter->addSelectfield(\__('section'), 'sw.kSprachsektion');
            $selectField->reloadOnChange = true;
            $selectField->addSelectOption('(' . \__('all') . ')', '');

            foreach ($sections as $oSektion) {
                $selectField->addSelectOption($oSektion->cName, $oSektion->kSprachsektion, Operation::EQUALS);
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

            $smarty->assign('oFilter', $filter)
                ->assign('pagination', $pagination)
                ->assign('oWert_arr', $pagination->getPageItems())
                ->assign('bSpracheAktiv', $langActive)
                ->assign('oSprache_arr', $availableLanguages)
                ->assign('oNotFound_arr', $notFound);
        }

        return $smarty->assign('tab', $tab)
            ->assign('step', $step)
            ->assign('route', $route->getPath())
            ->getResponse('sprache.tpl');
    }
}
