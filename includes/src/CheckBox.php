<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL;

use InvalidArgumentException;
use JTL\Customer\Kundengruppe;
use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Link\Link;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
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
    public $kCheckBox;

    /**
     * @var int
     */
    public $kLink;

    /**
     * @var int
     */
    public $kCheckBoxFunktion;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cKundengruppe;

    /**
     * @var string
     */
    public $cAnzeigeOrt;

    /**
     * @var int
     */
    public $nAktiv;

    /**
     * @var int
     */
    public $nPflicht;

    /**
     * @var int
     */
    public $nLogging;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var array
     */
    public $oCheckBoxSprache_arr;

    /**
     * @var stdClass
     */
    public $oCheckBoxFunktion;

    /**
     * @var array
     */
    public $kKundengruppe_arr;

    /**
     * @var array
     */
    public $kAnzeigeOrt_arr;

    /**
     * @var string
     */
    public $cID;

    /**
     * @var string
     */
    public $cLink;

    /**
     * @var Link
     */
    public $oLink;

    /**
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        $this->oLink = new Link(Shop::Container()->getDB());
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
                $this->$member = $checkbox->$member;
            }

            return $this;
        }
        $db       = Shop::Container()->getDB();
        $checkbox = $db->queryPrepared(
            "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE 
                FROM tcheckbox 
                WHERE kCheckBox = :cbid",
            ['cbid' => $id],
            ReturnType::SINGLE_OBJECT
        );
        if ($checkbox === false || $checkbox === null) {
            return $this;
        }
        foreach (\array_keys(\get_object_vars($checkbox)) as $member) {
            $this->$member = $checkbox->$member;
        }
        // Global Identifier
        $this->kCheckBox         = (int)$this->kCheckBox;
        $this->kLink             = (int)$this->kLink;
        $this->kCheckBoxFunktion = (int)$this->kCheckBoxFunktion;
        $this->nAktiv            = (int)$this->nAktiv;
        $this->nPflicht          = (int)$this->nPflicht;
        $this->nLogging          = (int)$this->nLogging;
        $this->nSort             = (int)$this->nSort;
        $this->cID               = 'CheckBox_' . $this->kCheckBox;
        $this->kKundengruppe_arr = Text::parseSSK($checkbox->cKundengruppe);
        $this->kAnzeigeOrt_arr   = Text::parseSSK($checkbox->cAnzeigeOrt);
        // Falls kCheckBoxFunktion gesetzt war aber diese Funktion nicht mehr existiert (deinstallation vom Plugin)
        // wird kCheckBoxFunktion auf 0 gesetzt
        if ($this->kCheckBoxFunktion > 0) {
            $func = $db->select(
                'tcheckboxfunktion',
                'kCheckBoxFunktion',
                (int)$this->kCheckBoxFunktion
            );
            if (isset($func->kCheckBoxFunktion) && $func->kCheckBoxFunktion > 0) {
                $func->cName             = __($func->cName);
                $this->oCheckBoxFunktion = $func;
            } else {
                $this->kCheckBoxFunktion = 0;
                $upd                     = new stdClass();
                $upd->kCheckBoxFunktion  = 0;
                $db->update('tcheckbox', 'kCheckBox', (int)$this->kCheckBox, $upd);
            }
        }
        if ($this->kLink > 0) {
            $this->oLink = new Link($db);
            try {
                $this->oLink->load($this->kLink);
            } catch (InvalidArgumentException $e) {
                $logger = Shop::Container()->getLogService();
                $logger->error('Checkbox cannot link to link ID ' . $this->kLink);
            }
        } else {
            $this->cLink = 'kein interner Link';
        }
        $localized = $db->selectAll(
            'tcheckboxsprache',
            'kCheckBox',
            (int)$this->kCheckBox
        );
        foreach ($localized as $translation) {
            $translation->kCheckBoxSprache = (int)$translation->kCheckBoxSprache;
            $translation->kCheckBox        = (int)$translation->kCheckBox;
            $translation->kSprache         = (int)$translation->kSprache;

            $this->oCheckBoxSprache_arr[$translation->kSprache] = $translation;
        }
        Shop::Container()->getCache()->set($cacheID, $this, [\CACHING_GROUP_CORE, 'checkbox']);

        return $this;
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
        if (!$customerGroupID) {
            if (isset($_SESSION['Kundengruppe']->kKundengruppe)) {
                $customerGroupID = Frontend::getCustomerGroup()->getID();
            } else {
                $customerGroupID = Kundengruppe::getDefaultGroupID();
            }
        }
        $checkboxes = [];
        $sql        = '';
        if ($active) {
            $sql .= ' AND nAktiv = 1';
        }
        if ($special) {
            $sql .= ' AND kCheckBoxFunktion > 0';
        }
        if ($logging) {
            $sql .= ' AND nLogging = 1';
        }
        $checkBoxIDs = Shop::Container()->getDB()->query(
            "SELECT kCheckBox FROM tcheckbox
                WHERE FIND_IN_SET('" . $location . "', REPLACE(cAnzeigeOrt, ';', ',')) > 0
                    AND FIND_IN_SET('" . $customerGroupID . "', REPLACE(cKundengruppe, ';', ',')) > 0
                    " . $sql . '
                ORDER BY nSort',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($checkBoxIDs as $item) {
            $checkboxes[] = new self((int)$item->kCheckBox);
        }
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
        $checkBoxes = $this->getCheckBoxFrontend($location, $customerGroupID, $active);
        $checks     = [];
        foreach ($checkBoxes as $checkBox) {
            if ((int)$checkBox->nPflicht === 1 && !isset($post[$checkBox->cID])) {
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
        $checkBoxes = $this->getCheckBoxFrontend($location, $customerGroupID, $active, true, true);
        foreach ($checkBoxes as $checkBox) {
            if (!isset($post[$checkBox->cID])) {
                continue;
            }
            if ($checkBox->oCheckBoxFunktion->kPlugin > 0) {
                $params['oCheckBox'] = $checkBox;
                \executeHook(\HOOK_CHECKBOX_CLASS_TRIGGERSPECIALFUNCTION, $params);
            } else {
                // Festdefinierte Shopfunktionen
                switch ($checkBox->oCheckBoxFunktion->cID) {
                    case 'jtl_newsletter': // Newsletteranmeldung
                        $params['oKunde'] = GeneralObject::copyMembers($params['oKunde']);
                        $this->sfCheckBoxNewsletter($params['oKunde']);
                        break;

                    case 'jtl_adminmail': // CheckBoxMail
                        $params['oKunde'] = GeneralObject::copyMembers($params['oKunde']);
                        $this->sfCheckBoxMailToAdmin($params['oKunde'], $checkBox, $location);
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
     * @param int   $kKundengruppe
     * @param array $post
     * @param bool  $bAktiv
     * @return $this
     */
    public function checkLogging(int $location, int $kKundengruppe, array $post, bool $bAktiv = false): self
    {
        $checkBoxes = $this->getCheckBoxFrontend($location, $kKundengruppe, $bAktiv, false, false, true);
        $db         = Shop::Container()->getDB();
        foreach ($checkBoxes as $checkBox) {
            //@todo: casting to bool does not seem to be a good idea.
            //$cPost_arr looks like this: array ( [CheckBox_31] => Y, [CheckBox_24] => Y, [abschluss] => 1)
            $checked          = isset($post[$checkBox->cID])
                ? (bool)$post[$checkBox->cID]
                : false;
            $checked          = ($checked === true) ? 1 : 0;
            $log              = new stdClass();
            $log->kCheckBox   = $checkBox->kCheckBox;
            $log->kBesucher   = (int)$_SESSION['oBesucher']->kBesucher;
            $log->kBestellung = isset($_SESSION['kBestellung'])
                ? (int)$_SESSION['kBestellung']
                : 0;
            $log->bChecked    = $checked;
            $log->dErstellt   = 'NOW()';
            $db->insert('tcheckboxlogging', $log);
        }

        return $this;
    }

    /**
     * @param string $limitSQL
     * @param bool   $active
     * @return CheckBox[]
     */
    public function getAllCheckBox(string $limitSQL = '', bool $active = false): array
    {
        $checkBoxes = [];
        $ids        = Shop::Container()->getDB()->query(
            'SELECT kCheckBox 
                FROM tcheckbox' . ($active ? ' WHERE nAktiv = 1' : '') . ' 
                ORDER BY nSort ' . $limitSQL,
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($ids as $i => $item) {
            $checkBoxes[$i] = new self((int)$item->kCheckBox);
        }

        return $checkBoxes;
    }

    /**
     * @param bool $active
     * @return int
     */
    public function getAllCheckBoxCount(bool $active = false): int
    {
        return (int)Shop::Container()->getDB()->query(
            'SELECT COUNT(*) AS nAnzahl 
                FROM tcheckbox' . ($active ? ' WHERE nAktiv = 1' : ''),
            ReturnType::SINGLE_OBJECT
        )->nAnzahl;
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function aktivateCheckBox($checkboxIDs): bool
    {
        if (!\is_array($checkboxIDs) || \count($checkboxIDs) === 0) {
            return false;
        }
        Shop::Container()->getDB()->query(
            'UPDATE tcheckbox
                SET nAktiv = 1
                WHERE kCheckBox IN (' . \implode(',', \array_map('\intval', $checkboxIDs)) . ');',
            ReturnType::DEFAULT
        );
        Shop::Container()->getCache()->flushTags(['checkbox']);

        return true;
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function deaktivateCheckBox($checkboxIDs): bool
    {
        if (!\is_array($checkboxIDs) || \count($checkboxIDs) === 0) {
            return false;
        }
        Shop::Container()->getDB()->query(
            'UPDATE tcheckbox
                SET nAktiv = 0
                WHERE kCheckBox IN (' . \implode(',', \array_map('\intval', $checkboxIDs)) . ');',
            ReturnType::DEFAULT
        );
        Shop::Container()->getCache()->flushTags(['checkbox']);

        return true;
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function deleteCheckBox($checkboxIDs): bool
    {
        if (!\is_array($checkboxIDs) || \count($checkboxIDs) === 0) {
            return false;
        }
        Shop::Container()->getDB()->query(
            'DELETE tcheckbox, tcheckboxsprache
                FROM tcheckbox
                LEFT JOIN tcheckboxsprache 
                    ON tcheckboxsprache.kCheckBox = tcheckbox.kCheckBox
                WHERE tcheckbox.kCheckBox IN (' . \implode(',', \array_map('\intval', $checkboxIDs)) . ')',
            ReturnType::AFFECTED_ROWS
        );
        Shop::Container()->getCache()->flushTags(['checkbox']);

        return true;
    }

    /**
     * @return stdClass[]
     */
    public function getCheckBoxFunctions(): array
    {
        return Shop::Container()->getDB()->query(
            'SELECT * 
                FROM tcheckboxfunktion 
                ORDER BY cName',
            ReturnType::COLLECTION
        )->each(function ($e) {
            $e->kCheckBoxFunktion = (int)$e->kCheckBoxFunktion;
            $e->cName             = __($e->cName);
        })->toArray();
    }

    /**
     * @param array $texts
     * @param array $descriptions
     * @return $this
     */
    public function insertDB($texts, $descriptions): self
    {
        if (\is_array($texts) && \count($texts) > 0) {
            $checkbox = GeneralObject::copyMembers($this);
            unset(
                $checkbox->kCheckBox,
                $checkbox->cID,
                $checkbox->kKundengruppe_arr,
                $checkbox->kAnzeigeOrt_arr,
                $checkbox->oCheckBoxFunktion,
                $checkbox->dErstellt_DE,
                $checkbox->oLink,
                $checkbox->oCheckBoxSprache_arr,
                $checkbox->cLink
            );
            $kCheckBox       = Shop::Container()->getDB()->insert('tcheckbox', $checkbox);
            $this->kCheckBox = !empty($checkbox->kCheckBox) ? (int)$checkbox->kCheckBox : $kCheckBox;
            $this->insertDBSprache($texts, $descriptions);
        }

        return $this;
    }

    /**
     * @param array $texts
     * @param array $descriptions
     * @return $this
     */
    private function insertDBSprache(array $texts, $descriptions): self
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
            $this->oCheckBoxSprache_arr[$iso]->kCheckBoxSprache = Shop::Container()->getDB()->insert(
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
        $lang = LanguageHelper::getLangIDFromIso($iso);

        return (int)($lang->kSprachISO ?? 0);
    }

    /**
     * @param object $customer
     * @return bool
     */
    private function sfCheckBoxNewsletter($customer): bool
    {
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'newsletter_inc.php';

        if (!\is_object($customer)) {
            return false;
        }
        $tmp            = new stdClass();
        $tmp->cAnrede   = $customer->cAnrede;
        $tmp->cVorname  = $customer->cVorname;
        $tmp->cNachname = $customer->cNachname;
        $tmp->cEmail    = $customer->cMail;

        \fuegeNewsletterEmpfaengerEin($tmp);

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
        $conf = Shop::getSettings([\CONF_EMAILS]);
        if (!empty($conf['emails']['email_master_absender'])) {
            $data                = new stdClass();
            $data->oCheckBox     = $checkBox;
            $data->oKunde        = $customer;
            $data->tkunde        = $customer;
            $data->cAnzeigeOrt   = $this->mappeCheckBoxOrte($location);
            $data->mail          = new stdClass();
            $data->mail->toEmail = $conf['emails']['email_master_absender'];

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
        $locations = self::gibCheckBoxAnzeigeOrte();

        return $locations[$location] ?? '';
    }

    /**
     * @return array
     */
    public static function gibCheckBoxAnzeigeOrte(): array
    {
        Shop::Container()->getGetText()->loadAdminLocale('pages/checkbox');

        return [
            \CHECKBOX_ORT_REGISTRIERUNG        => __('checkboxPositionRegistration'),
            \CHECKBOX_ORT_BESTELLABSCHLUSS     => __('checkboxPositionOrderFinal'),
            \CHECKBOX_ORT_NEWSLETTERANMELDUNG  => __('checkboxPositionNewsletterRegistration'),
            \CHECKBOX_ORT_KUNDENDATENEDITIEREN => __('checkboxPositionEditCustomerData'),
            \CHECKBOX_ORT_KONTAKT              => __('checkboxPositionContactForm'),
            \CHECKBOX_ORT_FRAGE_ZUM_PRODUKT    => __('checkboxPositionProductQuestion'),
            \CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT => __('checkboxPositionAvailabilityNotification')
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
