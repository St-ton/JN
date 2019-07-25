<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Cart\Warenkorb;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Extensions\Config\Item;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * @param Warenkorb $cart
 * @return string
 */
function lang_warenkorb_warenkorbEnthaeltXArtikel(Warenkorb $cart): string
{
    if ($cart->hatTeilbareArtikel()) {
        $nPositionen = $cart->gibAnzahlPositionenExt([C_WARENKORBPOS_TYP_ARTIKEL]);
        $ret         = Shop::Lang()->get('yourbasketcontains', 'checkout') . ' ' . $nPositionen . ' ';
        if ($nPositionen === 1) {
            $ret .= Shop::Lang()->get('position');
        } else {
            $ret .= Shop::Lang()->get('positions');
        }

        return $ret;
    }
    $nArtikel = $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]);
    $nArtikel = str_replace('.', ',', $nArtikel);
    if ($nArtikel === 1) {
        return Shop::Lang()->get('yourbasketcontains', 'checkout') . ' ' .
            $nArtikel . ' ' . Shop::Lang()->get('product');
    }
    if ($nArtikel > 1) {
        return Shop::Lang()->get('yourbasketcontains', 'checkout') . ' ' .
            $nArtikel . ' ' . Shop::Lang()->get('products');
    }
    if ($nArtikel === 0) {
        return Shop::Lang()->get('emptybasket', 'checkout');
    }

    return '';
}

/**
 * @param Warenkorb $cart
 * @return string,
 */
function lang_warenkorb_warenkorbLabel(Warenkorb $cart)
{
    return Shop::Lang()->get('basket', 'checkout') .
        ' (' .
        Preise::getLocalizedPriceString(
            $cart->gibGesamtsummeWarenExt(
                [C_WARENKORBPOS_TYP_ARTIKEL],
                !Frontend::getCustomerGroup()->isMerchant()
            )
        ) . ')';
}

/**
 * @param Warenkorb $cart
 * @return string
 */
function lang_warenkorb_bestellungEnthaeltXArtikel(Warenkorb $cart)
{
    $ret = Shop::Lang()->get('yourordercontains', 'checkout') . ' ' . count($cart->PositionenArr) . ' ';
    if (count($cart->PositionenArr) === 1) {
        $ret .= Shop::Lang()->get('position');
    } else {
        $ret .= Shop::Lang()->get('positions');
    }
    $count = !empty($cart->kWarenkorb)
        ? $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL])
        : 0;

    return $ret . ' ' . Shop::Lang()->get('with') . ' ' . lang_warenkorb_Artikelanzahl($count);
}

/**
 * @param int $count
 * @return string
 */
function lang_warenkorb_Artikelanzahl($count)
{
    return $count == 1
        ? ($count . ' ' . Shop::Lang()->get('product'))
        : ($count . ' ' . Shop::Lang()->get('products'));
}

/**
 * @param int $laenge
 * @return string
 */
function lang_passwortlaenge($laenge)
{
    return $laenge . ' ' . Shop::Lang()->get('min', 'characters') . '!';
}

/**
 * @param int  $ust
 * @param bool $netto
 * @return string
 */
function lang_steuerposition($ust, $netto)
{
    if ($ust == (int)$ust) {
        $ust = (int)$ust;
    }
    return $netto
        ? Shop::Lang()->get('plus', 'productDetails') . ' ' . $ust . '% ' . Shop::Lang()->get('vat', 'productDetails')
        : Shop::Lang()->get('incl', 'productDetails') . ' ' . $ust . '% ' . Shop::Lang()->get('vat', 'productDetails');
}

/**
 * @param string $query
 * @param int    $count
 * @return string
 */
function lang_suche_mindestanzahl($query, $count)
{
    return Shop::Lang()->get('expressionHasTo') . ' ' .
        $count . ' ' .
        Shop::Lang()->get('characters') . '<br />' .
        Shop::Lang()->get('yourSearch') . ': ' . $query;
}

/**
 * @param int $state
 * @return string
 */
function lang_bestellstatus(int $state): string
{
    switch ($state) {
        case BESTELLUNG_STATUS_OFFEN:
            return Shop::Lang()->get('statusPending', 'order');
        case BESTELLUNG_STATUS_IN_BEARBEITUNG:
            return Shop::Lang()->get('statusProcessing', 'order');
        case BESTELLUNG_STATUS_BEZAHLT:
            return Shop::Lang()->get('statusPaid', 'order');
        case BESTELLUNG_STATUS_VERSANDT:
            return Shop::Lang()->get('statusShipped', 'order');
        case BESTELLUNG_STATUS_STORNO:
            return Shop::Lang()->get('statusCancelled', 'order');
        case BESTELLUNG_STATUS_TEILVERSANDT:
            return Shop::Lang()->get('statusPartialShipped', 'order');
        default:
            return '';
    }
}

/**
 * @param Artikel   $product
 * @param int|float $amount
 * @param int       $configItemID
 * @return string
 */
function lang_mindestbestellmenge($product, $amount, int $configItemID = 0)
{
    if ($product->cEinheit) {
        $product->cEinheit = ' ' . $product->cEinheit;
    }
    $name = $product->cName;
    if ($configItemID > 0 && class_exists('Konfigitem')) {
        $name = (new Item($configItemID))->getName();
    }

    return Shop::Lang()->get('product') . ' &quot;' . $name . '&quot; ' .
        Shop::Lang()->get('hasMbm', 'messages') . ' (' .
        $product->fMindestbestellmenge . $product->cEinheit . '). ' .
        Shop::Lang()->get('yourQuantity', 'messages') . ' ' .
        (float)$amount . $product->cEinheit . '.';
}
