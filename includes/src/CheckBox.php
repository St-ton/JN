<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\GeneralObject;

/**
 * Class CheckBox
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
     * @var \Link\Link
     */
    public $oLink;

    /**
     * @param int $kCheckBox
     */
    public function __construct(int $kCheckBox = 0)
    {
        $this->oLink = new \Link\Link(Shop::Container()->getDB());
        $this->loadFromDB($kCheckBox);
    }

    /**
     * @param int $kCheckBox
     * @return $this
     */
    private function loadFromDB(int $kCheckBox): self
    {
        if ($kCheckBox <= 0) {
            return $this;
        }
        $cacheID = 'chkbx_' . $kCheckBox;
        if (($oCheckBox = Shop::Container()->getCache()->get($cacheID)) !== false) {
            foreach (array_keys(get_object_vars($oCheckBox)) as $cMember) {
                $this->$cMember = $oCheckBox->$cMember;
            }

            return $this;
        }
        $db        = Shop::Container()->getDB();
        $oCheckBox = $db->queryPrepared(
            "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE 
                FROM tcheckbox 
                WHERE kCheckBox = :cbid",
            ['cbid' => $kCheckBox],
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($oCheckBox === false || $oCheckBox === null) {
            return $this;
        }
        foreach (array_keys(get_object_vars($oCheckBox)) as $cMember) {
            $this->$cMember = $oCheckBox->$cMember;
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
        $this->kKundengruppe_arr = StringHandler::parseSSK($oCheckBox->cKundengruppe);
        $this->kAnzeigeOrt_arr   = StringHandler::parseSSK($oCheckBox->cAnzeigeOrt);
        // CheckBoxFunktion
        // Falls mal kCheckBoxFunktion gesetzt war aber diese Funktion nicht mehr existiert (deinstallation vom Plugin)
        // wird kCheckBoxFunktion auf 0 gesetzt
        if ($this->kCheckBoxFunktion > 0) {
            $oCheckBoxFunktion = $db->select(
                'tcheckboxfunktion',
                'kCheckBoxFunktion',
                (int)$this->kCheckBoxFunktion
            );
            if (isset($oCheckBoxFunktion->kCheckBoxFunktion) && $oCheckBoxFunktion->kCheckBoxFunktion > 0) {
                $this->oCheckBoxFunktion = $oCheckBoxFunktion;
            } else {
                $this->kCheckBoxFunktion = 0;
                $upd                     = new stdClass();
                $upd->kCheckBoxFunktion  = 0;
                $db->update('tcheckbox', 'kCheckBox', (int)$this->kCheckBox, $upd);
            }
        }
        if ($this->kLink > 0) {
            $this->oLink = new \Link\Link($db);
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
            $this->oCheckBoxSprache_arr[$translation->kSprache] = $translation;
        }
        Shop::Container()->getCache()->set($cacheID, $this, [CACHING_GROUP_CORE, 'checkbox']);

        return $this;
    }

    /**
     * @param int $nAnzeigeOrt
     * @param int $kKundengruppe
     * @param bool $bAktiv
     * @param bool $bSprache
     * @param bool $bSpecial
     * @param bool $bLogging
     * @return CheckBox[]
     */
    public function getCheckBoxFrontend(
        int $nAnzeigeOrt,
        int $kKundengruppe = 0,
        bool $bAktiv = false,
        bool $bSprache = false,
        bool $bSpecial = false,
        bool $bLogging = false
    ): array {
        if (!$kKundengruppe) {
            if (isset($_SESSION['Kundengruppe']->kKundengruppe)) {
                $kKundengruppe = \Session\Frontend::getCustomerGroup()->getID();
            } else {
                $kKundengruppe = Kundengruppe::getDefaultGroupID();
            }
        }
        $checkboxes = [];
        $cSQL       = '';
        if ($bAktiv) {
            $cSQL .= ' AND nAktiv = 1';
        }
        if ($bSpecial) {
            $cSQL .= ' AND kCheckBoxFunktion > 0';
        }
        if ($bLogging) {
            $cSQL .= ' AND nLogging = 1';
        }
        $checkBoxIDs = Shop::Container()->getDB()->query(
            "SELECT kCheckBox FROM tcheckbox
                WHERE FIND_IN_SET('" . $nAnzeigeOrt . "', REPLACE(cAnzeigeOrt, ';', ',')) > 0
                    AND FIND_IN_SET('{$kKundengruppe}', REPLACE(cKundengruppe, ';', ',')) > 0
                    " . $cSQL . '
                ORDER BY nSort',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($checkBoxIDs as $item) {
            $checkboxes[] = new self((int)$item->kCheckBox);
        }
        executeHook(HOOK_CHECKBOX_CLASS_GETCHECKBOXFRONTEND, [
            'oCheckBox_arr' => &$checkboxes,
            'nAnzeigeOrt'   => $nAnzeigeOrt,
            'kKundengruppe' => $kKundengruppe,
            'bAktiv'        => $bAktiv,
            'bSprache'      => $bSprache,
            'bSpecial'      => $bSpecial,
            'bLogging'      => $bLogging
        ]);

        return $checkboxes;
    }

    /**
     * @param int   $nAnzeigeOrt
     * @param int   $kKundengruppe
     * @param array $post
     * @param bool  $bAktiv
     * @return array
     */
    public function validateCheckBox(
        int $nAnzeigeOrt,
        int $kKundengruppe,
        array $post,
        bool $bAktiv = false
    ): array {
        $checkBoxes = $this->getCheckBoxFrontend($nAnzeigeOrt, $kKundengruppe, $bAktiv);
        $checks     = [];
        foreach ($checkBoxes as $oCheckBox) {
            if ((int)$oCheckBox->nPflicht === 1 && !isset($post[$oCheckBox->cID])) {
                $checks[$oCheckBox->cID] = 1;
            }
        }

        return $checks;
    }

    /**
     * @param int   $location
     * @param int   $kKundengruppe
     * @param bool  $bAktiv
     * @param array $post
     * @param array $params
     * @return $this
     */
    public function triggerSpecialFunction(
        int $location,
        int $kKundengruppe,
        bool $bAktiv,
        array $post,
        array $params = []
    ): self {
        $checkBoxes = $this->getCheckBoxFrontend($location, $kKundengruppe, $bAktiv, true, true);
        foreach ($checkBoxes as $checkBox) {
            if (!isset($post[$checkBox->cID])) {
                continue;
            }
            if ($checkBox->oCheckBoxFunktion->kPlugin > 0) {
                $params['oCheckBox'] = $checkBox;
                executeHook(HOOK_CHECKBOX_CLASS_TRIGGERSPECIALFUNCTION, $params);
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
        foreach ($checkBoxes as $checkBox) {
            //@todo: casting to bool does not seem to be a good idea.
            //$cPost_arr looks like this: array ( [CheckBox_31] => Y, [CheckBox_24] => Y, [abschluss] => 1)
            $checked                       = isset($post[$checkBox->cID])
                ? (bool)$post[$checkBox->cID]
                : false;
            $checked                       = ($checked === true) ? 1 : 0;
            $oCheckBoxLogging              = new stdClass();
            $oCheckBoxLogging->kCheckBox   = $checkBox->kCheckBox;
            $oCheckBoxLogging->kBesucher   = (int)$_SESSION['oBesucher']->kBesucher;
            $oCheckBoxLogging->kBestellung = isset($_SESSION['kBestellung'])
                ? (int)$_SESSION['kBestellung']
                : 0;
            $oCheckBoxLogging->bChecked    = $checked;
            $oCheckBoxLogging->dErstellt   = 'NOW()';

            Shop::Container()->getDB()->insert('tcheckboxlogging', $oCheckBoxLogging);
        }

        return $this;
    }

    /**
     * @param string $cLimitSQL
     * @param bool   $bAktiv
     * @return CheckBox[]
     */
    public function getAllCheckBox(string $cLimitSQL = '', bool $bAktiv = false): array
    {
        $checkBoxes = [];
        $sql        = '';
        if ($bAktiv) {
            $sql = ' WHERE nAktiv = 1';
        }
        $ids = Shop::Container()->getDB()->query(
            'SELECT kCheckBox 
                FROM tcheckbox' . $sql . ' 
                ORDER BY nSort ' . $cLimitSQL,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($ids as $i => $item) {
            $checkBoxes[$i] = new self((int)$item->kCheckBox);
        }

        return $checkBoxes;
    }

    /**
     * @param bool $bAktiv
     * @return int
     */
    public function getAllCheckBoxCount(bool $bAktiv = false): int
    {
        return (int)Shop::Container()->getDB()->query(
            'SELECT COUNT(*) AS nAnzahl 
                FROM tcheckbox' . ($bAktiv ? ' WHERE nAktiv = 1' : ''),
            \DB\ReturnType::SINGLE_OBJECT
        )->nAnzahl;
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function aktivateCheckBox($checkboxIDs): bool
    {
        if (!is_array($checkboxIDs) || count($checkboxIDs) === 0) {
            return false;
        }
        foreach ($checkboxIDs as $kCheckBox) {
            Shop::Container()->getDB()->update('tcheckbox', 'kCheckBox', (int)$kCheckBox, (object)['nAktiv' => 1]);
        }
        Shop::Container()->getCache()->flushTags(['checkbox']);

        return true;
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function deaktivateCheckBox($checkboxIDs): bool
    {
        if (!is_array($checkboxIDs) || count($checkboxIDs) === 0) {
            return false;
        }
        foreach ($checkboxIDs as $kCheckBox) {
            Shop::Container()->getDB()->update('tcheckbox', 'kCheckBox', (int)$kCheckBox, (object)['nAktiv' => 0]);
        }
        Shop::Container()->getCache()->flushTags(['checkbox']);

        return true;
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function deleteCheckBox($checkboxIDs): bool
    {
        if (!is_array($checkboxIDs) || count($checkboxIDs) === 0) {
            return false;
        }
        Shop::Container()->getDB()->query(
            'DELETE tcheckbox, tcheckboxsprache
                FROM tcheckbox
                LEFT JOIN tcheckboxsprache 
                    ON tcheckboxsprache.kCheckBox = tcheckbox.kCheckBox
                WHERE tcheckbox.kCheckBox IN (' . implode(',', array_map('\intval', $checkboxIDs)) . ')',
            \DB\ReturnType::AFFECTED_ROWS
        );
        Shop::Container()->getCache()->flushTags(['checkbox']);

        return true;
    }

    /**
     * @return array
     */
    public function getCheckBoxFunctions(): array
    {
        return Shop::Container()->getDB()->query(
            'SELECT * 
                FROM tcheckboxfunktion 
                ORDER BY cName',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param array $texts
     * @param array $descriptions
     * @return $this
     */
    public function insertDB($texts, $descriptions): self
    {
        if (is_array($texts) && count($texts) > 0) {
            $oCheckBox = GeneralObject::copyMembers($this);
            unset(
                $oCheckBox->kCheckBox,
                $oCheckBox->cID,
                $oCheckBox->kKundengruppe_arr,
                $oCheckBox->kAnzeigeOrt_arr,
                $oCheckBox->oCheckBoxFunktion,
                $oCheckBox->dErstellt_DE,
                $oCheckBox->oLink,
                $oCheckBox->oCheckBoxSprache_arr,
                $oCheckBox->cLink
            );
            $kCheckBox       = Shop::Container()->getDB()->insert('tcheckbox', $oCheckBox);
            $this->kCheckBox = !empty($oCheckBox->kCheckBox) ? (int)$oCheckBox->kCheckBox : $kCheckBox;
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

        foreach ($texts as $cISO => $cTextAssoc) {
            if (strlen($cTextAssoc) > 0) {
                $this->oCheckBoxSprache_arr[$cISO]                = new stdClass();
                $this->oCheckBoxSprache_arr[$cISO]->kCheckBox     = $this->kCheckBox;
                $this->oCheckBoxSprache_arr[$cISO]->kSprache      = $this->getSprachKeyByISO($cISO);
                $this->oCheckBoxSprache_arr[$cISO]->cText         = $cTextAssoc;
                $this->oCheckBoxSprache_arr[$cISO]->cBeschreibung = '';
                if (isset($descriptions[$cISO]) && strlen($descriptions[$cISO]) > 0) {
                    $this->oCheckBoxSprache_arr[$cISO]->cBeschreibung = $descriptions[$cISO];
                }
                $this->oCheckBoxSprache_arr[$cISO]->kCheckBoxSprache = Shop::Container()->getDB()->insert(
                    'tcheckboxsprache',
                    $this->oCheckBoxSprache_arr[$cISO]
                );
            }
        }

        return $this;
    }

    /**
     * @param string $cISO
     * @return int
     */
    private function getSprachKeyByISO(string $cISO): int
    {
        if (strlen($cISO) > 0) {
            $oSprache = Shop::Container()->getDB()->select('tsprache', 'cISO', $cISO);
            if ($oSprache !== null && $oSprache->kSprache > 0) {
                return (int)$oSprache->kSprache;
            }
        }

        return 0;
    }

    /**
     * @param object $knd
     * @return bool
     */
    private function sfCheckBoxNewsletter($knd): bool
    {
        require_once PFAD_ROOT . PFAD_INCLUDES . 'newsletter_inc.php';

        if (!is_object($knd)) {
            return false;
        }
        $oKundeTMP            = new stdClass();
        $oKundeTMP->cAnrede   = $knd->cAnrede;
        $oKundeTMP->cVorname  = $knd->cVorname;
        $oKundeTMP->cNachname = $knd->cNachname;
        $oKundeTMP->cEmail    = $knd->cMail;

        fuegeNewsletterEmpfaengerEin($oKundeTMP);

        return true;
    }

    /**
     * @param object $oKunde
     * @param object $oCheckBox
     * @param int    $nAnzeigeOrt
     * @return bool
     */
    public function sfCheckBoxMailToAdmin($oKunde, $oCheckBox, int $nAnzeigeOrt): bool
    {
        if (!isset($oKunde->cVorname, $oKunde->cNachname, $oKunde->cMail)) {
            return false;
        }
        $conf = Shop::getSettings([CONF_EMAILS]);
        if (isset($conf['emails']['email_master_absender']) && strlen($conf['emails']['email_master_absender']) > 0) {
            require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
            $mail                = new stdClass();
            $mail->oCheckBox     = $oCheckBox;
            $mail->oKunde        = $oKunde;
            $mail->tkunde        = $oKunde;
            $mail->cAnzeigeOrt   = $this->mappeCheckBoxOrte($nAnzeigeOrt);
            $mail->mail          = new stdClass();
            $mail->mail->toEmail = $conf['emails']['email_master_absender'];

            sendeMail(MAILTEMPLATE_CHECKBOX_SHOPBETREIBER, $mail);
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
        return [
            CHECKBOX_ORT_REGISTRIERUNG        => 'Registrierung',
            CHECKBOX_ORT_BESTELLABSCHLUSS     => 'Bestellabschluss',
            CHECKBOX_ORT_NEWSLETTERANMELDUNG  => 'Newsletteranmeldung',
            CHECKBOX_ORT_KUNDENDATENEDITIEREN => 'Editieren von Kundendaten',
            CHECKBOX_ORT_KONTAKT              => 'Kontaktformular',
            CHECKBOX_ORT_FRAGE_ZUM_PRODUKT    => 'Frage zum Produkt',
            CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT => 'Verf&uuml;gbarkeitsanfrage'
        ];
    }

    /**
     * @return \Link\Link
     */
    public function getLink(): \Link\Link
    {
        return $this->oLink;
    }
}
