<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cart;

use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\DB\ReturnType;
use JTL\Extensions\Konfigitemsprache;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Tax;
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
     * @param int  $customerID
     * @param bool $addProducts
     */
    public function __construct(int $customerID = 0, bool $addProducts = false)
    {
        if ($customerID > 0) {
            $this->kKunde = $customerID;
            $this->ladeWarenkorbPers($addProducts);
        }
    }

    /**
     * fügt eine Position zur WarenkorbPers hinzu
     *
     * @param int    $productID
     * @param string $productName
     * @param array  $attrValues
     * @param float  $qty
     * @param string $unique
     * @param int    $kKonfigitem
     * @param int    $type
     * @param string $responsibility
     * @return $this
     */
    public function fuegeEin(
        int $productID,
        $productName,
        $attrValues,
        $qty,
        $unique = '',
        int $kKonfigitem = 0,
        int $type = \C_WARENKORBPOS_TYP_ARTIKEL,
        $responsibility = 'core'
    ): self {
        $exists = false;
        $idx    = 0;
        foreach ($this->oWarenkorbPersPos_arr as $i => $item) {
            /** @var WarenkorbPersPos $item */
            $item->kArtikel = (int)$item->kArtikel;
            if ($exists) {
                break;
            }
            if ($item->kArtikel === $productID
                && $item->cUnique === $unique
                && (int)$item->kKonfigitem === $kKonfigitem
                && \count($item->oWarenkorbPersPosEigenschaft_arr) > 0
            ) {
                $idx    = $i;
                $exists = true;
                foreach ($attrValues as $oEigenschaftwerte) {
                    // kEigenschaftsWert is not set when using free text variations
                    if (!$item->istEigenschaftEnthalten(
                        $oEigenschaftwerte->kEigenschaft,
                        $oEigenschaftwerte->kEigenschaftWert ?? null,
                        $oEigenschaftwerte->cFreifeldWert ?? ''
                    )) {
                        $exists = false;
                        break;
                    }
                }
            } elseif ($item->kArtikel === $productID
                && $unique !== ''
                && $item->cUnique === $unique
                && (int)$item->kKonfigitem === $kKonfigitem
            ) {
                $idx    = $i;
                $exists = true;
                break;
            }
        }
        if ($exists) {
            $this->oWarenkorbPersPos_arr[$idx]->fAnzahl += $qty;
            $this->oWarenkorbPersPos_arr[$idx]->updateDB();
        } else {
            $item = new WarenkorbPersPos(
                $productID,
                $productName,
                $qty,
                $this->kWarenkorbPers,
                $unique,
                $kKonfigitem,
                $type,
                $responsibility
            );
            $item->schreibeDB();
            $item->erstellePosEigenschaften($attrValues);
            $this->oWarenkorbPersPos_arr[] = $item;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function entferneAlles(): self
    {
        $db = Shop::Container()->getDB();
        foreach ($this->oWarenkorbPersPos_arr as $item) {
            $db->delete(
                'twarenkorbpersposeigenschaft',
                'kWarenkorbPersPos',
                (int)$item->kWarenkorbPersPos
            );
            $db->delete(
                'twarenkorbperspos',
                'kWarenkorbPers',
                (int)$item->kWarenkorbPers
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
        $customer = Shop::Container()->getDB()->queryPrepared(
            'SELECT twarenkorbpers.kKunde
                FROM twarenkorbpers
                JOIN twarenkorbperspos 
                    ON twarenkorbpers.kWarenkorbPers = twarenkorbperspos.kWarenkorbPers
                WHERE twarenkorbperspos.kWarenkorbPersPos = :kwpp',
            ['kwpp' => $id],
            ReturnType::SINGLE_OBJECT
        );
        // Prüfen ob der eingeloggte Kunde auch der Besitzer der zu löschenden WarenkorbPersPos ist
        if (!isset($customer->kKunde) || (int)$customer->kKunde === Frontend::getCustomer()->getID()) {
            return $this;
        }
        // Alle Eigenschaften löschen
        Shop::Container()->getDB()->delete('twarenkorbpersposeigenschaft', 'kWarenkorbPersPos', $id);
        // Die Position mit ID $id löschen
        Shop::Container()->getDB()->delete('twarenkorbperspos', 'kWarenkorbPersPos', $id);
        // WarenkorbPers Position aus der Session löschen
        $source = $_SESSION['WarenkorbPers'];
        if (GeneralObject::hasCount('oWarenkorbPersPos_arr', $source)) {
            foreach ($source->oWarenkorbPersPos_arr as $i => $item) {
                if ((int)$item->kWarenkorbPersPos === $id) {
                    unset($source->oWarenkorbPersPos_arr[$i]);
                }
            }
            // Positionen Array in der WarenkorbPers neu nummerieren
            $source->oWarenkorbPersPos_arr = \array_merge($source->oWarenkorbPersPos_arr);
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
        foreach ($this->oWarenkorbPersPos_arr as $item) {
            if ((int)$item->nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $this->entfernePos($item->kWarenkorbPersPos);
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
     * @param bool $addProducts
     * @return $this
     */
    public function ladeWarenkorbPers(bool $addProducts): self
    {
        // Prüfe ob die WarenkorbPers dem eingeloggten Kunden gehört
        $persCart = Shop::Container()->getDB()->select('twarenkorbpers', 'kKunde', (int)$this->kKunde);
        if (!isset($persCart->kWarenkorbPers) || $persCart->kWarenkorbPers < 1) {
            $this->dErstellt = 'NOW()';
            $this->schreibeDB();
        }

        if ($persCart === false || $persCart === null) {
            return $this;
        }
        $this->kWarenkorbPers = (int)$persCart->kWarenkorbPers;
        $this->kKunde         = (int)$persCart->kKunde;
        $this->dErstellt      = $persCart->dErstellt ?? null;

        if ($this->kWarenkorbPers <= 0) {
            return $this;
        }
        // Hole alle Positionen für eine WarenkorbPers
        $cartItems = Shop::Container()->getDB()->selectAll(
            'twarenkorbperspos',
            'kWarenkorbPers',
            (int)$this->kWarenkorbPers,
            '*, date_format(dHinzugefuegt, \'%d.%m.%Y %H:%i\') AS dHinzugefuegt_de',
            'kKonfigitem, kWarenkorbPersPos'
        );
        // Wenn Positionen vorhanden sind
        if (!\is_array($cartItems) || \count($cartItems) === 0) {
            return $this;
        }
        $itemsValue     = 0.0;
        $defaultOptions = Artikel::getDefaultOptions();
        if (!isset($_SESSION['Steuersatz'])) {
            Tax::setTaxRates();
        }
        // Hole alle Eigenschaften für eine Position
        foreach ($cartItems as $item) {
            $item->kWarenkorbPersPos = (int)$item->kWarenkorbPersPos;
            $item->kWarenkorbPers    = (int)$item->kWarenkorbPers;
            $item->kArtikel          = (int)$item->kArtikel;
            $item->kKonfigitem       = (int)$item->kKonfigitem;
            $item->nPosTyp           = (int)$item->nPosTyp;

            $persItem                    = new WarenkorbPersPos(
                $item->kArtikel,
                $item->cArtikelName,
                $item->fAnzahl,
                $item->kWarenkorbPers,
                $item->cUnique,
                $item->kKonfigitem,
                $item->nPosTyp,
                $item->cResponsibility
            );
            $persItem->kWarenkorbPersPos = $item->kWarenkorbPersPos;
            $persItem->cKommentar        = $item->cKommentar ?? null;
            $persItem->dHinzugefuegt     = $item->dHinzugefuegt;
            $persItem->dHinzugefuegt_de  = $item->dHinzugefuegt_de;

            $attributes = Shop::Container()->getDB()->selectAll(
                'twarenkorbpersposeigenschaft',
                'kWarenkorbPersPos',
                (int)$item->kWarenkorbPersPos
            );
            foreach ($attributes as $attribute) {
                $persItem->oWarenkorbPersPosEigenschaft_arr[] = new WarenkorbPersPosEigenschaft(
                    (int)$attribute->kEigenschaft,
                    (int)$attribute->kEigenschaftWert,
                    $attribute->cFreifeldWert ?? null,
                    $attribute->cEigenschaftName,
                    $attribute->cEigenschaftWertName,
                    (int)$attribute->kWarenkorbPersPos
                );
            }
            if ($addProducts) {
                $persItem->Artikel = new Artikel();
                $persItem->Artikel->fuelleArtikel($persItem->kArtikel, $defaultOptions);
                $persItem->cArtikelName = $persItem->Artikel->cName;

                $itemsValue += $persItem->Artikel->Preise->fVK[$persItem->Artikel->kSteuerklasse];
            }
            $persItem->fAnzahl             = (float)$persItem->fAnzahl;
            $this->oWarenkorbPersPos_arr[] = $persItem;
        }
        $this->cWarenwertLocalized = Preise::getLocalizedPriceString($itemsValue);

        return $this;
    }

    /**
     * @param bool $forceDelete
     * @return string
     */
    public function ueberpruefePositionen(bool $forceDelete = false): string
    {
        $productNames = [];
        $productIDs   = [];
        $msg          = '';
        $db           = Shop::Container()->getDB();
        foreach ($this->oWarenkorbPersPos_arr as $item) {
            // Hat die Position einen Artikel
            if ($item->kArtikel > 0) {
                // Prüfe auf kArtikel
                $productExists = $db->select(
                    'tartikel',
                    'kArtikel',
                    (int)$item->kArtikel
                );
                // Falls Artikel vorhanden
                if (isset($productExists->kArtikel) && $productExists->kArtikel > 0) {
                    // Sichtbarkeit Prüfen
                    if (!empty($item->cUnique) && (int)$item->kKonfigitem > 0) {
                        // config components are always visible in cart...
                        $visibility = null;
                    } else {
                        $visibility = $db->select(
                            'tartikelsichtbarkeit',
                            'kArtikel',
                            (int)$item->kArtikel,
                            'kKundengruppe',
                            Frontend::getCustomerGroup()->getID()
                        );
                    }
                    if ($visibility === null || !isset($visibility->kArtikel) || !$visibility->kArtikel) {
                        // Prüfe welche kEigenschaft gesetzt ist
                        $attributes = $db->selectAll(
                            'teigenschaft',
                            'kArtikel',
                            (int)$item->kArtikel,
                            'kEigenschaft, cName, cTyp'
                        );
                        foreach ($attributes as $attribute) {
                            if ($attribute->cTyp === 'FREIFELD'
                                || $attribute->cTyp === 'PFLICHT-FREIFELD'
                                || \count($item->oWarenkorbPersPosEigenschaft_arr) === 0
                            ) {
                                continue;
                            }
                            foreach ($item->oWarenkorbPersPosEigenschaft_arr as $oWarenkorbPersPosEigenschaft) {
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
                                        $item->kWarenkorbPersPos
                                    );
                                    $db->delete(
                                        'twarenkorbpersposeigenschaft',
                                        'kWarenkorbPersPos',
                                        $item->kWarenkorbPersPos
                                    );
                                    $productNames[] = $item->cArtikelName;
                                    $msg           .= '<br />' . Shop::Lang()->get('noProductWishlist', 'messages');
                                }
                            }
                        }
                        $productIDs[] = (int)$productExists->kArtikel;
                    }
                }
                // Konfigitem ohne Artikelbezug?
            } elseif ($item->kArtikel === 0 && !empty($item->kKonfigitem)) {
                $productIDs[] = (int)$item->kArtikel;
            }
        }
        if ($forceDelete) {
            foreach ($this->oWarenkorbPersPos_arr as $i => $item) {
                if (!\in_array((int)$item->kArtikel, $productIDs, true)) {
                    $this->entfernePos($item->kWarenkorbPersPos);
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
        foreach (Frontend::getCart()->PositionenArr as $item) {
            if ($item->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL) {
                continue;
            }
            $values = [];
            foreach ($item->WarenkorbPosEigenschaftArr as $wkpe) {
                $value                       = new stdClass();
                $value->kEigenschaftWert     = $wkpe->kEigenschaftWert;
                $value->kEigenschaft         = $wkpe->kEigenschaft;
                $value->cEigenschaftName     = $wkpe->cEigenschaftName[$_SESSION['cISOSprache']];
                $value->cEigenschaftWertName = $wkpe->cEigenschaftWertName[$_SESSION['cISOSprache']];
                if ($wkpe->cTyp === 'FREIFELD' || $wkpe->cTyp === 'PFLICHT-FREIFELD') {
                    $value->cFreifeldWert = $wkpe->cEigenschaftWertName[$_SESSION['cISOSprache']];
                }

                $values[] = $value;
            }

            $this->fuegeEin(
                $item->kArtikel,
                $item->Artikel->cName ?? null,
                $values,
                $item->nAnzahl,
                $item->cUnique,
                $item->kKonfigitem,
                $item->nPosTyp,
                $item->cResponsibility
            );
        }

        return $this;
    }

    /**
     * @param int    $productID
     * @param float  $amount
     * @param array  $attributeValues
     * @param bool   $unique
     * @param int    $configItemID
     * @param int    $type
     * @param string $responsibility
     */
    public static function addToCheck(
        int $productID,
        $amount,
        $attributeValues,
        $unique = false,
        int $configItemID = 0,
        int $type = \C_WARENKORBPOS_TYP_ARTIKEL,
        string $responsibility = 'core'
    ): void {
        if (!Frontend::getCustomer()->isLoggedIn()) {
            return;
        }
        $conf = Shop::getSettings([\CONF_GLOBAL]);
        if ($conf['global']['warenkorbpers_nutzen'] !== 'Y') {
            return;
        }
        // Persistenter Warenkorb
        if ($productID > 0) {
            // Pruefe auf kArtikel
            $existing = Shop::Container()->getDB()->select(
                'tartikel',
                'kArtikel',
                $productID,
                null,
                null,
                null,
                null,
                false,
                'kArtikel, cName'
            );
            // Falls Artikel vorhanden
            if ($existing !== null) {
                // Sichtbarkeit pruefen
                if (!empty($unique) && $configItemID > 0) {
                    // config components are always visible in cart...
                    $visibility = null;
                } else {
                    $visibility = Shop::Container()->getDB()->select(
                        'tartikelsichtbarkeit',
                        'kArtikel',
                        $productID,
                        'kKundengruppe',
                        Frontend::getCustomerGroup()->getID(),
                        null,
                        null,
                        false,
                        'kArtikel'
                    );
                }
                if ($visibility === null || !isset($visibility->kArtikel) || !$visibility->kArtikel) {
                    $persCart = new WarenkorbPers(Frontend::getCustomer()->getID());
                    if ($type === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                        $persCart->loescheGratisGeschenkAusWarenkorbPers();
                    }
                    $persCart->fuegeEin(
                        $productID,
                        $existing->cName,
                        $attributeValues,
                        $amount,
                        $unique,
                        $configItemID,
                        $type,
                        $responsibility
                    );
                }
            }
        } elseif ($productID === 0 && !empty($configItemID)) {
            // Konfigitems ohne Artikelbezug
            (new WarenkorbPers(Frontend::getCustomer()->getID()))->fuegeEin(
                $productID,
                (new Konfigitemsprache($configItemID, Shop::getLanguageID()))->getName(),
                $attributeValues,
                $amount,
                $unique,
                $configItemID,
                $type,
                $responsibility
            );
        }
    }
}
