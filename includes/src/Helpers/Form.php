<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Helpers;

use Exception;
use function Functional\none;
use JTL\DB\ReturnType;
use JTL\Customer\Kundengruppe;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\SimpleMail;
use stdClass;

/**
 * Class Form
 * @package JTL\Helpers
 * @since 5.0.0
 */
class Form
{
    /**
     * @param array $requestData
     * @return bool
     * @since 5.0.0
     */
    public static function validateCaptcha(array $requestData): bool
    {
        $valid = Shop::Container()->getCaptchaService()->validate($requestData);

        if ($valid) {
            Frontend::set('bAnti_spam_already_checked', true);
        } else {
            Shop::Smarty()->assign('bAnti_spam_failed', true);
        }

        return $valid;
    }

    /**
     * create a hidden input field for xsrf validation
     *
     * @return string
     * @throws Exception
     * @since 5.0.0
     */
    public static function getTokenInput(): string
    {
        if (!isset($_SESSION['jtl_token'])) {
            $_SESSION['jtl_token'] = Shop::Container()->getCryptoService()->randomString(32);
        }

        return '<input type="hidden" class="jtl_token" name="jtl_token" value="' . $_SESSION['jtl_token'] . '" />';
    }

    /**
     * validate token from POST/GET
     *
     * @return bool
     * @since 5.0.0
     */
    public static function validateToken(): bool
    {
        if (!isset($_SESSION['jtl_token'])) {
            return false;
        }

        $token = $_POST['jtl_token'] ?? $_GET['token'] ?? null;

        if ($token === null) {
            return false;
        }

        return Shop::Container()->getCryptoService()->stableStringEquals($_SESSION['jtl_token'], $token);
    }

    /**
     * @param array $fehlendeAngaben
     * @return int
     * @since 5.0.0
     */
    public static function eingabenKorrekt(array $fehlendeAngaben): int
    {
        return (int)none(
            $fehlendeAngaben,
            function ($e) {
                return $e > 0;
            }
        );
    }

    /**
     * @return array
     * @former gibFehlendeEingabenKontaktformular()
     * @since 5.0.0
     */
    public static function getMissingContactFormData(): array
    {
        $ret  = [];
        $conf = Shop::getSettings([\CONF_KONTAKTFORMULAR, \CONF_GLOBAL]);
        if (!$_POST['nachricht']) {
            $ret['nachricht'] = 1;
        }
        if (!$_POST['email']) {
            $ret['email'] = 1;
        }
        if (!$_POST['subject']) {
            $ret['subject'] = 1;
        }
        if (Text::filterEmailAddress($_POST['email']) === false) {
            $ret['email'] = 2;
        }
        if (SimpleMail::checkBlacklist($_POST['email'])) {
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
            $ret['fax'] = Text::checkPhoneNumber($_POST['fax']);
        }
        if ($conf['kontakt']['kontakt_abfragen_tel'] === 'Y') {
            $ret['tel'] = Text::checkPhoneNumber($_POST['tel']);
        }
        if ($conf['kontakt']['kontakt_abfragen_mobil'] === 'Y') {
            $ret['mobil'] = Text::checkPhoneNumber($_POST['mobil']);
        }
        if ($conf['kontakt']['kontakt_abfragen_captcha'] !== 'N' && !self::validateCaptcha($_POST)) {
            $ret['captcha'] = 2;
        }

        return $ret;
    }

    /**
     * @return stdClass
     * @since 5.0.0
     */
    public static function baueKontaktFormularVorgaben(): stdClass
    {
        $msg = new stdClass();
        if (isset($_SESSION['Kunde'])) {
            $msg->cAnrede   = $_SESSION['Kunde']->cAnrede;
            $msg->cVorname  = $_SESSION['Kunde']->cVorname;
            $msg->cNachname = $_SESSION['Kunde']->cNachname;
            $msg->cFirma    = $_SESSION['Kunde']->cFirma;
            $msg->cMail     = $_SESSION['Kunde']->cMail;
            $msg->cTel      = $_SESSION['Kunde']->cTel;
            $msg->cMobil    = $_SESSION['Kunde']->cMobil;
            $msg->cFax      = $_SESSION['Kunde']->cFax;
        }
        $msg->kKontaktBetreff = isset($_POST['subject'])
            ? (int)$_POST['subject']
            : null;
        $msg->cNachricht      = isset($_POST['nachricht'])
            ? Text::filterXSS($_POST['nachricht'])
            : null;

        if (isset($_POST['anrede']) && $_POST['anrede']) {
            $msg->cAnrede = Text::filterXSS($_POST['anrede']);
        }
        if (isset($_POST['vorname']) && $_POST['vorname']) {
            $msg->cVorname = Text::filterXSS($_POST['vorname']);
        }
        if (isset($_POST['nachname']) && $_POST['nachname']) {
            $msg->cNachname = Text::filterXSS($_POST['nachname']);
        }
        if (isset($_POST['firma']) && $_POST['firma']) {
            $msg->cFirma = Text::filterXSS($_POST['firma']);
        }
        if (isset($_POST['email']) && $_POST['email']) {
            $msg->cMail = Text::filterXSS($_POST['email']);
        }
        if (isset($_POST['fax']) && $_POST['fax']) {
            $msg->cFax = Text::filterXSS($_POST['fax']);
        }
        if (isset($_POST['tel']) && $_POST['tel']) {
            $msg->cTel = Text::filterXSS($_POST['tel']);
        }
        if (isset($_POST['mobil']) && $_POST['mobil']) {
            $msg->cMobil = Text::filterXSS($_POST['mobil']);
        }
        if (isset($_POST['subject']) && $_POST['subject']) {
            $msg->kKontaktBetreff = Text::filterXSS($_POST['subject']);
        }
        if (isset($_POST['nachricht']) && $_POST['nachricht']) {
            $msg->cNachricht = Text::filterXSS($_POST['nachricht']);
        }
        if (isset($msg->cAnrede) && \mb_strlen($msg->cAnrede) === 1) {
            if ($msg->cAnrede === 'm') {
                $msg->cAnredeLocalized = Shop::Lang()->get('salutationM');
            } elseif ($msg->cAnrede === 'w') {
                $msg->cAnredeLocalized = Shop::Lang()->get('salutationW');
            }
        }
        if (!isset($msg->cAnrede)) {
            $msg->cAnrede = '';
        }
        if (!isset($msg->cVorname)) {
            $msg->cVorname = '';
        }
        if (!isset($msg->cNachname)) {
            $msg->cNachname = '';
        }
        if (!isset($msg->cFirma)) {
            $msg->cFirma = '';
        }
        if (!isset($msg->cMail)) {
            $msg->cMail = '';
        }
        if (!isset($msg->cTel)) {
            $msg->cTel = '';
        }
        if (!isset($msg->cMobil)) {
            $msg->cMobil = '';
        }
        if (!isset($msg->cFax)) {
            $msg->cFax = '';
        }

        return $msg;
    }

    /**
     * @return bool
     * @former pruefeBetreffVorhanden()
     * @since 5.0.0
     */
    public static function checkSubject(): bool
    {
        $kKundengruppe = Frontend::getCustomerGroup()->getID();
        if (!$kKundengruppe) {
            $kKundengruppe = (int)$_SESSION['Kunde']->kKundengruppe;
            if (!$kKundengruppe) {
                $kKundengruppe = Kundengruppe::getDefaultGroupID();
            }
        }

        $subjects = Shop::Container()->getDB()->query(
            "SELECT kKontaktBetreff
                FROM tkontaktbetreff
                WHERE FIND_IN_SET('" . $kKundengruppe . "', REPLACE(cKundengruppen, ';', ',')) > 0
                    OR cKundengruppen = '0'",
            ReturnType::ARRAY_OF_OBJECTS
        );

        return \is_array($subjects) && \count($subjects) > 0;
    }

    /**
     * @return int|bool
     * @former bearbeiteNachricht()
     * @since 5.0.0
     */
    public static function editMessage()
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
        $Objekt->tnachricht           = self::baueKontaktFormularVorgaben();
        $Objekt->tnachricht->cBetreff = $betreffSprache->cName;

        $conf    = Shop::getSettings([\CONF_KONTAKTFORMULAR, \CONF_GLOBAL]);
        $from    = new stdClass();
        $senders = Shop::Container()->getDB()->selectAll('temailvorlageeinstellungen', 'kEmailvorlage', 11);
        $mail    = new stdClass();
        if (\is_array($senders) && \count($senders)) {
            foreach ($senders as $f) {
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
        \sendeMail(\MAILTEMPLATE_KONTAKTFORMULAR, $Objekt);

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
            \sendeMail(\MAILTEMPLATE_KONTAKTFORMULAR, $Objekt);
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
        $KontaktHistory->cIP             = Request::getRealIP();
        $KontaktHistory->dErstellt       = 'NOW()';

        return Shop::Container()->getDB()->insert('tkontakthistory', $KontaktHistory);
    }

    /**
     * @param int $min
     * @return bool
     * @since 5.0.0
     */
    public static function checkFloodProtection($min): bool
    {
        if (!$min) {
            return false;
        }
        $min     = (int)$min;
        $history = Shop::Container()->getDB()->executeQueryPrepared(
            'SELECT kKontaktHistory
                FROM tkontakthistory
                WHERE cIP = :ip
                    AND DATE_SUB(NOW(), INTERVAL :min MINUTE) < dErstellt',
            ['ip' => Request::getRealIP(), 'min' => $min],
            ReturnType::SINGLE_OBJECT
        );

        return isset($history->kKontaktHistory) && $history->kKontaktHistory > 0;
    }

    /**
     * @return stdClass
     * @since 5.0.0
     */
    public static function baueFormularVorgaben(): stdClass
    {
        return self::baueKontaktFormularVorgaben();
    }
}
