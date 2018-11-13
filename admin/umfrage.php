<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'umfrage_inc.php';

$oAccount->permission('EXTENSION_VOTE_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
$Einstellungen = Shop::getSettings([CONF_UMFRAGE]);
$db            = Shop::Container()->getDB();
$cHinweis      = '';
$cFehler       = '';
$step          = 'umfrage_uebersicht';
$kUmfrage      = 0;
$kUmfrageTMP   = RequestHelper::verifyGPCDataInt('kUmfrage') > 0
    ? RequestHelper::verifyGPCDataInt('kUmfrage')
    : RequestHelper::verifyGPCDataInt('kU');
setzeSprache();
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', RequestHelper::verifyGPDataString('tab'));
}
$Sprachen    = Sprache::getAllLanguages();
$oSpracheTMP = $db->select('tsprache', 'kSprache', (int)$_SESSION['kSprache']);
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_UMFRAGE)) {
    if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
        $cHinweis .= saveAdminSectionSettings(CONF_UMFRAGE, $_POST);
    }
    if (RequestHelper::verifyGPCDataInt('umfrage') === 1 && FormHelper::validateToken()) {
        if (isset($_POST['umfrage_erstellen']) && (int)$_POST['umfrage_erstellen'] === 1) {
            $step = 'umfrage_erstellen';
        } elseif (isset($_GET['umfrage_editieren']) && (int)$_GET['umfrage_editieren'] === 1) {
            $step     = 'umfrage_editieren';
            $kUmfrage = (int)$_GET['kUmfrage'];

            if ($kUmfrage > 0) {
                $oUmfrage = $db->query(
                    "SELECT *, DATE_FORMAT(dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de, 
                        DATE_FORMAT(dGueltigBis, '%d.%m.%Y %H:%i') AS dGueltigBis_de
                        FROM tumfrage
                        WHERE kUmfrage = " . $kUmfrage,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                $oUmfrage->kKundengruppe_arr = StringHandler::parseSSK($oUmfrage->cKundengruppe);

                $smarty->assign('oUmfrage', $oUmfrage)
                       ->assign('s1', RequestHelper::verifyGPCDataInt('s1'));
            } else {
                $cFehler .= 'Fehler: Ihre Umfrage konnte nicht gefunden werden.<br />';
                $step = 'umfrage_uebersicht';
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
            $cSeo              = $_POST['cSeo'];
            $kKundengruppe_arr = $_POST['kKundengruppe'];
            $cBeschreibung     = $_POST['cBeschreibung'];
            $fGuthaben         = isset($_POST['fGuthaben']) ?
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
            if (strlen($cName) > 0
                && (is_array($kKundengruppe_arr) && count($kKundengruppe_arr) > 0)
                && strlen($dGueltigVon) > 0
            ) {
                if (($kKupon === 0 && $fGuthaben === 0 && $nBonuspunkte === 0)
                    || ($kKupon > 0 && $fGuthaben === 0 && $nBonuspunkte === 0)
                    || ($kKupon === 0 && $fGuthaben > 0 && $nBonuspunkte === 0)
                    || ($kKupon === 0 && $fGuthaben === 0 && $nBonuspunkte > 0)
                ) {
                    $step                    = 'umfrage_frage_erstellen';
                    $oUmfrage                = new stdClass();
                    $oUmfrage->kSprache      = $_SESSION['kSprache'];
                    $oUmfrage->kKupon        = $kKupon;
                    $oUmfrage->cName         = $cName;
                    $oUmfrage->cKundengruppe = ';' . implode(';', $kKundengruppe_arr) . ';';
                    $oUmfrage->cBeschreibung = $cBeschreibung;
                    $oUmfrage->fGuthaben     = $fGuthaben;
                    $oUmfrage->nBonuspunkte  = $nBonuspunkte;
                    $oUmfrage->nAktiv        = $nAktiv;
                    $oUmfrage->dErstellt     = (new DateTime())->format('Y-m-d H:i:s');

                    $validFrom             = DateTime::createFromFormat('d.m.Y H:i', $dGueltigVon);
                    $validFrom             = $validFrom === false ? 'NOW()' : $validFrom->format('Y-m-d H:i:00');
                    $validUntil            = DateTime::createFromFormat('d.m.Y H:i', $dGueltigBis);
                    $validUntil            = $validUntil === false ? '_DBNULL_' : $validUntil->format('Y-m-d H:i:00');
                    $oUmfrage->dGueltigVon = $validFrom;
                    $oUmfrage->dGueltigBis = $validUntil;

                    $nNewsOld = 0;
                    if (isset($_POST['umfrage_edit_speichern']) && (int)$_POST['umfrage_edit_speichern'] === 1) {
                        $nNewsOld = 1;
                        $step     = 'umfrage_uebersicht';
                        $db->delete('tumfrage', 'kUmfrage', $kUmfrage);
                        $db->delete('tseo', ['cKey', 'kKey'], ['kUmfrage', $kUmfrage]);
                    }
                    $oUmfrage->cSeo = \JTL\SeoHelper::checkSeo(\JTL\SeoHelper::getSeo(strlen($cSeo) > 0 ? $cSeo : $cName));
                    if (isset($kUmfrage) && $kUmfrage > 0) {
                        $oUmfrage->kUmfrage = $kUmfrage;
                        $db->insert('tumfrage', $oUmfrage);
                    } else {
                        $kUmfrage = $db->insert('tumfrage', $oUmfrage);
                    }
                    $db->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kUmfrage', $kUmfrage, (int)$_SESSION['kSprache']]
                    );
                    // SEO tseo eintragen
                    $oSeo           = new stdClass();
                    $oSeo->cSeo     = $oUmfrage->cSeo;
                    $oSeo->cKey     = 'kUmfrage';
                    $oSeo->kKey     = $kUmfrage;
                    $oSeo->kSprache = $_SESSION['kSprache'];
                    $db->insert('tseo', $oSeo);

                    $kUmfrageTMP = $kUmfrage;

                    $cHinweis .= 'Ihre Umfrage wurde erfolgreich gespeichert. ' .
                        'Bitte folgen Sie nun den weiteren Schritten.<br />';
                    Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
                } else {
                    $cFehler .= 'Fehler: Bitte geben Sie nur eine Belohnungsart an.<br />';
                }
            } else {
                $cFehler .= 'Fehler: Bitte geben Sie einen Namen, mindestens eine Kundengruppe ' .
                    'und ein gültiges Anfangsdatum ein.<br />';
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

            if ($kUmfrage > 0 && strlen($cName) > 0 && strlen($cTyp) > 0) {
                unset($oUmfrageFrage);
                $oUmfrageFrage                = new stdClass();
                $oUmfrageFrage->kUmfrage      = $kUmfrage;
                $oUmfrageFrage->cTyp          = $cTyp;
                $oUmfrageFrage->cName         = $cName;
                $oUmfrageFrage->cBeschreibung = $cBeschreibung;
                $oUmfrageFrage->nSort         = $nSort;
                $oUmfrageFrage->nFreifeld     = $nFreifeld;
                $oUmfrageFrage->nNotwendig    = $nNotwendig;

                $nNewsOld = 0;
                if (isset($_POST['umfrage_frage_edit_speichern'])
                    && (int)$_POST['umfrage_frage_edit_speichern'] === 1
                ) {
                    $nNewsOld      = 1;
                    $step          = 'umfrage_vorschau';
                    $kUmfrageFrage = (int)$_POST['kUmfrageFrage'];
                    if (!pruefeTyp($cTyp, $kUmfrageFrage)) {
                        $cFehler .= 'Fehler: Ihr Fragentyp ist leider nicht kompatibel mit dem voherigen. ' .
                            'Um den Fragetyp zu ändern, resetten Sie bitte die Frage.';
                        $step = 'umfrage_frage_bearbeiten';
                    }
                    $db->delete('tumfragefrage', 'kUmfrageFrage', $kUmfrageFrage);
                }
                $oAnzahlAUndOVorhanden                   = new stdClass();
                $oAnzahlAUndOVorhanden->nAnzahlAntworten = 0;
                $oAnzahlAUndOVorhanden->nAnzahlOptionen  = 0;

                if ($kUmfrageFrage > 0 && $step !== 'umfrage_frage_bearbeiten') {
                    $oUmfrageFrage->kUmfrageFrage = $kUmfrageFrage;
                    $db->insert('tumfragefrage', $oUmfrageFrage);
                    // Update vorhandene Antworten bzw. Optionen
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
                    $kUmfrageFrage = $db->insert('tumfragefrage', $oUmfrageFrage);
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

                $cHinweis .= 'Ihr Frage wurde erfolgreich gespeichert.<br />';
                Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
            } else {
                $step = 'umfrage_frage_erstellen';
                $cFehler .= 'Fehler: Bitte tragen Sie mindestens einen Namen und einen Typ ein.<br />';
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
                    foreach ($oUmfrageFrage_arr as $oUmfrageFrage) {
                        loescheFrage($oUmfrageFrage->kUmfrageFrage);
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
                $cHinweis .= 'Ihre markierten Umfragen wurden erfolgreich gelöscht.<br />';
                Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine Umfrage.<br />';
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

                $cHinweis = 'Ihre markierten Fragen wurden erfolgreich gelöscht.<br>';
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
                $cHinweis .= 'Ihre markierten Antworten wurden erfolgreich gelöscht.<br>';
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

                $cHinweis .= 'Ihre markierten Optionen wurden erfolgreich gelöscht.<br />';
            }
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
        } elseif (isset($_POST['umfrage_frage_hinzufuegen'])
            && (int)$_POST['umfrage_frage_hinzufuegen'] === 1
        ) { // Frage hinzufuegen
            $step = 'umfrage_frage_erstellen';
            $smarty->assign('kUmfrageTMP', $kUmfrageTMP);
        } elseif (RequestHelper::verifyGPCDataInt('umfrage_statistik') === 1) {
            // Umfragestatistik anschauen
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
                $step = 'umfrage_vorschau';
                $cFehler .= 'Fehler: Für diese Umfrage gibt es noch keine Stastistik.';
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
            ($step === 'umfrage_frage_bearbeiten' && FormHelper::validateToken())
        ) { // Frage bearbeiten
            $step = 'umfrage_frage_erstellen';

            if (RequestHelper::verifyGPCDataInt('kUmfrageFrage') > 0) {
                $kUmfrageFrage = RequestHelper::verifyGPCDataInt('kUmfrageFrage');
            } else {
                $kUmfrageFrage = RequestHelper::verifyGPCDataInt('kUF');
            }
            $oUmfrageFrage = $db->select('tumfragefrage', 'kUmfrageFrage', $kUmfrageFrage);
            if (isset($oUmfrageFrage->kUmfrageFrage) && $oUmfrageFrage->kUmfrageFrage > 0) {
                $oUmfrageFrage->oUmfrageFrageAntwort_arr = $db->selectAll(
                    'tumfragefrageantwort',
                    'kUmfrageFrage',
                    (int)$oUmfrageFrage->kUmfrageFrage,
                    '*',
                    'nSort'
                );
                $oUmfrageFrage->oUmfrageMatrixOption_arr = $db->selectAll(
                    'tumfragematrixoption',
                    'kUmfrageFrage',
                    (int)$oUmfrageFrage->kUmfrageFrage,
                    '*',
                    'nSort'
                );
            }

            $smarty->assign('oUmfrageFrage', $oUmfrageFrage)
                   ->assign('kUmfrageTMP', $kUmfrageTMP);
        }
        // Umfrage Detail
        if ((isset($_GET['ud']) && (int)$_GET['ud'] === 1) || $step === 'umfrage_vorschau') {
            $kUmfrage = RequestHelper::verifyGPCDataInt('kUmfrage');

            if ($kUmfrage > 0) {
                $step     = 'umfrage_vorschau';
                $oUmfrage = $db->query(
                    "SELECT *, DATE_FORMAT(dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de, 
                        DATE_FORMAT(dGueltigBis, '%d.%m.%Y %H:%i') AS dGueltigBis_de,
                        DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de
                        FROM tumfrage
                        WHERE kUmfrage = " . $kUmfrage,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if ($oUmfrage->kUmfrage > 0) {
                    $oUmfrage->cKundengruppe_arr = [];

                    $kKundengruppe_arr = StringHandler::parseSSK($oUmfrage->cKundengruppe);
                    foreach ($kKundengruppe_arr as $kKundengruppe) {
                        if ($kKundengruppe == -1) {
                            $oUmfrage->cKundengruppe_arr[] = 'Alle';
                        } else {
                            $oKundengruppe = $db->select(
                                'tkundengruppe',
                                'kKundengruppe',
                                (int)$kKundengruppe
                            );
                            if (!empty($oKundengruppe->cName)) {
                                $oUmfrage->cKundengruppe_arr[] = $oKundengruppe->cName;
                            }
                        }
                    }
                    $oUmfrage->oUmfrageFrage_arr = $db->selectAll(
                        'tumfragefrage',
                        'kUmfrage',
                        $kUmfrage,
                        '*',
                        'nSort'
                    );
                    foreach ($oUmfrage->oUmfrageFrage_arr as $i => $oUmfrageFrage) {
                        // Mappe Fragentyp
                        $oUmfrage->oUmfrageFrage_arr[$i]->cTypMapped = mappeFragenTyp($oUmfrageFrage->cTyp);

                        $oUmfrage->oUmfrageFrage_arr[$i]->oUmfrageFrageAntwort_arr = $db->selectAll(
                            'tumfragefrageantwort',
                            'kUmfrageFrage',
                            (int)$oUmfrage->oUmfrageFrage_arr[$i]->kUmfrageFrage,
                            'kUmfrageFrageAntwort, kUmfrageFrage, cName',
                            'nSort'
                        );
                        $oUmfrage->oUmfrageFrage_arr[$i]->oUmfrageMatrixOption_arr = $db->selectAll(
                            'tumfragematrixoption',
                            'kUmfrageFrage',
                            (int)$oUmfrage->oUmfrageFrage_arr[$i]->kUmfrageFrage,
                            'kUmfrageMatrixOption, kUmfrageFrage, cName',
                            'nSort'
                        );
                    }
                    $smarty->assign('oUmfrage', $oUmfrage);
                }
            } else {
                $cFehler .= 'Fehler: Bitte wählen Sie eine korrekte Umfrage aus.<br>';
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
    // Hole Umfrage aus DB
    if ($step === 'umfrage_uebersicht') {
        $oUmfrageAnzahl = $db->query(
            'SELECT COUNT(*) AS nAnzahl
                FROM tumfrage
                WHERE kSprache = ' . (int)$_SESSION['kSprache'],
            \DB\ReturnType::SINGLE_OBJECT
        );
        // Pagination
        $oPagination = (new Pagination())
            ->setItemCount((int)$oUmfrageAnzahl->nAnzahl)
            ->assemble();
        $oUmfrage_arr = $db->query(
            "SELECT tumfrage.*, DATE_FORMAT(tumfrage.dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de, 
                DATE_FORMAT(tumfrage.dGueltigBis, '%d.%m.%Y %H:%i') AS dGueltigBis_de,
                DATE_FORMAT(tumfrage.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de, 
                COUNT(tumfragefrage.kUmfrageFrage) AS nAnzahlFragen
                FROM tumfrage
                LEFT JOIN tumfragefrage 
                    ON tumfragefrage.kUmfrage = tumfrage.kUmfrage
                WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
                GROUP BY tumfrage.kUmfrage
                ORDER BY dGueltigVon DESC
                LIMIT " . $oPagination->getLimitSQL(),
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($oUmfrage_arr as $i => $oUmfrage) {
            $oUmfrage_arr[$i]->cKundengruppe_arr = [];
            $kKundengruppe_arr                   = StringHandler::parseSSK($oUmfrage->cKundengruppe);

            foreach ($kKundengruppe_arr as $kKundengruppe) {
                if ($kKundengruppe == -1) {
                    $oUmfrage_arr[$i]->cKundengruppe_arr[] = 'Alle';
                } else {
                    $oKundengruppe = $db->query(
                        'SELECT cName
                            FROM tkundengruppe
                            WHERE kKundengruppe = ' . (int)$kKundengruppe,
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                    if (!empty($oKundengruppe->cName)) {
                        $oUmfrage_arr[$i]->cKundengruppe_arr[] = $oKundengruppe->cName;
                    }
                }
            }
        }

        $smarty->assign('oConfig_arr', getAdminSectionSettings(CONF_UMFRAGE))
               ->assign('oUmfrage_arr', $oUmfrage_arr)
               ->assign('oPagination', $oPagination);
    }
    $customerGroups = $db->query(
        'SELECT kKundengruppe, cName
            FROM tkundengruppe
            ORDER BY cStandard DESC',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $coupons = $db->query(
        "SELECT tkupon.kKupon, tkuponsprache.cName
            FROM tkupon
            LEFT JOIN tkuponsprache 
                ON tkuponsprache.kKupon = tkupon.kKupon
            WHERE tkupon.dGueltigAb <= NOW()
                AND (tkupon.dGueltigBis >= NOW() OR tkupon.dGueltigBis IS NULL)
                AND (tkupon.nVerwendungenBisher <= tkupon.nVerwendungen OR tkupon.nVerwendungen = 0)
                AND tkupon.cAktiv = 'Y'
                AND tkuponsprache.cISOSprache= '" . $oSpracheTMP->cISO . "'
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
