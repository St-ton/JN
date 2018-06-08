<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WarenkorbPers
 */
class WarenkorbPers
{
    /**
     * @var int
     */
    public $kWarenkorbPers;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var array
     */
    public $oWarenkorbPersPos_arr = [];

    /**
     * @var string
     */
    public $cWarenwertLocalized;

    /**
     * @param int  $kKunde
     * @param bool $bArtikel
     */
    public function __construct(int $kKunde = 0, bool $bArtikel = false)
    {
        if ($kKunde > 0) {
            $this->kKunde = $kKunde;
            $this->ladeWarenkorbPers($bArtikel);
        }
    }

    /**
     * fügt eine Position zur WarenkorbPers hinzu
     *
     * @param int        $kArtikel
     * @param string     $cArtikelName
     * @param array      $oEigenschaftwerte_arr
     * @param float      $fAnzahl
     * @param string     $cUnique
     * @param int        $kKonfigitem
     * @param int        $nPosTyp
     * @param string     $cResponsibility
     * @return $this
     */
    public function fuegeEin(
        int $kArtikel,
        $cArtikelName,
        $oEigenschaftwerte_arr,
        $fAnzahl,
        $cUnique = '',
        int $kKonfigitem = 0,
        int $nPosTyp = C_WARENKORBPOS_TYP_ARTIKEL,
        $cResponsibility = 'core'
    ) {
        $bBereitsEnthalten = false;
        $nPosition         = 0;
        foreach ($this->oWarenkorbPersPos_arr as $i => $oWarenkorbPersPos) {
            $oWarenkorbPersPos->kArtikel = (int)$oWarenkorbPersPos->kArtikel;
            if ($bBereitsEnthalten) {
                break;
            }
            if ($oWarenkorbPersPos->kArtikel === $kArtikel
                && $oWarenkorbPersPos->cUnique === $cUnique
                && (int)$oWarenkorbPersPos->kKonfigitem === $kKonfigitem
                && count($oWarenkorbPersPos->oWarenkorbPersPosEigenschaft_arr) > 0
            ) {
                $nPosition         = $i;
                $bBereitsEnthalten = true;
                foreach ($oEigenschaftwerte_arr as $oEigenschaftwerte) {
                    //kEigenschaftsWert is not set when using free text variations
                    if (!$oWarenkorbPersPos->istEigenschaftEnthalten(
                        $oEigenschaftwerte->kEigenschaft,
                        $oEigenschaftwerte->kEigenschaftWert ?? null,
                        $oEigenschaftwerte->cFreifeldWert ?? null
                    )) {
                        $bBereitsEnthalten = false;
                        break;
                    }
                }
            } elseif ($oWarenkorbPersPos->kArtikel === $kArtikel
                && $cUnique !== ''
                && $oWarenkorbPersPos->cUnique === $cUnique
                && (int)$oWarenkorbPersPos->kKonfigitem === $kKonfigitem
            ) {
                $nPosition         = $i;
                $bBereitsEnthalten = true;
                break;
            }
        }
        if ($bBereitsEnthalten) {
            $this->oWarenkorbPersPos_arr[$nPosition]->fAnzahl += $fAnzahl;
            $this->oWarenkorbPersPos_arr[$nPosition]->updateDB();
        } else {
            $oWarenkorbPersPos = new WarenkorbPersPos(
                $kArtikel,
                $cArtikelName,
                $fAnzahl,
                $this->kWarenkorbPers,
                $cUnique,
                $kKonfigitem,
                $nPosTyp,
                $cResponsibility
            );
            $oWarenkorbPersPos->schreibeDB();
            $oWarenkorbPersPos->erstellePosEigenschaften($oEigenschaftwerte_arr);
            $this->oWarenkorbPersPos_arr[] = $oWarenkorbPersPos;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function entferneAlles(): self
    {
        foreach ($this->oWarenkorbPersPos_arr as $oWarenkorbPersPos) {
            // Eigenschaften löschen
            Shop::Container()->getDB()->delete(
                'twarenkorbpersposeigenschaft',
                'kWarenkorbPersPos',
                (int)$oWarenkorbPersPos->kWarenkorbPersPos
            );
            // Postitionen löschen
            Shop::Container()->getDB()->delete(
                'twarenkorbperspos',
                'kWarenkorbPers',
                (int)$oWarenkorbPersPos->kWarenkorbPers
            );
        }

        $this->oWarenkorbPersPos_arr = [];

        return $this;
    }

    /**
     * @return bool
     */
    public function entferneSelf(): bool
    {
        if ($this->kWarenkorbPers > 0) {
            // Entferne Pos und PosEigenschaft
            $this->entferneAlles();
            // Entferne Pers
            Shop::Container()->getDB()->delete('twarenkorbpers', 'kWarenkorbPers', (int)$this->kWarenkorbPers);

            return true;
        }

        return false;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function entfernePos(int $id): self
    {
        $oKunde = Shop::Container()->getDB()->queryPrepared(
            "SELECT twarenkorbpers.kKunde
                FROM twarenkorbpers
                JOIN twarenkorbperspos 
                    ON twarenkorbpers.kWarenkorbPers = twarenkorbperspos.kWarenkorbPers
                WHERE twarenkorbperspos.kWarenkorbPersPos = :kwpp",
            ['kwpp' => $id],
            \DB\ReturnType::SINGLE_OBJECT
        );
        // Prüfen ob der eingeloggte Kunde auch der Besitzer der zu löschenden WarenkorbPersPos ist
        if (isset($oKunde->kKunde) && $oKunde->kKunde == $_SESSION['Kunde']->kKunde) {
            // Alle Eigenschaften löschen
            Shop::Container()->getDB()->delete('twarenkorbpersposeigenschaft', 'kWarenkorbPersPos', $id);
            // Die Position mit ID $id löschen
            Shop::Container()->getDB()->delete('twarenkorbperspos', 'kWarenkorbPersPos', $id);
            // WarenkorbPers Position aus der Session löschen
            if (isset($_SESSION['WarenkorbPers']->oWarenkorbPersPos_arr) 
                && is_array($_SESSION['WarenkorbPers']->oWarenkorbPersPos_arr) 
                && count($_SESSION['WarenkorbPers']->oWarenkorbPersPos_arr) > 0
            ) {
                foreach ($_SESSION['WarenkorbPers']->oWarenkorbPersPos_arr as $i => $oWarenkorbPersPos) {
                    if ($oWarenkorbPersPos->kWarenkorbPersPos == $id) {
                        unset($_SESSION['WarenkorbPers']->oWarenkorbPersPos_arr[$i]);
                    }
                }
                // Positionen Array in der WarenkorbPers neu nummerieren
                $_SESSION['WarenkorbPers']->oWarenkorbPersPos_arr = array_merge($_SESSION['WarenkorbPers']->oWarenkorbPersPos_arr);
            }
        }

        return $this;
    }

    /**
     * löscht alle Gratisgeschenke aus dem persistenten Warenkorb
     *
     * @return $this
     */
    public function loescheGratisGeschenkAusWarenkorbPers(): self
    {
        foreach ($this->oWarenkorbPersPos_arr as $oWarenkorbPersPos) {
            if ((int)$oWarenkorbPersPos->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $this->entfernePos($oWarenkorbPersPos->kWarenkorbPersPos);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function schreibeDB(): self
    {
        $oTemp                = new stdClass();
        $oTemp->kKunde        = $this->kKunde;
        $oTemp->dErstellt     = $this->dErstellt;
        $this->kWarenkorbPers = Shop::Container()->getDB()->insert('twarenkorbpers', $oTemp);
        unset($oTemp);

        return $this;
    }

    /**
     * @param bool $bArtikel
     * @return $this
     */
    public function ladeWarenkorbPers(bool $bArtikel): self
    {
        // Prüfe ob die WarenkorbPers dem eingeloggten Kunden gehört
        $oWarenkorbPers = Shop::Container()->getDB()->select('twarenkorbpers', 'kKunde', (int)$this->kKunde);
        if (!isset($oWarenkorbPers->kWarenkorbPers) || $oWarenkorbPers->kWarenkorbPers < 1) {
            $this->dErstellt = 'now()';
            $this->schreibeDB();
        }

        if ($oWarenkorbPers === false || $oWarenkorbPers === null) {
            return $this;
        }
        $this->kWarenkorbPers = $oWarenkorbPers->kWarenkorbPers ?? null;
        $this->kKunde         = $oWarenkorbPers->kKunde ?? 0;
        $this->dErstellt      = $oWarenkorbPers->dErstellt ?? null;

        if ($this->kWarenkorbPers > 0) {
            // Hole alle Positionen für eine WarenkorbPers
            $oWarenkorbPersPos_arr = Shop::Container()->getDB()->selectAll(
                'twarenkorbperspos',
                'kWarenkorbPers',
                (int)$this->kWarenkorbPers,
                '*, date_format(dHinzugefuegt, \'%d.%m.%Y %H:%i\') AS dHinzugefuegt_de',
                'kKonfigitem, kWarenkorbPersPos'
            );
            // Wenn Positionen vorhanden sind
            if (is_array($oWarenkorbPersPos_arr) && count($oWarenkorbPersPos_arr) > 0) {
                $fWarenwert     = 0.0;
                $defaultOptions = Artikel::getDefaultOptions();
                if (!isset($_SESSION['Steuersatz'])) {
                    setzeSteuersaetze();
                }
                // Hole alle Eigenschaften für eine Position
                foreach ($oWarenkorbPersPos_arr as $oWarenkorbPersPosTMP) {
                    $oWarenkorbPersPos = new WarenkorbPersPos(
                        $oWarenkorbPersPosTMP->kArtikel,
                        $oWarenkorbPersPosTMP->cArtikelName,
                        $oWarenkorbPersPosTMP->fAnzahl,
                        $oWarenkorbPersPosTMP->kWarenkorbPers,
                        $oWarenkorbPersPosTMP->cUnique,
                        $oWarenkorbPersPosTMP->kKonfigitem,
                        $oWarenkorbPersPosTMP->nPosTyp,
                        $oWarenkorbPersPosTMP->cResponsibility
                    );

                    $oWarenkorbPersPos->kWarenkorbPersPos = $oWarenkorbPersPosTMP->kWarenkorbPersPos;
                    $oWarenkorbPersPos->cKommentar        = $oWarenkorbPersPosTMP->cKommentar ?? null;
                    $oWarenkorbPersPos->dHinzugefuegt     = $oWarenkorbPersPosTMP->dHinzugefuegt;
                    $oWarenkorbPersPos->dHinzugefuegt_de  = $oWarenkorbPersPosTMP->dHinzugefuegt_de;

                    $oWarenkorbPersPosEigenschaft_arr = Shop::Container()->getDB()->selectAll(
                        'twarenkorbpersposeigenschaft',
                        'kWarenkorbPersPos', (int)$oWarenkorbPersPosTMP->kWarenkorbPersPos
                    );
                    foreach ($oWarenkorbPersPosEigenschaft_arr as $oWarenkorbPersPosEigenschaftTMP) {
                        $oWarenkorbPersPosEigenschaft = new WarenkorbPersPosEigenschaft(
                            $oWarenkorbPersPosEigenschaftTMP->kEigenschaft,
                            $oWarenkorbPersPosEigenschaftTMP->kEigenschaftWert,
                            $oWarenkorbPersPosEigenschaftTMP->cFreifeldWert ?? null,
                            $oWarenkorbPersPosEigenschaftTMP->cEigenschaftName,
                            $oWarenkorbPersPosEigenschaftTMP->cEigenschaftWertName,
                            $oWarenkorbPersPosEigenschaftTMP->kWarenkorbPersPos
                        );
                        $oWarenkorbPersPos->oWarenkorbPersPosEigenschaft_arr[] = $oWarenkorbPersPosEigenschaft;
                    }
                    if ($bArtikel) {
                        $oWarenkorbPersPos->Artikel = new Artikel();
                        $oWarenkorbPersPos->Artikel->fuelleArtikel($oWarenkorbPersPos->kArtikel, $defaultOptions);
                        $oWarenkorbPersPos->cArtikelName = $oWarenkorbPersPos->Artikel->cName;

                        $fWarenwert += $oWarenkorbPersPos->Artikel->Preise->fVK[$oWarenkorbPersPos->Artikel->kSteuerklasse];
                    }
                    $oWarenkorbPersPos->fAnzahl = (float)$oWarenkorbPersPos->fAnzahl;
                    $this->oWarenkorbPersPos_arr[] = $oWarenkorbPersPos;
                }
                $this->cWarenwertLocalized = gibPreisStringLocalized($fWarenwert);
            }
        }

        return $this;
    }

    /**
     * @param bool $bForceDelete
     * @return string
     */
    public function ueberpruefePositionen(bool $bForceDelete = false): string
    {
        $cArtikel_arr = [];
        $kArtikel_arr = [];
        $hinweis      = '';
        foreach ($this->oWarenkorbPersPos_arr as $WarenkorbPersPos) {
            // Hat die Position einen Artikel
            if ($WarenkorbPersPos->kArtikel > 0) {
                // Prüfe auf kArtikel
                $oArtikelVorhanden = Shop::Container()->getDB()->select('tartikel', 'kArtikel', (int)$WarenkorbPersPos->kArtikel);
                // Falls Artikel vorhanden
                if (isset($oArtikelVorhanden->kArtikel) && $oArtikelVorhanden->kArtikel > 0) {
                    // Sichtbarkeit Prüfen
                    $oSichtbarkeit = Shop::Container()->getDB()->select(
                        'tartikelsichtbarkeit',
                        'kArtikel', (int)$WarenkorbPersPos->kArtikel,
                        'kKundengruppe', Session::CustomerGroup()->getID()
                    );
                    if ($oSichtbarkeit === null || !isset($oSichtbarkeit->kArtikel) || !$oSichtbarkeit->kArtikel) {
                        // Prüfe welche kEigenschaft gesetzt ist
                        $oEigenschaft_arr = Shop::Container()->getDB()->selectAll(
                            'teigenschaft',
                            'kArtikel', (int)$WarenkorbPersPos->kArtikel,
                            'kEigenschaft, cName, cTyp'
                        );
                        foreach ($oEigenschaft_arr as $oEigenschaft) {
                            if ($oEigenschaft->cTyp !== 'FREIFELD'
                                && $oEigenschaft->cTyp !== 'PFLICHT-FREIFELD'
                                && count($WarenkorbPersPos->oWarenkorbPersPosEigenschaft_arr) > 0
                            ) {
                                foreach ($WarenkorbPersPos->oWarenkorbPersPosEigenschaft_arr as $oWarenkorbPersPosEigenschaft) {
                                    if ($oWarenkorbPersPosEigenschaft->kEigenschaft === $oEigenschaft->kEigenschaft) {
                                        $oEigenschaftWertVorhanden = Shop::Container()->getDB()->select(
                                            'teigenschaftwert',
                                            'kEigenschaftWert',
                                            (int)$oWarenkorbPersPosEigenschaft->kEigenschaftWert,
                                            'kEigenschaft',
                                            (int)$oEigenschaft->kEigenschaft
                                        );
                                        // Prüfe ob die Eigenschaft vorhanden ist
                                        if (!isset($oEigenschaftWertVorhanden->kEigenschaftWert) || !$oEigenschaftWertVorhanden->kEigenschaftWert) {
                                            Shop::Container()->getDB()->delete('twarenkorbperspos', 'kWarenkorbPersPos', $WarenkorbPersPos->kWarenkorbPersPos);
                                            Shop::Container()->getDB()->delete('twarenkorbpersposeigenschaft', 'kWarenkorbPersPos', $WarenkorbPersPos->kWarenkorbPersPos);
                                            $cArtikel_arr[] = $WarenkorbPersPos->cArtikelName;
                                            $hinweis .= '<br />' . Shop::Lang()->get('noProductWishlist', 'messages');
                                        }
                                    }
                                }
                            }
                        }
                        $kArtikel_arr[] = (int)$oArtikelVorhanden->kArtikel;
                    }
                }
            // Konfigitem ohne Artikelbezug?
            } elseif ($WarenkorbPersPos->kArtikel === 0 && !empty($WarenkorbPersPos->kKonfigitem)) {
                $kArtikel_arr[] = (int)$WarenkorbPersPos->kArtikel;
            }
        }
        // Artikel aus dem Array Löschen, die nicht mehr Gültig sind
        if ($bForceDelete) {
            foreach ($this->oWarenkorbPersPos_arr as $i => $WarenkorbPersPos) {
                if (!in_array((int)$WarenkorbPersPos->kArtikel, $kArtikel_arr, true)) {
                    $this->entfernePos($WarenkorbPersPos->kWarenkorbPersPos);
                    Jtllog::writeLog(
                        'Der Artikel ' . $WarenkorbPersPos->kArtikel . ' ist vom persistenten Warenkorb gelöscht worden.',
                        JTLLOG_LEVEL_NOTICE,
                        false,
                        'kWarenkorbPersPos',
                        $WarenkorbPersPos->kWarenkorbPersPos
                    );
                    unset($this->oWarenkorbPersPos_arr[$i]);
                }
            }
            $this->oWarenkorbPersPos_arr = array_merge($this->oWarenkorbPersPos_arr);
        }

        return $hinweis . implode(', ', $cArtikel_arr);
    }

    /**
     * return $this
     */
    public function bauePersVonSession(): self
    {
        if (!is_array($_SESSION['Warenkorb']->PositionenArr) || count($_SESSION['Warenkorb']->PositionenArr) === 0) {
            return $this;
        }
        foreach (Session::Cart()->PositionenArr as $oPosition) {
            if ($oPosition->nPosTyp !== C_WARENKORBPOS_TYP_ARTIKEL) {
                continue;
            }
            $oEigenschaftwerte_arr = [];
            foreach ($oPosition->WarenkorbPosEigenschaftArr as $oWarenkorbPosEigenschaft) {
                unset($oEigenschaftwerte);
                $oEigenschaftwerte                       = new stdClass();
                $oEigenschaftwerte->kEigenschaftWert     = $oWarenkorbPosEigenschaft->kEigenschaftWert;
                $oEigenschaftwerte->kEigenschaft         = $oWarenkorbPosEigenschaft->kEigenschaft;
                $oEigenschaftwerte->cEigenschaftName     = $oWarenkorbPosEigenschaft->cEigenschaftName[$_SESSION['cISOSprache']];
                $oEigenschaftwerte->cEigenschaftWertName = $oWarenkorbPosEigenschaft->cEigenschaftWertName[$_SESSION['cISOSprache']];
                if ($oWarenkorbPosEigenschaft->cTyp === 'FREIFELD' || $oWarenkorbPosEigenschaft->cTyp === 'PFLICHT-FREIFELD') {
                    $oEigenschaftwerte->cFreifeldWert = $oWarenkorbPosEigenschaft->cEigenschaftWertName[$_SESSION['cISOSprache']];
                }

                $oEigenschaftwerte_arr[] = $oEigenschaftwerte;
            }

            $this->fuegeEin(
                $oPosition->kArtikel,
                $oPosition->Artikel->cName ?? null,
                $oEigenschaftwerte_arr,
                $oPosition->nAnzahl,
                $oPosition->cUnique,
                $oPosition->kKonfigitem,
                $oPosition->nPosTyp,
                $oPosition->cResponsibility
            );
        }

        return $this;
    }
}
