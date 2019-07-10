<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\Customer\CustomerAttribute;
use JTL\Customer\CustomerField;
use JTL\Customer\Kunde;
use JTL\Customer\Kundendatenhistory;
use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\GeneralDataProtection\Journal;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Shop;
use JTL\SimpleMail;
use JTL\XML;
use stdClass;

/**
 * Class Customer
 * @package JTL\dbeS\Sync
 */
final class Customer extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return array|mixed|null
     */
    public function handle(Starter $starter)
    {
        foreach ($starter->getXML() as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            $fileName     = \pathinfo($file)['basename'];
            // the first 5 cases come from Kunden_xml.php
            if ($fileName === 'del_kunden.xml') {
                $this->handleDeletes($xml);
            } elseif ($fileName === 'ack_kunden.xml') {
                $this->handleACK($xml);
            } elseif ($fileName === 'gutscheine.xml') {
                $this->handleVouchers($xml);
            } elseif ($fileName === 'aktiviere_kunden.xml') {
                $this->activate($xml);
            } elseif ($fileName === 'passwort_kunden.xml') {
                $this->generatePasswords($xml);
            } else {
                return $this->handleInserts($xml); // from SetKunde_xml.php
            }
        }

        return null;
    }

    /**
     * @param array $xml
     */
    private function activate(array $xml): void
    {
        $customers = $this->mapper->mapArray($xml['aktiviere_kunden'], 'tkunde', '');
        foreach ($customers as $customerData) {
            if (!($customerData->kKunde > 0 && $customerData->kKundenGruppe > 0)) {
                continue;
            }
            $customerData->kKunde = (int)$customerData->kKunde;

            $customer = new Kunde($customerData->kKunde);
            if ($customer->kKunde > 0 && $customer->kKundengruppe !== $customerData->kKundenGruppe) {
                $this->db->update(
                    'tkunde',
                    'kKunde',
                    (int)$customerData->kKunde,
                    (object)['kKundengruppe' => (int)$customerData->kKundenGruppe]
                );
                $customer->kKundengruppe = (int)$customerData->kKundenGruppe;
                $obj                     = new stdClass();
                $obj->tkunde             = $customer;
                if ($customer->cMail) {
                    $mailer = Shop::Container()->get(Mailer::class);
                    $mail   = new Mail();
                    $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_KUNDENGRUPPE_ZUWEISEN, $obj));
                }
            }
            $this->db->update('tkunde', 'kKunde', (int)$customerData->kKunde, (object)['cAktiv' => 'Y']);
        }
    }

    /**
     * @param array $xml
     */
    private function generatePasswords(array $xml): void
    {
        $customers = $this->mapper->mapArray($xml['passwort_kunden'], 'tkunde', '');
        foreach ($customers as $customerData) {
            if (empty($customerData->kKunde)) {
                continue;
            }
            $customer = new Kunde((int)$customerData->kKunde);
            if ($customer->nRegistriert === 1 && $customer->cMail) {
                $customer->prepareResetPassword();
            } else {
                \syncException(
                    'Kunde hat entweder keine Emailadresse oder es ist ein unregistrierter Kunde',
                    \FREIDEFINIERBARER_FEHLER
                );
            }
        }
    }

    /**
     * @param array $xml
     */
    private function handleDeletes(array $xml): void
    {
        if (!isset($xml['del_kunden']['kKunde'])) {
            return;
        }
        if (!\is_array($xml['del_kunden']['kKunde'])) {
            $xml['del_kunden']['kKunde'] = [$xml['del_kunden']['kKunde']];
        }
        foreach ($xml['del_kunden']['kKunde'] as $customerID) {
            (new Kunde((int)$customerID))->deleteAccount(Journal::ISSUER_TYPE_DBES, 0, true);
        }
    }

    /**
     * @param array $xml
     */
    private function handleACK(array $xml): void
    {
        if (!isset($xml['ack_kunden']['kKunde'])) {
            return;
        }
        if (!\is_array($xml['ack_kunden']['kKunde']) && (int)$xml['ack_kunden']['kKunde'] > 0) {
            $xml['ack_kunden']['kKunde'] = [$xml['ack_kunden']['kKunde']];
        }
        if (\is_array($xml['ack_kunden']['kKunde'])) {
            foreach ($xml['ack_kunden']['kKunde'] as $customerID) {
                if ($customerID > 0) {
                    $this->db->update('tkunde', 'kKunde', (int)$customerID, (object)['cAbgeholt' => 'Y']);
                }
            }
        }
    }

    /**
     * @param array $xml
     */
    private function handleVouchers(array $xml): void
    {
        if (!isset($xml['gutscheine']['gutschein']) || !\is_array($xml['gutscheine']['gutschein'])) {
            return;
        }
        foreach ($this->mapper->mapArray($xml['gutscheine'], 'gutschein', 'mGutschein') as $voucher) {
            if (!($voucher->kGutschein > 0 && $voucher->kKunde > 0)) {
                continue;
            }
            $exists = $this->db->select('tgutschein', 'kGutschein', (int)$voucher->kGutschein);
            if (!isset($exists->kGutschein) || !$exists->kGutschein) {
                $this->db->insert('tgutschein', $voucher);
                $this->logger->debug(
                    'Gutschein fuer kKunde ' .
                    (int)$voucher->kKunde . ' wurde eingeloest. ' .
                    \print_r($voucher, true)
                );
                $this->db->query(
                    'UPDATE tkunde 
                    SET fGuthaben = fGuthaben + ' . (float)$voucher->fWert . ' 
                    WHERE kKunde = ' . (int)$voucher->kKunde,
                    ReturnType::DEFAULT
                );
                $this->db->query(
                    'UPDATE tkunde 
                    SET fGuthaben = 0 
                    WHERE kKunde = ' . (int)$voucher->kKunde . ' 
                        AND fGuthaben < 0',
                    ReturnType::AFFECTED_ROWS
                );
                $customer        = new Kunde((int)$voucher->kKunde);
                $obj             = new stdClass();
                $obj->tkunde     = $customer;
                $obj->tgutschein = $voucher;
                if ($customer->cMail) {
                    $mailer = Shop::Container()->get(Mailer::class);
                    $mail   = new Mail();
                    $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_GUTSCHEIN, $obj));
                }
            }
        }
    }

    /**
     * @param array $xml
     * @return array
     */
    private function handleInserts(array $xml): array
    {
        $res                     = [];
        $customer                = new Kunde();
        $customer->kKundengruppe = 0;

        if (\is_array($xml['tkunde attr'])) {
            $customer->kKundengruppe = (int)$xml['tkunde attr']['kKundengruppe'];
            $customer->kSprache      = (int)$xml['tkunde attr']['kSprache'];
        }
        if (!isset($xml['tkunde']) || !\is_array($xml['tkunde'])) {
            return $res;
        }
        $source = $xml['tkunde'];
        $this->mapper->mapObject($customer, $source, 'mKunde');
        $customerAttributes = $this->getCustomerAttributes($source);
        $customer->cAnrede  = $this->mapSalutation($customer->cAnrede);

        $lang = $this->db->select('tsprache', 'kSprache', (int)$customer->kSprache);
        if (empty($lang->kSprache)) {
            $lang               = $this->db->select('tsprache', 'cShopStandard', 'Y');
            $customer->kSprache = $lang->kSprache;
        }
        $kInetKunde  = (int)$xml['tkunde attr']['kKunde'];
        $oldCustomer = $kInetKunde > 0 ? new Kunde($kInetKunde) : new stdClass();
        // Kunde existiert mit dieser kInetKunde
        // Kunde wird aktualisiert bzw. seine KdGrp wird geändert
        if (isset($oldCustomer->kKunde) && $oldCustomer->kKunde > 0) {
            $res = $this->merge($customer, $oldCustomer, $kInetKunde, $customerAttributes, $res);
        } else {
            // Kunde existiert mit dieser kInetKunde im Shop nicht. Gib diese Info zurück an Wawi
            if ($kInetKunde > 0) {
                $res['keys']['tkunde attr']['kKunde'] = 0;
                $res['keys']['tkunde']                = '';
                $this->logger->error(
                    'Verknuepfter Kunde in Wawi existiert nicht im Shop: ' .
                    XML::serialize($res)
                );

                return $res;
            }
            // Kunde existiert nicht im Shop - check, ob email schon belegt
            $oldCustomer = $this->db->select(
                'tkunde',
                'nRegistriert',
                1,
                'cMail',
                $customer->cMail,
                null,
                null,
                false,
                'kKunde'
            );
            if (isset($oldCustomer->kKunde) && $oldCustomer->kKunde > 0) {
                // Email vergeben -> Kunde wird nicht neu angelegt, sondern der Kunde wird an Wawi zurückgegeben
                return $this->notifyDuplicateCustomer($oldCustomer, $xml);
            }
            // Email noch nicht belegt, der Kunde muss neu erstellt werden -> KUNDE WIRD NEU ERSTELLT
            $kInetKunde = $this->addNewCustomer($customer, $customerAttributes);

            $res['keys']['tkunde attr']['kKunde'] = $kInetKunde;
            $res['keys']['tkunde']                = '';
        }

        return $kInetKunde > 0 ? $this->addAddressData($kInetKunde, $res, $source) : $res;
    }

    /**
     * @param stdClass $oldCustomer
     * @param array    $xml
     * @return array
     */
    private function notifyDuplicateCustomer(stdClass $oldCustomer, array $xml): array
    {
        $cstmr  = $this->db->query(
            "SELECT kKunde, kKundengruppe, kSprache, cKundenNr, cPasswort, cAnrede, cTitel, cVorname,
                    cNachname, cFirma, cZusatz, cStrasse, cHausnummer, cAdressZusatz, cPLZ, cOrt, cBundesland, 
                    cLand, cTel, cMobil, cFax, cMail, cUSTID, cWWW, fGuthaben, cNewsletter, dGeburtstag, fRabatt,
                    cHerkunft, dErstellt, dVeraendert, cAktiv, cAbgeholt,
                    date_format(dGeburtstag, '%d.%m.%Y') AS dGeburtstag_formatted, nRegistriert
                    FROM tkunde
                    WHERE kKunde = " . (int)$oldCustomer->kKunde,
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        $crypto = Shop::Container()->getCryptoService();

        $cstmr[0]['cNachname'] = \trim($crypto->decryptXTEA($cstmr[0]['cNachname']));
        $cstmr[0]['cFirma']    = \trim($crypto->decryptXTEA($cstmr[0]['cFirma']));
        $cstmr[0]['cZusatz']   = \trim($crypto->decryptXTEA($cstmr[0]['cZusatz']));
        $cstmr[0]['cStrasse']  = \trim($crypto->decryptXTEA($cstmr[0]['cStrasse']));
        $cstmr[0]['cAnrede']   = Kunde::mapSalutation($cstmr[0]['cAnrede'], $cstmr[0]['kSprache']);
        // Strasse und Hausnummer zusammenführen
        $cstmr[0]['cStrasse'] .= ' ' . $cstmr[0]['cHausnummer'];
        unset($cstmr[0]['cHausnummer']);
        // Land ausgeschrieben der Wawi geben
        $cstmr[0]['cLand'] = LanguageHelper::getCountryCodeByCountryName($cstmr[0]['cLand']);
        unset($cstmr[0]['cPasswort']);
        $cstmr['0 attr']             = $this->buildAttributes($cstmr[0]);
        $cstmr[0]['tkundenattribut'] = $this->db->query(
            'SELECT *
                FROM tkundenattribut
                 WHERE kKunde = ' . (int)$cstmr['0 attr']['kKunde'],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        $attributeCount              = \count($cstmr[0]['tkundenattribut']);
        for ($o = 0; $o < $attributeCount; $o++) {
            $cstmr[0]['tkundenattribut'][$o . ' attr'] =
                $this->buildAttributes($cstmr[0]['tkundenattribut'][$o]);
        }
        $xml['kunden attr']['anzahl'] = 1;
        $xml['kunden']['tkunde']      = $cstmr;
        $this->logger->error('Dieser Kunde existiert: ' . XML::serialize($xml));

        return $xml;
    }

    /**
     * @param array $source
     * @return array
     */
    private function getCustomerAttributes(array $source): array
    {
        $customerAttributes = [];
        if (GeneralObject::hasCount('tkundenattribut', $source)) {
            $members = \array_keys($source['tkundenattribut']);
            if ($members[0] == '0') {
                foreach ($source['tkundenattribut'] as $data) {
                    $customerAttribute        = new stdClass();
                    $customerAttribute->cName = $data['cName'];
                    $customerAttribute->cWert = $data['cWert'];
                    $customerAttributes[]     = $customerAttribute;
                }
            } else {
                $customerAttribute        = new stdClass();
                $customerAttribute->cName = $source['tkundenattribut']['cName'];
                $customerAttribute->cWert = $source['tkundenattribut']['cWert'];
                $customerAttributes[]     = $customerAttribute;
            }
        }

        return $customerAttributes;
    }

    /**
     * @param Kunde $customer
     * @param array $customerAttributes
     * @return int
     */
    private function addNewCustomer(Kunde $customer, array $customerAttributes): int
    {
        $passwordService             = Shop::Container()->getPasswordService();
        $customer->dErstellt         = 'NOW()';
        $customer->cPasswortKlartext = $passwordService->generate(12);
        $customer->cPasswort         = $passwordService->hash($customer->cPasswortKlartext);
        $customer->nRegistriert      = 1;
        $customer->cAbgeholt         = 'Y';
        $customer->cAktiv            = 'Y';
        $customer->cSperre           = 'N';
        // mail an Kunden mit Accounterstellung durch Shopbetreiber
        $obj         = new stdClass();
        $obj->tkunde = $customer;
        if ($customer->cMail) {
            $mailer = Shop::Container()->get(Mailer::class);
            $mail   = new Mail();
            $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER, $obj));
        }
        unset($customer->cPasswortKlartext, $customer->Anrede);
        $kInetKunde = $customer->insertInDB();
        if (\count($customerAttributes) > 0) {
            $this->saveAttribute($customer->kKunde, $customer->kSprache, $customerAttributes);
        }

        return $kInetKunde;
    }

    /**
     * @param Kunde $customer
     * @param Kunde $oldCustomer
     * @param int   $kInetKunde
     * @param array $customerAttributes
     * @param array $res
     * @return array
     */
    private function merge(Kunde $customer, Kunde $oldCustomer, int $kInetKunde, array $customerAttributes, array $res): array
    {
        // Angaben vom alten Kunden übernehmen
        $customer->kKunde      = $kInetKunde;
        $customer->cAbgeholt   = 'Y';
        $customer->cAktiv      = 'Y';
        $customer->dVeraendert = 'NOW()';

        if ($customer->cMail !== $oldCustomer->cMail) {
            // E-Mail Adresse geändert - Verwendung prüfen!
            if (Text::filterEmailAddress($customer->cMail) === false
                || SimpleMail::checkBlacklist($customer->cMail)
                || $this->db->select('tkunde', 'cMail', $customer->cMail, 'nRegistriert', 1) !== null
            ) {
                // E-Mail ist invalide, blacklisted bzw. wird bereits im Shop verwendet
                $res['keys']['tkunde attr']['kKunde'] = 0;
                $res['keys']['tkunde']                = '';

                return $res;
            }
            // Mail an Kunden mit Info, dass Zugang verändert wurde
            $obj         = new stdClass();
            $obj->tkunde = $customer;

            $mailer = Shop::Container()->get(Mailer::class);
            $mail   = new Mail();
            $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER, $obj));
        }

        $customer->cPasswort    = $oldCustomer->cPasswort;
        $customer->nRegistriert = $oldCustomer->nRegistriert;
        $customer->dErstellt    = $oldCustomer->dErstellt;
        $customer->fGuthaben    = $oldCustomer->fGuthaben;
        $customer->cHerkunft    = $oldCustomer->cHerkunft;
        // schaue, ob dieser Kunde diese Kundengruppe schon hat
        if ($oldCustomer->kKundengruppe !== $customer->kKundengruppe && $customer->cMail) {
            // Mail an Kunden mit Info, dass Kundengruppe verändert wurde
            $obj         = new stdClass();
            $obj->tkunde = $customer;

            $mailer = Shop::Container()->get(Mailer::class);
            $mail   = new Mail();
            $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_KUNDENGRUPPE_ZUWEISEN, $obj));
        }
        // Hausnummer extrahieren
        $this->extractStreet($customer);
        // $this->upsert('tkunde', [$Kunde], 'kKunde');
        $customer->updateInDB();
        Kundendatenhistory::saveHistory($oldCustomer, $customer, Kundendatenhistory::QUELLE_DBES);
        if (\count($customerAttributes) > 0) {
            $this->saveAttribute($customer->kKunde, $customer->kSprache, $customerAttributes);
        }
        $res['keys']['tkunde attr']['kKunde'] = $kInetKunde;
        $res['keys']['tkunde']                = '';

        return $res;
    }

    /**
     * @param int   $kInetKunde
     * @param array $res
     * @param array $source
     * @return array
     */
    private function addAddressData(int $kInetKunde, array $res, array $source): array
    {
        // kunde akt. bzw. neu inserted
        $crypto = Shop::Container()->getCryptoService();
        if (GeneralObject::hasCount('tadresse', $source)
            && (!isset($source['tadresse attr']) || !\is_array($source['tadresse attr']))
        ) {
            // mehrere adressen
            $nr                = 0;
            $cntLieferadressen = \count($source['tadresse']) / 2;
            for ($i = 0; $i < $cntLieferadressen; $i++) {
                $deliveryAddress = new stdClass();
                if ($source['tadresse'][$i . ' attr']['kInetAdresse'] > 0) {
                    //update
                    $deliveryAddress->kLieferadresse = $source['tadresse'][$i . ' attr']['kInetAdresse'];
                    $deliveryAddress->kKunde         = $kInetKunde;
                    $this->mapper->mapObject($deliveryAddress, $source['tadresse'][$i], 'mLieferadresse');
                    // Hausnummer extrahieren
                    $this->extractStreet($deliveryAddress);
                    // verschlüsseln: Nachname, Firma, Strasse
                    $deliveryAddress->cNachname = $crypto->encryptXTEA(\trim($deliveryAddress->cNachname));
                    $deliveryAddress->cFirma    = $crypto->encryptXTEA(\trim($deliveryAddress->cFirma));
                    $deliveryAddress->cZusatz   = $crypto->encryptXTEA(\trim($deliveryAddress->cZusatz));
                    $deliveryAddress->cStrasse  = $crypto->encryptXTEA(\trim($deliveryAddress->cStrasse));
                    $deliveryAddress->cAnrede   = $this->mapSalutation($deliveryAddress->cAnrede);
                    $this->upsert('tlieferadresse', [$deliveryAddress], 'kLieferadresse');
                } else {
                    $deliveryAddress->kKunde = $kInetKunde;
                    $this->mapper->mapObject($deliveryAddress, $source['tadresse'][$i], 'mLieferadresse');
                    // Hausnummer extrahieren
                    $this->extractStreet($deliveryAddress);
                    // verschlüsseln: Nachname, Firma, Strasse
                    $deliveryAddress->cNachname = $crypto->encryptXTEA(\trim($deliveryAddress->cNachname));
                    $deliveryAddress->cFirma    = $crypto->encryptXTEA(\trim($deliveryAddress->cFirma));
                    $deliveryAddress->cZusatz   = $crypto->encryptXTEA(\trim($deliveryAddress->cZusatz));
                    $deliveryAddress->cStrasse  = $crypto->encryptXTEA(\trim($deliveryAddress->cStrasse));
                    $deliveryAddress->cAnrede   = $this->mapSalutation($deliveryAddress->cAnrede);
                    $kInetLieferadresse         = $this->db->insert('tlieferadresse', $deliveryAddress);
                    if ($kInetLieferadresse > 0) {
                        if (!\is_array($res['keys']['tkunde'])) {
                            $res['keys']['tkunde'] = [
                                'tadresse' => []
                            ];
                        }
                        $res['keys']['tkunde']['tadresse'][$nr . ' attr'] = [
                            'kAdresse'     => $source['tadresse'][$i . ' attr']['kAdresse'],
                            'kInetAdresse' => $kInetLieferadresse,
                        ];
                        $res['keys']['tkunde']['tadresse'][$nr]           = '';

                        $nr++;
                    }
                }
            }

            return $res;
        }
        if (GeneralObject::isCountable('tadresse attr', $source)) {
            // nur eine lieferadresse
            if ($source['tadresse attr']['kInetAdresse'] > 0) {
                //update
                $deliveryAddress                 = new stdClass();
                $deliveryAddress->kLieferadresse = $source['tadresse attr']['kInetAdresse'];
                $deliveryAddress->kKunde         = $kInetKunde;
                $this->mapper->mapObject($deliveryAddress, $source['tadresse'], 'mLieferadresse');
                // Hausnummer extrahieren
                $this->extractStreet($deliveryAddress);
                // verschlüsseln: Nachname, Firma, Strasse
                $deliveryAddress->cNachname = $crypto->encryptXTEA(\trim($deliveryAddress->cNachname));
                $deliveryAddress->cFirma    = $crypto->encryptXTEA(\trim($deliveryAddress->cFirma));
                $deliveryAddress->cZusatz   = $crypto->encryptXTEA(\trim($deliveryAddress->cZusatz));
                $deliveryAddress->cStrasse  = $crypto->encryptXTEA(\trim($deliveryAddress->cStrasse));
                $deliveryAddress->cAnrede   = $this->mapSalutation($deliveryAddress->cAnrede);
                $this->upsert('tlieferadresse', [$deliveryAddress], 'kLieferadresse');
            } else {
                $deliveryAddress         = new stdClass();
                $deliveryAddress->kKunde = $kInetKunde;
                $this->mapper->mapObject($deliveryAddress, $source['tadresse'], 'mLieferadresse');
                // Hausnummer extrahieren
                $this->extractStreet($deliveryAddress);
                // verschlüsseln: Nachname, Firma, Strasse
                $deliveryAddress->cNachname = $crypto->encryptXTEA(\trim($deliveryAddress->cNachname));
                $deliveryAddress->cFirma    = $crypto->encryptXTEA(\trim($deliveryAddress->cFirma));
                $deliveryAddress->cZusatz   = $crypto->encryptXTEA(\trim($deliveryAddress->cZusatz));
                $deliveryAddress->cStrasse  = $crypto->encryptXTEA(\trim($deliveryAddress->cStrasse));
                $deliveryAddress->cAnrede   = $this->mapSalutation($deliveryAddress->cAnrede);
                $kInetLieferadresse         = $this->db->insert('tlieferadresse', $deliveryAddress);
                if ($kInetLieferadresse > 0) {
                    $res['keys']['tkunde'] = [
                        'tadresse attr' => [
                            'kAdresse'     => $source['tadresse attr']['kAdresse'],
                            'kInetAdresse' => $kInetLieferadresse,
                        ],
                        'tadresse'      => '',
                    ];
                }
            }
        }

        return $res;
    }

    /**
     * @param int   $customerID
     * @param int   $languageID
     * @param array $attributes
     */
    private function saveAttribute(int $customerID, int $languageID, $attributes): void
    {
        if ($customerID <= 0 || $languageID <= 0 || !\is_array($attributes) || \count($attributes) === 0) {
            return;
        }
        foreach ($attributes as $attribute) {
            $field = CustomerField::loadByName($attribute->cName, $languageID);
            if ($field->getID() > 0 && $field->validate($attribute->cWert) !== CustomerField::VALIDATE_OK) {
                $customerAttr = new CustomerAttribute();
                $customerAttr->setCustomerID($customerID);
                $customerAttr->setCustomerFieldID($field->getID());
                $customerAttr->setName($attribute->cName);
                $customerAttr->setValue($attribute->cWert);
                $customerAttr->save();
            }
        }
    }
}
