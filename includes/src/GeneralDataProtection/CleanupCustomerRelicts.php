<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * clean up multiple tables at each run
 * (normaly one times a day)
 *
 * names of the tables, we manipulate:
 *
 * `tbesucherarchiv`
 * `tkundenattribut`
 * `tkundenkontodaten`
 * `tkundenwerbenkunden`
 * `tkundenwerbenkundenbonus`
 * `tzahlungsinfo`
 * `tlieferadresse`
 * `trechnungsadresse`
 *
 * data will be removed here!
 */
class CleanupCustomerRelicts extends Method implements MethodInterface
{
    /**
     * runs all anonymize-routines
     */
    public function execute()
    {
        $this->del_tbesucherarchiv();
        $this->del_tkundenattribut();
        $this->del_tkundenkontodaten();
        $this->del_tkundenwerbenkunden();
        $this->del_tzahlungsinfo();
        $this->del_tlieferadresse();
        $this->del_trechnungsadresse();
    }

    /**
     * delete visitors in the visitors-archive immediately (at each run of the cron),
     * without a valid customer-account
     */
    private function del_tbesucherarchiv()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT kBesucher
            FROM tbesucherarchiv
            WHERE
                kKunde > 0
                AND kKunde NOT IN (SELECT kKunde FROM tkunde)
                LIMIT :pLimit',
            ['pLimit' => $this->iWorkLimit],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            return;
        }
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE FROM tbesucherarchiv
                WHERE
                    kBesucher = :pKeyBesucher',
                ['pKeyBesucher' => $oResult->kBesucher],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete customer-recruitings
     * where no valid customer-accounts exists
     */
    private function del_tkundenwerbenkunden()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT
                k.kKunde,
                b.fGuthaben,
                b.nBonuspunkte,
                b.dErhalten
            FROM
                tkundenwerbenkunden k
                    LEFT JOIN tkundenwerbenkundenbonus b ON k.kKunde = b.kKunde
            WHERE
                k.kKunde > 0
                AND k.kKunde NOT IN (SELECT kKunde FROM tkunde)
            LIMIT :pLimit',
            ['pLimit' => $this->iWorkLimit],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            return;
        }
        foreach ($vResult as $oResult) {
            // delete each "kKunde", in multiple tables, in one shot
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE tkundenwerbenkunden, tkundenwerbenkundenbonus
                FROM
                    tkundenwerbenkunden
                    LEFT JOIN tkundenwerbenkundenbonus ON tkundenwerbenkundenbonus.kKunde = tkundenwerbenkunden.kKunde
                WHERE
                    tkundenwerbenkunden.kKunde = :pKeyKunde',
                ['pKeyKunde' => $oResult->kKunde],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete customer-attributes
     * for which there are no valid customer-accounts
     */
    private function del_tkundenattribut()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT kKundenAttribut
            FROM tkundenattribut
            WHERE
                kKunde NOT IN (SELECT kKunde FROM tkunde)
            LIMIT :pLimit',
            ['pLimit' => $this->iWorkLimit],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            return;
        }
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE FROM tkundenattribut
                WHERE
                    kKundenAttribut = :pKeykKundenAttribut',
                ['pKeykKundenAttribut' => $oResult->kKundenAttribut],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete orphaned payment-data of customers,
     * which have no valid account
     */
    private function del_tzahlungsinfo()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT kZahlungsInfo
            FROM tzahlungsinfo
            WHERE
                kKunde > 0
                AND kKunde NOT IN (SELECT kKunde FROM tkunde)
            LIMIT :pLimit',
            ['pLimit' => $this->iWorkLimit],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            return;
        }
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE FROM tzahlungsinfo
                WHERE
                    kZahlungsInfo = :pKeyZahlungsInfo',
                ['pKeyZahlungsInfo' => $oResult->kZahlungsInfo],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete orphaned bank-account-information of customers,
     * which have no valid account
     */
    private function del_tkundenkontodaten()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT kKundenKontodaten
            FROM tkundenkontodaten
            WHERE
                kKunde > 0
                AND kKunde NOT IN (SELECT kKunde FROM tkunde)
            LIMIT :pLimit',
            ['pLimit' => $this->iWorkLimit],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            return;
        }
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE FROM tkundenkontodaten
                WHERE
                    kKundenKontodaten = :pKeyKundenKontodaten',
                ['pKeyKundenKontodaten' => $oResult->kKundenKontodaten],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete delivery-addresses
     * which assigned to no valid customer-account
     */
    private function del_tlieferadresse()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            "SELECT kLieferadresse
            FROM tlieferadresse k
                JOIN tbestellung b ON b.kKunde = k.kKunde
            WHERE
                b.cStatus IN (' . BESTELLUNG_STATUS_VERSANDT . ', ' . BESTELLUNG_STATUS_STORNO . ')
                AND b.cAbgeholt = 'Y'
                AND k.kKunde NOT IN (SELECT kKunde FROM tkunde)
            LIMIT :pLimit",
            ['pLimit' => $this->iWorkLimit],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            return;
        }
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE FROM tlieferadresse
                WHERE
                    kLieferadresse = :pKeyLieferadresse',
                ['pKeyLieferadresse' => $oResult->kLieferadresse],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete billing-addresses
     * which assigned to no valid customer-account
     */
    private function del_trechnungsadresse()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
            "SELECT kRechnungsadresse
            FROM trechnungsadresse k
                JOIN tbestellung b ON b.kKunde = k.kKunde
            WHERE
                b.cStatus IN (" . BESTELLUNG_STATUS_VERSANDT . ", " . BESTELLUNG_STATUS_STORNO . ")
                AND b.cAbgeholt = 'Y'
                AND k.kKunde NOT IN (SELECT kKunde FROM tkunde)
            LIMIT :pLimit",
            ['pLimit' => $this->iWorkLimit],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            return;
        }
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared(
                'DELETE FROM trechnungsadresse
                WHERE
                    kRechnungsadresse = :pKeyRechnungsadresse',
                ['pKeyRechnungsadresse' => $oResult->kRechnungsadresse],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }
}

