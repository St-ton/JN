<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_BLOWFISH . 'xtea.class.php';

/**
 * Class Kunde
 */
class Kunde
{
    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var int
     */
    public $kKundengruppe;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var int
     */
    public $nRegistriert;

    /**
     * @var float
     */
    public $fRabatt = 0.00;

    /**
     * @var float
     */
    public $fGuthaben = 0.00;

    /**
     * @var string
     */
    public $cKundenNr;

    /**
     * @var string
     */
    public $cPasswort;

    /**
     * @var string
     */
    public $cAnrede = '';

    /**
     * @var string
     */
    public $cAnredeLocalized = '';

    /**
     * @var string
     */
    public $cTitel;

    /**
     * @var string
     */
    public $cVorname;

    /**
     * @var string
     */
    public $cNachname;

    /**
     * @var string
     */
    public $cFirma;

    /**
     * @var string
     */
    public $cStrasse = '';

    /**
     * @var string
     */
    public $cHausnummer;

    /**
     * @var string
     */
    public $cAdressZusatz;

    /**
     * @var string
     */
    public $cPLZ = '';

    /**
     * @var string
     */
    public $cOrt = '';

    /**
     * @var string
     */
    public $cBundesland = '';

    /**
     * @var string
     */
    public $cLand;

    /**
     * @var string
     */
    public $cTel;

    /**
     * @var string
     */
    public $cMobil;

    /**
     * @var string
     */
    public $cFax;

    /**
     * @var string
     */
    public $cMail = '';

    /**
     * @var string
     */
    public $cUSTID = '';

    /**
     * @var string
     */
    public $cWWW = '';

    /**
     * @var string
     */
    public $cSperre = 'N';

    /**
     * @var string
     */
    public $cNewsletter = '';

    /**
     * @var string
     */
    public $dGeburtstag;

    /**
     * @var string
     */
    public $dGeburtstag_formatted;

    /**
     * @var string
     */
    public $cHerkunft = '';

    /**
     * @var string
     */
    public $cAktiv;

    /**
     * @var string
     */
    public $cAbgeholt;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var string
     */
    public $dVeraendert;

    /**
     * @var array
     */
    public $cKundenattribut_arr;

    /**
     * @var string
     */
    public $cZusatz;

    /**
     * @var string
     */
    public $cGuthabenLocalized;

    /**
     * @var string
     */
    public $angezeigtesLand;

    /**
     * @var string
     */
    public $dErstellt_DE;

    /**
     * @var string
     */
    public $cPasswortKlartext;

    /**
     * @var int
     */
    public $nLoginversuche = 0;

    /**
     * @param int $kKunde
     */
    public function __construct(int $kKunde = null)
    {
        if ($kKunde > 0) {
            $this->loadFromDB($kKunde);
        }
    }

    /**
     * get customer by email address
     *
     * @param string $cEmail
     * @return Kunde|null
     */
    public function holRegKundeViaEmail($cEmail): ?Kunde
    {
        if (strlen($cEmail) > 0) {
            $oKundeTMP = Shop::Container()->getDB()->select(
                'tkunde',
                'cMail',
                StringHandler::filterXSS($cEmail),
                null,
                null,
                null,
                null,
                false,
                'kKunde'
            );

            if ($oKundeTMP !== null && isset($oKundeTMP->kKunde) && $oKundeTMP->kKunde > 0) {
                return new self($oKundeTMP->kKunde);
            }
        }

        return null;
    }

    /**
     * @param array $post
     * @return bool|int - true, if captcha verified or no captcha necessary
     */
    public function verifyLoginCaptcha($post)
    {
        $conf          = Shop::getSettings([CONF_KUNDEN]);
        $cBenutzername = $post['email'];
        if (isset($conf['kunden']['kundenlogin_max_loginversuche'])
            && $conf['kunden']['kundenlogin_max_loginversuche'] !== ''
            && $conf['kunden']['kundenlogin_max_loginversuche'] > 1
            && strlen($cBenutzername) > 0
        ) {
            $attempts = Shop::Container()->getDB()->select(
                'tkunde',
                'cMail',
                StringHandler::filterXSS($cBenutzername),
                'nRegistriert',
                1,
                null,
                null,
                false,
                'nLoginversuche'
            );
            if ($attempts !== null
                && isset($attempts->nLoginversuche)
                && (int)$attempts->nLoginversuche >= (int)$conf['kunden']['kundenlogin_max_loginversuche']
            ) {
                if (FormHelper::validateCaptcha($_POST)) {
                    return true;
                }

                return (int)$attempts->nLoginversuche;
            }
        }

        return true;
    }

    /**
     * @param string $cBenutzername
     * @param string $cPasswort
     * @return int 1 = Alles O.K., 2 = Kunde ist gesperrt
     * @throws Exception
     */
    public function holLoginKunde($cBenutzername, $cPasswort): int
    {
        $passwordService = Shop::Container()->getPasswordService();
        if (strlen($cBenutzername) > 0 && strlen($cPasswort) > 0) {
            $oUser = $this->checkCredentials($cBenutzername, $cPasswort);
            if ($oUser === false) {
                return 0;
            }
            if (isset($oUser->cSperre) && $oUser->cSperre === 'Y') {
                return 2; // Kunde ist gesperrt
            }
            if (isset($oUser->cAktiv) && $oUser->cAktiv === 'N') {
                return 3; // Kunde ist nicht aktiv
            }
            if (isset($oUser->kKunde) && $oUser->kKunde > 0) {
                foreach (get_object_vars($oUser) as $k => $v) {
                    $this->$k = $v;
                }
                $this->angezeigtesLand = Sprache::getCountryCodeByCountryName($this->cLand);
                $this->holeKundenattribute();
                // check if password has to be updated because of PASSWORD_DEFAULT method changes or using old md5 hash
                if (isset($oUser->cPasswort) && $passwordService->needsRehash($oUser->cPasswort)) {
                    $_upd            = new stdClass();
                    $_upd->cPasswort = $passwordService->hash($cPasswort);
                    Shop::Container()->getDB()->update('tkunde', 'kKunde', (int)$oUser->kKunde, $_upd);
                }
            }
            executeHook(HOOK_KUNDE_CLASS_HOLLOGINKUNDE, [
                'oKunde'        => &$this,
                'oUser'         => $oUser,
                'cBenutzername' => $cBenutzername,
                'cPasswort'     => $cPasswort
            ]);
            if ($this->kKunde > 0) {
                $this->entschluesselKundendaten();
                // Anrede mappen
                $this->cAnredeLocalized   = self::mapSalutation($this->cAnrede, $this->kSprache);
                $this->cGuthabenLocalized = $this->gibGuthabenLocalized();

                return 1;
            }
        }

        return 0;
    }

    /**
     * @param string $user
     * @param string $pass
     * @return bool|stdClass
     * @throws Exception
     */
    public function checkCredentials($user, $pass)
    {
        $passwordService = Shop::Container()->getPasswordService();
        $db              = Shop::Container()->getDB();
        $customer        = $db->select(
            'tkunde',
            'cMail',
            $user,
            'nRegistriert',
            1,
            null,
            null,
            false,
            '*, date_format(dGeburtstag, \'%d.%m.%Y\') AS dGeburtstag_formatted'
        );
        if (!$customer) {
            return false;
        }
        $customer->kKunde                = (int)$customer->kKunde;
        $customer->kKundengruppe         = (int)$customer->kKundengruppe;
        $customer->kSprache              = (int)$customer->kSprache;
        $customer->nLoginversuche        = (int)$customer->nLoginversuche;
        $customer->nRegistriert          = (int)$customer->nRegistriert;
        $customer->dGeburtstag_formatted = $customer->dGeburtstag_formatted !== '00.00.0000'
            ? $customer->dGeburtstag_formatted
            : '';

        if (!$passwordService->verify($pass, $customer->cPasswort)) {
            $tries = ++$customer->nLoginversuche;
            Shop::Container()->getDB()->update('tkunde', 'cMail', $user, (object)['nLoginversuche' => $tries]);
            return false;
        }
        $update = false;
        if ($passwordService->needsRehash($customer->cPasswort)) {
            $customer->cPasswort = $passwordService->hash($pass);
            $update = true;
        }

        if ($customer->nLoginversuche > 0) {
            $customer->nLoginversuche = 0;
            $update = true;
        }
        if ($update) {
            $update = (array)$customer;
            unset($update['dGeburtstag_formatted']);
            Shop::Container()->getDB()->update('tkunde', 'kKunde', $customer->kKunde, (object)$update);
        }

        return $customer;
    }

    /**
     * @return string
     */
    public function gibGuthabenLocalized(): string
    {
        return Preise::getLocalizedPriceString($this->fGuthaben);
    }

    /**
     * @param int $kKunde
     * @return $this
     */
    public function loadFromDB(int $kKunde): self
    {
        if ($kKunde <= 0) {
            return $this;
        }
        $obj = Shop::Container()->getDB()->select('tkunde', 'kKunde', $kKunde);
        if ($obj !== null && isset($obj->kKunde) && $obj->kKunde > 0) {
            $members = array_keys(get_object_vars($obj));
            foreach ($members as $member) {
                $this->$member = $obj->$member;
            }
            $this->kSprache         = (int)$this->kSprache;
            $this->cAnredeLocalized = self::mapSalutation($this->cAnrede, $this->kSprache);
            $this->angezeigtesLand  = Sprache::getCountryCodeByCountryName($this->cLand);
            $this->holeKundenattribute()->entschluesselKundendaten();
            $this->kKunde         = (int)$this->kKunde;
            $this->kKundengruppe  = (int)$this->kKundengruppe;
            $this->kSprache       = (int)$this->kSprache;
            $this->nLoginversuche = (int)$this->nLoginversuche;
            $this->nRegistriert   = (int)$this->nRegistriert;

            $this->dGeburtstag_formatted = $this->dGeburtstag === null
                ? ''
                : date_format(date_create($this->dGeburtstag), 'd.m.Y');

            $this->cGuthabenLocalized = $this->gibGuthabenLocalized();
            $cDatum_arr               = DateHelper::getDateParts($this->dErstellt ?? '');
            if (count($cDatum_arr) > 0) {
                $this->dErstellt_DE       = $cDatum_arr['cTag'] . '.' .
                    $cDatum_arr['cMonat'] . '.' .
                    $cDatum_arr['cJahr'];
            }
            executeHook(HOOK_KUNDE_CLASS_LOADFROMDB);
        }

        return $this;
    }

    /**
     * encrypt customer data
     *
     * @return $this
     */
    private function verschluesselKundendaten(): self
    {
        $cryptoService = Shop::Container()->getCryptoService();
        
        $this->cNachname = $cryptoService->encryptXTEA(trim($this->cNachname));
        $this->cFirma    = $cryptoService->encryptXTEA(trim($this->cFirma));
        $this->cZusatz   = $cryptoService->encryptXTEA(trim($this->cZusatz));
        $this->cStrasse  = $cryptoService->encryptXTEA(trim($this->cStrasse));

        return $this;
    }

    /**
     * decrypt customer data
     *
     * @return $this
     */
    private function entschluesselKundendaten(): self
    {
        $cryptoService = Shop::Container()->getCryptoService();
        
        $this->cNachname = trim($cryptoService->decryptXTEA($this->cNachname));
        $this->cFirma    = trim($cryptoService->decryptXTEA($this->cFirma));
        $this->cZusatz   = trim($cryptoService->decryptXTEA($this->cZusatz));
        $this->cStrasse  = trim($cryptoService->decryptXTEA($this->cStrasse));

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        executeHook(HOOK_KUNDE_DB_INSERT, ['oKunde' => &$this]);

        $this->verschluesselKundendaten();
        $obj                 = new stdClass();
        $obj->kKundengruppe  = $this->kKundengruppe;
        $obj->kSprache       = $this->kSprache;
        $obj->cKundenNr      = $this->cKundenNr;
        $obj->cPasswort      = $this->cPasswort;
        $obj->cAnrede        = $this->cAnrede;
        $obj->cTitel         = $this->cTitel;
        $obj->cVorname       = $this->cVorname;
        $obj->cNachname      = $this->cNachname;
        $obj->cFirma         = $this->cFirma;
        $obj->cZusatz        = $this->cZusatz;
        $obj->cStrasse       = $this->cStrasse;
        $obj->cHausnummer    = $this->cHausnummer;
        $obj->cAdressZusatz  = $this->cAdressZusatz;
        $obj->cPLZ           = $this->cPLZ;
        $obj->cOrt           = $this->cOrt;
        $obj->cBundesland    = $this->cBundesland;
        $obj->cLand          = $this->cLand;
        $obj->cTel           = $this->cTel;
        $obj->cMobil         = $this->cMobil;
        $obj->cFax           = $this->cFax;
        $obj->cMail          = $this->cMail;
        $obj->cUSTID         = $this->cUSTID;
        $obj->cWWW           = $this->cWWW;
        $obj->cSperre        = $this->cSperre;
        $obj->fGuthaben      = $this->fGuthaben;
        $obj->cNewsletter    = $this->cNewsletter;
        $obj->fRabatt        = $this->fRabatt;
        $obj->cHerkunft      = $this->cHerkunft;
        $obj->dErstellt      = $this->dErstellt ?? '_DBNULL_';
        $obj->dVeraendert    = $this->dVeraendert ?? 'NOW()';
        $obj->cAktiv         = $this->cAktiv;
        $obj->cAbgeholt      = $this->cAbgeholt;
        $obj->nRegistriert   = $this->nRegistriert;
        $obj->nLoginversuche = $this->nLoginversuche;
        $obj->dGeburtstag    = DateHelper::convertDateToMysqlStandard($this->dGeburtstag);

        $obj->cLand   = $this->pruefeLandISO($obj->cLand);
        $this->kKunde = Shop::Container()->getDB()->insert('tkunde', $obj);
        $this->entschluesselKundendaten();

        $this->cAnredeLocalized   = self::mapSalutation($this->cAnrede, $this->kSprache);
        $this->cGuthabenLocalized = $this->gibGuthabenLocalized();
        $cDatum_arr               = DateHelper::getDateParts($this->dErstellt);
        $this->dErstellt_DE       = $cDatum_arr['cTag'] . '.' . $cDatum_arr['cMonat'] . '.' . $cDatum_arr['cJahr'];

        return $this->kKunde;
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $this->dGeburtstag           = DateHelper::convertDateToMysqlStandard($this->dGeburtstag);
        $this->dGeburtstag_formatted = $this->dGeburtstag === '_DBNULL_'
            ? ''
            : DateTime::createFromFormat('Y-m-d', $this->dGeburtstag)->format('d.m.Y');

        $this->verschluesselKundendaten();
        $obj = ObjectHelper::copyMembers($this);

        $cKundenattribut_arr = [];
        if (is_array($obj->cKundenattribut_arr)) {
            $cKundenattribut_arr = $obj->cKundenattribut_arr;
        }

        unset(
            $obj->cKundenattribut_arr,
            $obj->cPasswort,
            $obj->angezeigtesLand,
            $obj->dGeburtstag_formatted,
            $obj->Anrede,
            $obj->cAnredeLocalized,
            $obj->cGuthabenLocalized,
            $obj->dErstellt_DE,
            $obj->cPasswortKlartext
        );
        if ($obj->dGeburtstag === '') {
            $obj->dGeburtstag = '_DBNULL_';
        }

        $obj->cLand       = $this->pruefeLandISO($obj->cLand);
        $obj->dVeraendert = 'NOW()';
        $cReturn          = Shop::Container()->getDB()->update('tkunde', 'kKunde', $obj->kKunde, $obj);
        if (is_array($cKundenattribut_arr) && count($cKundenattribut_arr) > 0) {
            $obj->cKundenattribut_arr = $cKundenattribut_arr;
        }
        if ($obj->dGeburtstag === '_DBNULL_') {
            $obj->dGeburtstag = '';
        }
        $this->entschluesselKundendaten();

        $this->cAnredeLocalized   = self::mapSalutation($this->cAnrede, $this->kSprache);
        $this->cGuthabenLocalized = $this->gibGuthabenLocalized();
        $cDatum_arr               = DateHelper::getDateParts($this->dErstellt);
        $this->dErstellt_DE       = $cDatum_arr['cTag'] . '.' . $cDatum_arr['cMonat'] . '.' . $cDatum_arr['cJahr'];

        return $cReturn;
    }

    /**
     * get customer attributes
     *
     * @return $this
     */
    public function holeKundenattribute(): self
    {
        $this->cKundenattribut_arr = [];
        $oKundenattribut_arr       = Shop::Container()->getDB()->selectAll(
            'tkundenattribut',
            'kKunde',
            (int)$this->kKunde,
            '*',
            'kKundenAttribut'
        );
        foreach ($oKundenattribut_arr as $oKundenattribut) {
            $this->cKundenattribut_arr[$oKundenattribut->kKundenfeld] = $oKundenattribut;
        }

        return $this;
    }

    /**
     * check country ISO code
     *
     * @param string $cLandISO
     * @return string
     */
    public function pruefeLandISO(string $cLandISO): string
    {
        preg_match('/[a-zA-Z]{2}/', $cLandISO, $cTreffer1_arr);
        if (strlen($cTreffer1_arr[0]) !== strlen($cLandISO)) {
            $cISO = Sprache::getIsoCodeByCountryName($cLandISO);
            if ($cISO !== 'noISO' && strlen($cISO) > 0) {
                $cLandISO = $cISO;
            }
        }

        return $cLandISO;
    }

    /**
     * @return $this
     */
    public function kopiereSession(): self
    {
        foreach (array_keys(get_object_vars($_SESSION['Kunde'])) as $oElement) {
            $this->$oElement = $_SESSION['Kunde']->$oElement;
        }
        $this->cAnredeLocalized = self::mapSalutation($this->cAnrede, $this->kSprache);

        return $this;
    }

    /**
     * encrypt all customer data
     *
     * @return $this
     */
    public function verschluesselAlleKunden(): self
    {
        foreach (Shop::Container()->getDB()->query(
            'SELECT * FROM tkunde',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        ) as $oKunden) {
            if ($oKunden->kKunde > 0) {
                unset($oKundeTMP);
                $oKundeTMP = new self($oKunden->kKunde);
                $oKundeTMP->updateInDB();
            }
        }

        return $this;
    }

    /**
     * @param Kunde $oKundeOne
     * @param Kunde $oKundeTwo
     * @return bool
     */
    public static function isEqual($oKundeOne, $oKundeTwo): bool
    {
        if (is_object($oKundeOne) && is_object($oKundeTwo)) {
            $cMemberOne_arr = array_keys(get_class_vars(get_class($oKundeOne)));
            $cMemberTwo_arr = array_keys(get_class_vars(get_class($oKundeTwo)));

            if (count($cMemberOne_arr) !== count($cMemberTwo_arr)) {
                return false;
            }
            foreach ($cMemberOne_arr as $cMemberOne) {
                if (!isset($oKundeTwo->{$cMemberOne})) {
                    return false;
                }
                $xValueOne = $oKundeOne->{$cMemberOne};
                $xValueTwo = null;
                foreach ($cMemberTwo_arr as $cMemberTwo) {
                    if ($cMemberOne == $cMemberTwo) {
                        $xValueTwo = $oKundeTwo->{$cMemberOne};
                    }
                }
                if ($xValueOne != $xValueTwo) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param null|string $password
     * @return $this
     * @throws Exception
     */
    public function updatePassword($password = null): self
    {
        $passwordService = Shop::Container()->getPasswordService();
        if ($password === null) {
            $cPasswortKlartext = $passwordService->generate(12);
            $this->cPasswort   = $passwordService->hash($cPasswortKlartext);

            $_upd                 = new stdClass();
            $_upd->cPasswort      = $this->cPasswort;
            $_upd->nLoginversuche = 0;
            Shop::Container()->getDB()->update('tkunde', 'kKunde', (int)$this->kKunde, $_upd);

            $obj                 = new stdClass();
            $obj->tkunde         = $this;
            $obj->neues_passwort = $cPasswortKlartext;
            sendeMail(MAILTEMPLATE_PASSWORT_VERGESSEN, $obj);
        } else {
            $this->cPasswort = $passwordService->hash($password);

            $_upd                 = new stdClass();
            $_upd->cPasswort      = $this->cPasswort;
            $_upd->nLoginversuche = 0;
            Shop::Container()->getDB()->update('tkunde', 'kKunde', (int)$this->kKunde, $_upd);
        }

        return $this;
    }

    /**
     * @param int $length
     * @return bool|string
     * @deprecated since 5.0.0
     * @throws Exception
     */
    public function generatePassword(int $length = 12)
    {
        return Shop::Container()->getPasswordService()->generate($length);
    }

    /**
     * @param string $password
     * @return false|string
     * @deprecated since 5.0.0
     * @throws Exception
     */
    public function generatePasswordHash($password)
    {
        return Shop::Container()->getPasswordService()->hash($password);
    }

    /**
     * creates a random string for password reset validation
     *
     * @return bool - true if valid account
     * @throws Exception
     */
    public function prepareResetPassword(): bool
    {
        $cryptoService = Shop::Container()->getCryptoService();
        if (!$this->kKunde) {
            return false;
        }
        $key        = $cryptoService->randomString(32);
        $linkHelper = Shop::Container()->getLinkService();
        $expires    = new DateTime();
        $interval   = new DateInterval('P1D');
        $expires->add($interval);
        Shop::Container()->getDB()->queryPrepared(
            'INSERT INTO tpasswordreset(kKunde, cKey, dExpires)
                VALUES (:kKunde, :cKey, :dExpires)
                ON DUPLICATE KEY UPDATE cKey = :cKey, dExpires = :dExpires',
            [
                'kKunde'   => $this->kKunde,
                'cKey'     => $key,
                'dExpires' => $expires->format(DateTime::ATOM),
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );

        require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
        $linkParams             = ['fpwh' => $key];
        $obj                    = new stdClass();
        $obj->tkunde            = $this;
        $obj->passwordResetLink = $linkHelper->getStaticRoute('pass.php') .
            '?' . http_build_query($linkParams, null, '&');
        $obj->cHash             = $key;
        $obj->neues_passwort    = 'Es ist leider ein Fehler aufgetreten. Bitte kontaktieren Sie uns.';
        sendeMail(MAILTEMPLATE_PASSWORT_VERGESSEN, $obj);

        return true;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return (int)$this->kKunde;
    }

    /**
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->kKunde > 0;
    }

    /**
     * @param string $cAnrede
     * @param int    $kSprache
     * @param int    $kKunde
     * @return mixed
     * @former mappeKundenanrede()
     */
    public static function mapSalutation($cAnrede, int $kSprache, int $kKunde = 0)
    {
        if (($kSprache > 0 || $kKunde > 0) && strlen($cAnrede) > 0) {
            if ($kSprache === 0 && $kKunde > 0) {
                $oKunde = Shop::Container()->getDB()->queryPrepared(
                    'SELECT kSprache
                        FROM tkunde
                        WHERE kKunde = :cid',
                    ['cid' => $kKunde],
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (isset($oKunde->kSprache) && $oKunde->kSprache > 0) {
                    $kSprache = (int)$oKunde->kSprache;
                }
            }
            $cISOSprache = '';
            if ($kSprache > 0) { // Kundensprache, falls gesetzt
                $oSprache = Shop::Container()->getDB()->select('tsprache', 'kSprache', $kSprache);
                if (isset($oSprache->kSprache) && $oSprache->kSprache > 0) {
                    $cISOSprache = $oSprache->cISO;
                }
            } else { // Ansonsten Standardsprache
                $oSprache = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
                if (isset($oSprache->kSprache) && $oSprache->kSprache > 0) {
                    $cISOSprache = $oSprache->cISO;
                }
            }
            $cName       = $cAnrede === 'm' ? 'salutationM' : 'salutationW';
            $oSprachWert = Shop::Container()->getDB()->queryPrepared(
                'SELECT tsprachwerte.cWert
                    FROM tsprachwerte
                    JOIN tsprachiso
                        ON tsprachiso.cISO = :ciso
                    WHERE tsprachwerte.kSprachISO = tsprachiso.kSprachISO
                        AND tsprachwerte.cName = :cname',
                ['ciso' => $cISOSprache, 'cname' => $cName],
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($oSprachWert->cWert) && strlen($oSprachWert->cWert) > 0) {
                $cAnrede = $oSprachWert->cWert;
            }
        }

        return $cAnrede;
    }

    /**
     * @param string $issuerType
     * @param int $issuerID
     * @param bool $force
     * @param bool $confirmationMail
     */
    public function deleteAccount(string $issuerType, int $issuerID, bool $force = false, bool $confirmationMail = false): void
    {
        $customerID = $this->getID();

        if (empty($customerID)) {
            return;
        }

        if ($force) {
            $this->erasePersonalData($issuerType, $issuerID);

            return;
        }

        $openOrders = $this->getOpenOrders();
        if (!$openOrders) {
            $this->erasePersonalData($issuerType, $issuerID);
            $logMessage = \sprintf('Account with ID kKunde = %s deleted', $customerID);
        } else {
            Shop::Container()->getDB()->update('tkunde', 'kKunde', $customerID, (object)[
                'cPasswort'    => '',
                'nRegistriert' => 0,
            ]);
            $logMessage = \sprintf('Account with ID kKunde = %s deleted, but had %s open orders with %s still in cancellation time. Account is deactivated until all orders are completed.',
                $customerID,
                $openOrders->openOrders,
                $openOrders->openOrderCancellations);

            (new GeneralDataProtection\Journal())->addEntry(
                $issuerType,
                $customerID,
                GeneralDataProtection\Journal::ACTION_CUSTOMER_DEACTIVATED,
                $logMessage,
                (object)['kKunde' => $customerID]
            );
        }

        Shop::Container()->getLogService()->notice($logMessage);

        if ($confirmationMail) {
            sendeMail(MAILTEMPLATE_KUNDENACCOUNT_GELOESCHT, (object)['tkunde' => \Session\Session::getCustomer()]);
        }
    }

    /**
     * @return false|stdClass
     */
    public function getOpenOrders()
    {
        $cancellationTime = 14;
        $db               = Shop::Container()->getDB();
        $customerID       = $this->getID();

        $openOrders = $db->queryPrepared(
            'SELECT COUNT(kBestellung) AS orderCount
                    FROM tbestellung
                    WHERE cStatus NOT IN (:orderSent, :orderCanceled)
                        AND kKunde = :customerId',
            [
                'customerId' => $customerID,
                'orderSent' => BESTELLUNG_STATUS_VERSANDT,
                'orderCanceled' => BESTELLUNG_STATUS_STORNO,
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );
        $openOrderCancellations = $db->queryPrepared(
            'SELECT COUNT(kBestellung) AS orderCount
                    FROM tbestellung
                    WHERE kKunde = :customerId
                        AND cStatus = :orderSent
                        AND DATE(dVersandDatum) > DATE_SUB(NOW(), INTERVAL :cancellationTime DAY)',
            [
                'customerId' => $customerID,
                'orderSent' => BESTELLUNG_STATUS_VERSANDT,
                'cancellationTime' => $cancellationTime,
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );

        if (!empty($openOrders->orderCount) || !empty($openOrderCancellations->orderCount))
        {
            return (object)[
                'openOrders' => $openOrders->orderCount,
                'openOrderCancellations' => $openOrderCancellations->orderCount
            ];
        }

        return false;
    }
    /**
     * @param string $issuerType
     * @param int $issuerID
     */
    private function erasePersonalData(string $issuerType, int $issuerID): void
    {
        $customerID = $this->getID();
        $db         = Shop::Container()->getDB();
        if (empty($customerID)) {
            return;
        }
        $anonymous = 'Anonym';

        $db->delete('tlieferadresse', 'kKunde', $customerID);
        $db->delete('trechnungsadresse', 'kKunde', $customerID);
        $db->delete('tkundenattribut', 'kKunde', $customerID);
        $db->delete('tkunde', 'kKunde', $customerID);
        $db->delete('tkundendatenhistory', 'kKunde', $customerID);
        $db->delete('tkundenkontodaten', 'kKunde', $customerID);
        $db->delete('tzahlungsinfo', 'kKunde', $customerID);
        $db->delete('tkundenwerbenkunden', 'kKunde', $customerID);
        $db->delete('tkundenwerbenkundenbonus', 'kKunde', $customerID);
        $db->delete('tkuponneukunde', 'cEmail', $this->cMail);
        $db->delete('tkontakthistory', 'cMail', $this->cMail);
        $db->delete('tproduktanfragehistory', 'cMail', $this->cMail);
        $db->delete('tverfuegbarkeitsbenachrichtigung', 'cMail', $this->cMail);

        $obj        = new stdClass();
        $obj->cName = $anonymous;
        $db->update('tbewertung', 'kKunde', $customerID, $obj);
        $obj->cEmail = $anonymous;
        $db->update('tnewskommentar', 'kKunde', $customerID, $obj);
        $obj        = new stdClass();
        $obj->cMail = $anonymous;
        $db->update('tkuponkunde', 'kKunde', $customerID, $obj);

        //newsletter
        $db->queryPrepared(
            'DELETE FROM tnewsletterempfaenger
                WHERE cEmail = :email
                    OR kKunde = :customerID',
            ['email' => $this->cMail, 'customerID' => $customerID],
            \DB\ReturnType::AFFECTED_ROWS
        );

        $obj            = new stdClass();
        $obj->cAnrede   = $anonymous;
        $obj->cVorname  = $anonymous;
        $obj->cNachname = $anonymous;
        $obj->cEmail    = $anonymous;
        $db->update('tnewsletterempfaengerhistory', 'kKunde', $customerID, $obj);
        $db->update('tnewsletterempfaengerhistory', 'cEmail', $this->cMail, $obj);

        $db->insert('tnewsletterempfaengerhistory', (object)[
            'kSprache'     => $this->kSprache,
            'kKunde'       => $customerID,
            'cAnrede'      => $anonymous,
            'cVorname'     => $anonymous,
            'cNachname'    => $anonymous,
            'cEmail'       => $anonymous,
            'cOptCode'     => '',
            'cLoeschCode'  => '',
            'cAktion'      => 'Geloescht',
            'dAusgetragen' => 'NOW()',
            'dEingetragen' => '',
            'dOptCode'     => '',
        ]);

        //wishlist
        $db->queryPrepared(
            'DELETE twunschliste, twunschlistepos, twunschlisteposeigenschaft, twunschlisteversand
                FROM twunschliste
                LEFT JOIN twunschlistepos
                    ON twunschliste.kWunschliste = twunschlistepos.kWunschliste
                LEFT JOIN twunschlisteposeigenschaft
                    ON twunschlisteposeigenschaft.kWunschlistePos = twunschlistepos.kWunschlistePos
                LEFT JOIN twunschlisteversand
                    ON twunschlisteversand.kWunschliste = twunschliste.kWunschliste
                WHERE twunschliste.kKunde = :customerID',
            ['customerID' => $customerID],
            \DB\ReturnType::DEFAULT
        );

        //cart
        $db->queryPrepared(
            'DELETE twarenkorbpers, twarenkorbperspos, twarenkorbpersposeigenschaft
                FROM twarenkorbpers
                LEFT JOIN twarenkorbperspos
                    ON twarenkorbperspos.kWarenkorbPers = twarenkorbpers.kWarenkorbPers
                LEFT JOIN twarenkorbpersposeigenschaft
                    ON twarenkorbpersposeigenschaft.kWarenkorbPersPos = twarenkorbperspos.kWarenkorbPersPos
                WHERE twarenkorbpers.kKunde = :customerID',
            ['customerID' => $customerID],
            \DB\ReturnType::DEFAULT
        );

        $logMessage = \sprintf('Account with ID kKunde = %s deleted', $customerID);
        (new GeneralDataProtection\Journal())->addEntry(
            $issuerType,
            $issuerID,
            GeneralDataProtection\Journal::ACTION_CUSTOMER_DELETED,
            $logMessage,
            (object)['kKunde' => $customerID]
        );
    }
}
