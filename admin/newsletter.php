<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_NEWSLETTER_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'newsletter_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$oNewsletterVorlage = null;
$Einstellungen      = Shop::getSettings([CONF_NEWSLETTER]);
$cHinweis           = '';
$cFehler            = '';
$step               = 'uebersicht';
$cOption            = '';
// Suche
$cInaktiveSucheSQL         = new stdClass();
$cInaktiveSucheSQL->cJOIN  = '';
$cInaktiveSucheSQL->cWHERE = '';
$cAktiveSucheSQL           = new stdClass();
$cAktiveSucheSQL->cJOIN    = '';
$cAktiveSucheSQL->cWHERE   = '';
// Standardkundengruppe Work Around
$oKundengruppe = Shop::Container()->getDB()->select('tkundengruppe', 'cStandard', 'Y');
$_SESSION['Kundengruppe'] = new Kundengruppe($oKundengruppe->kKundengruppe);

setzeSprache();
// Tabs
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', RequestHelper::verifyGPDataString('tab'));
}
// Einstellungen
if (FormHelper::validateToken()) {
    if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1) {
        if (isset($_POST['speichern'])) {
            $step = 'uebersicht';
            $cHinweis .= saveAdminSectionSettings(CONF_NEWSLETTER, $_POST);
        }
    } elseif ((isset($_POST['newsletterabonnent_loeschen'])
            && (int)$_POST['newsletterabonnent_loeschen'] === 1)
        || (RequestHelper::verifyGPCDataInt('inaktiveabonnenten') === 1
            && isset($_POST['abonnentloeschenSubmit']))
    ) {
        if (loescheAbonnenten($_POST['kNewsletterEmpfaenger'])) { // Newsletterabonnenten loeschen
            $cHinweis .= 'Ihre markierten Newsletter-Abonnenten wurden erfolgreich gelöscht.<br />';
        } else {
            $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newsletter-Abonnenten.<br />';
        }
    } elseif (isset($_POST['abonnentfreischaltenSubmit']) && RequestHelper::verifyGPCDataInt('inaktiveabonnenten') === 1) {
        // Newsletterabonnenten freischalten
        if (aktiviereAbonnenten($_POST['kNewsletterEmpfaenger'])) {
            $cHinweis .= 'Ihre markierten Newsletter-Abonnenten wurden erfolgreich freigeschaltet.<br />';
        } else {
            $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newsletter-Abonnenten.<br />';
        }
    } elseif (isset($_POST['newsletterabonnent_neu']) && (int)$_POST['newsletterabonnent_neu'] === 1) {
        // Newsletterabonnenten hinzufuegen
        $oNewsletter               = new stdClass();
        $oNewsletter->cAnrede      = $_POST['cAnrede'];
        $oNewsletter->cVorname     = $_POST['cVorname'];
        $oNewsletter->cNachname    = $_POST['cNachname'];
        $oNewsletter->cEmail       = $_POST['cEmail'];
        $oNewsletter->kSprache     = (int)$_POST['kSprache'];
        $oNewsletter->dEingetragen = 'now()';
        $oNewsletter->cOptCode     = create_NewsletterCode('cOptCode', $oNewsletter->cEmail);
        $oNewsletter->cLoeschCode  = create_NewsletterCode('cLoeschCode', $oNewsletter->cEmail);
        $oNewsletter->kKunde       = 0;

        if (!empty($oNewsletter->cEmail)) {
            $oNewsTmp = Shop::Container()->getDB()->select('tnewsletterempfaenger', 'cEmail', $oNewsletter->cEmail);
            if ($oNewsTmp) {
                $cFehler = 'E-Mail Adresse existiert bereits';
                $smarty->assign('oNewsletter', $oNewsletter);
            } else {
                Shop::Container()->getDB()->insert('tnewsletterempfaenger', $oNewsletter);
                $cHinweis = 'Newsletter-Empfänger wurde erfolgreich hinzugefügt';
            }
        } else {
            $cFehler = 'Bitte füllen Sie das Feld Email aus.';
            $smarty->assign('oNewsletter', $oNewsletter);
        }
    } elseif (isset($_POST['newsletterqueue']) && (int)$_POST['newsletterqueue'] === 1) { // Queue
        // Loeschen
        if (isset($_POST['loeschen'])) {
            if (is_array($_POST['kNewsletterQueue'])) {
                $cHinweis = 'Die Newsletterqueue "';
                foreach ($_POST['kNewsletterQueue'] as $kNewsletterQueue) {
                    // Queue Daten holen fuers spaetere Loeschen in anderen Tabellen
                    $oNewsletterQueue = Shop::Container()->getDB()->query(
                        "SELECT tnewsletterqueue.kNewsletter, tnewsletter.cBetreff
                            FROM tnewsletterqueue
                            JOIN tnewsletter 
                                ON tnewsletter.kNewsletter = tnewsletterqueue.kNewsletter
                            WHERE tnewsletterqueue.kNewsletterQueue = " . (int)$kNewsletterQueue,
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                    // tnewsletter loeoechen
                    Shop::Container()->getDB()->delete('tnewsletter', 'kNewsletter', (int)$oNewsletterQueue->kNewsletter);
                    // tjobqueue loeschen
                    Shop::Container()->getDB()->delete('tjobqueue', ['cKey', 'kKey'], ['kNewsletter', (int)$oNewsletterQueue->kNewsletter]);
                    // tnewsletterqueue loeschen
                    Shop::Container()->getDB()->delete('tnewsletterqueue', 'kNewsletterQueue', (int)$kNewsletterQueue);

                    $cHinweis .= $oNewsletterQueue->cBetreff . "\", ";
                }

                $cHinweis = substr($cHinweis, 0, -2);
                $cHinweis .= ' wurden erfolgreich gelöscht.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newsletter.<br />';
            }
        }
    } elseif ((isset($_POST['newsletterhistory']) && (int)$_POST['newsletterhistory'] === 1)
        || (isset($_GET['newsletterhistory']) && (int)$_GET['newsletterhistory'] === 1)
    ) { // History
        if (isset($_POST['loeschen'])) {
            if (is_array($_POST['kNewsletterHistory'])) {
                $cHinweis = 'Die Newsletterhistory ';
                foreach ($_POST['kNewsletterHistory'] as $kNewsletterHistory) {
                    Shop::Container()->getDB()->delete('tnewsletterhistory', 'kNewsletterHistory', (int)$kNewsletterHistory);
                    $cHinweis .= $kNewsletterHistory . ', ';
                }
                $cHinweis = substr($cHinweis, 0, -2);
                $cHinweis .= " wurden erfolgreich gelöscht.<br />";
            } else {
                $cFehler .= "Fehler: Bitte markieren Sie mindestens eine History.<br />";
            }
        } elseif (isset($_GET['anzeigen'])) {
            $step               = 'history_anzeigen';
            $kNewsletterHistory = (int)$_GET['anzeigen'];
            $oNewsletterHistory = Shop::Container()->getDB()->query(
                "SELECT kNewsletterHistory, cBetreff, cHTMLStatic, cKundengruppe, 
                    DATE_FORMAT(dStart, '%d.%m.%Y %H:%i') AS Datum
                    FROM tnewsletterhistory
                    WHERE kNewsletterHistory = " . $kNewsletterHistory . "
                        AND kSprache = " . (int)$_SESSION['kSprache'],
                \DB\ReturnType::SINGLE_OBJECT
            );

            if (isset($oNewsletterHistory->kNewsletterHistory) && $oNewsletterHistory->kNewsletterHistory > 0) {
                $smarty->assign('oNewsletterHistory', $oNewsletterHistory);
            }
        }
    } elseif (strlen(RequestHelper::verifyGPDataString('cSucheInaktiv')) > 0) { // Inaktive Abonnentensuche
        $cSuche = Shop::Container()->getDB()->escape(StringHandler::filterXSS(RequestHelper::verifyGPDataString('cSucheInaktiv')));

        if (strlen($cSuche) > 0) {
            $cInaktiveSucheSQL->cWHERE = " AND (tnewsletterempfaenger.cVorname LIKE '%" . $cSuche .
                "%' OR tnewsletterempfaenger.cNachname LIKE '%" . $cSuche .
                "%' OR tnewsletterempfaenger.cEmail LIKE '%" . $cSuche . "%')";
        }

        $smarty->assign('cSucheInaktiv', $cSuche);
    } elseif (strlen(RequestHelper::verifyGPDataString('cSucheAktiv')) > 0) { // Aktive Abonnentensuche
        $cSuche = Shop::Container()->getDB()->escape(StringHandler::filterXSS(RequestHelper::verifyGPDataString('cSucheAktiv')));

        if (strlen($cSuche) > 0) {
            $cAktiveSucheSQL->cWHERE = " AND (tnewsletterempfaenger.cVorname LIKE '%" . $cSuche .
                "%' OR tnewsletterempfaenger.cNachname LIKE '%" . $cSuche .
                "%' OR tnewsletterempfaenger.cEmail LIKE '%" . $cSuche . "%')";
        }

        $smarty->assign('cSucheAktiv', $cSuche);
    } elseif (RequestHelper::verifyGPCDataInt('vorschau') > 0) { // Vorschau
        $kNewsletterVorlage = RequestHelper::verifyGPCDataInt('vorschau');
        // Infos der Vorlage aus DB holen
        $oNewsletterVorlage = Shop::Container()->getDB()->query(
            "SELECT *, DATE_FORMAT(dStartZeit, '%d.%m.%Y %H:%i') AS Datum
                FROM tnewslettervorlage
                WHERE kNewsletterVorlage = " . $kNewsletterVorlage,
            \DB\ReturnType::SINGLE_OBJECT
        );
        $preview = null;
        if (RequestHelper::verifyGPCDataInt('iframe') === 1) {
            $step = 'vorlage_vorschau_iframe';
            $smarty->assign('cURL', 'newsletter.php?vorschau=' . $kNewsletterVorlage . '&token=' . $_SESSION['jtl_token']);
            $preview = baueNewsletterVorschau($oNewsletterVorlage);
        } elseif (isset($oNewsletterVorlage->kNewsletterVorlage) && $oNewsletterVorlage->kNewsletterVorlage > 0) {
            $step                      = 'vorlage_vorschau';
            $oNewsletterVorlage->oZeit = baueZeitAusDB($oNewsletterVorlage->dStartZeit);
            $preview                   = baueNewsletterVorschau($oNewsletterVorlage);
        }
        $smarty->assign('oNewsletterVorlage', $oNewsletterVorlage)
               ->assign('cFehler', is_string($preview) ? $preview : null)
               ->assign('NettoPreise', Session::CustomerGroup()->getIsMerchant());
    } elseif (RequestHelper::verifyGPCDataInt('newslettervorlagenstd') === 1) { // Vorlagen Std
        $oKundengruppe_arr = Shop::Container()->getDB()->query(
            "SELECT kKundengruppe, cName
                FROM tkundengruppe
                ORDER BY cStandard DESC",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $cArtNr_arr        = $_POST['cArtNr'] ?? null;
        $kKundengruppe_arr = $_POST['kKundengruppe'] ?? null;
        $cKundengruppe     = '';
        // Kundengruppen in einen String bauen
        if (is_array($kKundengruppe_arr) && count($kKundengruppe_arr) > 0) {
            foreach ($kKundengruppe_arr as $kKundengruppe) {
                $cKundengruppe .= ';' . $kKundengruppe . ';';
            }
        }
        $smarty->assign('oKundengruppe_arr', $oKundengruppe_arr)
               ->assign('oKampagne_arr', holeAlleKampagnen(false, true))
               ->assign('cTime', time());
        // Vorlage speichern
        if (RequestHelper::verifyGPCDataInt('vorlage_std_speichern') === 1) {
            $kNewslettervorlageStd = RequestHelper::verifyGPCDataInt('kNewslettervorlageStd');
            if ($kNewslettervorlageStd > 0) {
                $step               = 'vorlage_std_erstellen';
                $kNewslettervorlage = 0;
                if (RequestHelper::verifyGPCDataInt('kNewsletterVorlage') > 0) {
                    $kNewslettervorlage = RequestHelper::verifyGPCDataInt('kNewsletterVorlage');
                }
                $oNewslettervorlageStd = holeNewslettervorlageStd($kNewslettervorlageStd, $kNewslettervorlage);
                $cPlausiValue_arr      = speicherVorlageStd(
                    $oNewslettervorlageStd,
                    $kNewslettervorlageStd,
                    $_POST,
                    $kNewslettervorlage
                );
                if (is_array($cPlausiValue_arr) && count($cPlausiValue_arr) > 0) {
                    $smarty->assign('cPlausiValue_arr', $cPlausiValue_arr)
                           ->assign('cPostVar_arr', StringHandler::filterXSS($_POST))
                           ->assign('oNewslettervorlageStd', $oNewslettervorlageStd);
                } else {
                    $step = 'uebersicht';
                    $smarty->assign('cTab', 'newslettervorlagen');
                    $cHinweis = 'Ihre Newslettervorlage "' .
                        StringHandler::filterXSS($_POST['cName']) .
                        '" wurde erfolgreich ';
                    if ($kNewslettervorlage > 0) {
                         $cHinweis .= 'editiert.';
                    } else {
                        $cHinweis .= 'gespeichert.';
                    }
                }
            }
        } elseif (RequestHelper::verifyGPCDataInt('editieren') > 0) { // Editieren
            $kNewslettervorlage    = RequestHelper::verifyGPCDataInt('editieren');
            $step                  = 'vorlage_std_erstellen';
            $oNewslettervorlageStd = holeNewslettervorlageStd(0, $kNewslettervorlage);
            $oExplodedArtikel      = explodecArtikel($oNewslettervorlageStd->cArtikel);
            $kKundengruppe_arr     = explodecKundengruppe($oNewslettervorlageStd->cKundengruppe);
            $revisionData          = [];
            foreach ($oNewslettervorlageStd->oNewslettervorlageStdVar_arr as $item) {
                $revisionData[$item->kNewslettervorlageStdVar] = $item;
            }
            $smarty->assign('oNewslettervorlageStd', $oNewslettervorlageStd)
                   ->assign('kArtikel_arr', $oExplodedArtikel->kArtikel_arr)
                   ->assign('cArtNr_arr', $oExplodedArtikel->cArtNr_arr)
                   ->assign('revisionData', $revisionData)
                   ->assign('kKundengruppe_arr', $kKundengruppe_arr);
        }
        // Vorlage Std erstellen
        if (RequestHelper::verifyGPCDataInt('vorlage_std_erstellen') === 1 && RequestHelper::verifyGPCDataInt('kNewsletterVorlageStd') > 0) {
            $step                  = 'vorlage_std_erstellen';
            $kNewsletterVorlageStd = RequestHelper::verifyGPCDataInt('kNewsletterVorlageStd');
            // Hole Std Vorlage
            $oNewslettervorlageStd = holeNewslettervorlageStd($kNewsletterVorlageStd);
            $smarty->assign('oNewslettervorlageStd', $oNewslettervorlageStd);
        }
    } elseif (RequestHelper::verifyGPCDataInt('newslettervorlagen') === 1) {
        // Vorlagen
        $oKundengruppe_arr = Shop::Container()->getDB()->query(
            "SELECT kKundengruppe, cName
                FROM tkundengruppe
                ORDER BY cStandard DESC",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $smarty->assign('oKundengruppe_arr', $oKundengruppe_arr)
               ->assign('oKampagne_arr', holeAlleKampagnen(false, true));

        $cArtNr_arr        = $_POST['cArtNr'] ?? null;
        $kKundengruppe_arr = $_POST['kKundengruppe'] ?? null;
        $cKundengruppe     = '';
        // Kundengruppen in einen String bauen
        if (is_array($kKundengruppe_arr) && count($kKundengruppe_arr) > 0) {
            foreach ($kKundengruppe_arr as $kKundengruppe) {
                $cKundengruppe .= ';' . (int)$kKundengruppe . ';';
            }
        }
        // Vorlage hinzufuegen
        if (isset($_POST['vorlage_erstellen'])) {
            $step    = 'vorlage_erstellen';
            $cOption = 'erstellen';
        } elseif ((isset($_GET['editieren']) && (int)$_GET['editieren'] > 0)
            || (isset($_GET['vorbereiten']) && (int)$_GET['vorbereiten'] > 0)
        ) {
            // Vorlage editieren/vorbereiten
            $step               = 'vorlage_erstellen';
            $kNewsletterVorlage = RequestHelper::verifyGPCDataInt('vorbereiten');
            if ($kNewsletterVorlage === 0) {
                $kNewsletterVorlage = RequestHelper::verifyGPCDataInt('editieren');
            }
            // Infos der Vorlage aus DB holen
            $oNewsletterVorlage = Shop::Container()->getDB()->query(
                "SELECT *, DATE_FORMAT(dStartZeit, '%d.%m.%Y %H:%i') AS Datum
                FROM tnewslettervorlage
                WHERE kNewsletterVorlage = " . $kNewsletterVorlage,
                \DB\ReturnType::SINGLE_OBJECT
            );

            $oNewsletterVorlage->oZeit = baueZeitAusDB($oNewsletterVorlage->dStartZeit);

            if ($oNewsletterVorlage->kNewsletterVorlage > 0) {
                $oExplodedArtikel                = explodecArtikel($oNewsletterVorlage->cArtikel);
                $oNewsletterVorlage->cArtikel    = substr(
                    substr($oNewsletterVorlage->cArtikel, 1),
                    0,
                    strlen(substr($oNewsletterVorlage->cArtikel, 1)) - 1
                );
                $oNewsletterVorlage->cHersteller = substr(
                    substr($oNewsletterVorlage->cHersteller, 1),
                    0,
                    strlen(substr($oNewsletterVorlage->cHersteller, 1)) - 1
                );
                $oNewsletterVorlage->cKategorie  = substr(
                    substr($oNewsletterVorlage->cKategorie, 1),
                    0,
                    strlen(substr($oNewsletterVorlage->cKategorie, 1)) - 1
                );
                $kKundengruppe_arr               = explodecKundengruppe($oNewsletterVorlage->cKundengruppe);
                $smarty->assign('kArtikel_arr', $oExplodedArtikel->kArtikel_arr)
                       ->assign('cArtNr_arr', $oExplodedArtikel->cArtNr_arr)
                       ->assign('kKundengruppe_arr', $kKundengruppe_arr);
            }

            $smarty->assign('oNewsletterVorlage', $oNewsletterVorlage);
            if (isset($_GET['editieren'])) {
                $cOption = 'editieren';
            }
        } elseif (isset($_POST['speichern'])) { // Vorlage speichern
            $cPlausiValue_arr = speicherVorlage($_POST);
            if (is_array($cPlausiValue_arr) && count($cPlausiValue_arr) > 0) {
                $step = 'vorlage_erstellen';
                $smarty->assign('cPlausiValue_arr', $cPlausiValue_arr)
                       ->assign('cPostVar_arr', StringHandler::filterXSS($_POST))
                       ->assign('oNewsletterVorlage', $oNewsletterVorlage);
            }
        } elseif (isset($_POST['speichern_und_senden'])) { // Vorlage speichern und senden
            unset($oNewsletterVorlage, $oNewsletter, $oKunde, $oEmailempfaenger);

            $oNewsletterVorlage = speicherVorlage($_POST);
            if ($oNewsletterVorlage !== false) {
                // baue tnewsletter Objekt
                $oNewsletter                = new stdClass();
                $oNewsletter->kSprache      = $oNewsletterVorlage->kSprache;
                $oNewsletter->kKampagne     = $oNewsletterVorlage->kKampagne;
                $oNewsletter->cName         = $oNewsletterVorlage->cName;
                $oNewsletter->cBetreff      = $oNewsletterVorlage->cBetreff;
                $oNewsletter->cArt          = $oNewsletterVorlage->cArt;
                $oNewsletter->cArtikel      = $oNewsletterVorlage->cArtikel;
                $oNewsletter->cHersteller   = $oNewsletterVorlage->cHersteller;
                $oNewsletter->cKategorie    = $oNewsletterVorlage->cKategorie;
                $oNewsletter->cKundengruppe = $oNewsletterVorlage->cKundengruppe;
                $oNewsletter->cInhaltHTML   = $oNewsletterVorlage->cInhaltHTML;
                $oNewsletter->cInhaltText   = $oNewsletterVorlage->cInhaltText;
                $oNewsletter->dStartZeit    = $oNewsletterVorlage->dStartZeit;
                // tnewsletter fuellen
                $oNewsletter->kNewsletter = Shop::Container()->getDB()->insert('tnewsletter', $oNewsletter);
                // baue tnewsletterqueue Objekt
                $tnewsletterqueue                    = new stdClass();
                $tnewsletterqueue->kNewsletter       = $oNewsletter->kNewsletter;
                $tnewsletterqueue->nAnzahlEmpfaenger = 0;
                $tnewsletterqueue->dStart            = $oNewsletter->dStartZeit;
                // tnewsletterqueue fuellen
                Shop::Container()->getDB()->insert('tnewsletterqueue', $tnewsletterqueue);
                // baue jobqueue objekt
                $nLimitM   = JOBQUEUE_LIMIT_M_NEWSLETTER;
                $oJobQueue = new JobQueue(
                    null,
                    0,
                    $oNewsletter->kNewsletter,
                    0,
                    $nLimitM,
                    0,
                    'newsletter',
                    'tnewsletter',
                    'kNewsletter',
                    $oNewsletter->dStartZeit
                );
                $oJobQueue->speicherJobInDB();
                // Baue Arrays mit kKeys
                $kArtikel_arr    = gibAHKKeys($oNewsletterVorlage->cArtikel, true);
                $kHersteller_arr = gibAHKKeys($oNewsletterVorlage->cHersteller);
                $kKategorie_arr  = gibAHKKeys($oNewsletterVorlage->cKategorie);
                // Baue Kampagnenobjekt, falls vorhanden in der Newslettervorlage
                $oKampagne = new Kampagne($oNewsletterVorlage->kKampagne);
                // Baue Arrays von Objekten
                $oArtikel_arr    = gibArtikelObjekte($kArtikel_arr, $oKampagne);
                $oHersteller_arr = gibHerstellerObjekte($kHersteller_arr, $oKampagne);
                $oKategorie_arr  = gibKategorieObjekte($kKategorie_arr, $oKampagne);
                // Kunden Dummy bauen
                $oKunde            = new stdClass();
                $oKunde->cAnrede   = 'm';
                $oKunde->cVorname  = 'Max';
                $oKunde->cNachname = 'Mustermann';
                // Emailempfaenger dummy bauen
                $oEmailempfaenger              = new stdClass();
                $oEmailempfaenger->cEmail      = $Einstellungen['newsletter']['newsletter_emailtest'];
                $oEmailempfaenger->cLoeschCode = '78rev6gj8er6we87gw6er8';
                $oEmailempfaenger->cLoeschURL  = Shop::getURL() . '/newsletter.php?lang=ger&lc=' . $oEmailempfaenger->cLoeschCode;

                $mailSmarty = bereiteNewsletterVor($Einstellungen);
                // Baue Anzahl Newsletterempfaenger
                $oNewsletterEmpfaenger = getNewsletterEmpfaenger($oNewsletter->kNewsletter);
                // Baue Kundengruppe
                $cKundengruppe    = '';
                $cKundengruppeKey = '';
                if (is_array($oNewsletterEmpfaenger->cKundengruppe_arr)
                    && count($oNewsletterEmpfaenger->cKundengruppe_arr) > 0
                ) {
                    $nCount_arr    = [];
                    $nCount_arr[0] = 0;     // Count Kundengruppennamen
                    $nCount_arr[1] = 0;     // Count Kundengruppenkeys
                    foreach ($oNewsletterEmpfaenger->cKundengruppe_arr as $cKundengruppeTMP) {
                        if ($cKundengruppeTMP != '0') {
                            $oKundengruppeTMP = Shop::Container()->getDB()->select('tkundengruppe', 'kKundengruppe', (int)$cKundengruppeTMP);
                            if (strlen($oKundengruppeTMP->cName) > 0) {
                                if ($nCount_arr[0] > 0) {
                                    $cKundengruppe .= ', ' . $oKundengruppeTMP->cName;
                                } else {
                                    $cKundengruppe .= $oKundengruppeTMP->cName;
                                }
                                $nCount_arr[0]++;
                            }
                            if ((int)$oKundengruppeTMP->kKundengruppe > 0) {
                                if ($nCount_arr[1] > 0) {
                                    $cKundengruppeKey .= ';' . $oKundengruppeTMP->kKundengruppe;
                                } else {
                                    $cKundengruppeKey .= $oKundengruppeTMP->kKundengruppe;
                                }
                                $nCount_arr[1]++;
                            }
                        } else {
                            if ($nCount_arr[0] > 0) {
                                $cKundengruppe .= ', Newsletterempfänger ohne Kundenkonto';
                            } else {
                                $cKundengruppe .= 'Newsletterempfänger ohne Kundenkonto';
                            }
                            if ($nCount_arr[1] > 0) {
                                $cKundengruppeKey .= ';0';
                            } else {
                                $cKundengruppeKey .= '0';
                            }
                            $nCount_arr[0]++;
                            $nCount_arr[1]++;
                        }
                    }
                }
                if (strlen($cKundengruppe) > 0) {
                    $cKundengruppe = substr($cKundengruppe, 0, -2);
                }
                // tnewsletterhistory objekt bauen
                $oNewsletterHistory                   = new stdClass();
                $oNewsletterHistory->kSprache         = $oNewsletter->kSprache;
                $oNewsletterHistory->nAnzahl          = $oNewsletterEmpfaenger->nAnzahl;
                $oNewsletterHistory->cBetreff         = $oNewsletter->cBetreff;
                $oNewsletterHistory->cHTMLStatic      = gibStaticHtml(
                    $mailSmarty,
                    $oNewsletter,
                    $oArtikel_arr,
                    $oHersteller_arr,
                    $oKategorie_arr,
                    $oKampagne,
                    $oEmailempfaenger,
                    $oKunde
                );
                $oNewsletterHistory->cKundengruppe    = $cKundengruppe;
                $oNewsletterHistory->cKundengruppeKey = ';' . $cKundengruppeKey . ';';
                $oNewsletterHistory->dStart           = $oNewsletterVorlage->dStartZeit;
                // tnewsletterhistory fuellen
                Shop::Container()->getDB()->insert('tnewsletterhistory', $oNewsletterHistory);

                $cHinweis .= 'Der Newsletter "' . $oNewsletter->cName . '" wurde zum Versenden vorbereitet.<br />';
            }
        } elseif (isset($_POST['speichern_und_testen'])) { // Vorlage speichern und testen
            $oNewsletterVorlage = speicherVorlage($_POST);
            // Baue Arrays mit kKeys
            $kArtikel_arr    = gibAHKKeys($oNewsletterVorlage->cArtikel, true);
            $kHersteller_arr = gibAHKKeys($oNewsletterVorlage->cHersteller);
            $kKategorie_arr  = gibAHKKeys($oNewsletterVorlage->cKategorie);
            // Baue Kampagnenobjekt, falls vorhanden in der Newslettervorlage
            $oKampagne = new Kampagne($oNewsletterVorlage->kKampagne);
            // Baue Arrays von Objekten
            $oArtikel_arr    = gibArtikelObjekte($kArtikel_arr, $oKampagne);
            $oHersteller_arr = gibHerstellerObjekte($kHersteller_arr, $oKampagne);
            $oKategorie_arr  = gibKategorieObjekte($kKategorie_arr, $oKampagne);
            // Kunden Dummy bauen
            $oKunde            = new stdClass();
            $oKunde->cAnrede   = 'm';
            $oKunde->cVorname  = 'Max';
            $oKunde->cNachname = 'Mustermann';
            // Emailempfaenger dummy bauen
            $oEmailempfaenger              = new stdClass();
            $oEmailempfaenger->cEmail      = $Einstellungen['newsletter']['newsletter_emailtest'];
            $oEmailempfaenger->cLoeschCode = '78rev6gj8er6we87gw6er8';
            $oEmailempfaenger->cLoeschURL  = Shop::getURL() . '/newsletter.php?lang=ger&lc=' . $oEmailempfaenger->cLoeschCode;
            if (empty($oEmailempfaenger->cEmail)) {
                $result = 'Die Empfänger-Adresse zum Testen ist leer.';
            } else {
                $mailSmarty = bereiteNewsletterVor($Einstellungen);
                $result     = versendeNewsletter(
                    $mailSmarty,
                    $oNewsletterVorlage,
                    $Einstellungen,
                    $oEmailempfaenger,
                    $oArtikel_arr,
                    $oHersteller_arr,
                    $oKategorie_arr,
                    $oKampagne,
                    $oKunde
                );
            }
            if ($result !== true) {
                $smarty->assign('cFehler', $result);
            } else {
                $cHinweis .= 'Die Newslettervorlage "' . $oNewsletterVorlage->cName .
                    '" wurde zum Testen an "' . $oEmailempfaenger->cEmail . '" gesendet.<br />';
            }
        } elseif (isset($_POST['loeschen'])) { // Vorlage loeschen
            $step = 'uebersicht';
            if (is_array($_POST['kNewsletterVorlage'])) {
                foreach ($_POST['kNewsletterVorlage'] as $kNewsletterVorlage) {
                    $oNewslettervorlage = Shop::Container()->getDB()->query(
                        "SELECT kNewsletterVorlage, kNewslettervorlageStd
                            FROM tnewslettervorlage
                            WHERE kNewsletterVorlage = " . (int)$kNewsletterVorlage,
                        \DB\ReturnType::SINGLE_OBJECT
                    );

                    if (isset($oNewslettervorlage->kNewsletterVorlage) && $oNewslettervorlage->kNewsletterVorlage > 0) {
                        if (isset($oNewslettervorlage->kNewslettervorlageStd) && $oNewslettervorlage->kNewslettervorlageStd > 0) {
                            Shop::Container()->getDB()->query(
                                "DELETE tnewslettervorlage, tnewslettervorlagestdvarinhalt 
                                    FROM tnewslettervorlage
                                    LEFT JOIN tnewslettervorlagestdvarinhalt 
                                        ON tnewslettervorlagestdvarinhalt.kNewslettervorlage = tnewslettervorlage.kNewsletterVorlage
                                    WHERE tnewslettervorlage.kNewsletterVorlage = " . (int)$kNewsletterVorlage,
                                \DB\ReturnType::AFFECTED_ROWS
                            );
                        } else {
                            Shop::Container()->getDB()->delete('tnewslettervorlage', 'kNewsletterVorlage', (int)$kNewsletterVorlage);
                        }
                    }
                }
                $cHinweis .= 'Die Newslettervorlage wurde erfolgreich gelöscht.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newsletter.<br />';
            }
        }
        $smarty->assign('cOption', $cOption);
    }
}
// Steps
if ($step === 'uebersicht') {
    $oNewsletterEmpfaengerAnzahl = Shop::Container()->getDB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tnewsletterempfaenger
            WHERE tnewsletterempfaenger.nAktiv = 0" . $cInaktiveSucheSQL->cWHERE,
        \DB\ReturnType::SINGLE_OBJECT
    );
    $oNewsletterQueueAnzahl = Shop::Container()->getDB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tnewsletterqueue
            JOIN tnewsletter 
                ON tnewsletterqueue.kNewsletter = tnewsletter.kNewsletter
            WHERE tnewsletter.kSprache = " . (int)$_SESSION['kSprache'],
        \DB\ReturnType::SINGLE_OBJECT
    );
    $oNewsletterVorlageAnzahl = Shop::Container()->getDB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tnewslettervorlage
            WHERE kSprache = " . (int)$_SESSION['kSprache'],
        \DB\ReturnType::SINGLE_OBJECT
    );
    $oNewsletterHistoryAnzahl = Shop::Container()->getDB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tnewsletterhistory
            WHERE kSprache = " . (int)$_SESSION['kSprache'],
        \DB\ReturnType::SINGLE_OBJECT
    );
    // Paginationen
    $oPagiInaktiveAbos = (new Pagination('inaktive'))
        ->setItemCount($oNewsletterEmpfaengerAnzahl->nAnzahl)
        ->assemble();
    $oPagiWarteschlange = (new Pagination('warteschlange'))
        ->setItemCount($oNewsletterQueueAnzahl->nAnzahl)
        ->assemble();
    $oPagiVorlagen = (new Pagination('vorlagen'))
        ->setItemCount($oNewsletterVorlageAnzahl->nAnzahl)
        ->assemble();
    $oPagiHistory = (new Pagination('history'))
        ->setItemCount($oNewsletterHistoryAnzahl->nAnzahl)
        ->assemble();
    $oPagiAlleAbos = (new Pagination('alle'))
        ->setItemCount(holeAbonnentenAnzahl($cAktiveSucheSQL))
        ->assemble();

    // Kundengruppen
    $oKundengruppe_arr = Shop::Container()->getDB()->query(
        "SELECT kKundengruppe, cName
            FROM tkundengruppe
            ORDER BY cStandard DESC",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $smarty->assign('oKundengruppe_arr', $oKundengruppe_arr);
    // Hole alle Newsletter die in der Queue sind
    $oNewsletterQueue_arr = Shop::Container()->getDB()->query(
        "SELECT tnewsletter.cBetreff, tnewsletterqueue.kNewsletterQueue, tnewsletterqueue.kNewsletter, 
            DATE_FORMAT(tnewsletterqueue.dStart, '%d.%m.%Y %H:%i') AS Datum
            FROM tnewsletterqueue
            JOIN tnewsletter 
                ON tnewsletterqueue.kNewsletter = tnewsletter.kNewsletter
            WHERE tnewsletter.kSprache = " . (int)$_SESSION['kSprache'] . "
            ORDER BY Datum DESC 
            LIMIT " . $oPagiWarteschlange->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    if (is_array($oNewsletterQueue_arr) && count($oNewsletterQueue_arr) > 0) {
        // Hole JobQueue fortschritt fuer Newsletterqueue
        foreach ($oNewsletterQueue_arr as $i => $oNewsletterQueue) {
            // Bereits verschickte holen
            $oJobQueue = Shop::Container()->getDB()->query(
                "SELECT nLimitN
                    FROM tjobqueue
                    WHERE kKey = " . (int)$oNewsletterQueue->kNewsletter . "
                        AND cKey = 'kNewsletter'",
                \DB\ReturnType::SINGLE_OBJECT
            );
            $oNewsletterEmpfaenger                       = getNewsletterEmpfaenger($oNewsletterQueue->kNewsletter);
            $oNewsletterQueue_arr[$i]->nLimitN           = $oJobQueue->nLimitN;
            $oNewsletterQueue_arr[$i]->nAnzahlEmpfaenger = $oNewsletterEmpfaenger->nAnzahl;
            $oNewsletterQueue_arr[$i]->cKundengruppe_arr = $oNewsletterEmpfaenger->cKundengruppe_arr;
        }
        $smarty->assign('oNewsletterQueue_arr', $oNewsletterQueue_arr);
    }
    // Hole alle Newslettervorlagen
    $oNewsletterVorlage_arr = Shop::Container()->getDB()->query(
        "SELECT kNewsletterVorlage, kNewslettervorlageStd, cBetreff, cName
            FROM tnewslettervorlage
            WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
            ORDER BY cName LIMIT " . $oPagiVorlagen->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    if (is_array($oNewsletterVorlage_arr) && count($oNewsletterVorlage_arr) > 0) {
        $smarty->assign('oNewsletterVorlage_arr', $oNewsletterVorlage_arr);
    }
    // Hole alle NewslettervorlagenStd
    $oNewslettervorlageStd_arr = Shop::Container()->getDB()->query(
        "SELECT *
            FROM tnewslettervorlagestd
            WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
            ORDER BY cName",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    $oNewslettervorlageStdAnzahl = Shop::Container()->getDB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tnewslettervorlagestd
            WHERE kSprache = " . (int)$_SESSION['kSprache'],
        \DB\ReturnType::SINGLE_OBJECT
    );
    foreach ($oNewslettervorlageStd_arr as $i => $oNewslettervorlageStd) {
        // tnewslettervorlagestdvars holen
        $oNewslettervorlageStd_arr[$i]->oNewsletttervorlageStdVar_arr = Shop::Container()->getDB()->query(
            "SELECT *
                FROM tnewslettervorlagestdvar
                WHERE kNewslettervorlageStd = " . (int)$oNewslettervorlageStd->kNewslettervorlageStd,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }
    $smarty->assign('oNewslettervorlageStd_arr', $oNewslettervorlageStd_arr);
    // Inaktive Abonnenten
    $oNewsletterEmpfaenger_arr = Shop::Container()->getDB()->query(
        "SELECT tnewsletterempfaenger.kNewsletterEmpfaenger, tnewsletterempfaenger.cVorname AS newsVorname,
            tnewsletterempfaenger.cNachname AS newsNachname, tkunde.cVorname, tkunde.cNachname, 
            tnewsletterempfaenger.cEmail, tnewsletterempfaenger.nAktiv, tkunde.kKundengruppe, tkundengruppe.cName, 
            DATE_FORMAT(tnewsletterempfaenger.dEingetragen, '%d.%m.%Y %H:%i') AS Datum
            FROM tnewsletterempfaenger
            LEFT JOIN tkunde 
                ON tkunde.kKunde = tnewsletterempfaenger.kKunde
            LEFT JOIN tkundengruppe 
                ON tkundengruppe.kKundengruppe = tkunde.kKundengruppe
            WHERE tnewsletterempfaenger.nAktiv = 0
            " . $cInaktiveSucheSQL->cWHERE . "
            ORDER BY Datum DESC 
            LIMIT " . $oPagiInaktiveAbos->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($oNewsletterEmpfaenger_arr as $i => $oNewsletterEmpfaenger) {
        $oKunde                                   = new Kunde($oNewsletterEmpfaenger->kKunde ?? null);
        $oNewsletterEmpfaenger_arr[$i]->cNachname = $oKunde->cNachname;
    }

    $smarty->assign('oNewsletterEmpfaenger_arr', $oNewsletterEmpfaenger_arr);
    // Hole alle Newsletter die in der History sind
    $oNewsletterHistory_arr = Shop::Container()->getDB()->query(
        "SELECT kNewsletterHistory, nAnzahl, cBetreff, cKundengruppe,  
            DATE_FORMAT(dStart, '%d.%m.%Y %H:%i') AS Datum
            FROM tnewsletterhistory
            WHERE kSprache = " . (int)$_SESSION['kSprache'] . "
                AND nAnzahl > 0
            ORDER BY dStart DESC 
            LIMIT " . $oPagiHistory->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    if (is_array($oNewsletterHistory_arr) && count($oNewsletterHistory_arr) > 0) {
        $smarty->assign('oNewsletterHistory_arr', $oNewsletterHistory_arr);
    }
    // Einstellungen
    $oConfig_arr = Shop::Container()->getDB()->selectAll('teinstellungenconf', 'kEinstellungenSektion', CONF_NEWSLETTER, '*', 'nSort');
    $configCount = count($oConfig_arr);
    for ($i = 0; $i < $configCount; $i++) {
        if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
            $oConfig_arr[$i]->ConfWerte = Shop::Container()->getDB()->selectAll(
                'teinstellungenconfwerte',
                'kEinstellungenConf',
                $oConfig_arr[$i]->kEinstellungenConf,
                '*',
                'nSort'
            );
        }

        $oSetValue = Shop::Container()->getDB()->select(
            'teinstellungen',
            ['kEinstellungenSektion', 'cName'],
            [CONF_NEWSLETTER,  $oConfig_arr[$i]->cWertName]
        );
        $oConfig_arr[$i]->gesetzterWert = $oSetValue->cWert ?? null;
    }

    $kundengruppen = Shop::Container()->getDB()->query(
        "SELECT * 
            FROM tkundengruppe 
            ORDER BY cName",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $smarty->assign('kundengruppen', $kundengruppen)
           ->assign('oConfig_arr', $oConfig_arr)
           ->assign('oAbonnenten_arr', holeAbonnenten(' LIMIT ' . $oPagiAlleAbos->getLimitSQL(), $cAktiveSucheSQL))
           ->assign('nMaxAnzahlAbonnenten', holeAbonnentenAnzahl($cAktiveSucheSQL))
           ->assign('oPagiInaktiveAbos', $oPagiInaktiveAbos)
           ->assign('oPagiWarteschlange', $oPagiWarteschlange)
           ->assign('oPagiVorlagen', $oPagiVorlagen)
           ->assign('oPagiHistory', $oPagiHistory)
           ->assign('oPagiAlleAbos', $oPagiAlleAbos);
}
$smarty->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('nRand', time())
       ->display('newsletter.tpl');
