<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\FormHelper;
use Helpers\RequestHelper;
use Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_NEWSLETTER_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'newsletter_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$db            = Shop::Container()->getDB();
$newsletterTPL = null;
$conf          = Shop::getSettings([CONF_NEWSLETTER]);
$cHinweis      = '';
$cFehler       = '';
$step          = 'uebersicht';
$option        = '';

$inactiveSearchSQL         = new stdClass();
$inactiveSearchSQL->cJOIN  = '';
$inactiveSearchSQL->cWHERE = '';
$activeSearchSQL           = new stdClass();
$activeSearchSQL->cJOIN    = '';
$activeSearchSQL->cWHERE   = '';
$customerGroup             = $db->select('tkundengruppe', 'cStandard', 'Y');
$_SESSION['Kundengruppe']  = new Kundengruppe($customerGroup->kKundengruppe);

setzeSprache();
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', RequestHelper::verifyGPDataString('tab'));
}
if (FormHelper::validateToken()) {
    if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] === 1) {
        if (isset($_POST['speichern'])) {
            $step      = 'uebersicht';
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
    } elseif (isset($_POST['abonnentfreischaltenSubmit'])
        && RequestHelper::verifyGPCDataInt('inaktiveabonnenten') === 1
    ) {
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
        $oNewsletter->dEingetragen = 'NOW()';
        $oNewsletter->cOptCode     = create_NewsletterCode('cOptCode', $oNewsletter->cEmail);
        $oNewsletter->cLoeschCode  = create_NewsletterCode('cLoeschCode', $oNewsletter->cEmail);
        $oNewsletter->kKunde       = 0;

        if (!empty($oNewsletter->cEmail)) {
            $oNewsTmp = $db->select('tnewsletterempfaenger', 'cEmail', $oNewsletter->cEmail);
            if ($oNewsTmp) {
                $cFehler = 'E-Mail Adresse existiert bereits';
                $smarty->assign('oNewsletter', $oNewsletter);
            } else {
                $db->insert('tnewsletterempfaenger', $oNewsletter);
                $cHinweis = 'Newsletter-Empfänger wurde erfolgreich hinzugefügt';
            }
        } else {
            $cFehler = 'Bitte füllen Sie das Feld Email aus.';
            $smarty->assign('oNewsletter', $oNewsletter);
        }
    } elseif (isset($_POST['newsletterqueue']) && (int)$_POST['newsletterqueue'] === 1) { // Queue
        if (isset($_POST['loeschen'])) {
            if (is_array($_POST['kNewsletterQueue'])) {
                $cHinweis = 'Die Newsletterqueue "';
                foreach ($_POST['kNewsletterQueue'] as $kNewsletterQueue) {
                    $entry = $db->query(
                        'SELECT tnewsletterqueue.kNewsletter, tnewsletter.cBetreff
                            FROM tnewsletterqueue
                            JOIN tnewsletter 
                                ON tnewsletter.kNewsletter = tnewsletterqueue.kNewsletter
                            WHERE tnewsletterqueue.kNewsletterQueue = ' . (int)$kNewsletterQueue,
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                    $db->delete('tnewsletter', 'kNewsletter', (int)$entry->kNewsletter);
                    $db->delete('tjobqueue', ['cKey', 'kKey'], ['kNewsletter', (int)$entry->kNewsletter]);
                    $db->delete('tnewsletterqueue', 'kNewsletterQueue', (int)$kNewsletterQueue);
                    $cHinweis .= $entry->cBetreff . '", ';
                }
                $cHinweis  = substr($cHinweis, 0, -2);
                $cHinweis .= ' wurden erfolgreich gelöscht.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newsletter.<br />';
            }
        }
    } elseif ((isset($_POST['newsletterhistory']) && (int)$_POST['newsletterhistory'] === 1)
        || (isset($_GET['newsletterhistory']) && (int)$_GET['newsletterhistory'] === 1)
    ) {
        if (isset($_POST['loeschen'])) {
            if (is_array($_POST['kNewsletterHistory'])) {
                $cHinweis = 'Die Newsletterhistory ';
                foreach ($_POST['kNewsletterHistory'] as $kNewsletterHistory) {
                    $db->delete('tnewsletterhistory', 'kNewsletterHistory', (int)$kNewsletterHistory);
                    $cHinweis .= $kNewsletterHistory . ', ';
                }
                $cHinweis  = substr($cHinweis, 0, -2);
                $cHinweis .= ' wurden erfolgreich gelöscht.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine History.<br />';
            }
        } elseif (isset($_GET['anzeigen'])) {
            $step               = 'history_anzeigen';
            $kNewsletterHistory = (int)$_GET['anzeigen'];
            $oNewsletterHistory = $db->queryPrepared(
                "SELECT kNewsletterHistory, cBetreff, cHTMLStatic, cKundengruppe, 
                    DATE_FORMAT(dStart, '%d.%m.%Y %H:%i') AS Datum
                    FROM tnewsletterhistory
                    WHERE kNewsletterHistory = :hid
                        AND kSprache = :lid",
                ['hid' => $kNewsletterHistory, 'lid' => (int)$_SESSION['kSprache']],
                \DB\ReturnType::SINGLE_OBJECT
            );

            if (isset($oNewsletterHistory->kNewsletterHistory) && $oNewsletterHistory->kNewsletterHistory > 0) {
                $smarty->assign('oNewsletterHistory', $oNewsletterHistory);
            }
        }
    } elseif (strlen(RequestHelper::verifyGPDataString('cSucheInaktiv')) > 0) { // Inaktive Abonnentensuche
        $cSuche = $db->escape(StringHandler::filterXSS(RequestHelper::verifyGPDataString('cSucheInaktiv')));

        if (strlen($cSuche) > 0) {
            $inactiveSearchSQL->cWHERE = " AND (tnewsletterempfaenger.cVorname LIKE '%" . $cSuche .
                "%' OR tnewsletterempfaenger.cNachname LIKE '%" . $cSuche .
                "%' OR tnewsletterempfaenger.cEmail LIKE '%" . $cSuche . "%')";
        }

        $smarty->assign('cSucheInaktiv', $cSuche);
    } elseif (strlen(RequestHelper::verifyGPDataString('cSucheAktiv')) > 0) { // Aktive Abonnentensuche
        $cSuche = $db->escape(StringHandler::filterXSS(RequestHelper::verifyGPDataString('cSucheAktiv')));

        if (strlen($cSuche) > 0) {
            $activeSearchSQL->cWHERE = " AND (tnewsletterempfaenger.cVorname LIKE '%" . $cSuche .
                "%' OR tnewsletterempfaenger.cNachname LIKE '%" . $cSuche .
                "%' OR tnewsletterempfaenger.cEmail LIKE '%" . $cSuche . "%')";
        }

        $smarty->assign('cSucheAktiv', $cSuche);
    } elseif (RequestHelper::verifyGPCDataInt('vorschau') > 0) { // Vorschau
        $kNewsletterVorlage = RequestHelper::verifyGPCDataInt('vorschau');
        // Infos der Vorlage aus DB holen
        $newsletterTPL = $db->query(
            "SELECT *, DATE_FORMAT(dStartZeit, '%d.%m.%Y %H:%i') AS Datum
                FROM tnewslettervorlage
                WHERE kNewsletterVorlage = " . $kNewsletterVorlage,
            \DB\ReturnType::SINGLE_OBJECT
        );
        $preview       = null;
        if (RequestHelper::verifyGPCDataInt('iframe') === 1) {
            $step = 'vorlage_vorschau_iframe';
            $smarty->assign(
                'cURL',
                'newsletter.php?vorschau=' . $kNewsletterVorlage . '&token=' . $_SESSION['jtl_token']
            );
            $preview = baueNewsletterVorschau($newsletterTPL);
        } elseif (isset($newsletterTPL->kNewsletterVorlage) && $newsletterTPL->kNewsletterVorlage > 0) {
            $step                 = 'vorlage_vorschau';
            $newsletterTPL->oZeit = baueZeitAusDB($newsletterTPL->dStartZeit);
            $preview              = baueNewsletterVorschau($newsletterTPL);
        }
        $smarty->assign('oNewsletterVorlage', $newsletterTPL)
               ->assign('cFehler', is_string($preview) ? $preview : null)
               ->assign('NettoPreise', \Session\Session::getCustomerGroup()->getIsMerchant());
    } elseif (RequestHelper::verifyGPCDataInt('newslettervorlagenstd') === 1) { // Vorlagen Std
        $customerGroups    = $db->query(
            'SELECT kKundengruppe, cName
                FROM tkundengruppe
                ORDER BY cStandard DESC',
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
        $smarty->assign('oKundengruppe_arr', $customerGroups)
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
                $tpl              = holeNewslettervorlageStd($kNewslettervorlageStd, $kNewslettervorlage);
                $cPlausiValue_arr = speicherVorlageStd(
                    $tpl,
                    $kNewslettervorlageStd,
                    $_POST,
                    $kNewslettervorlage
                );
                if (is_array($cPlausiValue_arr) && count($cPlausiValue_arr) > 0) {
                    $smarty->assign('cPlausiValue_arr', $cPlausiValue_arr)
                           ->assign('cPostVar_arr', StringHandler::filterXSS($_POST))
                           ->assign('oNewslettervorlageStd', $tpl);
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
            $kNewslettervorlage = RequestHelper::verifyGPCDataInt('editieren');
            $step               = 'vorlage_std_erstellen';
            $tpl                = holeNewslettervorlageStd(0, $kNewslettervorlage);
            $oExplodedArtikel   = explodecArtikel($tpl->cArtikel);
            $kKundengruppe_arr  = explodecKundengruppe($tpl->cKundengruppe);
            $revisionData       = [];
            foreach ($tpl->oNewslettervorlageStdVar_arr as $item) {
                $revisionData[$item->kNewslettervorlageStdVar] = $item;
            }
            $smarty->assign('oNewslettervorlageStd', $tpl)
                   ->assign('kArtikel_arr', $oExplodedArtikel->kArtikel_arr)
                   ->assign('cArtNr_arr', $oExplodedArtikel->cArtNr_arr)
                   ->assign('revisionData', $revisionData)
                   ->assign('kKundengruppe_arr', $kKundengruppe_arr);
        }
        // Vorlage Std erstellen
        if (RequestHelper::verifyGPCDataInt('vorlage_std_erstellen') === 1
            && RequestHelper::verifyGPCDataInt('kNewsletterVorlageStd') > 0
        ) {
            $step                  = 'vorlage_std_erstellen';
            $kNewsletterVorlageStd = RequestHelper::verifyGPCDataInt('kNewsletterVorlageStd');
            // Hole Std Vorlage
            $tpl = holeNewslettervorlageStd($kNewsletterVorlageStd);
            $smarty->assign('oNewslettervorlageStd', $tpl);
        }
    } elseif (RequestHelper::verifyGPCDataInt('newslettervorlagen') === 1) {
        // Vorlagen
        $customerGroups = $db->query(
            'SELECT kKundengruppe, cName
                FROM tkundengruppe
                ORDER BY cStandard DESC',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $smarty->assign('oKundengruppe_arr', $customerGroups)
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
            $step   = 'vorlage_erstellen';
            $option = 'erstellen';
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
            $newsletterTPL = $db->query(
                "SELECT *, DATE_FORMAT(dStartZeit, '%d.%m.%Y %H:%i') AS Datum
                    FROM tnewslettervorlage
                    WHERE kNewsletterVorlage = " . $kNewsletterVorlage,
                \DB\ReturnType::SINGLE_OBJECT
            );

            $newsletterTPL->oZeit = baueZeitAusDB($newsletterTPL->dStartZeit);

            if ($newsletterTPL->kNewsletterVorlage > 0) {
                $oExplodedArtikel           = explodecArtikel($newsletterTPL->cArtikel);
                $newsletterTPL->cArtikel    = substr(
                    substr($newsletterTPL->cArtikel, 1),
                    0,
                    strlen(substr($newsletterTPL->cArtikel, 1)) - 1
                );
                $newsletterTPL->cHersteller = substr(
                    substr($newsletterTPL->cHersteller, 1),
                    0,
                    strlen(substr($newsletterTPL->cHersteller, 1)) - 1
                );
                $newsletterTPL->cKategorie  = substr(
                    substr($newsletterTPL->cKategorie, 1),
                    0,
                    strlen(substr($newsletterTPL->cKategorie, 1)) - 1
                );
                $kKundengruppe_arr          = explodecKundengruppe($newsletterTPL->cKundengruppe);
                $smarty->assign('kArtikel_arr', $oExplodedArtikel->kArtikel_arr)
                       ->assign('cArtNr_arr', $oExplodedArtikel->cArtNr_arr)
                       ->assign('kKundengruppe_arr', $kKundengruppe_arr);
            }

            $smarty->assign('oNewsletterVorlage', $newsletterTPL);
            if (isset($_GET['editieren'])) {
                $option = 'editieren';
            }
        } elseif (isset($_POST['speichern'])) { // Vorlage speichern
            $cPlausiValue_arr = speicherVorlage($_POST);
            if (is_array($cPlausiValue_arr) && count($cPlausiValue_arr) > 0) {
                $step = 'vorlage_erstellen';
                $smarty->assign('cPlausiValue_arr', $cPlausiValue_arr)
                       ->assign('cPostVar_arr', StringHandler::filterXSS($_POST))
                       ->assign('oNewsletterVorlage', $newsletterTPL);
            }
        } elseif (isset($_POST['speichern_und_senden'])) { // Vorlage speichern und senden
            unset($newsletterTPL, $oNewsletter, $oKunde, $oEmailempfaenger);

            $newsletterTPL = speicherVorlage($_POST);
            if ($newsletterTPL !== false) {
                // baue tnewsletter Objekt
                $oNewsletter                = new stdClass();
                $oNewsletter->kSprache      = $newsletterTPL->kSprache;
                $oNewsletter->kKampagne     = $newsletterTPL->kKampagne;
                $oNewsletter->cName         = $newsletterTPL->cName;
                $oNewsletter->cBetreff      = $newsletterTPL->cBetreff;
                $oNewsletter->cArt          = $newsletterTPL->cArt;
                $oNewsletter->cArtikel      = $newsletterTPL->cArtikel;
                $oNewsletter->cHersteller   = $newsletterTPL->cHersteller;
                $oNewsletter->cKategorie    = $newsletterTPL->cKategorie;
                $oNewsletter->cKundengruppe = $newsletterTPL->cKundengruppe;
                $oNewsletter->cInhaltHTML   = $newsletterTPL->cInhaltHTML;
                $oNewsletter->cInhaltText   = $newsletterTPL->cInhaltText;
                $oNewsletter->dStartZeit    = $newsletterTPL->dStartZeit;
                $oNewsletter->kNewsletter   = $db->insert('tnewsletter', $oNewsletter);
                // baue tnewsletterqueue Objekt
                $tnewsletterqueue                    = new stdClass();
                $tnewsletterqueue->kNewsletter       = $oNewsletter->kNewsletter;
                $tnewsletterqueue->nAnzahlEmpfaenger = 0;
                $tnewsletterqueue->dStart            = $oNewsletter->dStartZeit;
                // tnewsletterqueue fuellen
                $db->insert('tnewsletterqueue', $tnewsletterqueue);
                // baue jobqueue objekt
                $nLimitM  = JOBQUEUE_LIMIT_M_NEWSLETTER;
                $jobQueue = new JobQueue(
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
                $jobQueue->speicherJobInDB();
                // Baue Arrays mit kKeys
                $kArtikel_arr    = gibAHKKeys($newsletterTPL->cArtikel, true);
                $kHersteller_arr = gibAHKKeys($newsletterTPL->cHersteller);
                $kKategorie_arr  = gibAHKKeys($newsletterTPL->cKategorie);
                // Baue Kampagnenobjekt, falls vorhanden in der Newslettervorlage
                $oKampagne = new Kampagne($newsletterTPL->kKampagne);
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
                $oEmailempfaenger->cEmail      = $conf['newsletter']['newsletter_emailtest'];
                $oEmailempfaenger->cLoeschCode = '78rev6gj8er6we87gw6er8';
                $oEmailempfaenger->cLoeschURL  = Shop::getURL() .
                    '/newsletter.php?lang=ger&lc=' .
                    $oEmailempfaenger->cLoeschCode;

                $mailSmarty = bereiteNewsletterVor($conf);
                // Baue Anzahl Newsletterempfaenger
                $recipient = getNewsletterEmpfaenger($oNewsletter->kNewsletter);
                // Baue Kundengruppe
                $cKundengruppe    = '';
                $cKundengruppeKey = '';
                if (is_array($recipient->cKundengruppe_arr)
                    && count($recipient->cKundengruppe_arr) > 0
                ) {
                    $nCount_arr    = [];
                    $nCount_arr[0] = 0;     // Count Kundengruppennamen
                    $nCount_arr[1] = 0;     // Count Kundengruppenkeys
                    foreach ($recipient->cKundengruppe_arr as $cKundengruppeTMP) {
                        if ($cKundengruppeTMP != '0') {
                            $oKundengruppeTMP = $db->select('tkundengruppe', 'kKundengruppe', (int)$cKundengruppeTMP);
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
                $oNewsletterHistory->nAnzahl          = $recipient->nAnzahl;
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
                $oNewsletterHistory->dStart           = $newsletterTPL->dStartZeit;
                // tnewsletterhistory fuellen
                $db->insert('tnewsletterhistory', $oNewsletterHistory);

                $cHinweis .= 'Der Newsletter "' . $oNewsletter->cName . '" wurde zum Versenden vorbereitet.<br />';
            }
        } elseif (isset($_POST['speichern_und_testen'])) { // Vorlage speichern und testen
            $newsletterTPL = speicherVorlage($_POST);
            // Baue Arrays mit kKeys
            $kArtikel_arr    = gibAHKKeys($newsletterTPL->cArtikel, true);
            $kHersteller_arr = gibAHKKeys($newsletterTPL->cHersteller);
            $kKategorie_arr  = gibAHKKeys($newsletterTPL->cKategorie);
            // Baue Kampagnenobjekt, falls vorhanden in der Newslettervorlage
            $oKampagne = new Kampagne($newsletterTPL->kKampagne);
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
            $oEmailempfaenger->cEmail      = $conf['newsletter']['newsletter_emailtest'];
            $oEmailempfaenger->cLoeschCode = '78rev6gj8er6we87gw6er8';
            $oEmailempfaenger->cLoeschURL  = Shop::getURL() .
                '/newsletter.php?lang=ger&lc=' .
                $oEmailempfaenger->cLoeschCode;
            if (empty($oEmailempfaenger->cEmail)) {
                $result = 'Die Empfänger-Adresse zum Testen ist leer.';
            } else {
                $mailSmarty = bereiteNewsletterVor($conf);
                $result     = versendeNewsletter(
                    $mailSmarty,
                    $newsletterTPL,
                    $conf,
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
                $cHinweis .= 'Die Newslettervorlage "' . $newsletterTPL->cName .
                    '" wurde zum Testen an "' . $oEmailempfaenger->cEmail . '" gesendet.<br />';
            }
        } elseif (isset($_POST['loeschen'])) { // Vorlage loeschen
            $step = 'uebersicht';
            if (is_array($_POST['kNewsletterVorlage'])) {
                foreach ($_POST['kNewsletterVorlage'] as $kNewsletterVorlage) {
                    $oNewslettervorlage = $db->query(
                        'SELECT kNewsletterVorlage, kNewslettervorlageStd
                            FROM tnewslettervorlage
                            WHERE kNewsletterVorlage = ' . (int)$kNewsletterVorlage,
                        \DB\ReturnType::SINGLE_OBJECT
                    );

                    if (isset($oNewslettervorlage->kNewsletterVorlage) && $oNewslettervorlage->kNewsletterVorlage > 0) {
                        if (isset($oNewslettervorlage->kNewslettervorlageStd)
                            && $oNewslettervorlage->kNewslettervorlageStd > 0
                        ) {
                            $db->query(
                                'DELETE tnewslettervorlage, tnewslettervorlagestdvarinhalt 
                                    FROM tnewslettervorlage
                                    LEFT JOIN tnewslettervorlagestdvarinhalt 
                                        ON tnewslettervorlagestdvarinhalt.kNewslettervorlage = 
                                           tnewslettervorlage.kNewsletterVorlage
                                    WHERE tnewslettervorlage.kNewsletterVorlage = ' . (int)$kNewsletterVorlage,
                                \DB\ReturnType::AFFECTED_ROWS
                            );
                        } else {
                            $db->delete(
                                'tnewslettervorlage',
                                'kNewsletterVorlage',
                                (int)$kNewsletterVorlage
                            );
                        }
                    }
                }
                $cHinweis .= 'Die Newslettervorlage wurde erfolgreich gelöscht.<br />';
            } else {
                $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newsletter.<br />';
            }
        }
        $smarty->assign('cOption', $option);
    }
}
if ($step === 'uebersicht') {
    $recipientsCount   = (int)$db->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tnewsletterempfaenger
            WHERE tnewsletterempfaenger.nAktiv = 0' . $inactiveSearchSQL->cWHERE,
        \DB\ReturnType::SINGLE_OBJECT
    )->nAnzahl;
    $queueCount        = (int)$db->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tnewsletterqueue
            JOIN tnewsletter 
                ON tnewsletterqueue.kNewsletter = tnewsletter.kNewsletter
            WHERE tnewsletter.kSprache = ' . (int)$_SESSION['kSprache'],
        \DB\ReturnType::SINGLE_OBJECT
    )->nAnzahl;
    $templateCount     = (int)$db->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tnewslettervorlage
            WHERE kSprache = ' . (int)$_SESSION['kSprache'],
        \DB\ReturnType::SINGLE_OBJECT
    )->nAnzahl;
    $historyCount      = (int)$db->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM tnewsletterhistory
            WHERE kSprache = ' . (int)$_SESSION['kSprache'],
        \DB\ReturnType::SINGLE_OBJECT
    )->nAnzahl;
    $pagiInactive      = (new Pagination('inaktive'))
        ->setItemCount($recipientsCount)
        ->assemble();
    $pagiQueue         = (new Pagination('warteschlange'))
        ->setItemCount($queueCount)
        ->assemble();
    $pagiTemplates     = (new Pagination('vorlagen'))
        ->setItemCount($templateCount)
        ->assemble();
    $pagiHistory       = (new Pagination('history'))
        ->setItemCount($historyCount)
        ->assemble();
    $pagiSubscriptions = (new Pagination('alle'))
        ->setItemCount(holeAbonnentenAnzahl($activeSearchSQL))
        ->assemble();
    $customerGroups    = $db->query(
        'SELECT kKundengruppe, cName
            FROM tkundengruppe
            ORDER BY cStandard DESC',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $queue             = $db->queryPrepared(
        "SELECT tnewsletter.cBetreff, tnewsletterqueue.kNewsletterQueue, tnewsletterqueue.kNewsletter, 
            DATE_FORMAT(tnewsletterqueue.dStart, '%d.%m.%Y %H:%i') AS Datum
            FROM tnewsletterqueue
            JOIN tnewsletter 
                ON tnewsletterqueue.kNewsletter = tnewsletter.kNewsletter
            WHERE tnewsletter.kSprache = :lid
            ORDER BY tnewsletterqueue.dStart DESC 
            LIMIT " . $pagiQueue->getLimitSQL(),
        ['lid' => (int)$_SESSION['kSprache']],
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($queue as $entry) {
        $entry->kNewsletter       = (int)$entry->kNewsletter;
        $jobQueue                 = $db->queryPrepared(
            "SELECT nLimitN
                FROM tjobqueue
                WHERE kKey = :nlid
                    AND cKey = 'kNewsletter'",
            ['nlid' => $entry->kNewsletter],
            \DB\ReturnType::SINGLE_OBJECT
        );
        $recipient                = getNewsletterEmpfaenger($entry->kNewsletter);
        $entry->nLimitN           = $jobQueue->nLimitN;
        $entry->nAnzahlEmpfaenger = $recipient->nAnzahl;
        $entry->cKundengruppe_arr = $recipient->cKundengruppe_arr;
    }
    $templates   = $db->query(
        'SELECT kNewsletterVorlage, kNewslettervorlageStd, cBetreff, cName
            FROM tnewslettervorlage
            WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
            ORDER BY cName LIMIT ' . $pagiTemplates->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $defaultData = $db->query(
        'SELECT *
            FROM tnewslettervorlagestd
            WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
            ORDER BY cName',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($defaultData as $tpl) {
        $tpl->oNewsletttervorlageStdVar_arr = $db->query(
            'SELECT *
                FROM tnewslettervorlagestdvar
                WHERE kNewslettervorlageStd = ' . (int)$tpl->kNewslettervorlageStd,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }
    $inactiveRecipients = $db->query(
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
            " . $inactiveSearchSQL->cWHERE . '
            ORDER BY tnewsletterempfaenger.dEingetragen DESC 
            LIMIT ' . $pagiInactive->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($inactiveRecipients as $recipient) {
        $oKunde               = new Kunde($recipient->kKunde ?? null);
        $recipient->cNachname = $oKunde->cNachname;
    }

    $history       = $db->queryPrepared(
        "SELECT kNewsletterHistory, nAnzahl, cBetreff, cKundengruppe,  
            DATE_FORMAT(dStart, '%d.%m.%Y %H:%i') AS Datum
            FROM tnewsletterhistory
            WHERE kSprache = :lid
                AND nAnzahl > 0
            ORDER BY dStart DESC 
            LIMIT " . $pagiHistory->getLimitSQL(),
        ['lid' => (int)$_SESSION['kSprache']],
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $kundengruppen = $db->query(
        'SELECT * 
            FROM tkundengruppe 
            ORDER BY cName',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $smarty->assign('kundengruppen', $kundengruppen)
           ->assign('oKundengruppe_arr', $customerGroups)
           ->assign('oNewsletterQueue_arr', $queue)
           ->assign('oNewsletterVorlage_arr', $templates)
           ->assign('oNewslettervorlageStd_arr', $defaultData)
           ->assign('oNewsletterEmpfaenger_arr', $inactiveRecipients)
           ->assign('oNewsletterHistory_arr', $history)
           ->assign('oConfig_arr', getAdminSectionSettings(CONF_NEWSLETTER))
           ->assign('oAbonnenten_arr', holeAbonnenten(' LIMIT ' . $pagiSubscriptions->getLimitSQL(), $activeSearchSQL))
           ->assign('nMaxAnzahlAbonnenten', holeAbonnentenAnzahl($activeSearchSQL))
           ->assign('oPagiInaktiveAbos', $pagiInactive)
           ->assign('oPagiWarteschlange', $pagiQueue)
           ->assign('oPagiVorlagen', $pagiTemplates)
           ->assign('oPagiHistory', $pagiHistory)
           ->assign('oPagiAlleAbos', $pagiSubscriptions);
}
$smarty->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('nRand', time())
       ->display('newsletter.tpl');
