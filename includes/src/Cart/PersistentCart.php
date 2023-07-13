<?php

namespace JTL\Cart;

use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\DB\DbInterface;
use JTL\Extensions\Config\ItemLocalization;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Product;
use JTL\Helpers\Tax;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class PersistentCart
 * @package JTL\Cart
 */
class PersistentCart
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
     * @var PersistentCartItem[]
     */
    public $oWarenkorbPersPos_arr = [];

    /**
     * @var string
     */
    public $cWarenwertLocalized;

    /**
     * @var self
     */
    private static $instance;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * PersistentCart constructor.
     * @param int              $customerID
     * @param bool             $addProducts
     * @param DbInterface|null $db
     */
    public function __construct(int $customerID = 0, bool $addProducts = false, DbInterface $db = null)
    {
        $this->db = $db ?? Shop::Container()->getDB();
        if ($customerID > 0) {
            $this->kKunde = $customerID;
            $this->ladeWarenkorbPers($addProducts);
        }
    }

    /**
     * @param int              $customerID
     * @param bool             $addProducts
     * @param DbInterface|null $db
     * @return PersistentCart
     */
    public static function getInstance(int $customerID = 0, bool $addProducts = false, DbInterface $db = null): self
    {
        if (self::$instance === null || self::$instance->kKunde !== $customerID) {
            self::$instance = new self($customerID, $addProducts, $db);
        }

        return self::$instance;
    }

    /**
     * fügt eine Position zur WarenkorbPers hinzu
     *
     * @param int         $productID
     * @param string|null $productName
     * @param array       $properties
     * @param float|int   $qty
     * @param string      $unique
     * @param int         $configItemID
     * @param int         $type
     * @param string      $responsibility
     * @return $this
     */
    public function fuegeEin(
        int     $productID,
        ?string $productName,
        array   $properties,
        $qty,
        $unique = '',
        int     $configItemID = 0,
        int     $type = \C_WARENKORBPOS_TYP_ARTIKEL,
        string  $responsibility = 'core'
    ): self {
        $exists = false;
        $idx    = 0;
        foreach ($this->oWarenkorbPersPos_arr as $i => $item) {
            /** @var PersistentCartItem $item */
            if ($exists) {
                break;
            }
            if ($item->kArtikel === $productID
                && $item->cUnique === $unique
                && $item->kKonfigitem === $configItemID
                && \count($item->oWarenkorbPersPosEigenschaft_arr) > 0
            ) {
                $idx    = $i;
                $exists = true;
                foreach ($properties as $property) {
                    // kEigenschaftsWert is not set when using free text variations
                    if (!$item->istEigenschaftEnthalten(
                        $property->kEigenschaft,
                        $property->kEigenschaftWert ?? null,
                        $property->cFreifeldWert ?? ''
                    )) {
                        $exists = false;
                        break;
                    }
                }
            } elseif ($item->kArtikel === $productID
                && $unique !== ''
                && $item->cUnique === $unique
                && $item->kKonfigitem === $configItemID
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
            $item = new PersistentCartItem(
                $productID,
                $productName,
                $qty,
                $this->kWarenkorbPers,
                $unique,
                $configItemID,
                $type,
                $responsibility
            );
            $item->schreibeDB();
            $item->erstellePosEigenschaften($properties);
            $this->oWarenkorbPersPos_arr[] = $item;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function entferneAlles(): self
    {
        foreach ($this->oWarenkorbPersPos_arr as $item) {
            $this->db->delete(
                'twarenkorbpersposeigenschaft',
                'kWarenkorbPersPos',
                $item->kWarenkorbPersPos
            );
            $this->db->delete(
                'twarenkorbperspos',
                'kWarenkorbPers',
                $item->kWarenkorbPers
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
        $this->db->delete('twarenkorbpers', 'kWarenkorbPers', $this->kWarenkorbPers);

        return true;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function entfernePos(int $id): self
    {
        $customer = $this->db->getSingleObject(
            'SELECT twarenkorbpers.kKunde
                FROM twarenkorbpers
                JOIN twarenkorbperspos 
                    ON twarenkorbpers.kWarenkorbPers = twarenkorbperspos.kWarenkorbPers
                WHERE twarenkorbperspos.kWarenkorbPersPos = :kwpp',
            ['kwpp' => $id]
        );
        // Prüfen ob der eingeloggte Kunde auch der Besitzer der zu löschenden WarenkorbPersPos ist
        if ($customer === null || (int)$customer->kKunde !== Frontend::getCustomer()->getID()) {
            return $this;
        }
        // Alle Eigenschaften löschen
        $this->db->delete('twarenkorbpersposeigenschaft', 'kWarenkorbPersPos', $id);
        // Die Position mit ID $id löschen
        $this->db->delete('twarenkorbperspos', 'kWarenkorbPersPos', $id);
        // WarenkorbPers Position aus der Session löschen
        $source = $_SESSION['WarenkorbPers'] ?? [];
        if (GeneralObject::hasCount('oWarenkorbPersPos_arr', $source)) {
            foreach ($source->oWarenkorbPersPos_arr as $i => $item) {
                if ($item->kWarenkorbPersPos === $id) {
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
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
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
        $this->kWarenkorbPers = $this->db->insert('twarenkorbpers', $ins);
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
        $persCart = $this->db->select('twarenkorbpers', 'kKunde', $this->kKunde);
        if ($persCart === null || !isset($persCart->kWarenkorbPers) || $persCart->kWarenkorbPers < 1) {
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
        $cartItems = $this->db->selectAll(
            'twarenkorbperspos',
            'kWarenkorbPers',
            $this->kWarenkorbPers,
            '*, date_format(dHinzugefuegt, \'%d.%m.%Y %H:%i\') AS dHinzugefuegt_de',
            'kWarenkorbPersPos'
        );
        // Wenn Positionen vorhanden sind
        if (\count($cartItems) === 0) {
            return $this;
        }
        $itemsValue     = 0.0;
        $defaultOptions = Artikel::getDefaultOptions();
        if (!isset($_SESSION['Steuersatz'])) {
            Tax::setTaxRates();
        }
        $customerGroup = Frontend::getCustomerGroup();
        $currency      = Frontend::getCurrency();
        $cache         = Shop::Container()->getCache();
        // Hole alle Eigenschaften für eine Position
        foreach ($cartItems as $item) {
            $item->kWarenkorbPersPos = (int)$item->kWarenkorbPersPos;
            $item->kWarenkorbPers    = (int)$item->kWarenkorbPers;
            $item->kArtikel          = (int)$item->kArtikel;
            $item->kKonfigitem       = (int)$item->kKonfigitem;
            $item->nPosTyp           = (int)$item->nPosTyp;

            $persItem                    = new PersistentCartItem(
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

            $attributes = $this->db->selectAll(
                'twarenkorbpersposeigenschaft',
                'kWarenkorbPersPos',
                $item->kWarenkorbPersPos
            );
            foreach ($attributes as $attribute) {
                $persItem->oWarenkorbPersPosEigenschaft_arr[] = new PersistentCartItemProperty(
                    (int)$attribute->kEigenschaft,
                    (int)$attribute->kEigenschaftWert,
                    $attribute->cFreifeldWert ?? null,
                    $attribute->cEigenschaftName,
                    $attribute->cEigenschaftWertName,
                    (int)$attribute->kWarenkorbPersPos
                );
            }
            if ($addProducts) {
                $persItem->Artikel = new Artikel($this->db, $customerGroup, $currency, $cache);
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
        $productNames   = [];
        $productPersIDs = [];
        $msg            = '';
        $cgroupID       = Frontend::getCustomerGroup()->getID();
        foreach ($this->oWarenkorbPersPos_arr as $item) {
            $productExists = $this->db->select(
                'tartikel',
                'kArtikel',
                $item->kArtikel
            );
            if ($item->kArtikel > 0 && $productExists !== null) {
                $visibility = (!empty($item->cUnique) && $item->kKonfigitem > 0)
                    || Product::checkProductVisibility($item->kArtikel, $cgroupID, $this->db);
                if ($visibility === true) {
                    // Prüfe welche kEigenschaft gesetzt ist
                    $attributes = $this->db->selectAll(
                        'teigenschaft',
                        'kArtikel',
                        $item->kArtikel,
                        'kEigenschaft, cName, cTyp'
                    );
                    foreach ($attributes as $attribute) {
                        if ($attribute->cTyp === 'FREIFELD'
                            || $attribute->cTyp === 'PFLICHT-FREIFELD'
                            || \count($item->oWarenkorbPersPosEigenschaft_arr) === 0
                        ) {
                            continue;
                        }
                        foreach ($item->oWarenkorbPersPosEigenschaft_arr as $property) {
                            if ($property->kEigenschaft !== $attribute->kEigenschaft) {
                                continue;
                            }
                            $exists = $this->db->select(
                                'teigenschaftwert',
                                'kEigenschaftWert',
                                $property->kEigenschaftWert,
                                'kEigenschaft',
                                (int)$attribute->kEigenschaft
                            );
                            // Prüfe ob die Eigenschaft vorhanden ist
                            if ($exists === null || !$exists->kEigenschaftWert) {
                                $this->db->delete(
                                    'twarenkorbperspos',
                                    'kWarenkorbPersPos',
                                    $item->kWarenkorbPersPos
                                );
                                $this->db->delete(
                                    'twarenkorbpersposeigenschaft',
                                    'kWarenkorbPersPos',
                                    $item->kWarenkorbPersPos
                                );
                                $productNames[] = $item->cArtikelName;
                                $msg           .= '<br />' . Shop::Lang()->get('noProductWishlist', 'messages');
                            }
                        }
                    }
                    $productPersIDs[] = $item->kWarenkorbPersPos;
                }
            } elseif ($item->kArtikel === 0 && !empty($item->kKonfigitem)) {
                $productPersIDs[] = $item->kWarenkorbPersPos;
            }
        }
        if ($forceDelete) {
            $productPersIDs = $this->checkForOrphanedConfigItems($productPersIDs);
            foreach ($this->oWarenkorbPersPos_arr as $i => $item) {
                if (!\in_array($item->kWarenkorbPersPos, $productPersIDs, true)) {
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
                $value->cEigenschaftName     = \is_array($wkpe->cEigenschaftName)
                    ? $wkpe->cEigenschaftName[$_SESSION['cISOSprache']]
                    : (string)$wkpe->cEigenschaftName;
                $value->cEigenschaftWertName = \is_array($wkpe->cEigenschaftWertName)
                    ? $wkpe->cEigenschaftWertName[$_SESSION['cISOSprache']]
                    : (string)$wkpe->cEigenschaftWertName;
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
     * @param int         $productID
     * @param float|int   $amount
     * @param array       $attributeValues
     * @param bool|string $unique
     * @param int         $configItemID
     * @param int         $type
     * @param string      $responsibility
     */
    public function check(
        int    $productID,
        $amount,
        array  $attributeValues,
        $unique = false,
        int    $configItemID = 0,
        int    $type = \C_WARENKORBPOS_TYP_ARTIKEL,
        string $responsibility = 'core'
    ): void {
        if (!Frontend::getCustomer()->isLoggedIn()) {
            return;
        }
        if (Shop::getSettingValue(\CONF_KAUFABWICKLUNG, 'warenkorbpers_nutzen') !== 'Y') {
            return;
        }
        $product = $this->db->getSingleObject(
            'SELECT cName 
                FROM tartikel
                WHERE kArtikel = :pid',
            ['pid' => $productID]
        );
        if ($productID > 0 && $product !== null) {
            $visibility = (!empty($unique) && $configItemID > 0)
                || Product::checkProductVisibility($productID, Frontend::getCustomerGroup()->getID(), $this->db);
            if ($visibility === true) {
                if ($type === \C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                    $this->loescheGratisGeschenkAusWarenkorbPers();
                }
                $this->fuegeEin(
                    $productID,
                    $product->cName,
                    $attributeValues,
                    $amount,
                    $unique,
                    $configItemID,
                    $type,
                    $responsibility
                );
            }
        } elseif ($productID === 0 && !empty($configItemID)) {
            // Konfigitems ohne Artikelbezug
            $this->fuegeEin(
                $productID,
                (new ItemLocalization($configItemID, Shop::getLanguageID()))->getName(),
                $attributeValues,
                $amount,
                $unique,
                $configItemID,
                $type,
                $responsibility
            );
        }
    }

    /**
     * @param int         $productID
     * @param float|int   $amount
     * @param array       $attributeValues
     * @param bool|string $unique
     * @param int         $configItemID
     * @param int         $type
     * @param string      $responsibility
     * @deprecated since 5.2.0
     */
    public static function addToCheck(
        int    $productID,
        $amount,
        array  $attributeValues,
        $unique = false,
        int    $configItemID = 0,
        int    $type = \C_WARENKORBPOS_TYP_ARTIKEL,
        string $responsibility = 'core'
    ): void {
        self::getInstance(Frontend::getCustomer()->getID())->check(
            $productID,
            $amount,
            $attributeValues,
            $unique,
            $configItemID,
            $type,
            $responsibility
        );
    }

    /**
     * @param array $ids
     * @return array
     */
    private function checkForOrphanedConfigItems(array $ids): array
    {
        foreach ($this->oWarenkorbPersPos_arr as $item) {
            if ($item->kKonfigitem === 0) {
                continue;
            }
            $mainProduct = \array_values(
                \array_filter($this->oWarenkorbPersPos_arr, static function ($persItem) use ($item): bool {
                    return $persItem->kWarenkorbPers === $item->kWarenkorbPers
                        && $persItem->cUnique === $item->cUnique
                        && $persItem->kKonfigitem === 0;
                })
            );
            // if main product not found, remove the child id
            if (\count($mainProduct) === 0) {
                $ids = \array_values(
                    \array_filter($ids, static function ($id) use ($item): bool {
                        return (int)$id !== $item->kWarenkorbPersPos;
                    })
                );
                continue;
            }
            $configItem = $this->db->getSingleObject(
                'SELECT * FROM tkonfigitem WHERE kKonfigitem = :ciid ',
                ['ciid' => $item->kKonfigitem]
            );
            $parents    = $this->db->getObjects(
                'SELECT * FROM tartikelkonfiggruppe 
                    WHERE kArtikel = :pid
                    AND kKonfiggruppe = :cgid',
                [
                    'pid'  => $mainProduct[0]->kArtikel,
                    'cgid' => $configItem->kKonfiggruppe ?? 0,
                ]
            );
            if (\count($parents) === 0) {
                $ids = \array_values(
                    \array_filter($ids, static function ($id) use ($item, $mainProduct): bool {
                        return (int)$id !== $item->kWarenkorbPersPos
                            && (int)$id !== $mainProduct[0]->kWarenkorbPersPos;
                    })
                );
            }
        }

        return $ids;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->kWarenkorbPers;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->kWarenkorbPers = $id;
    }

    /**
     * @return int
     */
    public function getCustomerID(): int
    {
        return $this->kKunde;
    }

    /**
     * @param int $kKunde
     */
    public function setCustomerID(int $kKunde): void
    {
        $this->kKunde = $kKunde;
    }

    /**
     * @return string
     */
    public function getDateCreated(): string
    {
        return $this->dErstellt;
    }

    /**
     * @param string $dErstellt
     */
    public function setDateCreated(string $dErstellt): void
    {
        $this->dErstellt = $dErstellt;
    }

    /**
     * @return PersistentCartItem[]
     */
    public function getItems(): array
    {
        return $this->oWarenkorbPersPos_arr;
    }

    /**
     * @param PersistentCartItem[] $oWarenkorbPersPos_arr
     */
    public function setOWarenkorbPersPosArr(array $oWarenkorbPersPos_arr): void
    {
        $this->oWarenkorbPersPos_arr = $oWarenkorbPersPos_arr;
    }

    /**
     * @return string
     */
    public function getLocalizedCartSum(): string
    {
        return $this->cWarenwertLocalized;
    }

    /**
     * @param string $cWarenwertLocalized
     */
    public function setLocalizedCartSum(string $cWarenwertLocalized): void
    {
        $this->cWarenwertLocalized = $cWarenwertLocalized;
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }
}
