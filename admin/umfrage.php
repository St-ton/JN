<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\Request;
use Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'umfrage_inc.php';

$oAccount->permission('EXTENSION_VOTE_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
$db          = Shop::Container()->getDB();
$cHinweis    = '';
$cFehler     = '';
$step        = 'umfrage_uebersicht';
$kUmfrage    = 0;
$kUmfrageTMP = Request::verifyGPCDataInt('kUmfrage') > 0
    ? Request::verifyGPCDataInt('kUmfrage')
    : Request::verifyGPCDataInt('kU');
setzeSprache();
if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', Request::verifyGPDataString('tab'));
}
$Sprachen    = Sprache::getAllLanguages();
$oSpracheTMP = $db->select('tsprache', 'kSprache', (int)$_SESSION['kSprache']);
$oNice       = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_UMFRAGE)) {
    if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
        $cHinweis .= saveAdminSectionSettings(CONF_UMFRAGE, $_POST);
    }
    if (Request::verifyGPCDataInt('umfrage') === 1 && Form::validateToken()) {
        if (isset($_POST['umfrage_erstellen']) && (int)$_POST['umfrage_erstellen'] === 1) {
            $step = 'umfrage_erstellen';
        } elseif (isset($_GET['umfrage_editieren']) && (int)$_GET['umfrage_editieren'] === 1) {
            $step     = 'umfrage_editieren';
            $kUmfrage = (int)$_GET['kUmfrage'];

            if ($kUmfrage > 0) {
                $survey                    = $db->query(
                    "SELECT *, DATE_FORMAT(dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de, 
                        DATE_FORMAT(dGueltigBis, '%d.%m.%Y %H:%i') AS dGueltigBis_de
                        FROM tumfrage
                        WHERE kUmfrage = " . $kUmfrage,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                $survey->kKundengruppe_arr = StringHandler::parseSSK($survey->cKundengruppe);

                $smarty->assign('oUmfrage', $survey)
                       ->assign('s1', Request::verifyGPCDataInt('s1'));
            } else {
                $cFehler .= __('errorPollNotFound') . '<br />';
                $step     = 'umfrage_uebersicht';
            }
        }
        if (isset($_GET['a']) && $_GET['a'] === 'a_loeschen') {
            $step                 = 'umfrage_frage_bearbeiten';
            $kUmfrageFrage        = (int)$_GET['kUF'];
            $kUmfrageFrageAntwort = (int)$_GET['kUFA'];
            if ($kUmfrageFrageAntwort > 0) {
                $db->query(
                    'DELETE tumfragefrageantwort, tumfragedurchfuehrungantwort
                        FROM tumfragefrageantwort
                        LEFT JOIN tumfragedurchfuehrungantwort
                            ON tumfragedurchfuehrungantwort.kUmfrageFrageAntwort = 
                               tumfragefrageantwort.kUmfrageFrageAntwort
                        WHERE tumfragefrageantwort.kUmfrageFrageAntwort = ' . $kUmfrageFrageAntwort,
                    \DB\ReturnType::AFFECTED_ROWS
                );
            }
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
        } elseif (isset($_GET['a']) && $_GET['a'] === 'o_loeschen') {
            $step                 = 'umfrage_frage_bearbeiten';
            $kUmfrageFrage        = (int)$_GET['kUF'];
            $kUmfrageMatrixOption = (int)$_GET['kUFO'];
            if ($kUmfrageMatrixOption > 0) {
                $db->query(
                    'DELETE tumfragematrixoption, tumfragedurchfuehrungantwort
                        FROM tumfragematrixoption
                        LEFT JOIN tumfragedurchfuehrungantwort
                            ON tumfragedurchfuehrungantwort.kUmfrageMatrixOption = 
                               tumfragematrixoption.kUmfrageMatrixOption
                        WHERE tumfragematrixoption.kUmfrageMatrixOption = ' . $kUmfrageMatrixOption,
                    \DB\ReturnType::AFFECTED_ROWS
                );
            }
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
        }

        // Umfrage speichern
        if (isset($_POST['umfrage_speichern']) && (int)$_POST['umfrage_speichern']) {
            $step = 'umfrage_erstellen';

            if (isset($_POST['umfrage_edit_speichern'], $_POST['kUmfrage'])
                && (int)$_POST['umfrage_edit_speichern'] === 1 && (int)$_POST['kUmfrage'] > 0
            ) {
                $kUmfrage = (int)$_POST['kUmfrage'];
            }
            $cName  = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $kKupon = isset($_POST['kKupon']) ? (int)$_POST['kKupon'] : 0;
            if ($kKupon <= 0 || !isset($kKupon)) {
                $kKupon = 0;
            }
            $cSeo             = $_POST['cSeo'];
            $customerGroupIDs = $_POST['kKundengruppe'];
            $cBeschreibung    = $_POST['cBeschreibung'];
            $fGuthaben        = isset($_POST['fGuthaben']) ?
                (float)str_replace(',', '.', $_POST['fGuthaben'])
                : 0;
            if ($fGuthaben <= 0 || !isset($kKupon)) {
                $fGuthaben = 0;
            }
            $nBonuspunkte = isset($_POST['nBonuspunkte'])
                ? (int)$_POST['nBonuspunkte']
                : 0;
            if ($nBonuspunkte <= 0 || !isset($kKupon)) {
                $nBonuspunkte = 0;
            }
            $nAktiv      = (int)$_POST['nAktiv'];
            $dGueltigVon = $_POST['dGueltigVon'];
            $dGueltigBis = $_POST['dGueltigBis'];

            // Sind die wichtigen Daten vorhanden?
            if (mb_strlen($cName) > 0
                && (is_array($customerGroupIDs) && count($customerGroupIDs) > 0)
                && mb_strlen($dGueltigVon) > 0
            ) {
                if (($kKupon === 0 && $fGuthaben === 0 && $nBonuspunkte === 0)
                    || ($kKupon > 0 && $fGuthaben === 0 && $nBonuspunkte === 0)
                    || ($kKupon === 0 && $fGuthaben > 0 && $nBonuspunkte === 0)
                    || ($kKupon === 0 && $fGuthaben === 0 && $nBonuspunkte > 0)
                ) {
                    $step                  = 'umfrage_frage_erstellen';
                    $survey                = new stdClass();
                    $survey->kSprache      = $_SESSION['kSprache'];
                    $survey->kKupon        = $kKupon;
                    $survey->cName         = $cName;
                    $survey->cKundengruppe = ';' . implode(';', $customerGroupIDs) . ';';
                    $survey->cBeschreibung = $cBeschreibung;
                    $survey->fGuthaben     = $fGuthaben;
                    $survey->nBonuspunkte  = $nBonuspunkte;
                    $survey->nAktiv        = $nAktiv;
                    $survey->dErstellt     = (new DateTime())->format('Y-m-d H:i:s');

                    $validFrom           = DateTime::createFromFormat('d.m.Y H:i', $dGueltigVon);
                    $validFrom           = $validFrom === false ? 'NOW()' : $validFrom->format('Y-m-d H:i:00');
                    $validUntil          = DateTime::createFromFormat('d.m.Y H:i', $dGueltigBis);
                    $validUntil          = $validUntil === false ? '_DBNULL_' : $validUntil->format('Y-m-d H:i:00');
                    $survey->dGueltigVon = $validFrom;
                    $survey->dGueltigBis = $validUntil;

                    $nNewsOld = 0;
                    if (isset($_POST['umfrage_edit_speichern']) && (int)$_POST['umfrage_edit_speichern'] === 1) {
                        $nNewsOld = 1;
                        $step     = 'umfrage_uebersicht';
                        $db->delete('tumfrage', 'kUmfrage', $kUmfrage);
                        $db->delete('tseo', ['cKey', 'kKey'], ['kUmfrage', $kUmfrage]);
                    }
                    $survey->cSeo = \JTL\SeoHelper::checkSeo(
                        \JTL\SeoHelper::getSeo(mb_strlen($cSeo) > 0 ? $cSeo : $cName)
                    );
                    if (isset($kUmfrage) && $kUmfrage > 0) {
                        $survey->kUmfrage = $kUmfrage;
                        $db->insert('tumfrage', $survey);
                    } else {
                        $kUmfrage = $db->insert('tumfrage', $survey);
                    }
                    $db->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kUmfrage', $kUmfrage, (int)$_SESSION['kSprache']]
                    );
                    // SEO tseo eintragen
                    $seo           = new stdClass();
                    $seo->cSeo     = $survey->cSeo;
                    $seo->cKey     = 'kUmfrage';
                    $seo->kKey     = $kUmfrage;
                    $seo->kSprache = $_SESSION['kSprache'];
                    $db->insert('tseo', $seo);

                    $kUmfrageTMP = $kUmfrage;

                    $cHinweis .= __('successPollCreateNextSteps') . '<br />';
                    Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
                } else {
                    $cFehler .= __('errorRewardMissing') . '<br />';
                }
            } else {
                $cFehler .= __('errorDataMissing') . '<br />';
            }
        } elseif (isset($_POST['umfrage_frage_speichern']) && (int)$_POST['umfrage_frage_speichern'] === 1) {
            $kUmfrage                 = (int)$_POST['kUmfrage'];
            $kUmfrageFrage            = isset($_POST['kUmfrageFrage']) ? (int)$_POST['kUmfrageFrage'] : 0;
            $cName                    = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $cTyp                     = $_POST['cTyp'];
            $nSort                    = isset($_POST['nSort']) ? (int)$_POST['nSort'] : 0;
            $cBeschreibung            = $_POST['cBeschreibung'] ?? '';
            $cNameOption              = $_POST['cNameOption'] ?? null;
            $cNameAntwort             = $_POST['cNameAntwort'] ?? null;
            $nFreifeld                = $_POST['nFreifeld'] ?? null;
            $nNotwendig               = $_POST['nNotwendig'] ?? null;
            $kUmfrageFrageAntwort_arr = $_POST['kUmfrageFrageAntwort'] ?? [];
            $kUmfrageMatrixOption_arr = $_POST['kUmfrageMatrixOption'] ?? [];
            $nSortAntwort_arr         = $_POST['nSortAntwort'] ?? 0;
            $nSortOption_arr          = $_POST['nSortOption'] ?? null;

            if (isset($_POST['nocheinefrage'])) {
                $step = 'umfrage_frage_erstellen';
            }

            if ($kUmfrage > 0 && mb_strlen($cName) > 0 && mb_strlen($cTyp) > 0) {
                $question                = new stdClass();
                $question->kUmfrage      = $kUmfrage;
                $question->cTyp          = $cTyp;
                $question->cName         = $cName;
                $question->cBeschreibung = $cBeschreibung;
                $question->nSort         = $nSort;
                $question->nFreifeld     = $nFreifeld;
                $question->nNotwendig    = $nNotwendig;

                $nNewsOld = 0;
                if (isset($_POST['umfrage_frage_edit_speichern'])
                    && (int)$_POST['umfrage_frage_edit_speichern'] === 1
                ) {
                    $nNewsOld      = 1;
                    $step          = 'umfrage_vorschau';
                    $kUmfrageFrage = (int)$_POST['kUmfrageFrage'];
                    if (!pruefeTyp($cTyp, $kUmfrageFrage)) {
                        $cFehler .= __('errorQuestionTypeNotCompatible');
                        $step     = 'umfrage_frage_bearbeiten';
                    }
                    $db->delete('tumfragefrage', 'kUmfrageFrage', $kUmfrageFrage);
                }
                $oAnzahlAUndOVorhanden                   = new stdClass();
                $oAnzahlAUndOVorhanden->nAnzahlAntworten = 0;
                $oAnzahlAUndOVorhanden->nAnzahlOptionen  = 0;

                if ($kUmfrageFrage > 0 && $step !== 'umfrage_frage_bearbeiten') {
                    $question->kUmfrageFrage = $kUmfrageFrage;
                    $db->insert('tumfragefrage', $question);
                    $oAnzahlAUndOVorhanden = updateAntwortUndOption(
                        $kUmfrageFrage,
                        $cTyp,
                        $cNameOption,
                        $cNameAntwort,
                        $nSortAntwort_arr,
                        $nSortOption_arr,
                        $kUmfrageFrageAntwort_arr,
                        $kUmfrageMatrixOption_arr
                    );
                } else {
                    $kUmfrageFrage = $db->insert('tumfragefrage', $question);
                }
                speicherAntwortZuFrage(
                    $kUmfrageFrage,
                    $cTyp,
                    $cNameOption,
                    $cNameAntwort,
                    $nSortAntwort_arr,
                    $nSortOption_arr,
                    $oAnzahlAUndOVorhanden
                );

                $cHinweis .= __('successQuestionSave') . '<br />';
                Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
            } else {
                $step     = 'umfrage_frage_erstellen';
                $cFehler .= __('errorMinNameTypeMissing') . '<br />';
            }
        } elseif (isset($_POST['umfrage_loeschen']) && (int)$_POST['umfrage_loeschen'] === 1) {
            // Umfrage loeschen
            if (is_array($_POST['kUmfrage']) && count($_POST['kUmfrage']) > 0) {
                foreach ($_POST['kUmfrage'] as $kUmfrage) {
                    $kUmfrage = (int)$kUmfrage;
                    // tumfrage loeschen
                    $db->delete('tumfrage', 'kUmfrage', $kUmfrage);

                    $oUmfrageFrage_arr = $db->query(
                        'SELECT kUmfrageFrage
                            FROM tumfragefrage
                            WHERE kUmfrage = ' . $kUmfrage,
                        \DB\ReturnType::ARRAY_OF_OBJECTS
                    );
                    foreach ($oUmfrageFrage_arr as $question) {
                        loescheFrage($question->kUmfrageFrage);
                    }
                    // tseo loeschen
                    $db->delete('tseo', ['cKey', 'kKey'], ['kUmfrage', $kUmfrage]);
                    // Umfrage Durchfuehrung loeschen
                    $db->query(
                        'DELETE tumfragedurchfuehrung, tumfragedurchfuehrungantwort 
                            FROM tumfragedurchfuehrung
                            LEFT JOIN tumfragedurchfuehrungantwort 
                              ON tumfragedurchfuehrungantwort.kUmfrageDurchfuehrung = 
                                 tumfragedurchfuehrung.kUmfrageDurchfuehrung
                            WHERE tumfragedurchfuehrung.kUmfrage = ' . $kUmfrage,
                        \DB\ReturnType::AFFECTED_ROWS
                    );
                }
                $cHinweis .= __('successPollDelete') . '<br />';
                Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
            } else {
                $cFehler .= __('successAtLeastOnePoll') . '<br />';
            }
        } elseif (isset($_POST['umfrage_frage_loeschen']) && (int)$_POST['umfrage_frage_loeschen'] === 1) {
            // Frage loeschen
            $step = 'umfrage_vorschau';
            // Ganze Frage loeschen mit allen Antworten und Matrixen
            if (is_array($_POST['kUmfrageFrage']) && count($_POST['kUmfrageFrage']) > 0) {
                foreach ($_POST['kUmfrageFrage'] as $kUmfrageFrage) {
                    $kUmfrageFrage = (int)$kUmfrageFrage;

                    loescheFrage($kUmfrageFrage);
                }

                $cHinweis = __('successQuestionDelete') . '<br>';
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
                        \DB\ReturnType::AFFECTED_ROWS
                    );
                }
                $cHinweis .= __('successAnswerDelete') . '<br>';
            }
            // Bestimmte Optionen loeschen
            if (isset($_POST['kUmfrageMatrixOption'])
                && is_array($_POST['kUmfrageMatrixOption'])
                && count($_POST['kUmfrageMatrixOption']) > 0
            ) {
                foreach ($_POST['kUmfrageMatrixOption'] as $kUmfrageMatrixOption) {
                    $kUmfrageMatrixOption = (int)$kUmfrageMatrixOption;
                    $db->query(
                        'DELETE tumfragematrixoption, tumfragedurchfuehrungantwort 
                            FROM tumfragematrixoption
                            LEFT JOIN tumfragedurchfuehrungantwort
                                ON tumfragedurchfuehrungantwort.kUmfrageMatrixOption = 
                                   tumfragematrixoption.kUmfrageMatrixOption
                            WHERE tumfragematrixoption.kUmfrageMatrixOption = ' . $kUmfrageMatrixOption,
                        \DB\ReturnType::AFFECTED_ROWS
                    );
                }

                $cHinweis .= __('successOptionDelete') . '<br />';
            }
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
        } elseif (isset($_POST['umfrage_frage_hinzufuegen'])
            && (int)$_POST['umfrage_frage_hinzufuegen'] === 1
        ) {
            $step = 'umfrage_frage_erstellen';
            $smarty->assign('kUmfrageTMP', $kUmfrageTMP);
        } elseif (Request::verifyGPCDataInt('umfrage_statistik') === 1) {
            $oUmfrageDurchfuehrung_arr = $db->query(
                'SELECT kUmfrageDurchfuehrung
                    FROM tumfragedurchfuehrung
                    WHERE kUmfrage = ' . $kUmfrageTMP,
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );

            if (count($oUmfrageDurchfuehrung_arr) > 0) {
                $step = 'umfrage_statistik';
                $smarty->assign('oUmfrageStats', holeUmfrageStatistik($kUmfrageTMP));
            } else {
                $step     = 'umfrage_vorschau';
                $cFehler .= __('errorNoStatistic');
            }
        } elseif (isset($_GET['a']) && $_GET['a'] === 'zeige_sonstige') {
            // Umfragestatistik Sonstige Texte anzeigen
            $step          = 'umfrage_statistik';
            $kUmfrageFrage = (int)$_GET['uf'];
            $nAnzahlAnwort = (int)$_GET['aa'];
            $nMaxAntworten = (int)$_GET['ma'];

            if ($kUmfrageFrage > 0 && $nMaxAntworten > 0) {
                $step = 'umfrage_statistik_sonstige_texte';
                $smarty->assign('oUmfrageFrage', holeSonstigeTextAntworten(
                    $kUmfrageFrage,
                    $nAnzahlAnwort,
                    $nMaxAntworten
                ));
            }
        } elseif ((isset($_GET['fe']) && (int)$_GET['fe'] === 1) ||
            ($step === 'umfrage_frage_bearbeiten' && Form::validateToken())
        ) { // Frage bearbeiten
            $step = 'umfrage_frage_erstellen';

            if (Request::verifyGPCDataInt('kUmfrageFrage') > 0) {
                $kUmfrageFrage = Request::verifyGPCDataInt('kUmfrageFrage');
            } else {
                $kUmfrageFrage = Request::verifyGPCDataInt('kUF');
            }
            $question = $db->select('tumfragefrage', 'kUmfrageFrage', $kUmfrageFrage);
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
                   ->assign('kUmfrageTMP', $kUmfrageTMP);
        }
        // Umfrage Detail
        if ((isset($_GET['ud']) && (int)$_GET['ud'] === 1) || $step === 'umfrage_vorschau') {
            $kUmfrage = Request::verifyGPCDataInt('kUmfrage');
            if ($kUmfrage > 0) {
                $step   = 'umfrage_vorschau';
                $survey = $db->query(
                    "SELECT *, DATE_FORMAT(dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de, 
                        DATE_FORMAT(dGueltigBis, '%d.%m.%Y %H:%i') AS dGueltigBis_de,
                        DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de
                        FROM tumfrage
                        WHERE kUmfrage = " . $kUmfrage,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if ($survey->kUmfrage > 0) {
                    $survey->cKundengruppe_arr = [];
                    foreach (StringHandler::parseSSK($survey->cKundengruppe) as $kKundengruppe) {
                        if ($kKundengruppe == -1) {
                            $survey->cKundengruppe_arr[] = 'Alle';
                        } else {
                            $oKundengruppe = $db->select(
                                'tkundengruppe',
                                'kKundengruppe',
                                (int)$kKundengruppe
                            );
                            if (!empty($oKundengruppe->cName)) {
                                $survey->cKundengruppe_arr[] = $oKundengruppe->cName;
                            }
                        }
                    }
                    $survey->oUmfrageFrage_arr = $db->selectAll(
                        'tumfragefrage',
                        'kUmfrage',
                        $kUmfrage,
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
                $cFehler .= __('errorPollSelect') . '<br>';
            }
        }
        if ($kUmfrageTMP > 0
            && (!isset($_POST['umfrage_frage_edit_speichern']) || (int)$_POST['umfrage_frage_edit_speichern'] !== 1)
            && (!isset($_GET['fe']) || (int)$_GET['fe']) !== 1
        ) {
            $smarty->assign(
                'oUmfrageFrage_arr',
                $db->selectAll(
                    'tumfragefrage',
                    'kUmfrage',
                    $kUmfrageTMP,
                    '*',
                    'nSort'
                )
            )->assign('kUmfrageTMP', $kUmfrageTMP);
        }
    }
    if ($step === 'umfrage_uebersicht') {
        $surveyCount = $db->query(
            'SELECT COUNT(*) AS nAnzahl
                FROM tumfrage
                WHERE kSprache = ' . (int)$_SESSION['kSprache'],
            \DB\ReturnType::SINGLE_OBJECT
        );
        $pagination  = (new Pagination())
            ->setItemCount((int)$surveyCount->nAnzahl)
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
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($surveys as $i => $survey) {
            $survey->cKundengruppe_arr = [];
            foreach (StringHandler::parseSSK($survey->cKundengruppe) as $kKundengruppe) {
                if ($kKundengruppe == -1) {
                    $surveys[$i]->cKundengruppe_arr[] = 'Alle';
                } else {
                    $oKundengruppe = $db->query(
                        'SELECT cName
                            FROM tkundengruppe
                            WHERE kKundengruppe = ' . (int)$kKundengruppe,
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                    if (!empty($oKundengruppe->cName)) {
                        $surveys[$i]->cKundengruppe_arr[] = $oKundengruppe->cName;
                    }
                }
            }
        }

        $smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_UMFRAGE))
               ->assign('oUmfrage_arr', $surveys)
               ->assign('oPagination', $pagination);
    }
    $customerGroups = $db->query(
        'SELECT kKundengruppe, cName
            FROM tkundengruppe
            ORDER BY cStandard DESC',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $coupons        = $db->query(
        "SELECT tkupon.kKupon, tkuponsprache.cName
            FROM tkupon
            LEFT JOIN tkuponsprache 
                ON tkuponsprache.kKupon = tkupon.kKupon
            WHERE tkupon.dGueltigAb <= NOW()
                AND (tkupon.dGueltigBis >= NOW() OR tkupon.dGueltigBis IS NULL)
                AND (tkupon.nVerwendungenBisher <= tkupon.nVerwendungen OR tkupon.nVerwendungen = 0)
                AND tkupon.cAktiv = 'Y'
                AND tkuponsprache.cISOSprache = '" . $oSpracheTMP->cISO . "'
            ORDER BY tkupon.cName",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    $smarty->assign('oKundengruppe_arr', $customerGroups)
           ->assign('oKupon_arr', $coupons);
} else {
    $smarty->assign('noModule', true);
}

$smarty->assign('Sprachen', $Sprachen)
       ->assign('kSprache', $_SESSION['kSprache'])
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('umfrage.tpl');
