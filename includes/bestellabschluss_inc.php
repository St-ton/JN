<?php declare(strict_types=1);

use JTL\Campaign;
use JTL\Cart\CartItem;
use JTL\Catalog\Currency;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\EigenschaftWert;
use JTL\Catalog\Wishlist\Wishlist;
use JTL\CheckBox;
use JTL\Checkout\Bestellung;
use JTL\Checkout\Kupon;
use JTL\Checkout\KuponBestellung;
use JTL\Checkout\Lieferadresse;
use JTL\Checkout\Nummern;
use JTL\Checkout\OrderHandler;
use JTL\Checkout\Rechnungsadresse;
use JTL\Checkout\StockUpdater;
use JTL\Checkout\ZahlungsInfo;
use JTL\Customer\Customer;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\Date;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Plugin\Helper;
use JTL\Plugin\Payment\MethodInterface;
use JTL\Session\Frontend;
use JTL\Shop;

function getOrderHandler(): OrderHandler
{
    return new OrderHandler(Shop::Container()->getDB(), Frontend::getCustomer(), Frontend::getCart());
}

function getStockUpdater(): StockUpdater
{
    return new StockUpdater(Shop::Container()->getDB(), Frontend::getCustomer(), Frontend::getCart());
}

/**
 * @return int
 * @deprecated since 5.2.0
 */
function bestellungKomplett(): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $checkbox                = new CheckBox();
    $_SESSION['cPlausi_arr'] = $checkbox->validateCheckBox(
        CHECKBOX_ORT_BESTELLABSCHLUSS,
        Frontend::getCustomerGroup()->getID(),
        $_POST,
        true
    );
    $_SESSION['cPost_arr']   = $_POST;

    return (isset($_SESSION['Kunde'], $_SESSION['Lieferadresse'], $_SESSION['Versandart'], $_SESSION['Zahlungsart'])
        && $_SESSION['Kunde']
        && $_SESSION['Lieferadresse']
        && (int)$_SESSION['Versandart']->kVersandart > 0
        && (int)$_SESSION['Zahlungsart']->kZahlungsart > 0
        && Request::verifyGPCDataInt('abschluss') === 1
        && count($_SESSION['cPlausi_arr']) === 0
    ) ? 1 : 0;
}

/**
 * @return int
 */
function gibFehlendeEingabe(): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    if (!isset($_SESSION['Kunde']) || !$_SESSION['Kunde']) {
        return 1;
    }
    if (!isset($_SESSION['Lieferadresse']) || !$_SESSION['Lieferadresse']) {
        return 2;
    }
    if (!isset($_SESSION['Versandart'])
        || !$_SESSION['Versandart']
        || (int)$_SESSION['Versandart']->kVersandart === 0
    ) {
        return 3;
    }
    if (!isset($_SESSION['Zahlungsart'])
        || !$_SESSION['Zahlungsart']
        || (int)$_SESSION['Zahlungsart']->kZahlungsart === 0
    ) {
        return 4;
    }
    if (count($_SESSION['cPlausi_arr']) > 0) {
        return 6;
    }

    return -1;
}

/**
 * @param int    $cleared
 * @param string $orderNo
 */
function bestellungInDB($cleared = 0, $orderNo = '')
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return getOrderHandler()->bestellungInDB($cleared, $orderNo);
}

/**
 * @param object $paymentInfo
 * @deprecated since 5.2.0
 */
function speicherKundenKontodaten($paymentInfo): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    getOrderHandler()->saveCustomerAccountData($paymentInfo);
}

/**
 *
 */
function unhtmlSession(): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    getOrderHandler()->unhtmlSession();
}

/**
 * @param int       $productID
 * @param int|float $amount
 * @deprecated since 5.2.0
 */
function aktualisiereBestseller(int $productID, $amount): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    getStockUpdater()->updateBestsellers($productID, $amount);
}

/**
 * @param int $productID
 * @param int $targetID
 * @deprecated since 5.2.0
 */
function aktualisiereXselling(int $productID, int $targetID): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    getStockUpdater()->updateXSelling($productID, $targetID);
}

/**
 * @param Artikel   $product
 * @param int|float $amount
 * @param array     $attributeValues
 * @param int       $productFilter
 * @return int|float - neuer Lagerbestand
 * @deprecated since 5.2.0
 */
function aktualisiereLagerbestand(Artikel $product, $amount, $attributeValues, int $productFilter = 1)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return getStockUpdater()->updateStock($product, $amount, $attributeValues, $productFilter);
}

/**
 * @param int $productID
 * @param float|int $amount
 * @param float|int $packeinheit
 */
function updateStock(int $productID, $amount, $packeinheit)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    getStockUpdater()->updateProductStockLevel($productID, $amount, $packeinheit);
}

/**
 * @param Artikel   $bomProduct
 * @param int|float $amount
 * @return int|float - neuer Lagerbestand
 */
function aktualisiereStuecklistenLagerbestand(Artikel $bomProduct, $amount)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return getStockUpdater()->updateBOMStockLevel($bomProduct, $amount);
}

/**
 * @param int   $productID
 * @param float $stockLevel
 * @param bool  $allowNegativeStock
 * @deprecated since 5.2.0
 */
function aktualisiereKomponenteLagerbestand(int $productID, float $stockLevel, bool $allowNegativeStock): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    getStockUpdater()->updateBOMStock($productID, $stockLevel, $allowNegativeStock);
}

/**
 * @param Bestellung $order
 * @deprecated since 5.2.0
 */
function KuponVerwendungen($order): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    getStockUpdater()->updateCouponUsages($order);
}

/**
 * @return string
 * @deprecated since 5.2.0
 */
function baueBestellnummer(): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return getOrderHandler()->createOrderNo();
}

/**
 * @param Bestellung $order
 * @deprecated since 5.2.0
 */
function speicherUploads($order): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    getOrderHandler()->saveUploads($order);
}

/**
 * @param Bestellung $order
 * @deprecated since 5.2.0
 */
function setzeSmartyWeiterleitung(Bestellung $order): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
}

/**
 * @return Bestellung
 * @deprecated since 5.2.0
 */
function fakeBestellung()
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return getOrderHandler()->fakeBestellung();
}

/**
 * @return null|stdClass
 * @deprecated since 5.2.0
 */
function gibLieferadresseAusSession()
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return getOrderHandler()->getShippingAddress();
}

/**
 * Schaut nach ob eine Bestellmenge > Lagersbestand ist und falls dies erlaubt ist, gibt es einen Hinweis.
 *
 * @return array
 * @deprecated since 5.2.0
 */
function pruefeVerfuegbarkeit(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return getOrderHandler()->pruefeVerfuegbarkeit();
}

/**
 * @param string $orderNo
 * @param bool   $sendMail
 * @return Bestellung
 * @deprecated since 5.2.0
 */
function finalisiereBestellung($orderNo = '', bool $sendMail = true): Bestellung
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return getOrderHandler()->finalisiereBestellung($orderNo, $sendMail);
}
