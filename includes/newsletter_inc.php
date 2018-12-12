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
function create_NewsletterCode($dbfeld, $email): string
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
function unique_NewsletterCode($dbfeld, $code): bool
{
    $res = Shop::Container()->getDB()->select('tnewsletterempfaenger', $dbfeld, $code);

    return !(isset($res->kNewsletterEmpfaenger) && $res->kNewsletterEmpfaenger > 0);
}

/**
 * @param Kunde|stdClass $oKunde
 * @param bool  $bPruefeDaten
 * @return stdClass
 */
function fuegeNewsletterEmpfaengerEin($oKunde, $bPruefeDaten = false): stdClass
{
    global $cFehler, $cHinweis;

    $Einstellungen              = Shop::getSettings([CONF_NEWSLETTER]);
    $oPlausi                    = new stdClass();
    $oPlausi->nPlausi_arr       = [];
    $oNewsletterEmpfaengerKunde = null;

    if (!$bPruefeDaten || StringHandler::filterEmailAddress($oKunde->cEmail) !== false) {
        $oPlausi->nPlausi_arr = newsletterAnmeldungPlausi();
        $kKundengruppe        = \Session\Session::getCustomerGroup()->getID();
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
                $history               = new stdClass();
                $history->kSprache     = Shop::getLanguage();
                $history->kKunde       = isset($_SESSION['Kunde']->kKunde)
                    ? (int)$_SESSION['Kunde']->kKunde
                    : 0;
                $history->cAnrede      = $oKunde->cAnrede;
                $history->cVorname     = $oKunde->cVorname;
                $history->cNachname    = $oKunde->cNachname;
                $history->cEmail       = $oKunde->cEmail;
                $history->cOptCode     = $oNewsletterEmpfaenger->cOptCode;
                $history->cLoeschCode  = $oNewsletterEmpfaenger->cLoeschCode;
                $history->cAktion      = 'Eingetragen';
                $history->dEingetragen = 'NOW()';
                $history->dAusgetragen = '_DBNULL_';
                $history->dOptCode     = '_DBNULL_';
                $history->cRegIp       = $oKunde->cRegIp;

                $kNewsletterEmpfaengerHistory = Shop::Container()->getDB()->insert(
                    'tnewsletterempfaengerhistory',
                    $history
                );

                executeHook(HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN, [
                    'oNewsletterEmpfaengerHistory' => $history
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
                    $oObjekt                               = new stdClass();
                    $oObjekt->tkunde                       = $_SESSION['Kunde'] ?? null;
                    $oObjekt->NewsletterEmpfaenger         = $oNewsletterEmpfaenger;

                    $mail = sendeMail(MAILTEMPLATE_NEWSLETTERANMELDEN, $oObjekt);
                    Shop::Container()->getDB()->update(
                        'tnewsletterempfaengerhistory',
                        'kNewsletterEmpfaengerHistory',
                        $kNewsletterEmpfaengerHistory,
                        (object)['cEmailBodyHtml' => $mail->bodyHtml]
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
 * @return array
 */
function newsletterAnmeldungPlausi(): array
{
    $res = [];
    if (Shop::getConfigValue(CONF_NEWSLETTER, 'newsletter_sicherheitscode') !== 'N'
        && !FormHelper::validateCaptcha($_POST)
    ) {
        $res['captcha'] = 2;
    }

    return $res;
}

/**
 * @param int $kKunde
 * @return bool
 */
function pruefeObBereitsAbonnent(int $kKunde): bool
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
function pruefeNLHistoryKundengruppe(int $kKundengruppe, $cKundengruppeKey): bool
{
    if (strlen($cKundengruppeKey) > 0) {
        $kKundengruppe_arr    = [];
        $cKundengruppeKey_arr = explode(';', $cKundengruppeKey);
        foreach ($cKundengruppeKey_arr as $_cKundengruppeKey) {
            if ((int)$_cKundengruppeKey > 0 || (strlen($_cKundengruppeKey) > 0 && (int)$_cKundengruppeKey === 0)) {
                $kKundengruppe_arr[] = (int)$_cKundengruppeKey;
            }
        }
        if (in_array(0, $kKundengruppe_arr, true)) {
            return true;
        }
        if ($kKundengruppe > 0 && in_array($kKundengruppe, $kKundengruppe_arr, true)) {
            return true;
        }
    }

    return false;
}
