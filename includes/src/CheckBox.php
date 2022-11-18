<?php declare(strict_types=1);

namespace JTL;

use InvalidArgumentException;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Link\Link;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Optin\Optin;
use JTL\Optin\OptinNewsletter;
use JTL\Optin\OptinRefData;
use JTL\Session\Frontend;
use stdClass;

/**
 * Class CheckBox
 * @package JTL
 */
class CheckBox
{
    /**
     * @var int
     */
    public int $kCheckBox = 0;

    /**
     * @var int
     */
    public int $kLink = 0;

    /**
     * @var int
     */
    public int $kCheckBoxFunktion = 0;

    /**
     * @var string
     */
    public string $cName = '';

    /**
     * @var string
     */
    public string $cKundengruppe = '';

    /**
     * @var string
     */
    public string $cAnzeigeOrt = '';

    /**
     * @var int
     */
    public int $nAktiv = 0;

    /**
     * @var int
     */
    public int $nPflicht = 0;

    /**
     * @var int
     */
    public int $nLogging = 0;

    /**
     * @var int
     */
    public int $nSort = 0;

    /**
     * @var string
     */
    public string $dErstellt = '';

    /**
     * @var string
     */
    public string $dErstellt_DE = '';

    /**
     * @var array
     */
    public array $oCheckBoxSprache_arr = [];

    /**
     * @var stdClass|null
     */
    public ?stdClass $oCheckBoxFunktion = null;

    /**
     * @var array
     */
    public array $kKundengruppe_arr = [];

    /**
     * @var array
     */
    public array $kAnzeigeOrt_arr = [];

    /**
     * @var string|null
     */
    public ?string $cID = null;

    /**
     * @var string|null
     */
    public ?string $cLink = null;

    /**
     * @var Link|null
     */
    public ?Link $oLink = null;

    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * @var bool|null
     */
    public ?bool $isActive = null;

    /**
     * @var string|null
     */
    public ?string $cLinkURL = null;

    /**
     * @var string|null
     */
    public ?string $cLinkURLFull = null;

    /**
     * @var string|null
     */
    public ?string $cBeschreibung = null;

    /**
     * @var string|null
     */
    public ?string $cErrormsg = null;

    /**
     * @param int              $id
     * @param DbInterface|null $db
     */
    public function __construct(int $id = 0, DbInterface $db = null)
    {
        $this->db    = $db ?? Shop::Container()->getDB();
        $this->oLink = new Link($this->db);
        $this->loadFromDB($id);
    }

    /**
     * @param int $id
     * @return $this
     */
    private function loadFromDB(int $id): self
    {
        if ($id <= 0) {
            return $this;
        }
        $cacheID = 'chkbx_' . $id;
        if (($checkbox = Shop::Container()->getCache()->get($cacheID)) !== false) {
            foreach (\array_keys(\get_object_vars($checkbox)) as $member) {
                if ($member === 'db') {
                    continue;
                }
                $this->$member = $checkbox->$member;
            }
            $this->loadLink();

            return $this;
        }
        $checkbox = $this->db->getSingleObject(
            "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE
                FROM tcheckbox
                WHERE kCheckBox = :cbid",
            ['cbid' => $id]
        );
        if ($checkbox === null) {
            return $this;
        }
        $this->kCheckBox         = (int)$checkbox->kCheckBox;
        $this->kLink             = (int)$checkbox->kLink;
        $this->kCheckBoxFunktion = (int)$checkbox->kCheckBoxFunktion;
        $this->cName             = $checkbox->cName;
        $this->cKundengruppe     = $checkbox->cKundengruppe;
        $this->cAnzeigeOrt       = $checkbox->cAnzeigeOrt;
        $this->nAktiv            = (int)$checkbox->nAktiv;
        $this->nPflicht          = (int)$checkbox->nPflicht;
        $this->nLogging          = (int)$checkbox->nLogging;
        $this->nSort             = (int)$checkbox->nSort;
        $this->cID               = 'CheckBox_' . $this->kCheckBox;
        $this->dErstellt         = $checkbox->dErstellt;
        $this->dErstellt_DE      = $checkbox->dErstellt_DE;
        $this->kKundengruppe_arr = Text::parseSSKint($checkbox->cKundengruppe);
        $this->kAnzeigeOrt_arr   = Text::parseSSKint($checkbox->cAnzeigeOrt);
        // Falls kCheckBoxFunktion gesetzt war aber diese Funktion nicht mehr existiert (deinstallation vom Plugin)
        // wird kCheckBoxFunktion auf 0 gesetzt
        if ($this->kCheckBoxFunktion > 0) {
            $func = $this->db->select(
                'tcheckboxfunktion',
                'kCheckBoxFunktion',
                $this->kCheckBoxFunktion
            );
            if ($func !== null && $func->kCheckBoxFunktion > 0) {
                $func->kCheckBoxFunktion = (int)$func->kCheckBoxFunktion;
                if (Shop::isAdmin()) {
                    Shop::Container()->getGetText()->loadAdminLocale('pages/checkbox');
                    $func->cName = \__($func->cName);
                }
                $this->oCheckBoxFunktion = $func;
            } else {
                $this->kCheckBoxFunktion = 0;
                $this->db->update('tcheckbox', 'kCheckBox', $this->kCheckBox, (object)['kCheckBoxFunktion' => 0]);
            }
        }
        $this->loadLink();
        $localized = $this->db->selectAll(
            'tcheckboxsprache',
            'kCheckBox',
            $this->kCheckBox
        );
        foreach ($localized as $translation) {
            $translation->kCheckBoxSprache = (int)$translation->kCheckBoxSprache;
            $translation->kCheckBox        = (int)$translation->kCheckBox;
            $translation->kSprache         = (int)$translation->kSprache;

            $this->oCheckBoxSprache_arr[$translation->kSprache] = $translation;
        }
        $this->saveToCache($cacheID);

        return $this;
    }

    /**
     * @param string $cacheID
     * @return void
     */
    private function saveToCache(string $cacheID): void
    {
        $item = new stdClass();
        foreach (\get_object_vars($this) as $name => $value) {
            if ($name === 'db' || $name === 'oLink') {
                continue;
            }
            $item->$name = $value;
        }
        Shop::Container()->getCache()->set($cacheID, $item, [\CACHING_GROUP_CORE, 'checkbox']);
    }

    /**
     * @return void
     */
    private function loadLink(): void
    {
        $this->oLink = new Link($this->db);
        if ($this->kLink > 0) {
            try {
                $this->oLink->load($this->kLink);
            } catch (InvalidArgumentException) {
                Shop::Container()->getLogService()->error('Checkbox cannot link to link ID ' . $this->kLink);
            }
        } else {
            $this->cLink = 'kein interner Link';
        }
    }

    /**
     * @param int  $location
     * @param int  $customerGroupID
     * @param bool $active
     * @param bool $lang
     * @param bool $special
     * @param bool $logging
     * @return CheckBox[]
     */
    public function getCheckBoxFrontend(
        int $location,
        int $customerGroupID = 0,
        bool $active = false,
        bool $lang = false,
        bool $special = false,
        bool $logging = false
    ): array {
        if ($customerGroupID === 0) {
            $customerGroupID = Frontend::getCustomer()->getGroupID();
        }
        $sql = '';
        if ($active) {
            $sql .= ' AND nAktiv = 1';
        }
        if ($special) {
            $sql .= ' AND kCheckBoxFunktion > 0';
        }
        if ($logging) {
            $sql .= ' AND nLogging = 1';
        }
        $checkboxes = $this->db->getCollection(
            "SELECT kCheckBox AS id
                FROM tcheckbox
                WHERE FIND_IN_SET('" . $location . "', REPLACE(cAnzeigeOrt, ';', ',')) > 0
                    AND FIND_IN_SET('" . $customerGroupID . "', REPLACE(cKundengruppe, ';', ',')) > 0
                    " . $sql . '
                ORDER BY nSort'
        )->map(function (stdClass $e): self {
            return new self((int)$e->id, $this->db);
        })->all();
        \executeHook(\HOOK_CHECKBOX_CLASS_GETCHECKBOXFRONTEND, [
            'oCheckBox_arr' => &$checkboxes,
            'nAnzeigeOrt'   => $location,
            'kKundengruppe' => $customerGroupID,
            'bAktiv'        => $active,
            'bSprache'      => $lang,
            'bSpecial'      => $special,
            'bLogging'      => $logging
        ]);

        return $checkboxes;
    }

    /**
     * @param int   $location
     * @param int   $customerGroupID
     * @param array $post
     * @param bool  $active
     * @return array
     */
    public function validateCheckBox(int $location, int $customerGroupID, array $post, bool $active = false): array
    {
        $checks = [];
        foreach ($this->getCheckBoxFrontend($location, $customerGroupID, $active) as $checkBox) {
            if ($checkBox->nPflicht === 1 && !isset($post[$checkBox->cID])) {
                $checks[$checkBox->cID] = 1;
            }
        }

        return $checks;
    }

    /**
     * @param int   $location
     * @param int   $customerGroupID
     * @param bool  $active
     * @param array $post
     * @param array $params
     * @return $this
     */
    public function triggerSpecialFunction(
        int $location,
        int $customerGroupID,
        bool $active,
        array $post,
        array $params = []
    ): self {
        $checkboxes = $this->getCheckBoxFrontend($location, $customerGroupID, $active, true, true);
        foreach ($checkboxes as $checkbox) {
            if (!isset($post[$checkbox->cID])) {
                continue;
            }
            if ($checkbox->oCheckBoxFunktion->kPlugin > 0) {
                $params['oCheckBox'] = $checkbox;
                \executeHook(\HOOK_CHECKBOX_CLASS_TRIGGERSPECIALFUNCTION, $params);
            } else {
                // Festdefinierte Shopfunktionen
                switch ($checkbox->oCheckBoxFunktion->cID) {
                    case 'jtl_newsletter': // Newsletteranmeldung
                        $params['oKunde'] = GeneralObject::copyMembers($params['oKunde']);
                        $this->sfCheckBoxNewsletter($params['oKunde'], $location);
                        break;

                    case 'jtl_adminmail': // CheckBoxMail
                        $params['oKunde'] = GeneralObject::copyMembers($params['oKunde']);
                        $this->sfCheckBoxMailToAdmin($params['oKunde'], $checkbox, $location);
                        break;

                    default:
                        break;
                }
            }
        }

        return $this;
    }

    /**
     * @param int   $location
     * @param int   $customerGroupID
     * @param array $post
     * @param bool  $active
     * @return $this
     */
    public function checkLogging(int $location, int $customerGroupID, array $post, bool $active = false): self
    {
        $checkboxes = $this->getCheckBoxFrontend($location, $customerGroupID, $active, false, false, true);
        foreach ($checkboxes as $checkbox) {
            $checked          = $this->checkboxWasChecked($checkbox->cID, $post);
            $log              = new stdClass();
            $log->kCheckBox   = $checkbox->kCheckBox;
            $log->kBesucher   = (int)($_SESSION['oBesucher']->kBesucher ?? 0);
            $log->kBestellung = (int)($_SESSION['kBestellung'] ?? 0);
            $log->bChecked    = (int)$checked;
            $log->dErstellt   = 'NOW()';
            $this->db->insert('tcheckboxlogging', $log);
        }

        return $this;
    }

    /**
     * @param string $idx
     * @param array  $post
     * @return bool
     */
    private function checkboxWasChecked(string $idx, array $post): bool
    {
        $value = $post[$idx] ?? null;
        if ($value === null) {
            return false;
        }
        if ($value === 'on' || $value === 'Y' || $value === 'y') {
            $value = true;
        } elseif ($value === 'N' || $value === 'n' || $value === '') {
            $value = false;
        } else {
            $value = (bool)$value;
        }

        return $value;
    }

    /**
     * @param string $limitSQL
     * @param bool   $active
     * @return CheckBox[]
     * @deprecated since 5.1.0
     */
    public function getAllCheckBox(string $limitSQL = '', bool $active = false): array
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\CheckBox\getAll() instead.', \E_USER_DEPRECATED);
        return $this->getAll($limitSQL, $active);
    }

    /**
     * @param string $limitSQL
     * @param bool   $active
     * @return CheckBox[]
     */
    public function getAll(string $limitSQL = '', bool $active = false): array
    {
        return $this->db->getCollection(
            'SELECT kCheckBox AS id
                FROM tcheckbox' . ($active ? ' WHERE nAktiv = 1' : '') . '
                ORDER BY nSort ' . $limitSQL
        )->map(function (stdClass $e): self {
            return new self((int)$e->id, $this->db);
        })->all();
    }

    /**
     * @param bool $active
     * @return int
     * @deprecated since 5.1.0
     */
    public function getAllCheckBoxCount(bool $active = false): int
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\CheckBox\getTotalCount() instead.', \E_USER_DEPRECATED);
        return $this->getTotalCount($active);
    }

    /**
     * @param bool $active
     * @return int
     */
    public function getTotalCount(bool $active = false): int
    {
        return (int)$this->db->getSingleObject(
            'SELECT COUNT(*) AS cnt
                FROM tcheckbox' . ($active ? ' WHERE nAktiv = 1' : '')
        )->cnt;
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     * @deprecated since 5.1.0
     */
    public function aktivateCheckBox(array $checkboxIDs): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\CheckBox\activate() instead.', \E_USER_DEPRECATED);
        return $this->activate($checkboxIDs);
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function activate(array $checkboxIDs): bool
    {
        if (\count($checkboxIDs) === 0) {
            return false;
        }
        $this->db->query(
            'UPDATE tcheckbox
                SET nAktiv = 1
                WHERE kCheckBox IN (' . \implode(',', \array_map('\intval', $checkboxIDs)) . ')'
        );
        Shop::Container()->getCache()->flushTags(['checkbox']);

        return true;
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     * @deprecated since 5.1.0
     */
    public function deaktivateCheckBox(array $checkboxIDs): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\CheckBox\deactivate() instead.', \E_USER_DEPRECATED);
        return $this->deactivate($checkboxIDs);
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function deactivate(array $checkboxIDs): bool
    {
        if (\count($checkboxIDs) === 0) {
            return false;
        }
        $this->db->query(
            'UPDATE tcheckbox
                SET nAktiv = 0
                WHERE kCheckBox IN (' . \implode(',', \array_map('\intval', $checkboxIDs)) . ')'
        );
        Shop::Container()->getCache()->flushTags(['checkbox']);

        return true;
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     * @deprecated since 5.1.0
     */
    public function deleteCheckBox(array $checkboxIDs): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use JTL\CheckBox\delete() instead.', \E_USER_DEPRECATED);
        return $this->delete($checkboxIDs);
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function delete(array $checkboxIDs): bool
    {
        if (\count($checkboxIDs) === 0) {
            return false;
        }
        $this->db->query(
            'DELETE tcheckbox, tcheckboxsprache
                FROM tcheckbox
                LEFT JOIN tcheckboxsprache
                    ON tcheckboxsprache.kCheckBox = tcheckbox.kCheckBox
                WHERE tcheckbox.kCheckBox IN (' . \implode(',', \array_map('\intval', $checkboxIDs)) . ')'
        );
        Shop::Container()->getCache()->flushTags(['checkbox']);

        return true;
    }

    /**
     * @return stdClass[]
     */
    public function getCheckBoxFunctions(): array
    {
        return $this->db->getCollection(
            'SELECT *
                FROM tcheckboxfunktion
                ORDER BY cName'
        )->each(static function (stdClass $e): void {
            $e->kCheckBoxFunktion = (int)$e->kCheckBoxFunktion;
            $e->cName             = \__($e->cName);
        })->all();
    }

    /**
     * @param array $texts
     * @param array $descriptions
     * @return $this
     */
    public function insertDB(array $texts, array $descriptions): self
    {
        if (\count($texts) === 0) {
            return $this;
        }
        $checkbox               = GeneralObject::copyMembers($this);
        $ins                    = new stdClass();
        $ins->kLink             = $checkbox->kLink;
        $ins->kCheckBoxFunktion = $checkbox->kCheckBoxFunktion;
        $ins->cName             = $checkbox->cName;
        $ins->cKundengruppe     = $checkbox->cKundengruppe;
        $ins->cAnzeigeOrt       = $checkbox->cAnzeigeOrt;
        $ins->nAktiv            = $checkbox->nAktiv;
        $ins->nPflicht          = $checkbox->nPflicht;
        $ins->nLogging          = $checkbox->nLogging;
        $ins->nSort             = $checkbox->nSort;
        $ins->dErstellt         = $checkbox->dErstellt;
        if ((int)$checkbox->kCheckBox !== 0) {
            $ins->kCheckBox = (int)$checkbox->kCheckBox;
        }
        $id              = $this->db->insert('tcheckbox', $ins);
        $this->kCheckBox = !empty($checkbox->kCheckBox) ? (int)$checkbox->kCheckBox : $id;
        $this->addLocalization($texts, $descriptions);

        return $this;
    }

    /**
     * @param array $texts
     * @param array $descriptions
     * @return $this
     */
    private function addLocalization(array $texts, array $descriptions): self
    {
        $this->oCheckBoxSprache_arr = [];
        foreach ($texts as $iso => $text) {
            if (\mb_strlen($text) === 0) {
                continue;
            }
            $this->oCheckBoxSprache_arr[$iso]                = new stdClass();
            $this->oCheckBoxSprache_arr[$iso]->kCheckBox     = $this->kCheckBox;
            $this->oCheckBoxSprache_arr[$iso]->kSprache      = $this->getSprachKeyByISO($iso);
            $this->oCheckBoxSprache_arr[$iso]->cText         = $text;
            $this->oCheckBoxSprache_arr[$iso]->cBeschreibung = '';
            if (isset($descriptions[$iso]) && \mb_strlen($descriptions[$iso]) > 0) {
                $this->oCheckBoxSprache_arr[$iso]->cBeschreibung = $descriptions[$iso];
            }
            $this->oCheckBoxSprache_arr[$iso]->kCheckBoxSprache = $this->db->insert(
                'tcheckboxsprache',
                $this->oCheckBoxSprache_arr[$iso]
            );
        }

        return $this;
    }

    /**
     * @param string $iso
     * @return int
     */
    private function getSprachKeyByISO(string $iso): int
    {
        return (int)(LanguageHelper::getLangIDFromIso($iso)->kSprache ?? 0);
    }

    /**
     * @param object $customer
     * @param int    $location
     * @return bool
     * @throws Exceptions\CircularReferenceException
     * @throws Exceptions\ServiceNotFoundException
     */
    private function sfCheckBoxNewsletter($customer, int $location): bool
    {
        if (!\is_object($customer)) {
            return false;
        }
        $refData = (new OptinRefData())
            ->setSalutation($customer->cAnrede ?? '')
            ->setFirstName($customer->cVorname ?? '')
            ->setLastName($customer->cNachname ?? '')
            ->setEmail($customer->cMail)
            ->setLanguageID(Shop::getLanguageID())
            ->setRealIP(Request::getRealIP());
        try {
            (new Optin(OptinNewsletter::class))
                ->getOptinInstance()
                ->createOptin($refData, $location)
                ->sendActivationMail();
        } catch (\Exception $e) {
            Shop::Container()->getLogService()->error($e->getMessage());
        }

        return true;
    }

    /**
     * @param object $customer
     * @param object $checkBox
     * @param int    $location
     * @return bool
     */
    public function sfCheckBoxMailToAdmin($customer, $checkBox, int $location): bool
    {
        if (!isset($customer->cVorname, $customer->cNachname, $customer->cMail)) {
            return false;
        }
        $conf = Shop::getSettingSection(\CONF_EMAILS);
        if (!empty($conf['email_master_absender'])) {
            $data                = new stdClass();
            $data->oCheckBox     = $checkBox;
            $data->oKunde        = $customer;
            $data->tkunde        = $customer;
            $data->cAnzeigeOrt   = $this->mappeCheckBoxOrte($location);
            $data->mail          = new stdClass();
            $data->mail->toEmail = $conf['email_master_absender'];
            $data->mail->toName  = $conf['email_master_absender_name'];
            /** @var Mailer $mailer */
            $mailer = Shop::Container()->get(Mailer::class);
            $mail   = new Mail();
            $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_CHECKBOX_SHOPBETREIBER, $data));
        }

        return true;
    }

    /**
     * @param int $location
     * @return string
     */
    public function mappeCheckBoxOrte(int $location): string
    {
        return self::gibCheckBoxAnzeigeOrte()[$location] ?? '';
    }

    /**
     * @return array
     */
    public static function gibCheckBoxAnzeigeOrte(): array
    {
        Shop::Container()->getGetText()->loadAdminLocale('pages/checkbox');

        return [
            \CHECKBOX_ORT_REGISTRIERUNG        => \__('checkboxPositionRegistration'),
            \CHECKBOX_ORT_BESTELLABSCHLUSS     => \__('checkboxPositionOrderFinal'),
            \CHECKBOX_ORT_NEWSLETTERANMELDUNG  => \__('checkboxPositionNewsletterRegistration'),
            \CHECKBOX_ORT_KUNDENDATENEDITIEREN => \__('checkboxPositionEditCustomerData'),
            \CHECKBOX_ORT_KONTAKT              => \__('checkboxPositionContactForm'),
            \CHECKBOX_ORT_FRAGE_ZUM_PRODUKT    => \__('checkboxPositionProductQuestion'),
            \CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT => \__('checkboxPositionAvailabilityNotification')
        ];
    }

    /**
     * @return Link
     */
    public function getLink(): Link
    {
        return $this->oLink;
    }
}
