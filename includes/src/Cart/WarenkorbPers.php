<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cart;

use JTL\Catalog\Product\Artikel;
use JTL\DB\ReturnType;
use JTL\Extensions\Konfigitemsprache;
use JTL\Helpers\Tax;
use JTL\Catalog\Product\Preise;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class WarenkorbPers
 * @package JTL\Cart
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
     * @param int    $kArtikel
     * @param string $cArtikelName
     * @param array  $oEigenschaftwerte_arr
     * @param float  $fAnzahl
     * @param string $cUnique
     * @param int    $kKonfigitem
     * @param int    $nPosTyp
     * @param string $cResponsibility
     * @return $this
     */
    public function fuegeEin(
        int $kArtikel,
        $cArtikelName,
        $oEigenschaftwerte_arr,
        $fAnzahl,
        $cUnique = '',
        int $kKonfigitem = 0,
        int $nPosTyp = \C_WARENKORBPOS_TYP_ARTIKEL,
        $cResponsibility = 'core'
    ): self {
        $exists    = false;
        $nPosition = 0;
        foreach ($this->oWarenkorbPersPos_arr as $i => $position) {
            $position->kArtikel = (int)$position->kArtikel;
            if ($exists) {
                break;
            }
            if ($position->kArtikel === $kArtikel
                && $position->cUnique === $cUnique
                && (int)$position->kKonfigitem === $kKonfigitem
                && \count($position->oWarenkorbPersPosEigenschaft_arr) > 0
            ) {
                $nPosition = $i;
                $exists    = true;
                foreach ($oEigenschaftwerte_arr as $oEigenschaftwerte) {
                    //kEigenschaftsWert is not set when using free text variations
                    if (!$position->istEigenschaftEnthalten(
                        $oEigenschaftwerte->kEigenschaft,
                        $oEigenschaftwerte->kEigenschaftWert ?? null,
                        $oEigenschaftwerte->cFreifeldWert ?? ''
                    )) {
                        $exists = false;
                        break;
                    }
                }
            } elseif ($position->kArtikel === $kArtikel
                && $cUnique !== ''
                && $position->cUnique === $cUnique
                && (int)$position->kKonfigitem === $kKonfigitem
            ) {
                $nPosition = $i;
                $exists    = true;
                break;
            }
        }
        if ($exists) {
            $this->oWarenkorbPersPos_arr[$nPosition]->fAnzahl += $fAnzahl;
            $this->oWarenkorbPersPos_arr[$nPosition]->updateDB();
        } else {
            $position = new WarenkorbPersPos(
                $kArtikel,
                $cArtikelName,
                $fAnzahl,
                $this->kWarenkorbPers,
                $cUnique,
                $kKonfigitem,
                $nPosTyp,
                $cResponsibility
            );
            $position->schreibeDB();
            $position->erstellePosEigenschaften($oEigenschaftwerte_arr);
            $this->oWarenkorbPersPos_arr[] = $position;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function entferneAlles(): self
    {
        $db = Shop::Container()->getDB();
        foreach ($this->oWarenkorbPersPos_arr as $position) {
            // Eigenschaften löschen
            $db->delete(
                'twarenkorbpersposeigenschaft',
                'kWarenkorbPersPos',
                (int)$position->kWarenkorbPersPos
            );
            // Postitionen löschen
            $db->delete(
                'twarenkorbperspos',
                'kWarenkorbPers',
                (int)$position->kWarenkorbPers
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
        if ($this->kWarenkorbPers <= 0) {
            return false;
        }
        $this->entferneAlles();
        Shop::Container()->getDB()->delete('twarenkorbpers', 'kWarenkorbPers', (int)$this->kWarenkorbPers);

        return true;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function entfernePos(int $id): self
    {
        $oKunde = Shop::Container()->getDB()->queryPrepared(
            'SELECT twarenkorbpers.kKunde
                FROM twarenkorbpers
                JOIN twarenkorbperspos 
                    ON twarenkorbpers.kWarenkorbPers = twarenkorbperspos.kWarenkorbPers
                WHERE twarenkorbperspos.kWarenkorbPersPos = :kwpp',
            ['kwpp' => $id],
            ReturnType::SINGLE_OBJECT
        );
        // Prüfen ob der eingeloggte Kunde auch der Besitzer der zu löschenden WarenkorbPersPos ist
        if (isset($oKunde->kKunde) && (int)$oKunde->kKunde === Frontend::getCustomer()->getID()) {
            // Alle Eigenschaften löschen
            Shop::Container()->getDB()->delete('twarenkorbpersposeigenschaft', 'kWarenkorbPersPos', $id);
            // Die Position mit ID $id löschen
            Shop::Container()->getDB()->delete('twarenkorbperspos', 'kWarenkorbPersPos', $id);
            // WarenkorbPers Position aus der Session löschen
            if (isset($_SESSION['WarenkorbPers']->oWarenkorbPersPos_arr)
                && \is_array($_SESSION['WarenkorbPers']->oWarenkorbPersPos_arr)
                && \count($_SESSION['WarenkorbPers']->oWarenkorbPersPos_arr) > 0
            ) {
                foreach ($_SESSION['WarenkorbPers']->oWarenkorbPersPos_arr as $i => $oWarenkorbPersPos) {
                    if ($oWarenkorbPersPos->kWarenkorbPersPos == $id) {
                        unset($_SESSION['WarenkorbPers']->oWarenkorbPersPos_arr[$i]);
                    }
                }
                // Positionen Array in der WarenkorbPers neu nummerieren
                $_SESSION['WarenkorbPers']->oWarenkorbPersPos_arr =
                    \array_merge($_SESSION['WarenkorbPers']->oWarenkorbPersPos_arr);
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
        foreach ($this->oWarenkorbPersPos_arr as $position) {
            if ((int)$position->nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $this->entfernePos($position->kWarenkorbPersPos);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function schreibeDB(): self
    {
        $ins                  = new stdClass();
        $ins->kKunde          = $this->kKunde;
        $ins->dErstellt       = $this->dErstellt;
        $this->kWarenkorbPers = Shop::Container()->getDB()->insert('twarenkorbpers', $ins);
        unset($ins);

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
            $this->dErstellt = 'NOW()';
            $this->schreibeDB();
        }

        if ($oWarenkorbPers === false || $oWarenkorbPers === null) {
            return $this;
        }
        $this->kWarenkorbPers = (int)$oWarenkorbPers->kWarenkorbPers;
        $this->kKunde         = (int)$oWarenkorbPers->kKunde;
        $this->dErstellt      = $oWarenkorbPers->dErstellt ?? null;

        if ($this->kWarenkorbPers <= 0) {
            return $this;
        }
        // Hole alle Positionen für eine WarenkorbPers
        $cartPositions = Shop::Container()->getDB()->selectAll(
            'twarenkorbperspos',
            'kWarenkorbPers',
            (int)$this->kWarenkorbPers,
            '*, date_format(dHinzugefuegt, \'%d.%m.%Y %H:%i\') AS dHinzugefuegt_de',
            'kKonfigitem, kWarenkorbPersPos'
        );
        // Wenn Positionen vorhanden sind
        if (!\is_array($cartPositions) || \count($cartPositions) === 0) {
            return $this;
        }
        $fWarenwert     = 0.0;
        $defaultOptions = Artikel::getDefaultOptions();
        if (!isset($_SESSION['Steuersatz'])) {
            Tax::setTaxRates();
        }
        // Hole alle Eigenschaften für eine Position
        foreach ($cartPositions as $posData) {
            $posData->kWarenkorbPersPos = (int)$posData->kWarenkorbPersPos;
            $posData->kWarenkorbPers    = (int)$posData->kWarenkorbPers;
            $posData->kArtikel          = (int)$posData->kArtikel;
            $posData->kKonfigitem       = (int)$posData->kKonfigitem;
            $posData->nPosTyp           = (int)$posData->nPosTyp;
            $cartPos                    = new WarenkorbPersPos(
                $posData->kArtikel,
                $posData->cArtikelName,
                $posData->fAnzahl,
                $posData->kWarenkorbPers,
                $posData->cUnique,
                $posData->kKonfigitem,
                $posData->nPosTyp,
                $posData->cResponsibility
            );

            $cartPos->kWarenkorbPersPos = $posData->kWarenkorbPersPos;
            $cartPos->cKommentar        = $posData->cKommentar ?? null;
            $cartPos->dHinzugefuegt     = $posData->dHinzugefuegt;
            $cartPos->dHinzugefuegt_de  = $posData->dHinzugefuegt_de;

            $attributes = Shop::Container()->getDB()->selectAll(
                'twarenkorbpersposeigenschaft',
                'kWarenkorbPersPos',
                (int)$posData->kWarenkorbPersPos
            );
            foreach ($attributes as $attribute) {
                $oWarenkorbPersPosEigenschaft                = new WarenkorbPersPosEigenschaft(
                    (int)$attribute->kEigenschaft,
                    (int)$attribute->kEigenschaftWert,
                    $attribute->cFreifeldWert ?? null,
                    $attribute->cEigenschaftName,
                    $attribute->cEigenschaftWertName,
                    (int)$attribute->kWarenkorbPersPos
                );
                $cartPos->oWarenkorbPersPosEigenschaft_arr[] = $oWarenkorbPersPosEigenschaft;
            }
            if ($bArtikel) {
                $cartPos->Artikel = new Artikel();
                $cartPos->Artikel->fuelleArtikel($cartPos->kArtikel, $defaultOptions);
                $cartPos->cArtikelName = $cartPos->Artikel->cName;

                $fWarenwert += $cartPos->Artikel->Preise->fVK[$cartPos->Artikel->kSteuerklasse];
            }
            $cartPos->fAnzahl              = (float)$cartPos->fAnzahl;
            $this->oWarenkorbPersPos_arr[] = $cartPos;
        }
        $this->cWarenwertLocalized = Preise::getLocalizedPriceString($fWarenwert);

        return $this;
    }

    /**
     * @param bool $bForceDelete
     * @return string
     */
    public function ueberpruefePositionen(bool $bForceDelete = false): string
    {
        $productNames = [];
        $productIDs   = [];
        $msg          = '';
        $db           = Shop::Container()->getDB();
        foreach ($this->oWarenkorbPersPos_arr as $cartPos) {
            // Hat die Position einen Artikel
            if ($cartPos->kArtikel > 0) {
                // Prüfe auf kArtikel
                $oArtikelVorhanden = $db->select(
                    'tartikel',
                    'kArtikel',
                    (int)$cartPos->kArtikel
                );
                // Falls Artikel vorhanden
                if (isset($oArtikelVorhanden->kArtikel) && $oArtikelVorhanden->kArtikel > 0) {
                    // Sichtbarkeit Prüfen
                    $visibility = $db->select(
                        'tartikelsichtbarkeit',
                        'kArtikel',
                        (int)$cartPos->kArtikel,
                        'kKundengruppe',
                        Frontend::getCustomerGroup()->getID()
                    );
                    if ($visibility === null || !isset($visibility->kArtikel) || !$visibility->kArtikel) {
                        // Prüfe welche kEigenschaft gesetzt ist
                        $attributes = $db->selectAll(
                            'teigenschaft',
                            'kArtikel',
                            (int)$cartPos->kArtikel,
                            'kEigenschaft, cName, cTyp'
                        );
                        foreach ($attributes as $attribute) {
                            if ($attribute->cTyp === 'FREIFELD'
                                || $attribute->cTyp === 'PFLICHT-FREIFELD'
                                || \count($cartPos->oWarenkorbPersPosEigenschaft_arr) === 0
                            ) {
                                continue;
                            }
                            foreach ($cartPos->oWarenkorbPersPosEigenschaft_arr as $oWarenkorbPersPosEigenschaft) {
                                if ($oWarenkorbPersPosEigenschaft->kEigenschaft !== $attribute->kEigenschaft) {
                                    continue;
                                }
                                $exists = $db->select(
                                    'teigenschaftwert',
                                    'kEigenschaftWert',
                                    (int)$oWarenkorbPersPosEigenschaft->kEigenschaftWert,
                                    'kEigenschaft',
                                    (int)$attribute->kEigenschaft
                                );
                                // Prüfe ob die Eigenschaft vorhanden ist
                                if (!isset($exists->kEigenschaftWert) || !$exists->kEigenschaftWert) {
                                    $db->delete(
                                        'twarenkorbperspos',
                                        'kWarenkorbPersPos',
                                        $cartPos->kWarenkorbPersPos
                                    );
                                    $db->delete(
                                        'twarenkorbpersposeigenschaft',
                                        'kWarenkorbPersPos',
                                        $cartPos->kWarenkorbPersPos
                                    );
                                    $productNames[] = $cartPos->cArtikelName;
                                    $msg           .= '<br />' . Shop::Lang()->get('noProductWishlist', 'messages');
                                }
                            }
                        }
                        $productIDs[] = (int)$oArtikelVorhanden->kArtikel;
                    }
                }
                // Konfigitem ohne Artikelbezug?
            } elseif ($cartPos->kArtikel === 0 && !empty($cartPos->kKonfigitem)) {
                $productIDs[] = (int)$cartPos->kArtikel;
            }
        }
        if ($bForceDelete) {
            foreach ($this->oWarenkorbPersPos_arr as $i => $cartPos) {
                if (!\in_array((int)$cartPos->kArtikel, $productIDs, true)) {
                    $this->entfernePos($cartPos->kWarenkorbPersPos);
                    unset($this->oWarenkorbPersPos_arr[$i]);
                }
            }
            $this->oWarenkorbPersPos_arr = \array_merge($this->oWarenkorbPersPos_arr);
        }

        return $msg . \implode(', ', $productNames);
    }

    /**
     * return $this
     */
    public function bauePersVonSession(): self
    {
        if (!\is_array($_SESSION['Warenkorb']->PositionenArr) || \count($_SESSION['Warenkorb']->PositionenArr) === 0) {
            return $this;
        }
        foreach (Frontend::getCart()->PositionenArr as $oPosition) {
            if ($oPosition->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL) {
                continue;
            }
            $values = [];
            foreach ($oPosition->WarenkorbPosEigenschaftArr as $wkpe) {
                $oEigenschaftwerte                       = new stdClass();
                $oEigenschaftwerte->kEigenschaftWert     = $wkpe->kEigenschaftWert;
                $oEigenschaftwerte->kEigenschaft         = $wkpe->kEigenschaft;
                $oEigenschaftwerte->cEigenschaftName     = $wkpe->cEigenschaftName[$_SESSION['cISOSprache']];
                $oEigenschaftwerte->cEigenschaftWertName = $wkpe->cEigenschaftWertName[$_SESSION['cISOSprache']];
                if ($wkpe->cTyp === 'FREIFELD' || $wkpe->cTyp === 'PFLICHT-FREIFELD') {
                    $oEigenschaftwerte->cFreifeldWert = $wkpe->cEigenschaftWertName[$_SESSION['cISOSprache']];
                }

                $values[] = $oEigenschaftwerte;
            }

            $this->fuegeEin(
                $oPosition->kArtikel,
                $oPosition->Artikel->cName ?? null,
                $values,
                $oPosition->nAnzahl,
                $oPosition->cUnique,
                $oPosition->kKonfigitem,
                $oPosition->nPosTyp,
                $oPosition->cResponsibility
            );
        }

        return $this;
    }

    /**
     * @param int    $kArtikel
     * @param float  $fAnzahl
     * @param array  $attributeValues
     * @param bool   $cUnique
     * @param int    $kKonfigitem
     * @param int    $nPosTyp
     * @param string $cResponsibility
     */
    public static function addToCheck(
        int $kArtikel,
        $fAnzahl,
        $attributeValues,
        $cUnique = false,
        int $kKonfigitem = 0,
        int $nPosTyp = \C_WARENKORBPOS_TYP_ARTIKEL,
        string $cResponsibility = 'core'
    ): void {
        if (!Frontend::getCustomer()->isLoggedIn()) {
            return;
        }
        $conf = Shop::getSettings([\CONF_GLOBAL]);
        if ($conf['global']['warenkorbpers_nutzen'] !== 'Y') {
            return;
        }
        // Persistenter Warenkorb
        if ($kArtikel > 0) {
            // Pruefe auf kArtikel
            $oArtikelVorhanden = Shop::Container()->getDB()->select(
                'tartikel',
                'kArtikel',
                $kArtikel,
                null,
                null,
                null,
                null,
                false,
                'kArtikel, cName'
            );
            // Falls Artikel vorhanden
            if ($oArtikelVorhanden !== null) {
                // Sichtbarkeit pruefen
                $visibility = Shop::Container()->getDB()->select(
                    'tartikelsichtbarkeit',
                    'kArtikel',
                    $kArtikel,
                    'kKundengruppe',
                    Frontend::getCustomerGroup()->getID(),
                    null,
                    null,
                    false,
                    'kArtikel'
                );
                if ($visibility === null || !isset($visibility->kArtikel) || !$visibility->kArtikel) {
                    $oWarenkorbPers = new WarenkorbPers(Frontend::getCustomer()->getID());
                    if ($nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                        $oWarenkorbPers->loescheGratisGeschenkAusWarenkorbPers();
                    }
                    $oWarenkorbPers->fuegeEin(
                        $kArtikel,
                        $oArtikelVorhanden->cName,
                        $attributeValues,
                        $fAnzahl,
                        $cUnique,
                        $kKonfigitem,
                        $nPosTyp,
                        $cResponsibility
                    );
                }
            }
        } elseif ($kArtikel === 0 && !empty($kKonfigitem)) {
            // Konfigitems ohne Artikelbezug
            (new WarenkorbPers(Frontend::getCustomer()->getID()))->fuegeEin(
                $kArtikel,
                (new Konfigitemsprache($kKonfigitem, Shop::getLanguageID()))->getName(),
                $attributeValues,
                $fAnzahl,
                $cUnique,
                $kKonfigitem,
                $nPosTyp,
                $cResponsibility
            );
        }
    }
}
