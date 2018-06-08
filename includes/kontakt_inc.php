<?php

/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return array
 */
function gibFehlendeEingabenKontaktformular()
{
    $ret  = [];
    $conf = Shop::getSettings([CONF_KONTAKTFORMULAR, CONF_GLOBAL]);
    if (!$_POST['nachricht']) {
        $ret['nachricht'] = 1;
    }
    if (!$_POST['email']) {
        $ret['email'] = 1;
    }
    if (!$_POST['subject']) {
        $ret['subject'] = 1;
    }
    if (!valid_email($_POST['email'])) {
        $ret['email'] = 2;
    }
    if (pruefeEmailblacklist($_POST['email'])) {
        $ret['email'] = 3;
    }
    if (!$_POST['vorname'] && $conf['kontakt']['kontakt_abfragen_vorname'] === 'Y') {
        $ret['vorname'] = 1;
    }
    if (!$_POST['nachname'] && $conf['kontakt']['kontakt_abfragen_nachname'] === 'Y') {
        $ret['nachname'] = 1;
    }
    if (!$_POST['firma'] && $conf['kontakt']['kontakt_abfragen_firma'] === 'Y') {
        $ret['firma'] = 1;
    }
    if ($conf['kontakt']['kontakt_abfragen_fax'] === 'Y') {
        $ret['fax'] = StringHandler::checkPhoneNumber($_POST['fax']);
    }
    if ($conf['kontakt']['kontakt_abfragen_tel'] === 'Y') {
        $ret['tel'] = StringHandler::checkPhoneNumber($_POST['tel']);
    }
    if ($conf['kontakt']['kontakt_abfragen_mobil'] === 'Y') {
        $ret['mobil'] = StringHandler::checkPhoneNumber($_POST['mobil']);
    }
    if ($conf['kontakt']['kontakt_abfragen_captcha'] !== 'N' && !validateCaptcha($_POST)) {
        $ret['captcha'] = 2;
    }

    return $ret;
}

/**
 * @return stdClass
 */
function baueKontaktFormularVorgaben()
{
    $Nachricht = new stdClass();
    if (isset($_SESSION['Kunde'])) {
        $Nachricht->cAnrede   = $_SESSION['Kunde']->cAnrede;
        $Nachricht->cVorname  = $_SESSION['Kunde']->cVorname;
        $Nachricht->cNachname = $_SESSION['Kunde']->cNachname;
        $Nachricht->cFirma    = $_SESSION['Kunde']->cFirma;
        $Nachricht->cMail     = $_SESSION['Kunde']->cMail;
        $Nachricht->cTel      = $_SESSION['Kunde']->cTel;
        $Nachricht->cMobil    = $_SESSION['Kunde']->cMobil;
        $Nachricht->cFax      = $_SESSION['Kunde']->cFax;
    }
    $Nachricht->kKontaktBetreff = isset($_POST['subject'])
        ? (int)$_POST['subject']
        : null;
    $Nachricht->cNachricht      = isset($_POST['nachricht'])
        ? StringHandler::filterXSS($_POST['nachricht'])
        : null;

    if (isset($_POST['anrede']) && $_POST['anrede']) {
        $Nachricht->cAnrede = StringHandler::filterXSS($_POST['anrede']);
    }
    if (isset($_POST['vorname']) && $_POST['vorname']) {
        $Nachricht->cVorname = StringHandler::filterXSS($_POST['vorname']);
    }
    if (isset($_POST['nachname']) && $_POST['nachname']) {
        $Nachricht->cNachname = StringHandler::filterXSS($_POST['nachname']);
    }
    if (isset($_POST['firma']) && $_POST['firma']) {
        $Nachricht->cFirma = StringHandler::filterXSS($_POST['firma']);
    }
    if (isset($_POST['email']) && $_POST['email']) {
        $Nachricht->cMail = StringHandler::filterXSS($_POST['email']);
    }
    if (isset($_POST['fax']) && $_POST['fax']) {
        $Nachricht->cFax = StringHandler::filterXSS($_POST['fax']);
    }
    if (isset($_POST['tel']) && $_POST['tel']) {
        $Nachricht->cTel = StringHandler::filterXSS($_POST['tel']);
    }
    if (isset($_POST['mobil']) && $_POST['mobil']) {
        $Nachricht->cMobil = StringHandler::filterXSS($_POST['mobil']);
    }
    if (isset($_POST['subject']) && $_POST['subject']) {
        $Nachricht->kKontaktBetreff = StringHandler::filterXSS($_POST['subject']);
    }
    if (isset($_POST['nachricht']) && $_POST['nachricht']) {
        $Nachricht->cNachricht = StringHandler::filterXSS($_POST['nachricht']);
    }
    if (isset($Nachricht->cAnrede) && strlen($Nachricht->cAnrede) === 1) {
        if ($Nachricht->cAnrede === 'm') {
            $Nachricht->cAnredeLocalized = Shop::Lang()->get('salutationM');
        } elseif ($Nachricht->cAnrede === 'w') {
            $Nachricht->cAnredeLocalized = Shop::Lang()->get('salutationW');
        }
    }
    if (!isset($Nachricht->cAnrede)) {
        $Nachricht->cAnrede = '';
    }
    if (!isset($Nachricht->cVorname)) {
        $Nachricht->cVorname = '';
    }
    if (!isset($Nachricht->cNachname)) {
        $Nachricht->cNachname = '';
    }
    if (!isset($Nachricht->cFirma)) {
        $Nachricht->cFirma = '';
    }
    if (!isset($Nachricht->cMail)) {
        $Nachricht->cMail = '';
    }
    if (!isset($Nachricht->cTel)) {
        $Nachricht->cTel = '';
    }
    if (!isset($Nachricht->cMobil)) {
        $Nachricht->cMobil = '';
    }
    if (!isset($Nachricht->cFax)) {
        $Nachricht->cFax = '';
    }

    return $Nachricht;
}

/**
 * @return bool
 */
function pruefeBetreffVorhanden()
{
    $kKundengruppe = Session::CustomerGroup()->getID();
    if (!$kKundengruppe) {
        $kKundengruppe = (int)$_SESSION['Kunde']->kKundengruppe;
        if (!$kKundengruppe) {
            $kKundengruppe = Kundengruppe::getDefaultGroupID();
        }
    }

    $oBetreff_arr = Shop::Container()->getDB()->query(
        "SELECT kKontaktBetreff
            FROM tkontaktbetreff
            WHERE FIND_IN_SET('" . $kKundengruppe . "', REPLACE(cKundengruppen, ';', ',')) > 0
                OR cKundengruppen = '0'",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    return (is_array($oBetreff_arr) && count($oBetreff_arr) > 0);
}

/**
 * @return int|bool
 */
function bearbeiteNachricht()
{
    $betreff = isset($_POST['subject'])
        ? Shop::Container()->getDB()->select('tkontaktbetreff', 'kKontaktBetreff', (int)$_POST['subject'])
        : null;
    if (empty($betreff->kKontaktBetreff)) {
        return false;
    }
    $betreffSprache               = Shop::Container()->getDB()->select(
        'tkontaktbetreffsprache',
        'kKontaktBetreff',
        (int)$betreff->kKontaktBetreff,
        'cISOSprache',
        Shop::getLanguageCode()
    );
    $Objekt                       = new stdClass();
    $Objekt->tnachricht           = baueKontaktFormularVorgaben();
    $Objekt->tnachricht->cBetreff = $betreffSprache->cName;

    $conf     = Shop::getSettings([CONF_KONTAKTFORMULAR, CONF_GLOBAL]);
    $from     = new stdClass();
    $from_arr = Shop::Container()->getDB()->selectAll('temailvorlageeinstellungen', 'kEmailvorlage', 11);
    $mail     = new stdClass();
    if (is_array($from_arr) && count($from_arr)) {
        foreach ($from_arr as $f) {
            $from->{$f->cKey} = $f->cValue;
        }
        $mail->fromEmail = $from->cEmailOut;
        $mail->fromName  = $from->cEmailSenderName;
    }
    $mail->toEmail      = $betreff->cMail;
    $mail->toName       = $conf['global']['global_shopname'];
    $mail->replyToEmail = $Objekt->tnachricht->cMail;
    $mail->replyToName  = '';
    if (isset($Objekt->tnachricht->cVorname)) {
        $mail->replyToName .= $Objekt->tnachricht->cVorname . ' ';
    }
    if (isset($Objekt->tnachricht->cNachname)) {
        $mail->replyToName .= $Objekt->tnachricht->cNachname;
    }
    if (isset($Objekt->tnachricht->cFirma)) {
        $mail->replyToName .= ' - ' . $Objekt->tnachricht->cFirma;
    }
    $Objekt->mail = $mail;
    if (isset($_SESSION['kSprache']) && !isset($Objekt->tkunde)) {
        if (!isset($Objekt->tkunde)) {
            $Objekt->tkunde = new stdClass();
        }
        $Objekt->tkunde->kSprache = $_SESSION['kSprache'];
    }
    sendeMail(MAILTEMPLATE_KONTAKTFORMULAR, $Objekt);

    if ($conf['kontakt']['kontakt_kopiekunde'] === 'Y') {
        $mail->toEmail = $Objekt->tnachricht->cMail;
        $mail->toName  = $mail->toEmail;
        if (isset($Objekt->tnachricht->cVorname)
            || isset($Objekt->tnachricht->cNachname)
            || isset($Objekt->tnachricht->cFirma)
        ) {
            $mail->toName = '';
            if (isset($Objekt->tnachricht->cVorname)) {
                $mail->toName .= $Objekt->tnachricht->cVorname . ' ';
            }
            if (isset($Objekt->tnachricht->cNachname)) {
                $mail->toName .= $Objekt->tnachricht->cNachname;
            }
            if (isset($Objekt->tnachricht->cFirma)) {
                $mail->toName .= ' - ' . $Objekt->tnachricht->cFirma;
            }
        }
        $mail->replyToEmail = $Objekt->tnachricht->cMail;
        $mail->replyToName  = $mail->toName;
        $Objekt->mail       = $mail;
        sendeMail(MAILTEMPLATE_KONTAKTFORMULAR, $Objekt);
    }
    $KontaktHistory                  = new stdClass();
    $KontaktHistory->kKontaktBetreff = $betreff->kKontaktBetreff;
    $KontaktHistory->kSprache        = $_SESSION['kSprache'];
    $KontaktHistory->cAnrede         = $Objekt->tnachricht->cAnrede ?? null;
    $KontaktHistory->cVorname        = $Objekt->tnachricht->cVorname ?? null;
    $KontaktHistory->cNachname       = $Objekt->tnachricht->cNachname ?? null;
    $KontaktHistory->cFirma          = $Objekt->tnachricht->cFirma ?? null;
    $KontaktHistory->cTel            = $Objekt->tnachricht->cTel ?? null;
    $KontaktHistory->cMobil          = $Objekt->tnachricht->cMobil ?? null;
    $KontaktHistory->cFax            = $Objekt->tnachricht->cFax ?? null;
    $KontaktHistory->cMail           = $Objekt->tnachricht->cMail ?? null;
    $KontaktHistory->cNachricht      = $Objekt->tnachricht->cNachricht ?? null;
    $KontaktHistory->cIP             = gibIP();
    $KontaktHistory->dErstellt       = 'now()';

    return Shop::Container()->getDB()->insert('tkontakthistory', $KontaktHistory);
}

/**
 * @param int $min
 * @return bool
 */
function floodSchutz($min)
{
    if (!$min) {
        return false;
    }
    $min     = (int)$min;
    $history = Shop::Container()->getDB()->executeQueryPrepared(
        "SELECT kKontaktHistory 
            FROM tkontakthistory 
            WHERE cIP = :ip 
                AND date_sub(now(), INTERVAL :min MINUTE) < dErstellt",
        ['ip' => Shop::Container()->getDB()->escape(gibIP()), 'min' => $min],
        \DB\ReturnType::SINGLE_OBJECT
    );

    return (isset($history->kKontaktHistory) && $history->kKontaktHistory > 0);
}

if (!function_exists('baueFormularVorgaben')) {
    /**
     * @return stdClass
     */
    function baueFormularVorgaben()
    {
        return baueKontaktFormularVorgaben();
    }
}
