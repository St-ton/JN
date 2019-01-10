<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Bewertung
 */
class Bewertung
{
    /**
     * @var array
     */
    public $oBewertung_arr;

    /**
     * @var array
     */
    public $nSterne_arr;

    /**
     * @var int
     */
    public $nAnzahlSprache;

    /**
     * @var object
     */
    public $oBewertungGesamt;

    /**
     * @param int    $kArtikel
     * @param int    $kSprache
     * @param int    $nAnzahlSeite
     * @param int    $nSeite
     * @param int    $nSterne
     * @param string $cFreischalten
     * @param int    $nOption
     * @param bool   $bAlleSprachen
     */
    public function __construct(
        int $kArtikel,
        int $kSprache,
        int $nAnzahlSeite = -1,
        int $nSeite = 1,
        int $nSterne = 0,
        string $cFreischalten = 'N',
        int $nOption = 0,
        bool $bAlleSprachen = false
    ) {
        if (!$kSprache) {
            $kSprache = Shop::getLanguageID();
        }
        if ($nOption === 1) {
            $this->holeHilfreichsteBewertung($kArtikel, $kSprache);
        } else {
            $this->holeProduktBewertungen(
                $kArtikel,
                $kSprache,
                $nAnzahlSeite,
                $nSeite,
                $nSterne,
                $cFreischalten,
                $nOption,
                $bAlleSprachen
            );
        }
    }

    /**
     * @param int $kArtikel
     * @param int $kSprache
     * @return $this
     */
    public function holeHilfreichsteBewertung(int $kArtikel, int $kSprache): self
    {
        $this->oBewertung_arr = [];
        if ($kArtikel > 0 && $kSprache > 0) {
            $oBewertungHilfreich = Shop::Container()->getDB()->query(
                "SELECT *, DATE_FORMAT(dDatum, '%d.%m.%Y') AS Datum,
                        DATE_FORMAT(dAntwortDatum, '%d.%m.%Y') AS AntwortDatum
                    FROM tbewertung
                    WHERE kSprache = " . $kSprache . '
                        AND kArtikel = ' . $kArtikel . '
                        AND nAktiv = 1
                    ORDER BY nHilfreich DESC
                    LIMIT 1',
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (!empty($oBewertungHilfreich)) {
                $oBewertungHilfreich->nAnzahlHilfreich = $oBewertungHilfreich->nHilfreich +
                    $oBewertungHilfreich->nNichtHilfreich;
            }

            executeHook(HOOK_BEWERTUNG_CLASS_HILFREICHSTEBEWERTUNG);
            $this->oBewertung_arr[] = $oBewertungHilfreich;
        }

        return $this;
    }

    /**
     * @param int $nOption
     * @return string
     */
    private function getOrderSQL(int $nOption): string
    {
        switch ($nOption) {
            case 2:
                return ' dDatum DESC';
            case 3:
                return ' dDatum ASC';
            case 4:
                return ' nSterne DESC';
            case 5:
                return ' nSterne ASC';
            case 6:
                return ' nHilfreich DESC';
            case 7:
                return ' nHilfreich ASC';
            default:
                return ' dDatum DESC';
        }
    }

    /**
     * @param int    $kArtikel
     * @param int    $kSprache
     * @param int    $nAnzahlSeite
     * @param int    $nSeite
     * @param int    $nSterne
     * @param string $cFreischalten
     * @param int    $nOption
     * @param bool   $bAlleSprachen
     * @return $this
     */
    public function holeProduktBewertungen(
        int $kArtikel,
        int $kSprache,
        int $nAnzahlSeite,
        int $nSeite = 1,
        int $nSterne = 0,
        string $cFreischalten = 'N',
        int $nOption = 0,
        bool $bAlleSprachen = false
    ): self {
        $this->oBewertung_arr = [];
        if ($kArtikel <= 0 || $kSprache <= 0) {
            return $this;
        }
        $oBewertungAnzahl_arr = [];
        $cSQL                 = '';
        $cOrderSQL            = $this->getOrderSQL($nOption);
        $db                   = Shop::Container()->getDB();
        executeHook(HOOK_BEWERTUNG_CLASS_SWITCH_SORTIERUNG);

        $cSQLFreischalten = $cFreischalten === 'Y'
            ? ' AND nAktiv = 1'
            : '';
        // Bewertungen nur in einer bestimmten Sprache oder in allen Sprachen?
        $cSprachSQL = ' AND kSprache = ' . $kSprache;
        if ($bAlleSprachen) {
            $cSprachSQL = '';
        }
        // Anzahl Bewertungen für jeden Stern unabhängig von Sprache SHOP-2313
        if ($nSterne !== -1) {
            if ($nSterne > 0) {
                $cSQL = ' AND nSterne = ' . $nSterne;
            }
            $oBewertungAnzahl_arr = $db->query(
                'SELECT COUNT(*) AS nAnzahl, nSterne
                    FROM tbewertung
                    WHERE kArtikel = ' . $kArtikel . $cSQLFreischalten . '
                    GROUP BY nSterne
                    ORDER BY nSterne DESC',
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
        }
        if ($nSeite > 0) {
            $nLimit = '';
            if ($nAnzahlSeite > 0) {
                $nLimit = ($nSeite > 1)
                    ? ' LIMIT ' . (($nSeite - 1) * $nAnzahlSeite) . ', ' . $nAnzahlSeite
                    : ' LIMIT ' . $nAnzahlSeite;
            }
            $this->oBewertung_arr = $db->query(
                "SELECT *, DATE_FORMAT(dDatum, '%d.%m.%Y') AS Datum,
                        DATE_FORMAT(dAntwortDatum, '%d.%m.%Y') AS AntwortDatum
                    FROM tbewertung
                    WHERE kArtikel = " . $kArtikel . $cSprachSQL . $cSQL . $cSQLFreischalten . '
                    ORDER BY' . $cOrderSQL . $nLimit,
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
        }
        $oBewertungGesamt = $db->query(
            'SELECT COUNT(*) AS nAnzahl, tartikelext.fDurchschnittsBewertung AS fDurchschnitt
                FROM tartikelext
                JOIN tbewertung 
                    ON tbewertung.kArtikel = tartikelext.kArtikel
                WHERE tartikelext.kArtikel = ' . $kArtikel . $cSQLFreischalten . '
                GROUP BY tartikelext.kArtikel',
            \DB\ReturnType::SINGLE_OBJECT
        );
        // Anzahl Bewertungen für aktuelle Sprache
        $oBewertungGesamtSprache = $db->query(
            'SELECT COUNT(*) AS nAnzahlSprache
                FROM tbewertung
                WHERE kArtikel = ' . $kArtikel . $cSprachSQL . $cSQLFreischalten,
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($oBewertungGesamt->fDurchschnitt) && (int)$oBewertungGesamt->fDurchschnitt > 0) {
            $oBewertungGesamt->fDurchschnitt = round($oBewertungGesamt->fDurchschnitt * 2) / 2;
            $oBewertungGesamt->nAnzahl       = (int)$oBewertungGesamt->nAnzahl;
            $this->oBewertungGesamt          = $oBewertungGesamt;
        } else {
            $oBewertungGesamt                = new stdClass();
            $oBewertungGesamt->fDurchschnitt = 0;
            $oBewertungGesamt->nAnzahl       = 0;
            $this->oBewertungGesamt          = $oBewertungGesamt;
        }
        $this->nAnzahlSprache = ((int)$oBewertungGesamtSprache->nAnzahlSprache > 0)
            ? (int)$oBewertungGesamtSprache->nAnzahlSprache
            : 0;
        foreach ($this->oBewertung_arr as $i => $oBewertung) {
            $this->oBewertung_arr[$i]->nAnzahlHilfreich = $oBewertung->nHilfreich + $oBewertung->nNichtHilfreich;
        }
        $nSterne_arr = [0, 0, 0, 0, 0];
        foreach ($oBewertungAnzahl_arr as $oBewertungAnzahl) {
            $nSterne_arr[5 - $oBewertungAnzahl->nSterne] = $oBewertungAnzahl->nAnzahl;
        }
        $this->nSterne_arr = $nSterne_arr;

        executeHook(HOOK_BEWERTUNG_CLASS_BEWERTUNG, ['oBewertung' => &$this]);

        return $this;
    }
}
