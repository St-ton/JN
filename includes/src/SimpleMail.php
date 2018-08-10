<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class SimpleMail
 */
class SimpleMail
{
    /**
     * E-Mail des Absenders
     *
     * @var string
     */
    private $cVerfasserMail;

    /**
     * Name des Absenders
     *
     * @var string
     */
    private $cVerfasserName;

    /**
     * Betreff der E-Mail
     *
     * @var string
     */
    private $cBetreff;

    /**
     * HTML Inhalt der E-Mail
     *
     * @var string
     */
    private $cBodyHTML;

    /**
     * Text Inhalt der E-Mail
     *
     * @var string
     */
    private $cBodyText;

    /**
     * Pfade zu den Dateien die angehangen werden sollen
     *
     * @var array
     */
    private $cAnhang_arr = [];

    /**
     * Versandmethode
     *
     * @var string
     */
    private $cMethod;

    /**
     * SMTP Benutzer
     *
     * @var string
     */
    private $cSMTPUser;

    /**
     * SMTP Passwort
     *
     * @var string
     */
    private $cSMTPPass;

    /**
     * SMTP Port
     *
     * @var int
     */
    private $cSMTPPort = 25;

    /**
     * SMTP Host
     *
     * @var string
     */
    private $cSMTPHost;

    /**
     * SMTP Auth nutzen 0/1
     *
     * @var int
     */
    private $cSMTPAuth;

    /**
     * Pfad zu Sendmail
     *
     * @var string
     */
    private $cSendMailPfad;

    /**
     * Error Log
     *
     * @var array
     */
    private $cErrorLog = [];

    /**
     * @var bool
     */
    private $valid = true;

    /**
     *
     * @param bool  $bShopMail
     * @param array $cMailEinstellungen_arr
     */
    public function __construct(bool $bShopMail = true, array $cMailEinstellungen_arr = [])
    {
        if ($bShopMail === true) {
            $config = Shop::getSettings([CONF_EMAILS])['emails'];

            $this->cMethod        = $config['email_methode'];
            $this->cSendMailPfad  = $config['email_sendmail_pfad'];
            $this->cSMTPHost      = $config['email_smtp_hostname'];
            $this->cSMTPPort      = $config['email_smtp_port'];
            $this->cSMTPAuth      = $config['email_smtp_auth'];
            $this->cSMTPUser      = $config['email_smtp_user'];
            $this->cSMTPPass      = $config['email_smtp_pass'];
            $this->cVerfasserName = $config['email_master_absender_name'];
            $this->cVerfasserMail = $config['email_master_absender'];
        } elseif (!empty($cMailEinstellungen_arr)) {
            if (isset($cMailEinstellungen_arr['cMethod']) && !empty($cMailEinstellungen_arr['cMethod'])) {
                $this->valid = $this->setMethod($cMailEinstellungen_arr['cMethod']);
            }

            $this->cSendMailPfad = $cMailEinstellungen_arr['cSendMailPfad'];
            $this->cSMTPHost     = $cMailEinstellungen_arr['cSMTPHost'];
            $this->cSMTPPort     = (int)$cMailEinstellungen_arr['cSMTPPort'];
            $this->cSMTPAuth     = $cMailEinstellungen_arr['cSMTPAuth'];
            $this->cSMTPUser     = $cMailEinstellungen_arr['cSMTPUser'];
            $this->cSMTPPass     = $cMailEinstellungen_arr['cSMTPPass'];

            if (isset($cMailEinstellungen_arr['cVerfasserName']) && !empty($cMailEinstellungen_arr['cVerfasserName'])) {
                $this->setVerfasserName($cMailEinstellungen_arr['cVerfasserName']);
            }

            if (isset($cMailEinstellungen_arr['cVerfasserMail']) && !empty($cMailEinstellungen_arr['cVerfasserMail'])) {
                $this->valid = $this->setVerfasserMail($cMailEinstellungen_arr['cVerfasserMail']);
            }
        } else {
            $this->valid = false;
        }
    }

    /**
     * Anhang hinzufügen
     * array('cName' => 'Mein Anhang', 'cPath' => '/pfad/zu/meiner/datei.txt');
     *
     * @param string $cName
     * @param string $cPath
     * @param string $cEncoding
     * @param string $cType
     * @return bool
     */
    public function addAttachment(
        string $cName,
        string $cPath,
        string $cEncoding = 'base64',
        string $cType = 'application/octet-stream'
    ): bool {
        if (!empty($cName) && file_exists($cPath)) {
            $cAnhang_arr              = [];
            $cAnhang_arr['cName']     = $cName;
            $cAnhang_arr['cPath']     = $cPath;
            $cAnhang_arr['cEncoding'] = $cEncoding;
            $cAnhang_arr['cType']     = $cType;
            $this->cAnhang_arr[]      = $cAnhang_arr;

            return true;
        }

        return false;
    }

    /**
     * Validierung der Daten
     *
     * @throws Exception
     * @return bool
     */
    public function validate(): bool
    {
        if (!$this->valid) {
            $this->setErrorLog('cConfig', 'Konfiguration fehlerhaft');
        }
        if (empty($this->cVerfasserMail) || empty($this->cVerfasserName)) {
            $this->setErrorLog('cVerfasserMail', 'Verfasser nicht gesetzt!');
        }
        if (empty($this->cBodyHTML) && empty($this->cBodyText)) {
            $this->setErrorLog('cBody', 'Inhalt der E-Mail nicht gesetzt!');
        }
        if (empty($this->cBetreff)) {
            $this->setErrorLog('cBetreff', 'Betreff nicht gesetzt!');
        }
        if (empty($this->cMethod)) {
            $this->setErrorLog('cMethod', 'Versandmethode nicht gesetzt!');
        } else {
            switch ($this->cMethod) {
                case 'PHP Mail()':
                case 'sendmail':
                    if (empty($this->cSendMailPfad)) {
                        $this->setErrorLog('cSendMailPfad', 'SendMailPfad nicht gesetzt!!');
                    }
                    break;
                case 'QMail':
                    break;
                case 'smtp':
                    if (empty($this->cSMTPAuth) || empty($this->cSMTPHost) || empty($this->cSMTPPass) || empty($this->cSMTPUser)) {
                        $this->setErrorLog('SMTP', 'SMTP Daten nicht gesetzt!');
                    }
                    break;
            }
        }

        $cErrorLog = $this->getErrorLog();

        return empty($cErrorLog);
    }

    /**
     * E-Mail verschicken
     *
     * @param array $cEmpfaenger_arr
     * @param array $cCC_arr
     * @param array $cBCC_arr
     * @param array $cReply_arr
     * @return bool
     * @throws Exception
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function send(array $cEmpfaenger_arr, $cCC_arr = [], $cBCC_arr = [], $cReply_arr = []): bool
    {
        if ($this->validate() !== true) {
            return false;
        }
        $oPHPMailer           = new \PHPMailer\PHPMailer\PHPMailer();
        $oPHPMailer->CharSet  = JTL_CHARSET;
        $oPHPMailer->Timeout  = SOCKET_TIMEOUT;
        $oPHPMailer->From     = $this->cVerfasserMail;
        $oPHPMailer->Sender   = $this->cVerfasserMail;
        $oPHPMailer->FromName = $this->cVerfasserName;

        if (!empty($cEmpfaenger_arr)) {
            foreach ($cEmpfaenger_arr as $cEmpfaenger) {
                $oPHPMailer->addAddress($cEmpfaenger['cMail'], $cEmpfaenger['cName']);
            }
        }
        if (!empty($cCC_arr)) {
            foreach ($cCC_arr as $cCC) {
                $oPHPMailer->addCC($cCC['cMail'], $cCC['cName']);
            }
        }
        if (!empty($cBCC_arr)) {
            foreach ($cBCC_arr as $cBCC) {
                $oPHPMailer->addBCC($cBCC['cMail'], $cBCC['cName']);
            }
        }
        if (!empty($cReply_arr)) {
            foreach ($cReply_arr as $cReply) {
                $oPHPMailer->addReplyTo($cReply['cMail'], $cReply['cName']);
            }
        }

        $oPHPMailer->Subject = $this->cBetreff;

        switch ($this->cMethod) {
            case 'mail':
                $oPHPMailer->isMail();
                break;
            case 'sendmail':
                $oPHPMailer->isSendmail();
                $oPHPMailer->Sendmail = $this->cSendMailPfad;
                break;
            case 'qmail':
                $oPHPMailer->isQmail();
                break;
            case 'smtp':
                $oPHPMailer->isSMTP();
                $oPHPMailer->Host          = $this->cSMTPHost;
                $oPHPMailer->Port          = $this->cSMTPPort;
                $oPHPMailer->SMTPKeepAlive = true;
                $oPHPMailer->SMTPAuth      = $this->cSMTPAuth;
                $oPHPMailer->Username      = $this->cSMTPUser;
                $oPHPMailer->Password      = $this->cSMTPPass;
                break;
        }

        if (!empty($this->cBodyHTML)) {
            $oPHPMailer->isHTML(true);
            $oPHPMailer->Body    = $this->cBodyHTML;
            $oPHPMailer->AltBody = $this->cBodyText;
        } else {
            $oPHPMailer->isHTML(false);
            $oPHPMailer->Body = $this->cBodyText;
        }
        foreach ($this->cAnhang_arr as $cAnhang_arr) {
            $oPHPMailer->addAttachment(
                $cAnhang_arr['cPath'],
                $cAnhang_arr['cName'],
                $cAnhang_arr['cEncoding'],
                $cAnhang_arr['cType']
            );
        }
        $bSent = $oPHPMailer->send();
        $oPHPMailer->clearAddresses();

        return $bSent;
    }

    /**
     * @return string|null
     */
    public function getVerfasserMail()
    {
        return $this->cVerfasserMail;
    }

    /**
     *
     * @return string|null
     */
    public function getVerfasserName()
    {
        return $this->cVerfasserName;
    }

    /**
     *
     * @return string|null
     */
    public function getBetreff()
    {
        return $this->cBetreff;
    }

    /**
     *
     * @return string|null
     */
    public function getBodyHTML()
    {
        return $this->cBodyHTML;
    }

    /**
     *
     * @return string|null
     */
    public function getBodyText()
    {
        return $this->cBodyText;
    }

    /**
     * @param string $cVerfasserMail
     * @return bool
     */
    public function setVerfasserMail(string $cVerfasserMail): bool
    {
        if (filter_var($cVerfasserMail, FILTER_VALIDATE_EMAIL)) {
            $this->cVerfasserMail = $cVerfasserMail;

            return true;
        }

        return false;
    }

    /**
     * @param string $cVerfasserName
     * @return $this
     */
    public function setVerfasserName(string $cVerfasserName): self
    {
        $this->cVerfasserName = $cVerfasserName;

        return $this;
    }

    /**
     * @param string $cBetreff
     * @return $this
     */
    public function setBetreff(string $cBetreff): self
    {
        $this->cBetreff = $cBetreff;

        return $this;
    }

    /**
     * @param string $cBodyHTML
     * @return $this
     */
    public function setBodyHTML(string $cBodyHTML): self
    {
        $this->cBodyHTML = $cBodyHTML;

        return $this;
    }

    /**
     * @param string $cBodyText
     * @return $this
     */
    public function setBodyText(string $cBodyText): self
    {
        $this->cBodyText = $cBodyText;

        return $this;
    }

    /**
     * @return null
     */
    public function getErrorInfo()
    {
        return null;
    }

    /**
     * @return array
     */
    public function getErrorLog(): array
    {
        return $this->cErrorLog;
    }

    /**
     * @param string $cKey
     * @param mixed  $cValue
     */
    public function setErrorLog($cKey, $cValue)
    {
        $this->cErrorLog[$cKey] = $cValue;
    }

    /**
     * @return string|null
     */
    public function getMethod()
    {
        return $this->cMethod;
    }

    /**
     * @param string $cMethod
     * @return bool
     */
    public function setMethod(string $cMethod): bool
    {
        if ($cMethod === 'QMail' || $cMethod === 'smtp' || $cMethod === 'PHP Mail()' || $cMethod === 'sendmail') {
            $this->cMethod = $cMethod;

            return true;
        }

        return false;
    }
    /**
     * Prüft ob eine die angegebende Email in temailblacklist vorhanden ist
     * Gibt true zurück, falls Email geblockt, ansonsten false
     *
     * @param string $cEmail
     * @return bool
     */
    public static function checkBlacklist(string $cEmail): bool
    {
        $cEmail = strtolower(StringHandler::filterXSS($cEmail));
        if (StringHandler::filterEmailAddress($cEmail) === false) {
            return true;
        }
        $conf = Shop::getSettings([CONF_EMAILBLACKLIST]);
        if ($conf['emailblacklist']['blacklist_benutzen'] !== 'Y') {
            return false;
        }
        $blacklist = Shop::Container()->getDB()->query(
            'SELECT cEmail FROM temailblacklist',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($blacklist as $item) {
            if (strpos($item->cEmail, '*') !== false) {
                preg_match('/' . str_replace('*', "[a-z0-9\-\_\.\@\+]*", $item->cEmail) . '/', $cEmail, $hits);
                // Blocked
                if (isset($hits[0]) && strlen($cEmail) === strlen($hits[0])) {
                    // Email schonmal geblockt worden?
                    $block = Shop::Container()->getDB()->select('temailblacklistblock', 'cEmail', $cEmail);
                    if (!empty($block->cEmail)) {
                        $_upd                = new stdClass();
                        $_upd->dLetzterBlock = 'now()';
                        Shop::Container()->getDB()->update('temailblacklistblock', 'cEmail', $cEmail, $_upd);
                    } else {
                        // temailblacklistblock Eintrag
                        $block                = new stdClass();
                        $block->cEmail        = $cEmail;
                        $block->dLetzterBlock = 'now()';
                        Shop::Container()->getDB()->insert('temailblacklistblock', $block);
                    }

                    return true;
                }
            } elseif (strtolower($item->cEmail) === strtolower($cEmail)) {
                // Email schonmal geblockt worden?
                $block = Shop::Container()->getDB()->select('temailblacklistblock', 'cEmail', $cEmail);

                if (!empty($block->cEmail)) {
                    $_upd                = new stdClass();
                    $_upd->dLetzterBlock = 'now()';
                    Shop::Container()->getDB()->update('temailblacklistblock', 'cEmail', $cEmail, $_upd);
                } else {
                    // temailblacklistblock Eintrag
                    $block                = new stdClass();
                    $block->cEmail        = $cEmail;
                    $block->dLetzterBlock = 'now()';
                    Shop::Container()->getDB()->insert('temailblacklistblock', $block);
                }

                return true;
            }
        }

        return false;
    }
}
