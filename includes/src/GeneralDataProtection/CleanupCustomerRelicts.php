<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

class CleanupCustomerRelicts extends Method implements MethodInterface
{
    protected $szReason  = 'anonymize_orphaned_ratings';


    public function execute()
    {
        //$this->del_tbesucher();
        //$this->del_tbesucherarchiv();
        //$this->del_tkundenattribut(); // ATTENTION: NO DATE! should bound to a customer.
        //$this->del_tkundenkontodaten(); // ATTENTION: NO DATE! should bound to a customer.
        //$this->del_tkundenwerbenkunden();
        //$this->del_tkundenwerbenkundenbonus(); // --OBSOLETE-- doen in "del_tkundenwerbenkunden"
        //$this->del_tzahlungsinfo();
        //$this->del_tlieferadresse();
        //$this->del_trechnungsadresse();
    }

    private function del_tbesucher()
    {
        // CUSTOMIZABLE:
        // table fields, which we want to save in the journal (before they would change)
        // (null = don't save in journal, '1' = save in journal)
        $vTableFields = [
            'kBesucher'         => null,
            'cIP'               => null,
            'cSessID'           => null,
            'cID'               => null,
            'kKunde'            => 1,
            'kBestellung'       => 1,
            'cReferer'          => 1,
            'cUserAgent'        => 1,
            'cEinstiegsseite'   => null,
            'cBrowser'          => 1,
            'cAusstiegsseite'   => null,
            'kBesucherBot'      => null,
            'dLetzteAktivitaet' => null,
            'dZeit'             => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFileds = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `tbesucher`
            WHERE
                kKunde > 0
                AND `kKunde` NOT IN (SELECT `kKunde` FROM `tkunde`)
                AND date(`dZeit`) < date_sub(date(now()), INTERVAL :pInterval DAY)',
            ['pInterval' => $this->iInterval],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!is_array($vResult)) {
            // "no data, no operation"
            return;
        }
        // save parts of the old values in the changes-journal..
        $this->saveToJournal('tbesucher', $vUseFileds, $vResult);
        foreach ($vResult as $oResult) {
            //$vQueries[] = "DELETE FROM `tbesucher` WHERE kKunde > 0 AND `kKunde` NOT IN (SELECT `kKunde` FROM `tkunde`)";

            // anonymize the original data
            /*
             *\Shop::Container()->getDB()->queryPrepared('DELETE FROM `tbesucher`
             *    WHERE
             *        kBesucher = :pKeyBesucher',
             *    ['pKeyBesucher' => $oResult->kBesucher],
             *    \DB\ReturnType::AFFECTED_ROWS
             *);
             */
        }
    }

    private function del_tbesucherarchiv()
    {
        // CUSTOMIZABLE:
        // table fields, which we want to save in the journal (before they would change)
        // (null = don't save in journal, '1' = save in journal)
        $vTableFields = [
            'kBesucher'       => null,
            'cIP'             => null,
            'kKunde'          => 1,
            'kBestellung'     => 1,
            'cReferer'        => null,
            'cEinstiegsseite' => null,
            'cBrowser'        => 1,
            'cAusstiegsseite' => null,
            'nBesuchsdauer'   => null,
            'kBesucherBot'    => null,
            'dZeit'           => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFileds = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `tbesucherarchiv`
            WHERE
                kKunde > 0
                AND kKunde NOT IN (SELECT kKunde FROM tkunde)
                AND date(`dZeit`) < date_sub(date(now()), INTERVAL :pInterval DAY)',
            ['pInterval' => $this->iInterval],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!is_array($vResult)) {
            // "no data, no operation"
            return;
        }
        // save parts of the old values in the changes-journal..
        $this->saveToJournal('tbesucherarchiv', $vUseFileds, $vResult);
        foreach ($vResult as $oResult) {
            //$vQueries[] = "DELETE FROM `tbesucherarchiv` WHERE kKunde > 0 AND kKunde NOT IN (SELECT kKunde FROM tkunde)";

            // anonymize the original data
            /*
             *\Shop::Container()->getDB()->queryPrepared('DELETE FROM `tbesucherarchiv`
             *    WHERE
             *        kBesucher = :pKeyBesucher',
             *    ['pKeyBesucher' => $oResult->kBesucher],
             *    \DB\ReturnType::AFFECTED_ROWS
             *);
             */
        }

    }

    private function del_tkundenwerbenkunden()
    {
        // CUSTOMIZABLE:
        // table fields, which we want to save in the journal (before they would change)
        // (null = don't save in journal, '1' = save in journal)
        $vTableFields = [
            'kKundenWerbenKunden' => null,
            'kKunde'              => 1,
            'cVorname'            => 1,
            'cNachname'           => 1,
            'cEmail'              => 1,
            'nRegistriert'        => null,
            'nGuthabenVergeben'   => null,
            'fGuthaben'           => null,
            'dErstellt'           => 1,
            '_bonus_fGuthaben'    => 1,
            '_bonus_nBonuspunkte' => 1,
            '_bonus_dErhalten'    => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFileds = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT
                k.*,
                b.`fGuthaben` AS "_bonus_fGuthaben",
                b.`nBonuspunkte` AS "_bonus_nBonuspunkte",
                b.`dErhalten` AS "_bonus_dErhalten"
            FROM
                `tkundenwerbenkunden` k
                LEFT JOIN `tkundenwerbenkundenbonus` b ON k.`kKunde` = b.`kKunde`
            WHERE
                k.`kKunde` > 0
                AND k.`kKunde` NOT IN (SELECT `kKunde` FROM `tkunde`)
                AND (DATE(`dErhalten`) < DATE_SUB(NOW(), INTERVAL 7 DAY) OR `dErhalten` IS NULL)',
            ['pInterval' => $this->iInterval],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!is_array($vResult)) {
            // "no data, no operation"
            return;
        }

        // save parts of the old values in the changes-journal..
        $this->saveToJournal('tkundenwerbenkunden,tkundenwerbenkundenbonus', $vUseFileds, $vResult);
        // ..and anon the orignal
        foreach ($vResult as $oResult) {
            //"DELETE FROM `tkundenwerbenkunden` WHERE kKunde > 0 AND kKunde NOT IN (SELECT kKunde FROM tkunde)";
            //"DELETE FROM `tkundenwerbenkundenbonus` WHERE kKunde > 0 AND kKunde NOT IN (SELECT kKunde FROM tkunde)";

            // delete each "kKunde", in multiple tables, in one shot
            $nEffected = \Shop::Container()->getDB()->queryPrepared('DELETE `tkundenwerbenkunden`, `tkundenwerbenkundenbonus`
                FROM
                    `tkundenwerbenkunden`
                    LEFT JOIN `tkundenwerbenkundenbonus` ON `tkundenwerbenkundenbonus`.`kKunde` = `tkundenwerbenkunden`.`kKunde`
                WHERE
                    `tkundenwerbenkunden`.`kKunde` = :pKeyKunde',
                ['pKeyKunde' => $oResult->kKunde],
                \DB\ReturnType::AFFECTED_ROWS
            );
            //$this->oLogger->debug('nEffected: '.print_r($nEffected,true )); // --DEBUG--
        }

    }





    private function del_tkundenattribut()
    {
        // CUSTOMIZABLE:
        // table fields, which we want to save in the journal (before they would change)
        // (null = don't save in journal, '1' = save in journal)
        $vTableFields = [
            'kKundenAttribut' => null,
            'kKunde'          => 1,
            'kKundenfeld'     => 1,
            'cName'           => 1,
            'cWert'           => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFileds = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `tkundenattribut`
            WHERE
                kKunde NOT IN (SELECT kKunde FROM tkunde)'
        );

        // ATTENTION: we got NO DATE HERE!
        // so we can not mask a bunch of data - we have to fetch all that stuff! not good!
        //
        // maybe it makes no sense to save this table seperately,
        // because it depends on others and makes only sense there.
        // --TODO-- --TO-CHECK--

        //$vQueries[] = "DELETE FROM `tkundenattribut` WHERE kKunde NOT IN (SELECT kKunde FROM tkunde)";
    }

    private function del_tzahlungsinfo()
    {
        // CUSTOMIZABLE:
        // table fields, which we want to save in the journal (before they would change)
        // (null = don't save in journal, '1' = save in journal)
        $vTableFields = [
            'kZahlungsInfo'     => null,
            'kBestellung'       => 1,
            'kKunde'            => 1,
            'cBankName'         => null,
            'cBLZ'              => null,
            'cKontoNr'          => null,
            'cIBAN'             => 1,
            'cBIC'              => null,
            'cKartenNr'         => null,
            'cGueltigkeit'      => null,
            'cCVV'              => null,
            'cKartenTyp'        => null,
            'cInhaber'          => null,
            'cVerwendungszweck' => null,
            'cAbgeholt'         => null
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFileds = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `tzahlungsinfo`
            WHERE
                kKunde > 0
                AND kKunde NOT IN (SELECT kKunde FROM tkunde)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        // ATTENTION: we got NO DATE HERE!
        // so we can not mask a bunch of data - we have to fetch all that stuff! not good!
        //
        // maybe it makes no sense to save this table seperately,
        // because it depends on others and makes only sense there.
        // --TODO-- --TO-CHECK--



        //$vQueries[] = "DELETE FROM `tzahlungsinfo` WHERE kKunde > 0 AND kKunde NOT IN (SELECT kKunde FROM tkunde)";
    }

    private function del_tkundenkontodaten()
    {
        // CUSTOMIZABLE:
        // table fields, which we want to save in the journal (before they would change)
        // (null = don't save in journal, '1' = save in journal)
        $vTableFields = [
            'kKundenKontodaten' => null,
            'kKunde'            => null,
            'cBLZ'              => null,
            'nKonto'            => null,
            'cInhaber'          => null,
            'cBankName'         => null,
            'cIBAN'             => null,
            'cBIC'              => null
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFileds = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `tkundenkontodaten`
            WHERE
                kKunde > 0
                AND kKunde NOT IN (SELECT kKunde FROM tkunde)'
        );

        // ATTENTION: we got NO DATE HERE!
        // so we can not mask a bunch of data - we have to fetch all that stuff! not good!
        //
        // maybe it makes no sense to save this table seperately,
        // because it depends on others and makes only sense there.
        // --TODO-- --TO-CHECK--

        //$vQueries[] = "DELETE FROM `tkundenkontodaten` WHERE kKunde > 0 AND kKunde NOT IN (SELECT kKunde FROM tkunde)";
    }





    private function del_tlieferadresse()
    {
        // CUSTOMIZABLE:
        // table fields, which we want to save in the journal (before they would change)
        // (null = don't save in journal, '1' = save in journal)
        $vTableFields = [
            'kLieferadresse' => null,
            'kKunde'         => null,
            'cAnrede'        => null,
            'cVorname'       => null,
            'cNachname'      => null,
            'cTitel'         => null,
            'cFirma'         => null,
            'cZusatz'        => null,
            'cStrasse'       => null,
            'cHausnummer'    => null,
            'cAdressZusatz'  => null,
            'cPLZ'           => null,
            'cOrt'           => null,
            'cBundesland'    => null,
            'cLand'          => null,
            'cTel'           => null,
            'cMobil'         => null,
            'cFax'           => null,
            'cMail'          => null
        ];
        /*
         *$vQueries[] = "DELETE k
         *    FROM tlieferadresse k
         *        JOIN tbestellung b ON b.kKunde = k.kKunde
         *    WHERE b.cStatus IN (4, -1)
         *        AND b.cAbgeholt = 'Y'
         *        AND k.kKunde NOT IN (SELECT kKunde FROM tkunde)";
         */
    }

    private function del_trechnungsadresse()
    {
        // CUSTOMIZABLE:
        // table fields, which we want to save in the journal (before they would change)
        // (null = don't save in journal, '1' = save in journal)
        $vTableFields = [
            'kRechnungsadresse' => null,
            'kKunde'            => null,
            'cAnrede'           => null,
            'cTitel'            => null,
            'cVorname'          => null,
            'cNachname'         => null,
            'cFirma'            => null,
            'cZusatz'           => null,
            'cStrasse'          => null,
            'cHausnummer'       => null,
            'cAdressZusatz'     => null,
            'cPLZ'              => null,
            'cOrt'              => null,
            'cBundesland'       => null,
            'cLand'             => null,
            'cTel'              => null,
            'cMobil'            => null,
            'cFax'              => null,
            'cUSTID'            => null,
            'cWWW'              => null,
            'cMail'             => null
        ];
        /*
         *$vQueries[] = "DELETE k
         *    FROM trechnungsadresse k
         *        JOIN tbestellung b ON b.kKunde = k.kKunde
         *    WHERE b.cStatus IN (4, -1)
         *        AND b.cAbgeholt = 'Y'
         *        AND k.kKunde NOT IN (SELECT kKunde FROM tkunde)";
         */
    }

}
