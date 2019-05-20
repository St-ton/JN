<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\Customer\Kunde;
use JTL\Customer\Kundendatenhistory;
use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\GeneralDataProtection\Journal;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Shop;
use JTL\SimpleMail;
use JTL\Sprache;
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
        foreach ($xml['del_kunden']['kKunde'] as $kKunde) {
            (new Kunde((int)$kKunde))->deleteAccount(Journal::ISSUER_TYPE_DBES, 0, true);
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
            foreach ($xml['ack_kunden']['kKunde'] as $kKunde) {
                $kKunde = (int)$kKunde;
                if ($kKunde > 0) {
                    $this->db->update('tkunde', 'kKunde', $kKunde, (object)['cAbgeholt' => 'Y']);
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
        $vouchers = $this->mapper->mapArray($xml['gutscheine'], 'gutschein', 'mGutschein');
        foreach ($vouchers as $voucher) {
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
        $nr                      = 0;
        $customer                = new Kunde();
        $customer->kKundengruppe = 0;
        $customerAttributes      = [];

        if (\is_array($xml['tkunde attr'])) {
            $customer->kKundengruppe = (int)$xml['tkunde attr']['kKundengruppe'];
            $customer->kSprache      = (int)$xml['tkunde attr']['kSprache'];
        }
        if (!\is_array($xml['tkunde'])) {
            return $res;
        }
        $crypto = Shop::Container()->getCryptoService();
        $this->mapper->mapObject($customer, $xml['tkunde'], 'mKunde');
        // Kundenattribute
        if (isset($xml['tkunde']['tkundenattribut'])
            && \is_array($xml['tkunde']['tkundenattribut'])
            && \count($xml['tkunde']['tkundenattribut']) > 0
        ) {
            $members = \array_keys($xml['tkunde']['tkundenattribut']);
            if ($members[0] == '0') {
                foreach ($xml['tkunde']['tkundenattribut'] as $data) {
                    $customerAttribute        = new stdClass();
                    $customerAttribute->cName = $data['cName'];
                    $customerAttribute->cWert = $data['cWert'];
                    $customerAttributes[]     = $customerAttribute;
                }
            } else {
                $customerAttribute        = new stdClass();
                $customerAttribute->cName = $xml['tkunde']['tkundenattribut']['cName'];
                $customerAttribute->cWert = $xml['tkunde']['tkundenattribut']['cWert'];
                $customerAttributes[]     = $customerAttribute;
            }
        }
        $customer->cAnrede = $this->mapSalutation($customer->cAnrede);

        $lang = $this->db->select('tsprache', 'kSprache', (int)$customer->kSprache);
        if (empty($lang->kSprache)) {
            $lang               = $this->db->select('tsprache', 'cShopStandard', 'Y');
            $customer->kSprache = $lang->kSprache;
        }

        $kInetKunde  = (int)$xml['tkunde attr']['kKunde'];
        $oldCustomer = new stdClass();
        if ($kInetKunde > 0) {
            $oldCustomer = new Kunde($kInetKunde);
        }
        // Kunde existiert mit dieser kInetKunde
        // Kunde wird aktualisiert bzw. seine KdGrp wird geändert
        if (isset($oldCustomer->kKunde) && $oldCustomer->kKunde > 0) {
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
            if ($oldCustomer->kKundengruppe != $customer->kKundengruppe && $customer->cMail) {
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
                $this->saveAttribute($customer->kKunde, $customer->kSprache, $customerAttributes, false);
            }
            $res['keys']['tkunde attr']['kKunde'] = $kInetKunde;
            $res['keys']['tkunde']                = '';
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
                $cstmr                        = $this->db->query(
                    "SELECT kKunde, kKundengruppe, kSprache, cKundenNr, cPasswort, cAnrede, cTitel, cVorname,
                    cNachname, cFirma, cZusatz, cStrasse, cHausnummer, cAdressZusatz, cPLZ, cOrt, cBundesland, 
                    cLand, cTel, cMobil, cFax, cMail, cUSTID, cWWW, fGuthaben, cNewsletter, dGeburtstag, fRabatt,
                    cHerkunft, dErstellt, dVeraendert, cAktiv, cAbgeholt,
                    date_format(dGeburtstag, '%d.%m.%Y') AS dGeburtstag_formatted, nRegistriert
                    FROM tkunde
                    WHERE kKunde = " . (int)$oldCustomer->kKunde,
                    ReturnType::ARRAY_OF_ASSOC_ARRAYS
                );
                $xml['kunden attr']['anzahl'] = 1;

                $cstmr[0]['cNachname'] = \trim($crypto->decryptXTEA($cstmr[0]['cNachname']));
                $cstmr[0]['cFirma']    = \trim($crypto->decryptXTEA($cstmr[0]['cFirma']));
                $cstmr[0]['cZusatz']   = \trim($crypto->decryptXTEA($cstmr[0]['cZusatz']));
                $cstmr[0]['cStrasse']  = \trim($crypto->decryptXTEA($cstmr[0]['cStrasse']));
                $cstmr[0]['cAnrede']   = Kunde::mapSalutation($cstmr[0]['cAnrede'], $cstmr[0]['kSprache']);
                // Strasse und Hausnummer zusammenführen
                $cstmr[0]['cStrasse'] .= ' ' . $cstmr[0]['cHausnummer'];
                unset($cstmr[0]['cHausnummer']);
                // Land ausgeschrieben der Wawi geben
                $cstmr[0]['cLand'] = Sprache::getCountryCodeByCountryName($cstmr[0]['cLand']);
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
                $xml['kunden']['tkunde'] = $cstmr;
                $this->logger->error('Dieser Kunde existiert: ' . XML::serialize($xml));

                return $xml;
            }
            // Email noch nicht belegt, der Kunde muss neu erstellt werden -> KUNDE WIRD NEU ERSTELLT
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
                $this->saveAttribute($customer->kKunde, $customer->kSprache, $customerAttributes, true);
            }

            $res['keys']['tkunde attr']['kKunde'] = $kInetKunde;
            $res['keys']['tkunde']                = '';
        }

        if ($kInetKunde > 0) {
            // kunde akt. bzw. neu inserted
            if (isset($xml['tkunde']['tadresse'])
                && \is_array($xml['tkunde']['tadresse'])
                && \count($xml['tkunde']['tadresse']) > 0
                && (!isset($xml['tkunde']['tadresse attr']) || !\is_array($xml['tkunde']['tadresse attr']))
            ) {
                //mehrere adressen
                $cntLieferadressen = \count($xml['tkunde']['tadresse']) / 2;
                for ($i = 0; $i < $cntLieferadressen; $i++) {
                    unset($deliveryAddress);
                    $deliveryAddress = new stdClass();
                    if ($xml['tkunde']['tadresse'][$i . ' attr']['kInetAdresse'] > 0) {
                        //update
                        $deliveryAddress->kLieferadresse = $xml['tkunde']['tadresse'][$i . ' attr']['kInetAdresse'];
                        $deliveryAddress->kKunde         = $kInetKunde;
                        $this->mapper->mapObject($deliveryAddress, $xml['tkunde']['tadresse'][$i], 'mLieferadresse');
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
                        $this->mapper->mapObject($deliveryAddress, $xml['tkunde']['tadresse'][$i], 'mLieferadresse');
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
                                'kAdresse'     => $xml['tkunde']['tadresse'][$i . ' attr']['kAdresse'],
                                'kInetAdresse' => $kInetLieferadresse,
                            ];
                            $res['keys']['tkunde']['tadresse'][$nr]           = '';

                            $nr++;
                        }
                    }
                }
            } elseif (isset($xml['tkunde']['tadresse attr']) && \is_array($xml['tkunde']['tadresse attr'])) {
                // nur eine lieferadresse
                if ($xml['tkunde']['tadresse attr']['kInetAdresse'] > 0) {
                    //update
                    if (!isset($deliveryAddress)) {
                        $deliveryAddress = new stdClass();
                    }
                    $deliveryAddress->kLieferadresse = $xml['tkunde']['tadresse attr']['kInetAdresse'];
                    $deliveryAddress->kKunde         = $kInetKunde;
                    $this->mapper->mapObject($deliveryAddress, $xml['tkunde']['tadresse'], 'mLieferadresse');
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
                    if (!isset($deliveryAddress)) {
                        $deliveryAddress = new stdClass();
                    }
                    $deliveryAddress->kKunde = $kInetKunde;
                    $this->mapper->mapObject($deliveryAddress, $xml['tkunde']['tadresse'], 'mLieferadresse');
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
                                'kAdresse'     => $xml['tkunde']['tadresse attr']['kAdresse'],
                                'kInetAdresse' => $kInetLieferadresse,
                            ],
                            'tadresse'      => '',
                        ];
                    }
                }
            }
        }

        return $res;
    }

    /**
     * @param int   $customerID
     * @param int   $languageID
     * @param array $attributes
     * @param bool  $isNew
     */
    private function saveAttribute(int $customerID, int $languageID, $attributes, $isNew): void
    {
        if ($customerID <= 0 || $languageID <= 0 || !\is_array($attributes) || \count($attributes) === 0) {
            return;
        }
        foreach ($attributes as $attribute) {
            $field = $this->db->queryPrepared(
                'SELECT tkundenfeld.kKundenfeld, tkundenfeldwert.cWert
                 FROM tkundenfeld
                 LEFT JOIN tkundenfeldwert
                    ON tkundenfeldwert.kKundenfeld = tkundenfeld.kKundenfeld
                 WHERE tkundenfeld.cWawi = :nm
                    AND tkundenfeld.kSprache = :lid',
                ['nm' => $attribute->cName, 'lid' => $languageID],
                ReturnType::SINGLE_OBJECT
            );
            if (isset($field->kKundenfeld) && $field->kKundenfeld > 0) {
                if (\strlen($field->cWert) > 0 && $field->cWert != $attribute->cWert) {
                    continue;
                }
                if (!$isNew) {
                    $this->db->delete(
                        'tkundenattribut',
                        ['kKunde', 'kKundenfeld'],
                        [$customerID, (int)$field->kKundenfeld]
                    );
                }
                $ins              = new stdClass();
                $ins->kKunde      = $customerID;
                $ins->kKundenfeld = (int)$field->kKundenfeld;
                $ins->cName       = $attribute->cName;
                $ins->cWert       = $attribute->cWert;

                $this->db->insert('tkundenattribut', $ins);
            }
        }
    }
}
