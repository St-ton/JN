<?php declare(strict_types=1);

use JTL\Cart\Cart;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Extensions\Config\Item;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * @param Cart $cart
 * @return string
 */
function lang_warenkorb_warenkorbEnthaeltXArtikel(Cart $cart): string
{
    if ($cart->hatTeilbareArtikel()) {
        $itemCount = $cart->gibAnzahlPositionenExt([C_WARENKORBPOS_TYP_ARTIKEL]);
        if ($itemCount === 1) {
            return Shop::Lang()->get('yourbasketcontainsPositionsSingular', 'checkout', $itemCount);
        }

        return Shop::Lang()->get('yourbasketcontainsPositionsPlural', 'checkout', $itemCount);
    }
    $count       = $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]);
    $countString = str_replace('.', ',', (string)$count);
    if ($count === 1) {
        return Shop::Lang()->get('yourbasketcontainsItemsSingular', 'checkout', $countString);
    }
    if ($count > 1) {
        return Shop::Lang()->get('yourbasketcontainsItemsPlural', 'checkout', $countString);
    }

    return Shop::Lang()->get('emptybasket', 'checkout');
}

/**
 * @param Cart $cart
 * @return string
 * @deprecated since 5.2.0
 */
function lang_warenkorb_warenkorbLabel(Cart $cart): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return Shop::Lang()->get('cartSumLabel', 'checkout', Preise::getLocalizedPriceString(
        $cart->gibGesamtsummeWarenExt(
            [C_WARENKORBPOS_TYP_ARTIKEL],
            !Frontend::getCustomerGroup()->isMerchant()
        )
    ));
}

/**
 * @param Cart $cart
 * @return string
 */
function lang_warenkorb_bestellungEnthaeltXArtikel(Cart $cart): string
{
    $posCount  = count($cart->PositionenArr);
    $itemCount = !empty($cart->kWarenkorb)
        ? $cart->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL])
        : 0;
    if ($posCount === 1) {
        if ($itemCount === 1) {
            return Shop::Lang()->get('orderPositionSingularItemsSingular', 'checkout', $posCount, $itemCount);
        }
        return Shop::Lang()->get('orderPositionSingularItemsPlural', 'checkout', $posCount, $itemCount);
    }
    if ($itemCount === 1) {
        return Shop::Lang()->get('orderPositionPluralItemsSingular', 'checkout', $posCount, $itemCount);
    }

    return Shop::Lang()->get('orderPositionPluralItemsPlural', 'checkout', $posCount, $itemCount);
}

/**
 * @param int $count
 * @return string
 * @deprecated since 5.2.0
 */
function lang_warenkorb_Artikelanzahl($count): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return $count == 1
        ? ($count . ' ' . Shop::Lang()->get('product'))
        : ($count . ' ' . Shop::Lang()->get('products'));
}

/**
 * @param int $length
 * @return string
 * @deprecated since 5.2.0
 */
function lang_passwortlaenge($length): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return Shop::Lang()->get('minCharLen', 'messages', $length);
}

/**
 * @param int|string $ust
 * @param bool       $net
 * @return string
 */
function lang_steuerposition($ust, $net): string
{
    if ($ust == (int)$ust) {
        $ust = (int)$ust;
    }
    $showVat  = Shop::getSettingValue(\CONF_GLOBAL, 'global_ust_auszeichnung') === 'autoNoVat' ? '' : ($ust . '% ');
    $inklexkl = Shop::Lang()->get($net === true ? 'excl' : 'incl', 'productDetails');

    return $inklexkl . ' ' . $showVat . Shop::Lang()->get('vat', 'productDetails');
}

/**
 * @param string $query
 * @param int    $count
 * @return string
 * @deprecated since 5.2.0
 */
function lang_suche_mindestanzahl($query, $count): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return Shop::Lang()->get('searchQueryMinLength', 'messages', $count, $query);
}

/**
 * @param int $state
 * @return string
 */
function lang_bestellstatus(int $state): string
{
    return match ($state) {
        BESTELLUNG_STATUS_OFFEN          => Shop::Lang()->get('statusPending', 'order'),
        BESTELLUNG_STATUS_IN_BEARBEITUNG => Shop::Lang()->get('statusProcessing', 'order'),
        BESTELLUNG_STATUS_BEZAHLT        => Shop::Lang()->get('statusPaid', 'order'),
        BESTELLUNG_STATUS_VERSANDT       => Shop::Lang()->get('statusShipped', 'order'),
        BESTELLUNG_STATUS_STORNO         => Shop::Lang()->get('statusCancelled', 'order'),
        BESTELLUNG_STATUS_TEILVERSANDT   => Shop::Lang()->get('statusPartialShipped', 'order'),
        default                          => '',
    };
}

/**
 * @param Artikel   $product
 * @param int|float $amount
 * @param int       $configItemID
 * @return string
 */
function lang_mindestbestellmenge(Artikel $product, $amount, int $configItemID = 0): string
{
    if ($product->cEinheit) {
        $product->cEinheit = ' ' . $product->cEinheit;
    }
    $name = $product->cName;
    if ($configItemID > 0 && class_exists('Konfigitem')) {
        $name = (new Item($configItemID))->getName();
    }

    return Shop::Lang()->get(
        'productMinorderQty',
        'messages',
        $name,
        $product->fMindestbestellmenge . $product->cEinheit,
        (float)$amount . $product->cEinheit
    );
}
