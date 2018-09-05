<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

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
    $res = Shop::Container()->getDB()->select('tnewsletterempfaenger', $dbfeld, $code);

    return !(isset($res->kNewsletterEmpfaenger) && $res->kNewsletterEmpfaenger > 0);
}

/**
 * @param Kunde|stdClass $oKunde
 * @param bool  $bPruefeDaten
 * @return stdClass
 */
function fuegeNewsletterEmpfaengerEin($oKunde, $bPruefeDaten = false)
{
    global $cFehler, $cHinweis;

    $Einstellungen              = Shop::getSettings([CONF_NEWSLETTER]);
    $oPlausi                    = new stdClass();
    $oPlausi->nPlausi_arr       = [];
    $oNewsletterEmpfaengerKunde = null;

    if (!$bPruefeDaten || StringHandler::filterEmailAddress($oKunde->cEmail) !== false) {
        $oPlausi->nPlausi_arr = newsletterAnmeldungPlausi($oKunde);
        $kKundengruppe        = Session::CustomerGroup()->getID();
        // CheckBox Plausi
        $oCheckBox            = new CheckBox();
        $oPlausi->nPlausi_arr = array_merge(
            $oPlausi->nPlausi_arr,
            $oCheckBox->validateCheckBox(CHECKBOX_ORT_NEWSLETTERANMELDUNG, $kKundengruppe, $_POST, true)
        );

        $oPlausi->cPost_arr['cAnrede']   = $oKunde->cAnrede;
        $oPlausi->cPost_arr['cVorname']  = $oKunde->cVorname;
        $oPlausi->cPost_arr['cNachname'] = $oKunde->cNachname;
        $oPlausi->cPost_arr['cEmail']    = $oKunde->cEmail;
        $oPlausi->cPost_arr['captcha']   = isset($_POST['captcha'])
            ? StringHandler::htmlentities(StringHandler::filterXSS($_POST['captcha']))
            : null;
        if (!$bPruefeDaten || count($oPlausi->nPlausi_arr) === 0) {
            // Pruefen ob Email bereits vorhanden
            $oNewsletterEmpfaenger = Shop::Container()->getDB()->select(
                'tnewsletterempfaenger',
                'cEmail',
                $oKunde->cEmail
            );
            if (!empty($oNewsletterEmpfaenger->dEingetragen)) {
                $oNewsletterEmpfaenger->Datum =
                    (new DateTime($oNewsletterEmpfaenger->dEingetragen))->format('d.m.Y H:i');
            }
            // Pruefen ob Kunde bereits eingetragen
            if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
                $oNewsletterEmpfaengerKunde = Shop::Container()->getDB()->select(
                    'tnewsletterempfaenger',
                    'kKunde',
                    (int)$_SESSION['Kunde']->kKunde
                );
            }
            if ((isset($oNewsletterEmpfaenger->cEmail) && strlen($oNewsletterEmpfaenger->cEmail) > 0)
                || (isset($oNewsletterEmpfaengerKunde->kKunde) && $oNewsletterEmpfaengerKunde->kKunde > 0)
            ) {
                $cFehler = Shop::Lang()->get('newsletterExists', 'errorMessages');
            } else {
                // CheckBox Spezialfunktion ausführen
                $oCheckBox->triggerSpecialFunction(
                    CHECKBOX_ORT_NEWSLETTERANMELDUNG,
                    $kKundengruppe,
                    true,
                    $_POST,
                    ['oKunde' => $oKunde]
                );
                $oCheckBox->checkLogging(CHECKBOX_ORT_NEWSLETTERANMELDUNG, $kKundengruppe, $_POST, true);

                unset($oNewsletterEmpfaenger);

                // Neuen Newsletterempfaenger hinzufuegen
                $oNewsletterEmpfaenger           = new stdClass();
                $oNewsletterEmpfaenger->kSprache = Shop::getLanguage();
                $oNewsletterEmpfaenger->kKunde   = isset($_SESSION['Kunde']->kKunde)
                    ? (int)$_SESSION['Kunde']->kKunde
                    : 0;
                $oNewsletterEmpfaenger->nAktiv   = 0;
                // Double OPT nur für unregistrierte? --> Kunden brauchen nichts bestaetigen
                if (isset($_SESSION['Kunde']->kKunde)
                    && $_SESSION['Kunde']->kKunde > 0
                    && $Einstellungen['newsletter']['newsletter_doubleopt'] === 'U'
                ) {
                    $oNewsletterEmpfaenger->nAktiv = 1;
                }
                $oNewsletterEmpfaenger->cAnrede   = $oKunde->cAnrede;
                $oNewsletterEmpfaenger->cVorname  = $oKunde->cVorname;
                $oNewsletterEmpfaenger->cNachname = $oKunde->cNachname;
                $oNewsletterEmpfaenger->cEmail    = $oKunde->cEmail;
                // OptCode erstellen und ueberpruefen
                // Werte für $dbfeld 'cOptCode','cLoeschCode'

                $oNewsletterEmpfaenger->cOptCode           = create_NewsletterCode('cOptCode', $oKunde->cEmail);
                $oNewsletterEmpfaenger->cLoeschCode        = create_NewsletterCode('cLoeschCode', $oKunde->cEmail);
                $oNewsletterEmpfaenger->dEingetragen       = 'NOW()';
                $oNewsletterEmpfaenger->dLetzterNewsletter = '_DBNULL_';

                executeHook(HOOK_NEWSLETTER_PAGE_EMPFAENGEREINTRAGEN, [
                    'oNewsletterEmpfaenger' => $oNewsletterEmpfaenger
                ]);

                Shop::Container()->getDB()->insert('tnewsletterempfaenger', $oNewsletterEmpfaenger);
                // Protokollieren (hinzufuegen)
                $oNewsletterEmpfaengerHistory               = new stdClass();
                $oNewsletterEmpfaengerHistory->kSprache     = Shop::getLanguage();
                $oNewsletterEmpfaengerHistory->kKunde       = isset($_SESSION['Kunde']->kKunde)
                    ? (int)$_SESSION['Kunde']->kKunde
                    : 0;
                $oNewsletterEmpfaengerHistory->cAnrede      = $oKunde->cAnrede;
                $oNewsletterEmpfaengerHistory->cVorname     = $oKunde->cVorname;
                $oNewsletterEmpfaengerHistory->cNachname    = $oKunde->cNachname;
                $oNewsletterEmpfaengerHistory->cEmail       = $oKunde->cEmail;
                $oNewsletterEmpfaengerHistory->cOptCode     = $oNewsletterEmpfaenger->cOptCode;
                $oNewsletterEmpfaengerHistory->cLoeschCode  = $oNewsletterEmpfaenger->cLoeschCode;
                $oNewsletterEmpfaengerHistory->cAktion      = 'Eingetragen';
                $oNewsletterEmpfaengerHistory->dEingetragen = 'NOW()';
                $oNewsletterEmpfaengerHistory->dAusgetragen = '_DBNULL_';
                $oNewsletterEmpfaengerHistory->dOptCode     = '_DBNULL_';
                $oNewsletterEmpfaengerHistory->cRegIp       = $oKunde->cRegIp;

                $kNewsletterEmpfaengerHistory = Shop::Container()->getDB()->insert(
                    'tnewsletterempfaengerhistory',
                    $oNewsletterEmpfaengerHistory
                );

                executeHook(HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN, [
                    'oNewsletterEmpfaengerHistory' => $oNewsletterEmpfaengerHistory
                ]);

                if (($Einstellungen['newsletter']['newsletter_doubleopt'] === 'U'
                        && !$_SESSION['Kunde']->kKunde)
                    || $Einstellungen['newsletter']['newsletter_doubleopt'] === 'A'
                ) {
                    $oNewsletterEmpfaenger->cLoeschURL     = Shop::getURL() .
                        '/newsletter.php?lang=' . $_SESSION['cISOSprache'] . '&lc=' .
                        $oNewsletterEmpfaenger->cLoeschCode;
                    $oNewsletterEmpfaenger->cFreischaltURL = Shop::getURL() .
                        '/newsletter.php?lang=' . $_SESSION['cISOSprache'] . '&fc=' .
                        $oNewsletterEmpfaenger->cOptCode;
                    $oObjekt = new stdClass();
                    $oObjekt->tkunde               = $_SESSION['Kunde'] ?? null;
                    $oObjekt->NewsletterEmpfaenger = $oNewsletterEmpfaenger;

                    $mail = sendeMail(MAILTEMPLATE_NEWSLETTERANMELDEN, $oObjekt);
                    // UPDATE
                    $_upd                 = new stdClass();
                    $_upd->cEmailBodyHtml = $mail->bodyHtml;
                    Shop::Container()->getDB()->update(
                        'tnewsletterempfaengerhistory',
                        'kNewsletterEmpfaengerHistory',
                        $kNewsletterEmpfaengerHistory,
                        $_upd
                    );

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
    $Einstellungen = Shop::getSettings([CONF_NEWSLETTER]);
    $nPlausi_arr   = [];
    if ($Einstellungen['newsletter']['newsletter_sicherheitscode'] !== 'N' && !FormHelper::validateCaptcha($_POST)) {
        $nPlausi_arr['captcha'] = 2;
    }

    return $nPlausi_arr;
}

/**
 * @param int $kKunde
 * @return bool
 */
function pruefeObBereitsAbonnent(int $kKunde)
{
    if ($kKunde > 0) {
        $oNewsletterEmpfaenger = Shop::Container()->getDB()->select('tnewsletterempfaenger', 'kKunde', $kKunde);

        return isset($oNewsletterEmpfaenger->kKunde) && $oNewsletterEmpfaenger->kKunde > 0;
    }

    return false;
}

/**
 * @param int    $kKundengruppe
 * @param string $cKundengruppeKey
 * @return bool
 */
function pruefeNLHistoryKundengruppe(int $kKundengruppe, $cKundengruppeKey)
{
    if (strlen($cKundengruppeKey) > 0) {
        $kKundengruppe_arr    = [];
        $cKundengruppeKey_arr = explode(';', $cKundengruppeKey);
        foreach ($cKundengruppeKey_arr as $_cKundengruppeKey) {
            if ((int)$_cKundengruppeKey > 0 || (strlen($_cKundengruppeKey) > 0 && (int)$_cKundengruppeKey === 0)) {
                $kKundengruppe_arr[] = (int)$_cKundengruppeKey;
            }
        }
        // Für alle sichtbar
        if (in_array(0, $kKundengruppe_arr, true)) {
            return true;
        }
        if ($kKundengruppe > 0 && in_array($kKundengruppe, $kKundengruppe_arr, true)) {
            return true;
        }
    }

    return false;
}
