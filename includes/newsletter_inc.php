<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $dbfeld
 * @param string $email
 * @return string
 */
function create_NewsletterCode($dbfeld, $email)
{
    $CodeNeu = md5($email . time() . rand(123, 456));
    while (!unique_NewsletterCode($dbfeld, $CodeNeu)) {
        $CodeNeu = md5($email . time() . rand(123, 456));
    }

    return $CodeNeu;
}

/**
 * @param string     $dbfeld
 * @param string|int $code
 * @return bool
 */
function unique_NewsletterCode($dbfeld, $code)
{
    $res = Shop::DB()->select('tnewsletterempfaenger', $dbfeld, $code);

    return !(isset($res->kNewsletterEmpfaenger) && $res->kNewsletterEmpfaenger > 0);
}

/**
 * @param Kunde|stdClass $oKunde
 * @param bool  $bPruefeDaten
 * @return stdClass
 */
function fuegeNewsletterEmpfaengerEin($oKunde, $bPruefeDaten = false)
{
    global $cFehler, $cHinweis, $Einstellungen;

    if (!isset($Einstellungen['newsletter'])) {
        $oSettings_arr               = Shop::getSettings(array(CONF_NEWSLETTER));
        $Einstellungen['newsletter'] = $oSettings_arr['newsletter'];
    }

    $oPlausi              = new stdClass();
    $oPlausi->nPlausi_arr = array();

    if (valid_email($oKunde->cEmail) || !$bPruefeDaten) {
        $oPlausi->nPlausi_arr = newsletterAnmeldungPlausi($oKunde);
        $kKundengruppe        = Kundengruppe::getCurrent();
        // CheckBox Plausi
        $oCheckBox            = new CheckBox();
        $oPlausi->nPlausi_arr = array_merge($oPlausi->nPlausi_arr, $oCheckBox->validateCheckBox(CHECKBOX_ORT_NEWSLETTERANMELDUNG, $kKundengruppe, $_POST, true));

        $oPlausi->cPost_arr['cAnrede']   = $oKunde->cAnrede;
        $oPlausi->cPost_arr['cVorname']  = $oKunde->cVorname;
        $oPlausi->cPost_arr['cNachname'] = $oKunde->cNachname;
        $oPlausi->cPost_arr['cEmail']    = $oKunde->cEmail;
        $oPlausi->cPost_arr['captcha']   = (isset($_POST['captcha'])) ? StringHandler::htmlentities(StringHandler::filterXSS($_POST['captcha'])) : null;
        if (count($oPlausi->nPlausi_arr) === 0 || !$bPruefeDaten) {
            // Prüfen ob Email bereits vorhanden
            $oNewsletterEmpfaenger = Shop::DB()->query(
                "SELECT *, DATE_FORMAT(dEingetragen, '%d.%m.%Y %H:%i') AS Datum
                    FROM tnewsletterempfaenger
                    WHERE cEmail='" . $oKunde->cEmail . "'", 1
            );

            // Prüfen ob Kunde bereits eingetragen
            if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
                $oNewsletterEmpfaengerKunde = Shop::DB()->query(
                    "SELECT kKunde
                        FROM tnewsletterempfaenger
                        WHERE kKunde = " . intval($_SESSION['Kunde']->kKunde), 1
                );
            }
            if ((isset($oNewsletterEmpfaenger->cEmail) && strlen($oNewsletterEmpfaenger->cEmail) > 0) ||
                (isset($oNewsletterEmpfaengerKunde->kKunde) && $oNewsletterEmpfaengerKunde->kKunde > 0)
            ) {
                $cFehler = Shop::Lang()->get('newsletterExists', 'errorMessages');
            } else {
                // CheckBox Spezialfunktion ausführen
                $oCheckBox->triggerSpecialFunction(CHECKBOX_ORT_NEWSLETTERANMELDUNG, $kKundengruppe, true, $_POST, array('oKunde' => $oKunde));
                $oCheckBox->checkLogging(CHECKBOX_ORT_NEWSLETTERANMELDUNG, $kKundengruppe, $_POST, true);

                unset($oNewsletterEmpfaenger);

                // Neuen Newsletterempfaenger hinzufügen
                $oNewsletterEmpfaenger           = new stdClass();
                $oNewsletterEmpfaenger->kSprache = $_SESSION['kSprache'];
                $oNewsletterEmpfaenger->kKunde   = (isset($_SESSION['Kunde']->kKunde)) ? $_SESSION['Kunde']->kKunde : 0;
                $oNewsletterEmpfaenger->nAktiv   = 0;
                // Double OPT nur für unregistrierte? --> Kunden brauchen nichts bestätigen
                if ($Einstellungen['newsletter']['newsletter_doubleopt'] === 'U' && isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
                    $oNewsletterEmpfaenger->nAktiv = 1;
                }
                $oNewsletterEmpfaenger->cAnrede   = $oKunde->cAnrede;
                $oNewsletterEmpfaenger->cVorname  = $oKunde->cVorname;
                $oNewsletterEmpfaenger->cNachname = $oKunde->cNachname;
                $oNewsletterEmpfaenger->cEmail    = $oKunde->cEmail;
                // OptCode erstellen und überprüfen
                // Werte für $dbfeld 'cOptCode','cLoeschCode'

                $oNewsletterEmpfaenger->cOptCode           = create_NewsletterCode('cOptCode', $oKunde->cEmail);
                $oNewsletterEmpfaenger->cLoeschCode        = create_NewsletterCode('cLoeschCode', $oKunde->cEmail);
                $oNewsletterEmpfaenger->dEingetragen       = 'now()';
                $oNewsletterEmpfaenger->dLetzterNewsletter = '0000-00-00';

                executeHook(HOOK_NEWSLETTER_PAGE_EMPFAENGEREINTRAGEN, array('oNewsletterEmpfaenger' => $oNewsletterEmpfaenger));

                Shop::DB()->insert('tnewsletterempfaenger', $oNewsletterEmpfaenger);
                // Protokollieren (hinzufügen)
                $oNewsletterEmpfaengerHistory               = new stdClass();
                $oNewsletterEmpfaengerHistory->kSprache     = $_SESSION['kSprache'];
                $oNewsletterEmpfaengerHistory->kKunde       = (isset($_SESSION['Kunde']->kKunde)) ? $_SESSION['Kunde']->kKunde : 0;
                $oNewsletterEmpfaengerHistory->cAnrede      = $oKunde->cAnrede;
                $oNewsletterEmpfaengerHistory->cVorname     = $oKunde->cVorname;
                $oNewsletterEmpfaengerHistory->cNachname    = $oKunde->cNachname;
                $oNewsletterEmpfaengerHistory->cEmail       = $oKunde->cEmail;
                $oNewsletterEmpfaengerHistory->cOptCode     = $oNewsletterEmpfaenger->cOptCode;
                $oNewsletterEmpfaengerHistory->cLoeschCode  = $oNewsletterEmpfaenger->cLoeschCode;
                $oNewsletterEmpfaengerHistory->cAktion      = 'Eingetragen';
                $oNewsletterEmpfaengerHistory->dEingetragen = 'now()';
                $oNewsletterEmpfaengerHistory->dAusgetragen = '0000-00-00';
                $oNewsletterEmpfaengerHistory->dOptCode     = '0000-00-00';

                $kNewsletterEmpfaengerHistory = Shop::DB()->insert('tnewsletterempfaengerhistory', $oNewsletterEmpfaengerHistory);

                executeHook(HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN, array('oNewsletterEmpfaengerHistory' => $oNewsletterEmpfaengerHistory));

                if (($Einstellungen['newsletter']['newsletter_doubleopt'] === 'U' && !$_SESSION['Kunde']->kKunde) || $Einstellungen['newsletter']['newsletter_doubleopt'] === 'A') {
                    $oNewsletterEmpfaenger->cLoeschURL     = Shop::getURL() . '/newsletter.php?lang=' . $_SESSION['cISOSprache'] . '&lc=' . $oNewsletterEmpfaenger->cLoeschCode;
                    $oNewsletterEmpfaenger->cFreischaltURL = Shop::getURL() . '/newsletter.php?lang=' . $_SESSION['cISOSprache'] . '&fc=' . $oNewsletterEmpfaenger->cOptCode;
                    if (!isset($oObjekt)) {
                        $oObjekt = new stdClass();
                    }
                    $oObjekt->tkunde               = (isset($_SESSION['Kunde'])) ? $_SESSION['Kunde'] : null;
                    $oObjekt->NewsletterEmpfaenger = $oNewsletterEmpfaenger;

                    $mail = sendeMail(MAILTEMPLATE_NEWSLETTERANMELDEN, $oObjekt);
                    // UPDATE
                    $_upd                 = new stdClass();
                    $_upd->cEmailBodyHtml = $mail->bodyHtml;
                    Shop::DB()->update('tnewsletterempfaengerhistory', 'kNewsletterEmpfaengerHistory', (int)$kNewsletterEmpfaengerHistory, $_upd);

                    $cHinweis = Shop::Lang()->get('newsletterAdd', 'messages');
                    $oPlausi  = new stdClass();
                } else {
                    $cHinweis = Shop::Lang()->get('newsletterNomailAdd', 'messages');
                }
            }
        }
    } else {
        $cFehler = Shop::Lang()->get('newsletterWrongemail', 'errorMessages');
    }

    return $oPlausi;
}

/**
 * @param Kunde $oKunde
 * @return array
 */
function newsletterAnmeldungPlausi($oKunde)
{
    global $cFehler, $Einstellungen;

    $nPlausi_arr = array();
    if ((!isset($_SESSION['bAnti_spam_already_checked']) || $_SESSION['bAnti_spam_already_checked'] !== true) && isset($Einstellungen['newsletter']['newsletter_sicherheitscode']) && $Einstellungen['newsletter']['newsletter_sicherheitscode'] !== 'N' && empty($_SESSION['Kunde']->kKunde)) {
        if ($Einstellungen['newsletter']['newsletter_sicherheitscode'] !== 'N') {
            // reCAPTCHA
            if (isset($_POST['g-recaptcha-response'])) {
                if (!validateReCaptcha($_POST['g-recaptcha-response'])) {
                    $nPlausi_arr['captcha'] = true;
                }
            } else {
                if (!isset($_POST['captcha'])) {
                    $nPlausi_arr['captcha'] = 1;
                    $cFehler                = Shop::Lang()->get('newsletterCaptcha', 'errorMessages');
                }
                if ($Einstellungen['global']['anti_spam_method'] == 5) { //Prüfen ob der Token und der Name korrekt sind
                    $nPlausi_arr['captcha'] = 2;
                    if (validToken()) {
                        unset($nPlausi_arr['captcha']);
                    }
                }
            }
        }
    }

    return $nPlausi_arr;
}

/**
 * @param int $kKunde
 * @return bool
 */
function pruefeObBereitsAbonnent($kKunde)
{
    if ($kKunde > 0) {
        $oNewsletterEmpfaenger = Shop::DB()->query(
            "SELECT kKunde
                FROM tnewsletterempfaenger
                WHERE kKunde = " . intval($kKunde), 1
        );

        return (isset($oNewsletterEmpfaenger->kKunde) && $oNewsletterEmpfaenger->kKunde > 0);
    }

    return false;
}

/**
 * @param int    $kKundengruppe
 * @param string $cKundengruppeKey
 * @return bool
 */
function pruefeNLHistoryKundengruppe($kKundengruppe, $cKundengruppeKey)
{
    if (strlen($cKundengruppeKey) > 0) {
        $kKundengruppe_arr    = array();
        $cKundengruppeKey_arr = explode(';', $cKundengruppeKey);
        if (is_array($cKundengruppeKey_arr) && count($cKundengruppeKey_arr) > 0) {
            foreach ($cKundengruppeKey_arr as $cKundengruppeKey) {
                if (intval($cKundengruppeKey) > 0 || (strlen($cKundengruppeKey) > 0 && intval($cKundengruppeKey) === 0)) {
                    $kKundengruppe_arr[] = intval($cKundengruppeKey);
                }
            }
        }
        // Für alle sichtbar
        if (in_array(0, $kKundengruppe_arr)) {
            return true;
        }
        if (intval($kKundengruppe) > 0) {
            if (in_array(intval($kKundengruppe), $kKundengruppe_arr)) {
                return true;
            }
        }
    }

    return false;
}
