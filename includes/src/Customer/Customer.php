<?php

namespace JTL\Customer;

use DateInterval;
use DateTime;
use Exception;
use JTL\Catalog\Product\Preise;
use JTL\DB\DbInterface;
use JTL\GeneralDataProtection\Journal;
use JTL\Helpers\Date;
use JTL\Helpers\Form;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\MagicCompatibilityTrait;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Services\JTL\PasswordServiceInterface;
use JTL\Shop;
use JTL\Shopsetting;
use stdClass;
use function Functional\select;

/**
 * Class Customer
 * @package JTL\Customer
 */
class Customer
{
    use MagicCompatibilityTrait;

    public const OK = 1;

    public const ERROR_LOCKED = 2;

    public const ERROR_INACTIVE = 3;

    public const ERROR_CAPTCHA = 4;

    public const ERROR_NOT_ACTIVATED_YET = 5;

    public const ERROR_INVALID_DATA = 0;

    public const CUSTOMER_ANONYM = 'Anonym';

    public const CUSTOMER_DELETE_DONE = 0;

    public const CUSTOMER_DELETE_DEACT = 1;

    public const CUSTOMER_DELETE_NO = 2;

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
     * @var array
     */
    public static array $mapping = [
        'cKundenattribut_arr' => 'CustomerAttributes'
    ];

    /**
     * @var string|null
     */
    protected ?string $dLastLogin = null;

    /**
     * Customer constructor.
     * @param int|null $id
     * @param PasswordServiceInterface|null $passwordService
     * @param DbInterface|null $db
     */
    public function __construct(
        int $id = null,
        private ?PasswordServiceInterface $passwordService = null,
        private ?DbInterface $db = null
    ) {
        $this->passwordService = $passwordService ?? Shop::Container()->getPasswordService();
        $this->db              = $db ?? Shop::Container()->getDB();

        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @return array
     */
    public function __sleep(): array
    {
        return select(\array_keys(\get_object_vars($this)), static function ($e): bool {
            return $e !== 'db' && $e !== 'passwordService';
        });
    }

    /**
     * @return void
     */
    public function __wakeup(): void
    {
        $this->passwordService = Shop::Container()->getPasswordService();
        $this->db              = Shop::Container()->getDB();
    }

    /**
     * get customer by email address
     *
     * @param string $mail
     * @return Customer|null
     */
    public function holRegKundeViaEmail(string $mail): ?Customer
    {
        if ($mail !== '') {
            $data = $this->db->select(
                'tkunde',
                'cMail',
                Text::filterXSS($mail),
                null,
                null,
                null,
                null,
                false,
                'kKunde'
            );

            if ($data !== null && isset($data->kKunde) && $data->kKunde > 0) {
                return new self((int)$data->kKunde);
            }
        }

        return null;
    }

    /**
     * @param array $post
     * @return bool|int - true, if captcha verified or no captcha necessary
     */
    public function verifyLoginCaptcha(array $post)
    {
        $conf = Shop::getSettingValue(\CONF_KUNDEN, 'kundenlogin_max_loginversuche');
        $mail = $post['email'] ?? '';
        if ($mail !== '' && $conf > 1) {
            $attempts = $this->db->getSingleInt(
                'SELECT nLoginversuche
                    FROM tkunde
                    WHERE cMail = :ml AND nRegistriert = 1',
                'nLoginversuche',
                ['ml' => $mail]
            );
            if ($attempts >= (int)$conf) {
                if (Form::validateCaptcha($_POST)) {
                    return true;
                }

                return $attempts;
            }
        }

        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @return int 1 = Alles O.K., 2 = Kunde ist gesperrt
     * @throws Exception
     */
    public function holLoginKunde(string $username, string $password): int
    {
        if ($username === '' || $password === '') {
            return self::ERROR_INVALID_DATA;
        }
        $user = $this->checkCredentials($username, $password);
        if (($state = $this->validateCustomerData($user)) !== self::OK) {
            return $state;
        }
        if ($user->kKunde > 0) {
            $this->initCustomer($user);
        }
        \executeHook(\HOOK_KUNDE_CLASS_HOLLOGINKUNDE, [
            'oKunde'        => &$this,
            'oUser'         => $user,
            'cBenutzername' => $username,
            'cPasswort'     => $password
        ]);
        if ($this->kKunde > 0) {
            $this->entschluesselKundendaten();
            $this->cAnredeLocalized   = self::mapSalutation($this->cAnrede, $this->kSprache);
            $this->cGuthabenLocalized = $this->gibGuthabenLocalized();
            $this->setLastLogin();

            return self::OK;
        }

        return self::ERROR_INVALID_DATA;
    }

    /**
     * @param mixed $user
     * @return int
     */
    private function validateCustomerData($user): int
    {
        if ($user === false) {
            return self::ERROR_INVALID_DATA;
        }
        if ($user->cSperre === 'Y') {
            return self::ERROR_LOCKED;
        }
        if ($user->cAktiv === 'N') {
            return $user->cAbgeholt === 'Y' ? self::ERROR_INACTIVE : self::ERROR_NOT_ACTIVATED_YET;
        }

        return self::OK;
    }

    /**
     * @param stdClass $user
     * @throws Exception
     */
    private function initCustomer(stdClass $user): void
    {
        foreach (\get_object_vars($user) as $k => $v) {
            $this->$k = $v;
        }
        $this->angezeigtesLand = LanguageHelper::getCountryCodeByCountryName($this->cLand);
        // check if password has to be updated because of PASSWORD_DEFAULT method changes or using old md5 hash
        if (isset($user->cPasswort) && $this->passwordService->needsRehash($user->cPasswort)) {
            $upd            = new stdClass();
            $upd->cPasswort = $this->passwordService->hash($user->cPasswort);
            $this->db->update('tkunde', 'kKunde', (int)$user->kKunde, $upd);
        }
    }

    /**
     * @param string $user
     * @param string $pass
     * @return bool|stdClass
     * @throws Exception
     */
    public function checkCredentials(string $user, string $pass)
    {
        $user     = \mb_substr($user, 0, 255);
        $pass     = \mb_substr($pass, 0, 255);
        $customer = $this->db->select(
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

        if (!$this->passwordService->verify($pass, $customer->cPasswort)) {
            $tries = ++$customer->nLoginversuche;
            $this->db->update('tkunde', 'cMail', $user, (object)['nLoginversuche' => $tries]);

            return false;
        }
        $update = false;
        if ($this->passwordService->needsRehash($customer->cPasswort)) {
            $customer->cPasswort = $this->passwordService->hash($pass);
            $update              = true;
        }

        if ($customer->nLoginversuche > 0) {
            $customer->nLoginversuche = 0;
            $update                   = true;
        }
        if ($update) {
            $customerData = (array)$customer;
            unset($customerData['dGeburtstag_formatted']);
            $this->db->update('tkunde', 'kKunde', $customer->kKunde, (object)$customerData);
        }

        return $customer;
    }

    /**
     * @return void
     */
    protected function setLastLogin(): void
    {
        $this->dLastLogin = $this->db->getSingleArray('select NOW() as today')['today'];
        $this->db->queryPrepared(
            'UPDATE tkunde SET dLastLogin = :today WHERE kKunde = :kKunde',
            ['kKunde' => (int)$this->kKunde, 'today' => $this->dLastLogin]
        );
    }

    /**
     * @return string|null
     */
    public function getLastLogin(): ?string
    {
        return $this->dLastLogin;
    }

    /**
     * @return string
     */
    public function gibGuthabenLocalized(): string
    {
        return Preise::getLocalizedPriceString($this->fGuthaben);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function loadFromDB(int $id): self
    {
        if ($id <= 0) {
            return $this;
        }
        $data = $this->db->select('tkunde', 'kKunde', $id);
        if ($data !== null && isset($data->kKunde) && $data->kKunde > 0) {
            $members = \array_keys(\get_object_vars($data));
            foreach ($members as $member) {
                $this->$member = $data->$member;
            }
            $this->kSprache         = (int)$this->kSprache;
            $this->cAnredeLocalized = self::mapSalutation($this->cAnrede, $this->kSprache);
            $this->angezeigtesLand  = LanguageHelper::getCountryCodeByCountryName($this->cLand);
            $this->entschluesselKundendaten();
            $this->kKunde         = (int)$this->kKunde;
            $this->kKundengruppe  = (int)$this->kKundengruppe;
            $this->kSprache       = (int)$this->kSprache;
            $this->nLoginversuche = (int)$this->nLoginversuche;
            $this->nRegistriert   = (int)$this->nRegistriert;

            $this->dGeburtstag_formatted = $this->dGeburtstag === null
                ? ''
                : \date_format(\date_create($this->dGeburtstag), 'd.m.Y');

            $this->cGuthabenLocalized = $this->gibGuthabenLocalized();
            $this->dErstellt_DE       = $this->dErstellt !== null
                ? \date_format(\date_create($this->dErstellt), 'd.m.Y')
                : null;
            \executeHook(\HOOK_KUNDE_CLASS_LOADFROMDB);
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

        $this->cNachname = $cryptoService->encryptXTEA(\trim($this->cNachname ?? ''));
        $this->cFirma    = $cryptoService->encryptXTEA(\trim($this->cFirma ?? ''));
        $this->cZusatz   = $cryptoService->encryptXTEA(\trim($this->cZusatz ?? ''));
        $this->cStrasse  = $cryptoService->encryptXTEA(\trim($this->cStrasse ?? ''));

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

        $this->cNachname = \trim($cryptoService->decryptXTEA($this->cNachname ?? ''));
        $this->cFirma    = \trim($cryptoService->decryptXTEA($this->cFirma ?? ''));
        $this->cZusatz   = \trim($cryptoService->decryptXTEA($this->cZusatz ?? ''));
        $this->cStrasse  = \trim($cryptoService->decryptXTEA($this->cStrasse ?? ''));

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        \executeHook(\HOOK_KUNDE_DB_INSERT, ['oKunde' => &$this]);

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
        $obj->dGeburtstag    = Date::convertDateToMysqlStandard($this->dGeburtstag);
        $obj->cLand          = $this->pruefeLandISO($obj->cLand);
        $this->kKunde        = $this->db->insert('tkunde', $obj);
        $this->entschluesselKundendaten();

        $this->cAnredeLocalized   = self::mapSalutation($this->cAnrede, $this->kSprache);
        $this->cGuthabenLocalized = $this->gibGuthabenLocalized();
        if ($this->dErstellt !== null) {
            if (\mb_convert_case($this->dErstellt, \MB_CASE_LOWER) === 'now()') {
                $this->dErstellt = \date_format(\date_create(), 'Y-m-d');
            }
            $this->dErstellt_DE = \date_format(\date_create($this->dErstellt), 'd.m.Y');
        }

        return $this->kKunde;
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        if ($this->kKunde === null) {
            return 0;
        }
        $this->dGeburtstag           = Date::convertDateToMysqlStandard($this->dGeburtstag);
        $this->dGeburtstag_formatted = $this->dGeburtstag === '_DBNULL_'
            ? ''
            : DateTime::createFromFormat('Y-m-d', $this->dGeburtstag)->format('d.m.Y');

        $this->verschluesselKundendaten();
        $obj = GeneralObject::copyMembers($this);
        unset(
            $obj->cPasswort,
            $obj->angezeigtesLand,
            $obj->dGeburtstag_formatted,
            $obj->Anrede,
            $obj->cAnredeLocalized,
            $obj->cGuthabenLocalized,
            $obj->dErstellt_DE,
            $obj->cPasswortKlartext,
            $obj->dLastLogin
        );
        if ($obj->dGeburtstag === null || $obj->dGeburtstag === '') {
            $obj->dGeburtstag = '_DBNULL_';
        }
        if ($obj->dErstellt === null || $obj->dErstellt === '') {
            $obj->dErstellt = '_DBNULL_';
        }
        $obj->cLand       = $this->pruefeLandISO($obj->cLand);
        $obj->dVeraendert = 'NOW()';
        $return           = $this->db->update('tkunde', 'kKunde', $obj->kKunde, $obj);

        if ($obj->dGeburtstag === '_DBNULL_') {
            $obj->dGeburtstag = '';
        }
        $this->entschluesselKundendaten();

        $this->cAnredeLocalized   = self::mapSalutation($this->cAnrede, $this->kSprache);
        $this->cGuthabenLocalized = $this->gibGuthabenLocalized();
        $this->dErstellt_DE       = $this->dErstellt !== null
                ? \date_format(\date_create($this->dErstellt), 'd.m.Y')
                : null;

        return $return;
    }

    /**
     * check country ISO code
     *
     * @param string $iso
     * @return string
     */
    public function pruefeLandISO(string $iso): string
    {
        \preg_match('/[a-zA-Z]{2}/', $iso, $hits);
        if (\mb_strlen($hits[0]) !== \mb_strlen($iso)) {
            $cISO = LanguageHelper::getIsoCodeByCountryName($iso);
            if ($cISO !== 'noISO' && $cISO !== '') {
                $iso = $cISO;
            }
        }

        return $iso;
    }

    /**
     * @return $this
     */
    public function kopiereSession(): self
    {
        foreach (\array_keys(\get_object_vars($_SESSION['Kunde'])) as $oElement) {
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
        foreach ($this->db->getObjects('SELECT * FROM tkunde') as $customer) {
            if ($customer->kKunde > 0) {
                unset($tmp);
                $tmp = new self((int)$customer->kKunde);
                $tmp->updateInDB();
            }
        }

        return $this;
    }

    /**
     * @param Customer $customer1
     * @param Customer $customer2
     * @return bool
     */
    public static function isEqual($customer1, $customer2): bool
    {
        if (\is_object($customer1) && \is_object($customer2)) {
            $members1 = \array_keys(\get_class_vars(\get_class($customer1)));
            $members2 = \array_keys(\get_class_vars(\get_class($customer2)));
            if (\count($members1) !== \count($members2)) {
                return false;
            }
            foreach ($members1 as $member) {
                if (!isset($customer2->$member)) {
                    return false;
                }
                $value1 = $customer1->$member;
                $value2 = null;
                foreach ($members2 as $member2) {
                    if ($member == $member2) {
                        $value2 = $customer2->$member;
                    }
                }
                if ($value1 != $value2) {
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
        if ($password === null) {
            $clearTextPassword = $this->passwordService->generate(12);
            $this->cPasswort   = $this->passwordService->hash($clearTextPassword);

            $upd                 = new stdClass();
            $upd->cPasswort      = $this->cPasswort;
            $upd->nLoginversuche = 0;
            $this->db->update('tkunde', 'kKunde', (int)$this->kKunde, $upd);

            $obj                 = new stdClass();
            $obj->tkunde         = $this;
            $obj->neues_passwort = $clearTextPassword;

            $mailer = Shop::Container()->get(Mailer::class);
            $mail   = new Mail();
            $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_PASSWORT_VERGESSEN, $obj));
        } else {
            $this->cPasswort = $this->passwordService->hash(\mb_substr($password, 0, 255));

            $upd                 = new stdClass();
            $upd->cPasswort      = $this->cPasswort;
            $upd->nLoginversuche = 0;
            $this->db->update('tkunde', 'kKunde', (int)$this->kKunde, $upd);
        }

        return $this;
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
        $this->db->queryPrepared(
            'INSERT INTO tpasswordreset(kKunde, cKey, dExpires)
                VALUES (:kKunde, :cKey, :dExpires)
                ON DUPLICATE KEY UPDATE cKey = :cKey, dExpires = :dExpires',
            [
                'kKunde'   => $this->kKunde,
                'cKey'     => $key,
                'dExpires' => $expires->format('Y-m-d H:i:s'),
            ]
        );

        $obj                    = new stdClass();
        $obj->tkunde            = $this;
        $obj->passwordResetLink = $linkHelper->getStaticRoute('pass.php') . '?' . \http_build_query(['fpwh' => $key]);
        $obj->cHash             = $key;
        $obj->neues_passwort    = 'Es ist leider ein Fehler aufgetreten. Bitte kontaktieren Sie uns.';

        $mailer = Shop::Container()->get(Mailer::class);
        $mail   = new Mail();
        $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_PASSWORT_VERGESSEN, $obj));

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
     * @return int
     */
    public function getGroupID(): int
    {
        $customerGroupID = (int)$this->kKundengruppe > 0 ? (int)$this->kKundengruppe : CustomerGroup::getCurrent();

        return $customerGroupID > 0 ? $customerGroupID : CustomerGroup::getDefaultGroupID();
    }

    /**
     * @return int
     */
    public function getLanguageID(): int
    {
        return (int)$this->kSprache;
    }

    /**
     * @param int $languageID
     */
    public function setLanguageID(int $languageID): void
    {
        $this->kSprache = $languageID;
    }

    /**
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->kKunde > 0 && isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde === $this->kKunde;
    }

    /**
     * @param string $salutation
     * @param int    $languageID
     * @param int    $customerID
     * @return string
     * @former mappeKundenanrede()
     */
    public static function mapSalutation(string $salutation, int $languageID, int $customerID = 0): string
    {
        if (($languageID <= 0 && $customerID <= 0) || $salutation === '') {
            return $salutation;
        }
        if ($languageID === 0 && $customerID > 0) {
            $customerLangID = Shop::Container()->getDB()->getSingleInt(
                'SELECT kSprache
                    FROM tkunde
                    WHERE kKunde = :cid',
                'kSprache',
                ['cid' => $customerID]
            );
            if ($customerLangID > 0) {
                $languageID = $customerLangID;
            }
        }
        $lang     = null;
        $langCode = '';
        if ($languageID > 0) { // Kundensprache, falls gesetzt und gültig
            try {
                $lang       = Shop::Lang()->getLanguageByID($languageID);
                $langCode   = $lang->getCode();
                $languageID = $lang->getId();
            } catch (Exception) {
                $lang = null;
            }
        }
        if ($lang === null) { // Ansonsten Standardsprache
            $default    = Shop::Lang()->getDefaultLanguage();
            $langCode   = $default->getCode();
            $languageID = $default->getId();
        }
        if ($languageID === Shop::getLanguageID()) {
            return Shop::Lang()->get($salutation === 'm' ? 'salutationM' : 'salutationW');
        }
        $value = Shop::Container()->getDB()->getSingleObject(
            'SELECT tsprachwerte.cWert
                FROM tsprachwerte
                JOIN tsprachiso
                    ON tsprachiso.cISO = :ciso
                WHERE tsprachwerte.kSprachISO = tsprachiso.kSprachISO
                    AND tsprachwerte.cName = :cname',
            ['ciso' => $langCode, 'cname' => $salutation === 'm' ? 'salutationM' : 'salutationW']
        );
        if ($value !== null && $value->cWert !== '') {
            $salutation = $value->cWert;
        }

        return $salutation;
    }

    /**
     * @param string $issuerType
     * @param int    $issuerID
     * @param bool   $force
     * @param bool   $confirmationMail
     * @return int
     */
    public function deleteAccount(
        string $issuerType,
        int $issuerID,
        bool $force = false,
        bool $confirmationMail = false
    ): int {
        $customerID = $this->getID();
        if (empty($customerID)) {
            return self::CUSTOMER_DELETE_NO;
        }
        if ($force) {
            $this->erasePersonalData($issuerType, $issuerID);

            return self::CUSTOMER_DELETE_DONE;
        }
        $openOrders = $this->getOpenOrders();
        if (!$openOrders) {
            $this->erasePersonalData($issuerType, $issuerID);
            $logMessage = \sprintf('Account with ID kKunde = %s deleted', $customerID);

            $retVal = self::CUSTOMER_DELETE_DONE;
        } else {
            if ($this->nRegistriert === 0) {
                return self::CUSTOMER_DELETE_NO;
            }
            $this->db->update('tkunde', 'kKunde', $customerID, (object)[
                'cPasswort'    => '',
                'nRegistriert' => 0,
            ]);
            $logMessage = \sprintf(
                'Account with ID kKunde = %s deleted, but had %s open orders with %s still in cancellation time. ' .
                'Account is deactivated until all orders are completed.',
                $customerID,
                $openOrders->openOrders,
                $openOrders->ordersInCancellationTime
            );

            (new Journal())->addEntry(
                $issuerType,
                $customerID,
                Journal::ACTION_CUSTOMER_DEACTIVATED,
                $logMessage,
                (object)['kKunde' => $customerID]
            );

            $retVal = self::CUSTOMER_DELETE_DEACT;
        }
        Shop::Container()->getLogService()->notice($logMessage);
        if ($confirmationMail) {
            $mailer = Shop::Container()->get(Mailer::class);
            $mail   = new Mail();
            $mailer->send($mail->createFromTemplateID(
                \MAILTEMPLATE_KUNDENACCOUNT_GELOESCHT,
                (object)['tkunde' => $this]
            ));
        }

        return $retVal;
    }

    /**
     * @return false|stdClass
     */
    public function getOpenOrders()
    {
        $cancellationTime = Shopsetting::getInstance()->getValue(\CONF_GLOBAL, 'global_cancellation_time');
        $db               = $this->db;
        $customerID       = $this->getID();

        $openOrders               = $db->getSingleObject(
            'SELECT COUNT(kBestellung) AS orderCount
                FROM tbestellung
                WHERE cStatus NOT IN (:orderSent, :orderCanceled)
                    AND kKunde = :customerId',
            [
                'customerId'    => $customerID,
                'orderSent'     => \BESTELLUNG_STATUS_VERSANDT,
                'orderCanceled' => \BESTELLUNG_STATUS_STORNO,
            ]
        );
        $ordersInCancellationTime = $db->getSingleObject(
            'SELECT COUNT(kBestellung) AS orderCount
                    FROM tbestellung
                    WHERE kKunde = :customerId
                        AND cStatus = :orderSent
                        AND DATE(dVersandDatum) > DATE_SUB(NOW(), INTERVAL :cancellationTime DAY)',
            [
                'customerId'       => $customerID,
                'orderSent'        => \BESTELLUNG_STATUS_VERSANDT,
                'cancellationTime' => $cancellationTime,
            ]
        );

        if (!empty($openOrders->orderCount) || !empty($ordersInCancellationTime->orderCount)) {
            return (object)[
                'openOrders'               => (int)$openOrders->orderCount,
                'ordersInCancellationTime' => (int)$ordersInCancellationTime->orderCount
            ];
        }

        return false;
    }

    /**
     * @param bool $force
     * @return CustomerAttributes
     */
    public function getCustomerAttributes(bool $force = false): CustomerAttributes
    {
        static $customerAttributes = null;

        if ($customerAttributes === null || $force) {
            $customerAttributes = new CustomerAttributes($this->getID());
        }

        return $customerAttributes;
    }

    /**
     * @param string $issuerType
     * @param int    $issuerID
     */
    private function erasePersonalData(string $issuerType, int $issuerID): void
    {
        $customerID = $this->getID();
        $db         = $this->db;
        if (empty($customerID)) {
            return;
        }

        $db->delete('tlieferadresse', 'kKunde', $customerID);
        $db->delete('trechnungsadresse', 'kKunde', $customerID);
        $db->delete('tkundenattribut', 'kKunde', $customerID);
        $db->update('tkunde', 'kKunde', $customerID, (object)[
             'cKundenNr'     => self::CUSTOMER_ANONYM,
             'cPasswort'     => '',
             'cAnrede'       => '',
             'cTitel'        => '',
             'cVorname'      => self::CUSTOMER_ANONYM,
             'cNachname'     => self::CUSTOMER_ANONYM,
             'cFirma'        => '',
             'cZusatz'       => '',
             'cStrasse'      => '',
             'cHausnummer'   => '',
             'cAdressZusatz' => '',
             'cPLZ'          => '',
             'cOrt'          => '',
             'cBundesland'   => '',
             'cLand'         => '',
             'cTel'          => '',
             'cMobil'        => '',
             'cFax'          => '',
             'cMail'         => self::CUSTOMER_ANONYM,
             'cUSTID'        => '',
             'cWWW'          => '',
             'cSperre'       => 'Y',
             'fGuthaben'     => 0,
             'cNewsletter'   => 'N',
             'dGeburtstag'   => '_DBNULL_',
             'fRabatt'       => 0,
             'cHerkunft'     => '',
             'dVeraendert'   => 'now()',
             'cAktiv'        => 'N',
             'nRegistriert'  => 0,
        ]);
        $db->delete('tkundendatenhistory', 'kKunde', $customerID);
        $db->delete('tkundenkontodaten', 'kKunde', $customerID);
        $db->delete('tzahlungsinfo', 'kKunde', $customerID);
        $db->delete('tkontakthistory', 'cMail', $this->cMail);
        $db->delete('tproduktanfragehistory', 'cMail', $this->cMail);
        $db->delete('tverfuegbarkeitsbenachrichtigung', 'cMail', $this->cMail);

        $db->update('tbewertung', 'kKunde', $customerID, (object)['cName' => self::CUSTOMER_ANONYM]);
        $db->update('tnewskommentar', 'kKunde', $customerID, (object)[
            'cName'  => self::CUSTOMER_ANONYM,
            'cEmail' => self::CUSTOMER_ANONYM
        ]);
        $db->queryPrepared(
            'DELETE FROM tnewsletterempfaenger
                WHERE cEmail = :email
                    OR kKunde = :customerID',
            ['email' => $this->cMail, 'customerID' => $customerID]
        );

        $obj            = new stdClass();
        $obj->cAnrede   = self::CUSTOMER_ANONYM;
        $obj->cVorname  = self::CUSTOMER_ANONYM;
        $obj->cNachname = self::CUSTOMER_ANONYM;
        $obj->cEmail    = self::CUSTOMER_ANONYM;
        $db->update('tnewsletterempfaengerhistory', 'kKunde', $customerID, $obj);
        $db->update('tnewsletterempfaengerhistory', 'cEmail', $this->cMail, $obj);

        $db->insert('tnewsletterempfaengerhistory', (object)[
            'kSprache'     => $this->kSprache,
            'kKunde'       => $customerID,
            'cAnrede'      => self::CUSTOMER_ANONYM,
            'cVorname'     => self::CUSTOMER_ANONYM,
            'cNachname'    => self::CUSTOMER_ANONYM,
            'cEmail'       => self::CUSTOMER_ANONYM,
            'cOptCode'     => '',
            'cLoeschCode'  => '',
            'cAktion'      => 'Geloescht',
            'dAusgetragen' => 'NOW()',
            'dEingetragen' => '_DBNULL_',
            'dOptCode'     => '_DBNULL_'
        ]);
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
            ['customerID' => $customerID]
        );
        $db->queryPrepared(
            'DELETE twarenkorbpers, twarenkorbperspos, twarenkorbpersposeigenschaft
                FROM twarenkorbpers
                LEFT JOIN twarenkorbperspos
                    ON twarenkorbperspos.kWarenkorbPers = twarenkorbpers.kWarenkorbPers
                LEFT JOIN twarenkorbpersposeigenschaft
                    ON twarenkorbpersposeigenschaft.kWarenkorbPersPos = twarenkorbperspos.kWarenkorbPersPos
                WHERE twarenkorbpers.kKunde = :customerID',
            ['customerID' => $customerID]
        );

        $logMessage = \sprintf('Account with ID kKunde = %s deleted', $customerID);
        (new Journal())->addEntry(
            $issuerType,
            $issuerID,
            Journal::ACTION_CUSTOMER_DELETED,
            $logMessage,
            (object)['kKunde' => $customerID]
        );
    }

    /**
     * @param object $lang
     * @return $this
     */
    public function localize($lang): self
    {
        if (Shop::Lang()->gibISO() !== $lang->cISO) {
            Shop::Lang()->setzeSprache($lang->cISO);
        }
        if ($this->cAnrede === 'w') {
            $this->cAnredeLocalized = Shop::Lang()->get('salutationW');
        } elseif ($this->cAnrede === 'm') {
            $this->cAnredeLocalized = Shop::Lang()->get('salutationM');
        } else {
            $this->cAnredeLocalized = Shop::Lang()->get('salutationGeneral');
        }
        if ($this->cLand !== null) {
            if (isset($_SESSION['Kunde'])) {
                $_SESSION['Kunde']->cLand = $this->cLand;
            }
            if (($country = Shop::Container()->getCountryService()->getCountry($this->cLand)) !== null) {
                $this->angezeigtesLand = $country->getName($lang->id);
            }
        }

        return $this;
    }
}
