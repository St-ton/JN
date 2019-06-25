<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Extensions;

use JTL\Catalog\Product\Artikel;
use JTL\DB\ReturnType;
use JTL\Helpers\Cart;
use JTL\Nice;
use JTL\Catalog\Product\Preise;
use JTL\Shop;

/**
 * Class Konfigurator
 * @package JTL\Extensions
 */
class Konfigurator
{
    /**
     * @var array
     */
    private static $groups = [];

    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_KONFIGURATOR);
    }

    /**
     * getKonfig
     *
     * @param int $productID
     * @param int $languageID
     * @return array
     */
    public static function getKonfig(int $productID, int $languageID = 0): array
    {
        if (isset(self::$groups[$productID])) {
            //#7482
            return self::$groups[$productID];
        }
        $groups = Shop::Container()->getDB()->selectAll(
            'tartikelkonfiggruppe',
            'kArtikel',
            $productID,
            'kArtikel, kKonfigGruppe',
            'nSort ASC'
        );
        if (!\is_array($groups) || \count($groups) === 0 || !self::checkLicense()) {
            return [];
        }
        if (!$languageID) {
            $languageID = Shop::getLanguageID();
        }
        foreach ($groups as &$group) {
            $group = new Konfiggruppe((int)$group->kKonfigGruppe, $languageID);
        }
        unset($group);

        self::$groups[$productID] = $groups;

        return $groups;
    }

    /**
     * @param int $productID
     * @return bool
     */
    public static function hasKonfig(int $productID): bool
    {
        if (!self::checkLicense()) {
            return false;
        }
        $groups = Shop::Container()->getDB()->query(
            'SELECT kArtikel, kKonfigGruppe
                 FROM tartikelkonfiggruppe
                 WHERE tartikelkonfiggruppe.kArtikel = ' . $productID . '
                 ORDER BY tartikelkonfiggruppe.nSort ASC',
            ReturnType::ARRAY_OF_OBJECTS
        );

        return \is_array($groups) && \count($groups) > 0;
    }

    /**
     * @param int $productID
     * @return bool
     */
    public static function validateKonfig($productID): bool
    {
        /* Vorvalidierung deaktiviert */
        return true;
    }

    /**
     * @param object $cart
     * @deprecated since 5.0.0
     */
    public static function postcheckBasket(&$cart): void
    {
        self::postcheckCart($cart);
    }

    /**
     * @param object $cart
     */
    public static function postcheckCart(&$cart): void
    {
        if (!\is_array($cart->PositionenArr) || \count($cart->PositionenArr) === 0 || !self::checkLicense()) {
            return;
        }
        $deletedItems = [];
        foreach ($cart->PositionenArr as $index => $item) {
            $deleted = false;
            if ($item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
                if ($item->cUnique && $item->kKonfigitem == 0) {
                    $configItems = [];
                    foreach ($cart->PositionenArr as $child) {
                        if ($child->cUnique && $child->cUnique === $item->cUnique && $child->kKonfigitem > 0) {
                            $configItems[] = new Konfigitem($child->kKonfigitem);
                        }
                    }
                    // Konfiguration validieren
                    if (self::validateCart($item->kArtikel, $configItems) !== true) {
                        $deleted        = true;
                        $deletedItems[] = $index;
                        //loescheWarenkorbPosition($nPos);
                    }
                } elseif (!$item->cUnique) {
                    // Konfiguration vorhanden -> löschen
                    if (self::hasKonfig($item->kArtikel)) {
                        $deleted        = true;
                        $deletedItems[] = $index;
                        //loescheWarenkorbPosition($nPos);
                    }
                }
                if ($deleted) {
                    Shop::Container()->getLogService()->error(
                        'Validierung der Konfiguration fehlgeschlagen - Warenkorbposition wurde entfernt: ' .
                        $item->cName[$_SESSION['cISOSprache']] . '(' . $item->kArtikel . ')'
                    );
                }
            }
        }
        Cart::deleteCartItems($deletedItems, false);
    }

    /**
     * @param int   $productID
     * @param array $configItems
     * @return array|bool
     * @deprecated since 5.0.0
     */
    public static function validateBasket(int $productID, $configItems)
    {
        return self::validateCart($productID, $configItems);
    }

    /**
     * @param int   $productID
     * @param array $configItems
     * @return array|bool
     */
    public static function validateCart(int $productID, $configItems)
    {
        if ($productID === 0 || !\is_array($configItems)) {
            Shop::Container()->getLogService()->error(
                'Validierung der Konfiguration fehlgeschlagen - Ungültige Daten'
            );

            return false;
        }
        // Gesamtpreis
        $fFinalPrice = 0.0;
        // Hauptartikel
        $product = new Artikel();
        $product->fuelleArtikel($productID, Artikel::getDefaultOptions());
        // Grundpreis
        if ($product && (int)$product->kArtikel > 0) {
            $fFinalPrice += $product->Preise->fVKNetto;
        }
        foreach ($configItems as $configItem) {
            if (!isset($configItem->fAnzahl) ||
                $configItem->fAnzahl < $configItem->getMin() ||
                $configItem->fAnzahl > $configItem->getMax()) {
                $configItem->fAnzahl = $configItem->getInitial();
            }
            $fFinalPrice += $configItem->getPreis(true) * $configItem->fAnzahl;
        }
        $errors = [];
        foreach (self::getKonfig($productID) as $group) {
            $itemCount = 0;
            $groupID   = $group->getKonfiggruppe();
            foreach ($configItems as $configItem) {
                if ($configItem->getKonfiggruppe() == $groupID) {
                    $itemCount++;
                }
            }
            if ($itemCount < $group->getMin() && $group->getMin() > 0) {
                if ($group->getMin() == $group->getMax()) {
                    $errors[$groupID] =
                        Shop::Lang()->get('configChooseNComponents', 'productDetails', $group->getMin());
                } else {
                    $errors[$groupID] =
                        Shop::Lang()->get('configChooseMinComponents', 'productDetails', $group->getMin());
                }
                $errors[$groupID] .= self::langComponent($group->getMin() > 1);
            } elseif ($itemCount > $group->getMax() && $group->getMax() > 0) {
                if ($group->getMin() == $group->getMax()) {
                    $errors[$groupID] =
                        Shop::Lang()->get('configChooseNComponents', 'productDetails', $group->getMin()) .
                        self::langComponent($group->getMin() > 1);
                } else {
                    $errors[$groupID] =
                        Shop::Lang()->get('configChooseMaxComponents', 'productDetails', $group->getMax()) .
                        self::langComponent($group->getMax() > 1);
                }
            }
        }

        if ($fFinalPrice < 0.0) {
            $cError = \sprintf(
                "Negative Konfigurationssumme für Artikel '%s' (Art.Nr.: %s, Netto: %s) - Vorgang abgebrochen",
                $product->cName,
                $product->cArtNr,
                Preise::getLocalizedPriceString($fFinalPrice)
            );
            Shop::Container()->getLogService()->error($cError);

            return false;
        }

        return \count($errors) === 0 ? true : $errors;
    }

    /**
     * @param bool $bPlural
     * @param bool $bSpace
     * @return string
     */
    private static function langComponent(bool $bPlural = false, bool $bSpace = true): string
    {
        $component = $bSpace ? ' ' : '';

        return $component . Shop::Lang()->get($bPlural ? 'configComponents' : 'configComponent', 'productDetails');
    }

    /**
     * @param Konfiggruppe[] $confGroups
     * @return bool
     */
    public static function hasUnavailableGroup(array $confGroups): bool
    {
        foreach ($confGroups as $confGroup) {
            if (!$confGroup->minItemsInStock()) {
                return true;
            }
        }

        return false;
    }
}
