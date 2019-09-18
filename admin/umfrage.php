<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Seo;
use JTL\Helpers\Text;
use JTL\Nice;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'umfrage_inc.php';

$oAccount->permission('EXTENSION_VOTE_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$db          = Shop::Container()->getDB();
$alertHelper = Shop::Container()->getAlertService();
$step        = 'umfrage_uebersicht';
$surveyID    = 0;
$tmpID       = Request::verifyGPCDataInt('kUmfrage') > 0
    ? Request::verifyGPCDataInt('kUmfrage')
    : Request::verifyGPCDataInt('kU');
setzeSprache();
if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', Request::verifyGPDataString('tab'));
}
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_UMFRAGE)) {
    if (Request::postInt('einstellungen') > 0) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, saveAdminSectionSettings(CONF_UMFRAGE, $_POST), 'saveSettings');
    }
    if (Request::verifyGPCDataInt('umfrage') === 1 && Form::validateToken()) {
        if (Request::postInt('umfrage_erstellen') === 1) {
            $step = 'umfrage_erstellen';
        } elseif (Request::getInt('umfrage_editieren') === 1) {
            $step     = 'umfrage_editieren';
            $surveyID = Request::getInt('kUmfrage');
            if ($surveyID > 0) {
                $survey                    = $db->query(
                    "SELECT *, DATE_FORMAT(dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de, 
                        DATE_FORMAT(dGueltigBis, '%d.%m.%Y %H:%i') AS dGueltigBis_de
                        FROM tumfrage
                        WHERE kUmfrage = " . $surveyID,
                    ReturnType::SINGLE_OBJECT
                );
                $survey->kKundengruppe_arr = Text::parseSSK($survey->cKundengruppe);

                $smarty->assign('oUmfrage', $survey)
                    ->assign('s1', Request::verifyGPCDataInt('s1'));
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorPollNotFound'), 'errorPollNotFound');
                $step = 'umfrage_uebersicht';
            }
        }
        if (Request::getVar('a') === 'a_loeschen') {
            $step                 = 'umfrage_frage_bearbeiten';
            $questionID           = Request::getInt('kUF');
            $kUmfrageFrageAntwort = Request::getInt('kUFA');
            if ($kUmfrageFrageAntwort > 0) {
                $db->query(
                    'DELETE tumfragefrageantwort, tumfragedurchfuehrungantwort
                        FROM tumfragefrageantwort
                        LEFT JOIN tumfragedurchfuehrungantwort
                            ON tumfragedurchfuehrungantwort.kUmfrageFrageAntwort = 
                               tumfragefrageantwort.kUmfrageFrageAntwort
                        WHERE tumfragefrageantwort.kUmfrageFrageAntwort = ' . $kUmfrageFrageAntwort,
                    ReturnType::AFFECTED_ROWS
                );
            }
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
        } elseif (Request::getVar('a') === 'o_loeschen') {
            $step                 = 'umfrage_frage_bearbeiten';
            $questionID           = Request::getInt('kUF');
            $kUmfrageMatrixOption = Request::getInt('kUFO');
            if ($kUmfrageMatrixOption > 0) {
                $db->query(
                    'DELETE tumfragematrixoption, tumfragedurchfuehrungantwort
                        FROM tumfragematrixoption
                        LEFT JOIN tumfragedurchfuehrungantwort
                            ON tumfragedurchfuehrungantwort.kUmfrageMatrixOption = 
                               tumfragematrixoption.kUmfrageMatrixOption
                        WHERE tumfragematrixoption.kUmfrageMatrixOption = ' . $kUmfrageMatrixOption,
                    ReturnType::AFFECTED_ROWS
                );
            }
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
        }

        // Umfrage speichern
        if (Request::postInt('umfrage_speichern') > 0) {
            $step = 'umfrage_erstellen';
            if (Request::postInt('umfrage_edit_speichern') === 1 && Request::postInt('kUmfrage') > 0) {
                $surveyID = Request::postInt('kUmfrage');
            }
            $name     = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $couponID = Request::postInt('kKupon');
            if ($couponID <= 0 || !isset($couponID)) {
                $couponID = 0;
            }
            $cSeo             = $_POST['cSeo'];
            $customerGroupIDs = $_POST['kKundengruppe'];
            $description      = $_POST['cBeschreibung'];
            $fGuthaben        = isset($_POST['fGuthaben']) ?
                (float)str_replace(',', '.', $_POST['fGuthaben'])
                : 0;
            if ($fGuthaben <= 0 || !isset($couponID)) {
                $fGuthaben = 0;
            }
            $nBonuspunkte = Request::postInt('nBonuspunkte');
            if ($nBonuspunkte <= 0 || !isset($couponID)) {
                $nBonuspunkte = 0;
            }
            $active      = Request::postInt('nAktiv');
            $dGueltigVon = $_POST['dGueltigVon'];
            $dGueltigBis = $_POST['dGueltigBis'];

            // Sind die wichtigen Daten vorhanden?
            if (mb_strlen($name) > 0
                && (is_array($customerGroupIDs) && count($customerGroupIDs) > 0)
                && mb_strlen($dGueltigVon) > 0
            ) {
                if (($couponID === 0 && $fGuthaben === 0 && $nBonuspunkte === 0)
                    || ($couponID > 0 && $fGuthaben === 0 && $nBonuspunkte === 0)
                    || ($couponID === 0 && $fGuthaben > 0 && $nBonuspunkte === 0)
                    || ($couponID === 0 && $fGuthaben === 0 && $nBonuspunkte > 0)
                ) {
                    $step                  = 'umfrage_frage_erstellen';
                    $survey                = new stdClass();
                    $survey->kSprache      = $_SESSION['kSprache'];
                    $survey->kKupon        = $couponID;
                    $survey->cName         = $name;
                    $survey->cKundengruppe = ';' . implode(';', $customerGroupIDs) . ';';
                    $survey->cBeschreibung = $description;
                    $survey->fGuthaben     = $fGuthaben;
                    $survey->nBonuspunkte  = $nBonuspunkte;
                    $survey->nAktiv        = $active;
                    $survey->dErstellt     = (new DateTime())->format('Y-m-d H:i:s');

                    $validFrom           = DateTime::createFromFormat('d.m.Y H:i', $dGueltigVon);
                    $validFrom           = $validFrom === false ? 'NOW()' : $validFrom->format('Y-m-d H:i:00');
                    $validUntil          = DateTime::createFromFormat('d.m.Y H:i', $dGueltigBis);
                    $validUntil          = $validUntil === false ? '_DBNULL_' : $validUntil->format('Y-m-d H:i:00');
                    $survey->dGueltigVon = $validFrom;
                    $survey->dGueltigBis = $validUntil;

                    $nNewsOld = 0;
                    if (Request::postInt('umfrage_edit_speichern') === 1) {
                        $nNewsOld = 1;
                        $step     = 'umfrage_uebersicht';
                        $db->delete('tumfrage', 'kUmfrage', $surveyID);
                        $db->delete('tseo', ['cKey', 'kKey'], ['kUmfrage', $surveyID]);
                    }
                    $survey->cSeo = Seo::checkSeo(Seo::getSeo(mb_strlen($cSeo) > 0 ? $cSeo : $name));
                    if (isset($surveyID) && $surveyID > 0) {
                        $survey->kUmfrage = $surveyID;
                        $db->insert('tumfrage', $survey);
                    } else {
                        $surveyID = $db->insert('tumfrage', $survey);
                    }
                    $db->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kUmfrage', $surveyID, (int)$_SESSION['kSprache']]
                    );
                    // SEO tseo eintragen
                    $seo           = new stdClass();
                    $seo->cSeo     = $survey->cSeo;
                    $seo->cKey     = 'kUmfrage';
                    $seo->kKey     = $surveyID;
                    $seo->kSprache = $_SESSION['kSprache'];
                    $db->insert('tseo', $seo);

                    $tmpID = $surveyID;

                    $alertHelper->addAlert(
                        Alert::TYPE_SUCCESS,
                        __('successPollCreateNextSteps'),
                        'successPollCreateNextSteps'
                    );
                    Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
                } else {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorRewardMissing'), 'errorRewardMissing');
                }
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorDataMissing'), 'errorDataMissing');
            }
        } elseif (Request::postInt('umfrage_frage_speichern') === 1) {
            $surveyID      = Request::postInt('kUmfrage');
            $questionID    = Request::postInt('kUmfrageFrage');
            $name          = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $type          = $_POST['cTyp'];
            $sort          = Request::postInt('nSort');
            $description   = $_POST['cBeschreibung'] ?? '';
            $nameOption    = $_POST['cNameOption'] ?? null;
            $nameAnswer    = $_POST['cNameAntwort'] ?? null;
            $free          = $_POST['nFreifeld'] ?? null;
            $required      = $_POST['nNotwendig'] ?? null;
            $answerIDs     = $_POST['kUmfrageFrageAntwort'] ?? [];
            $matrixOptions = $_POST['kUmfrageMatrixOption'] ?? [];
            $sortAnwers    = $_POST['nSortAntwort'] ?? 0;
            $sortOptions   = $_POST['nSortOption'] ?? null;

            if (isset($_POST['nocheinefrage'])) {
                $step = 'umfrage_frage_erstellen';
            }

            if ($surveyID > 0 && mb_strlen($name) > 0 && mb_strlen($type) > 0) {
                $question                = new stdClass();
                $question->kUmfrage      = $surveyID;
                $question->cTyp          = $type;
                $question->cName         = $name;
                $question->cBeschreibung = $description;
                $question->nSort         = $sort;
                $question->nFreifeld     = $free;
                $question->nNotwendig    = $required;

                $nNewsOld = 0;
                if (Request::postInt('umfrage_frage_edit_speichern') === 1) {
                    $nNewsOld   = 1;
                    $step       = 'umfrage_vorschau';
                    $questionID = Request::postInt('kUmfrageFrage');
                    if (!pruefeTyp($type, $questionID)) {
                        $alertHelper->addAlert(
                            Alert::TYPE_ERROR,
                            __('errorQuestionTypeNotCompatible'),
                            'errorQuestionTypeNotCompatible'
                        );
                        $step = 'umfrage_frage_bearbeiten';
                    }
                    $db->delete('tumfragefrage', 'kUmfrageFrage', $questionID);
                }
                $oAnzahlAUndOVorhanden                   = new stdClass();
                $oAnzahlAUndOVorhanden->nAnzahlAntworten = 0;
                $oAnzahlAUndOVorhanden->nAnzahlOptionen  = 0;

                if ($questionID > 0 && $step !== 'umfrage_frage_bearbeiten') {
                    $question->kUmfrageFrage = $questionID;
                    $db->insert('tumfragefrage', $question);
                    $oAnzahlAUndOVorhanden = updateAntwortUndOption(
                        $questionID,
                        $type,
                        $nameOption,
                        $nameAnswer,
                        $sortAnwers,
                        $sortOptions,
                        $answerIDs,
                        $matrixOptions
                    );
                } else {
                    $questionID = $db->insert('tumfragefrage', $question);
                }
                speicherAntwortZuFrage(
                    $questionID,
                    $type,
                    $nameOption,
                    $nameAnswer,
                    $sortAnwers,
                    $sortOptions,
                    $oAnzahlAUndOVorhanden
                );

                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successQuestionSave'), 'successQuestionSave');
                Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
            } else {
                $step = 'umfrage_frage_erstellen';
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorMinNameTypeMissing'), 'errorMinNameTypeMissing');
            }
        } elseif (Request::postInt('umfrage_loeschen') === 1) {
            // Umfrage loeschen
            $surveyIDs = Request::verifyGPDataIntegerArray('kUmfrage');
            if (count($surveyIDs) > 0) {
                foreach ($surveyIDs as $surveyID) {
                    $db->delete('tumfrage', 'kUmfrage', $surveyID);
                    $surveyQuestions = $db->query(
                        'SELECT kUmfrageFrage
                            FROM tumfragefrage
                            WHERE kUmfrage = ' . $surveyID,
                        ReturnType::ARRAY_OF_OBJECTS
                    );
                    foreach ($surveyQuestions as $question) {
                        loescheFrage((int)$question->kUmfrageFrage);
                    }
                    // tseo loeschen
                    $db->delete('tseo', ['cKey', 'kKey'], ['kUmfrage', $surveyID]);
                    // Umfrage Durchfuehrung loeschen
                    $db->query(
                        'DELETE tumfragedurchfuehrung, tumfragedurchfuehrungantwort 
                            FROM tumfragedurchfuehrung
                            LEFT JOIN tumfragedurchfuehrungantwort 
                              ON tumfragedurchfuehrungantwort.kUmfrageDurchfuehrung = 
                                 tumfragedurchfuehrung.kUmfrageDurchfuehrung
                            WHERE tumfragedurchfuehrung.kUmfrage = ' . $surveyID,
                        ReturnType::AFFECTED_ROWS
                    );
                }
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successPollDelete'), 'successPollDelete');
                Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('successAtLeastOnePoll'), 'successAtLeastOnePoll');
            }
        } elseif (Request::postInt('umfrage_frage_loeschen') === 1) {
            // Frage loeschen
            $step = 'umfrage_vorschau';
            // Ganze Frage loeschen mit allen Antworten und Matrixen
            if (is_array($_POST['kUmfrageFrage']) && count($_POST['kUmfrageFrage']) > 0) {
                foreach ($_POST['kUmfrageFrage'] as $questionID) {
                    $questionID = (int)$questionID;
                    loescheFrage($questionID);
                }

                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successQuestionDelete'), 'successQuestionDelete');
            }
            // Bestimmte Antworten loeschen
            if (isset($_POST['kUmfrageFrageAntwort'])
                && is_array($_POST['kUmfrageFrageAntwort'])
                && count($_POST['kUmfrageFrageAntwort']) > 0
            ) {
                foreach ($_POST['kUmfrageFrageAntwort'] as $kUmfrageFrageAntwort) {
                    $kUmfrageFrageAntwort = (int)$kUmfrageFrageAntwort;

                    $db->query(
                        'DELETE tumfragefrageantwort, tumfragedurchfuehrungantwort 
                            FROM tumfragefrageantwort
                            LEFT JOIN tumfragedurchfuehrungantwort
                                ON tumfragedurchfuehrungantwort.kUmfrageFrageAntwort = 
                                   tumfragefrageantwort.kUmfrageFrageAntwort
                            WHERE tumfragefrageantwort.kUmfrageFrageAntwort = ' . $kUmfrageFrageAntwort,
                        ReturnType::AFFECTED_ROWS
                    );
                }
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successAnswerDelete'), 'successAnswerDelete');
            }
            // Bestimmte Optionen loeschen
            if (GeneralObject::hasCount('kUmfrageMatrixOption', $_POST)) {
                foreach ($_POST['kUmfrageMatrixOption'] as $kUmfrageMatrixOption) {
                    $kUmfrageMatrixOption = (int)$kUmfrageMatrixOption;
                    $db->query(
                        'DELETE tumfragematrixoption, tumfragedurchfuehrungantwort 
                            FROM tumfragematrixoption
                            LEFT JOIN tumfragedurchfuehrungantwort
                                ON tumfragedurchfuehrungantwort.kUmfrageMatrixOption = 
                                   tumfragematrixoption.kUmfrageMatrixOption
                            WHERE tumfragematrixoption.kUmfrageMatrixOption = ' . $kUmfrageMatrixOption,
                        ReturnType::AFFECTED_ROWS
                    );
                }

                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successOptionDelete'), 'successOptionDelete');
            }
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
        } elseif (Request::postInt('umfrage_frage_hinzufuegen') === 1) {
            $step = 'umfrage_frage_erstellen';
            $smarty->assign('kUmfrageTMP', $tmpID);
        } elseif (Request::verifyGPCDataInt('umfrage_statistik') === 1) {
            $conducts = $db->query(
                'SELECT kUmfrageDurchfuehrung
                    FROM tumfragedurchfuehrung
                    WHERE kUmfrage = ' . $tmpID,
                ReturnType::ARRAY_OF_OBJECTS
            );

            if (count($conducts) > 0) {
                $step = 'umfrage_statistik';
                $smarty->assign('oUmfrageStats', holeUmfrageStatistik($tmpID));
            } else {
                $step = 'umfrage_vorschau';
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorNoStatistic'), 'errorNoStatistic');
            }
        } elseif (Request::getVar('a') === 'zeige_sonstige') {
            // Umfragestatistik Sonstige Texte anzeigen
            $step       = 'umfrage_statistik';
            $questionID = Request::getInt('uf');
            $maxAnswers = Request::getInt('aa');
            $limit      = Request::getInt('ma');
            if ($questionID > 0 && $limit > 0) {
                $step = 'umfrage_statistik_sonstige_texte';
                $smarty->assign('oUmfrageFrage', holeSonstigeTextAntworten(
                    $questionID,
                    $maxAnswers,
                    $limit
                ));
            }
        } elseif (Request::getInt('fe') === 1 || ($step === 'umfrage_frage_bearbeiten' && Form::validateToken())) {
            // Frage bearbeiten
            $step = 'umfrage_frage_erstellen';
            if (Request::verifyGPCDataInt('kUmfrageFrage') > 0) {
                $questionID = Request::verifyGPCDataInt('kUmfrageFrage');
            } else {
                $questionID = Request::verifyGPCDataInt('kUF');
            }
            $question = $db->select('tumfragefrage', 'kUmfrageFrage', $questionID);
            if (isset($question->kUmfrageFrage) && $question->kUmfrageFrage > 0) {
                $question->oUmfrageFrageAntwort_arr = $db->selectAll(
                    'tumfragefrageantwort',
                    'kUmfrageFrage',
                    (int)$question->kUmfrageFrage,
                    '*',
                    'nSort'
                );
                $question->oUmfrageMatrixOption_arr = $db->selectAll(
                    'tumfragematrixoption',
                    'kUmfrageFrage',
                    (int)$question->kUmfrageFrage,
                    '*',
                    'nSort'
                );
            }

            $smarty->assign('oUmfrageFrage', $question)
                ->assign('kUmfrageTMP', $tmpID);
        }
        // Umfrage Detail
        if (Request::getInt('ud') === 1 || $step === 'umfrage_vorschau') {
            $surveyID = Request::verifyGPCDataInt('kUmfrage');
            if ($surveyID > 0) {
                $step   = 'umfrage_vorschau';
                $survey = $db->query(
                    "SELECT *, DATE_FORMAT(dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de, 
                        DATE_FORMAT(dGueltigBis, '%d.%m.%Y %H:%i') AS dGueltigBis_de,
                        DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de
                        FROM tumfrage
                        WHERE kUmfrage = " . $surveyID,
                    ReturnType::SINGLE_OBJECT
                );
                if ($survey->kUmfrage > 0) {
                    $survey->cKundengruppe_arr = [];
                    foreach (Text::parseSSKint($survey->cKundengruppe) as $customerGroupID) {
                        if ($customerGroupID === -1) {
                            $survey->cKundengruppe_arr[] = 'Alle';
                        } else {
                            $customerGroup = $db->select(
                                'tkundengruppe',
                                'kKundengruppe',
                                $customerGroupID
                            );
                            if (!empty($customerGroup->cName)) {
                                $survey->cKundengruppe_arr[] = $customerGroup->cName;
                            }
                        }
                    }
                    $survey->oUmfrageFrage_arr = $db->selectAll(
                        'tumfragefrage',
                        'kUmfrage',
                        $surveyID,
                        '*',
                        'nSort'
                    );
                    foreach ($survey->oUmfrageFrage_arr as $i => $question) {
                        $question->cTypMapped               = mappeFragenTyp($question->cTyp);
                        $question->oUmfrageFrageAntwort_arr = $db->selectAll(
                            'tumfragefrageantwort',
                            'kUmfrageFrage',
                            (int)$question->kUmfrageFrage,
                            'kUmfrageFrageAntwort, kUmfrageFrage, cName',
                            'nSort'
                        );
                        $question->oUmfrageMatrixOption_arr = $db->selectAll(
                            'tumfragematrixoption',
                            'kUmfrageFrage',
                            (int)$question->kUmfrageFrage,
                            'kUmfrageMatrixOption, kUmfrageFrage, cName',
                            'nSort'
                        );
                    }
                    $smarty->assign('oUmfrage', $survey);
                }
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorPollSelect'), 'errorPollSelect');
            }
        }
        if ($tmpID > 0 && Request::getInt('fe') !== 1 && Request::postInt('umfrage_frage_edit_speichern') !== 1) {
            $smarty->assign(
                'oUmfrageFrage_arr',
                $db->selectAll(
                    'tumfragefrage',
                    'kUmfrage',
                    $tmpID,
                    '*',
                    'nSort'
                )
            )->assign('kUmfrageTMP', $tmpID);
        }
    }
    if ($step === 'umfrage_uebersicht') {
        $surveyCount = (int)$db->query(
            'SELECT COUNT(*) AS cnt
                FROM tumfrage
                WHERE kSprache = ' . (int)$_SESSION['kSprache'],
            ReturnType::SINGLE_OBJECT
        )->cnt;
        $pagination  = (new Pagination())
            ->setItemCount($surveyCount)
            ->assemble();
        $surveys     = $db->query(
            "SELECT tumfrage.*, DATE_FORMAT(tumfrage.dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de, 
                DATE_FORMAT(tumfrage.dGueltigBis, '%d.%m.%Y %H:%i') AS dGueltigBis_de,
                DATE_FORMAT(tumfrage.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de, 
                COUNT(tumfragefrage.kUmfrageFrage) AS nAnzahlFragen
                FROM tumfrage
                LEFT JOIN tumfragefrage 
                    ON tumfragefrage.kUmfrage = tumfrage.kUmfrage
                WHERE kSprache = " . (int)$_SESSION['kSprache'] . '
                GROUP BY tumfrage.kUmfrage
                ORDER BY dGueltigVon DESC
                LIMIT ' . $pagination->getLimitSQL(),
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($surveys as $i => $survey) {
            $survey->cKundengruppe_arr = [];
            foreach (Text::parseSSKint($survey->cKundengruppe) as $customerGroupID) {
                if ($customerGroupID === -1) {
                    $surveys[$i]->cKundengruppe_arr[] = 'Alle';
                } else {
                    $customerGroup = $db->queryPrepared(
                        'SELECT cName
                            FROM tkundengruppe
                            WHERE kKundengruppe = :cgid',
                        ['cgid' => $customerGroupID],
                        ReturnType::SINGLE_OBJECT
                    );
                    if (!empty($customerGroup->cName)) {
                        $surveys[$i]->cKundengruppe_arr[] = $customerGroup->cName;
                    }
                }
            }
        }

        $smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_UMFRAGE))
            ->assign('oUmfrage_arr', $surveys)
            ->assign('pagination', $pagination);
    }
    $langData       = $db->select('tsprache', 'kSprache', (int)$_SESSION['kSprache']);
    $coupons        = $db->queryPrepared(
        "SELECT tkupon.kKupon, tkuponsprache.cName
            FROM tkupon
            LEFT JOIN tkuponsprache 
                ON tkuponsprache.kKupon = tkupon.kKupon
            WHERE tkupon.dGueltigAb <= NOW()
                AND (tkupon.dGueltigBis >= NOW() OR tkupon.dGueltigBis IS NULL)
                AND (tkupon.nVerwendungenBisher <= tkupon.nVerwendungen OR tkupon.nVerwendungen = 0)
                AND tkupon.cAktiv = 'Y'
                AND tkuponsprache.cISOSprache = :ccode
            ORDER BY tkupon.cName",
        ['ccode' => $langData->cISO],
        ReturnType::ARRAY_OF_OBJECTS
    );

    $smarty->assign('customerGroups', CustomerGroup::getGroups())
        ->assign('oKupon_arr', $coupons);
} else {
    $smarty->assign('noModule', true);
}

$smarty->assign('kSprache', $_SESSION['kSprache'])
    ->assign('step', $step)
    ->display('umfrage.tpl');
