<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Cart\Cart;
use JTL\Cart\CartHelper;
use JTL\Cart\PersistentCart;
use JTL\Catalog\Product\Preise;
use JTL\Checkout\Kupon;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\Form;
use JTL\Helpers\Order;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CartController
 * @package JTL\Router\Controller
 */
class CartController extends PageController
{
    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        parent::init();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';

        Shop::setPageType(\PAGE_WARENKORB);
        $warning         = '';
        $linkHelper      = Shop::Container()->getLinkService();
        $couponCodeValid = true;
        $cart            = Frontend::getCart();
        $link            = $linkHelper->getSpecialPage(\LINKTYP_WARENKORB);
        $valid           = Form::validateToken();
        // Warenkorbaktualisierung?
        if ($valid) {
            CartHelper::applyCartChanges();
        }
        CartHelper::validateCartConfig();
        Order::setUsedBalance();
        if ($valid && isset($_POST['land'], $_POST['plz'])
            && !ShippingMethod::getShippingCosts($_POST['land'], $_POST['plz'], $warning)
        ) {
            $warning = Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages');
        }
        if ($valid) {
            $this->checkCoupons($cart);
            $warning = $this->checkGifts($cart);
        }
        // Kupon nicht mehr verfügbar. Redirect im Bestellabschluss. Fehlerausgabe
        if (isset($_SESSION['checkCouponResult'])) {
            $couponCodeValid = false;
            $couponError     = $_SESSION['checkCouponResult'];
            unset($_SESSION['checkCouponResult']);
            $this->smarty->assign('cKuponfehler', $couponError['ungueltig']);
        }
        if (($msg = $this->checkErrors($cart)) !== null) {
            $warning = $msg;
        }
        $this->canonicalURL = $linkHelper->getStaticRoute('warenkorb.php');
        $uploads            = Upload::gibWarenkorbUploads($cart);
        $maxSize            = Upload::uploadMax();
        // alerts
        if (($quickBuyNote = CartHelper::checkQuickBuy()) !== '') {
            $this->alertService->addInfo($quickBuyNote, 'quickBuyNote');
        }
        if (!empty($_SESSION['Warenkorbhinweise'])) {
            foreach ($_SESSION['Warenkorbhinweise'] as $key => $cartNotice) {
                $this->alertService->addWarning($cartNotice, 'cartNotice' . $key);
            }
            unset($_SESSION['Warenkorbhinweise']);
        }
        if ($warning !== '') {
            $this->alertService->addDanger($warning, 'cartWarning', ['id' => 'msgWarning']);
        }
        if (($orderAmountStock = CartHelper::checkOrderAmountAndStock($this->config)) !== '') {
            $this->alertService->addWarning($orderAmountStock, 'orderAmountStock');
        }

        CartHelper::addVariationPictures($cart);
        $this->smarty->assign('MsgWarning', $warning)
            ->assign('nMaxUploadSize', $maxSize)
            ->assign('cMaxUploadSize', Upload::formatGroesse($maxSize))
            ->assign('oUploadSchema_arr', $uploads)
            ->assign('Link', $link)
            ->assign('laender', ShippingMethod::getPossibleShippingCountries($this->customerGroupID))
            ->assign('KuponMoeglich', Kupon::couponsAvailable())
            ->assign('currentCoupon', Shop::Lang()->get('currentCoupon', 'checkout'))
            ->assign('currentCouponName', (!empty($_SESSION['Kupon']->translationList)
                ? $_SESSION['Kupon']->translationList
                : null))
            ->assign('currentShippingCouponName', (!empty($_SESSION['oVersandfreiKupon']->translationList)
                ? $_SESSION['oVersandfreiKupon']->translationList
                : null))
            ->assign('xselling', CartHelper::getXSelling())
            ->assign('oArtikelGeschenk_arr', CartHelper::getFreeGifts($this->config))
            ->assign('KuponcodeUngueltig', !$couponCodeValid)
            ->assign('Warenkorb', $cart)
            ->assignDeprecated('C_WARENKORBPOS_TYP_ARTIKEL', \C_WARENKORBPOS_TYP_ARTIKEL, '5.0.0')
            ->assignDeprecated('C_WARENKORBPOS_TYP_GRATISGESCHENK', \C_WARENKORBPOS_TYP_GRATISGESCHENK, '5.0.0');

        $this->preRender();

        \executeHook(\HOOK_WARENKORB_PAGE);

        return $this->smarty->getResponse('basket/index.tpl');
    }

    /**
     * @param Cart $cart
     * @return string|null
     */
    protected function checkErrors(Cart $cart): ?string
    {
        if (($res = Request::getInt('fillOut', -1)) < 0) {
            return null;
        }
        $warning = null;
        $mbw     = Frontend::getCustomerGroup()->getAttribute(\KNDGRP_ATTRIBUT_MINDESTBESTELLWERT);
        if ($res === 9 && $mbw > 0 && $cart->gibGesamtsummeWarenOhne([\C_WARENKORBPOS_TYP_GUTSCHEIN], true) < $mbw) {
            $warning = Shop::Lang()->get('minordernotreached', 'checkout')
                . ' ' . Preise::getLocalizedPriceString($mbw);
        } elseif ($res === 8) {
            $warning = Shop::Lang()->get('orderNotPossibleNow', 'checkout');
        } elseif ($res === 3) {
            $warning = Shop::Lang()->get('yourbasketisempty', 'checkout');
        } elseif ($res === 10) {
            $warning = Shop::Lang()->get('missingProducts', 'checkout');
        } elseif ($res === \UPLOAD_ERROR_NEED_UPLOAD) {
            $warning = Shop::Lang()->get('missingFilesUpload', 'checkout');
        }

        return $warning;
    }

    /**
     * @param Cart $cart
     * @return string
     */
    protected function checkGifts(Cart $cart): string
    {
        if (!isset($_POST['gratis_geschenk'], $_POST['gratisgeschenk']) || (int)$_POST['gratis_geschenk'] !== 1) {
            return '';
        }
        $warning = '';
        $giftID  = (int)$_POST['gratisgeschenk'];
        $gift    = $this->db->getSingleObject(
            'SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand, 
                tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
                FROM tartikelattribut
                JOIN tartikel 
                    ON tartikel.kArtikel = tartikelattribut.kArtikel
                WHERE tartikelattribut.kArtikel = :gid
                    AND tartikelattribut.cName = :atr
                    AND CAST(tartikelattribut.cWert AS DECIMAL) <= :sum',
            [
                'gid' => $giftID,
                'atr' => \FKT_ATTRIBUT_GRATISGESCHENK,
                'sum' => $cart->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true)
            ]
        );
        if ($gift === null || $gift->kArtikel <= 0) {
            return $warning;
        }
        if ($gift->fLagerbestand <= 0 && $gift->cLagerKleinerNull === 'N' && $gift->cLagerBeachten === 'Y') {
            $warning = Shop::Lang()->get('freegiftsNostock', 'errorMessages');
        } else {
            \executeHook(\HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
            $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_GRATISGESCHENK)
                ->fuegeEin($giftID, 1, [], \C_WARENKORBPOS_TYP_GRATISGESCHENK);
            PersistentCart::getInstance(Frontend::getCustomer()->getID())
                ->check($giftID, 1, [], '', 0, \C_WARENKORBPOS_TYP_GRATISGESCHENK);
        }

        return $warning;
    }

    /**
     * @param Cart $cart
     * @return void
     */
    protected function checkCoupons(Cart $cart): void
    {
        if (Request::postVar('Kuponcode', '') === '' || $cart->gibAnzahlArtikelExt([\C_WARENKORBPOS_TYP_ARTIKEL]) < 1) {
            // Kupon darf nicht im leeren Warenkorb eingelöst werden
            return;
        }
        $coupon            = new Kupon();
        $coupon            = $coupon->getByCode($_POST['Kuponcode']);
        $invalidCouponCode = 11;
        if ($coupon !== false && $coupon->kKupon > 0) {
            $couponError       = $coupon->check();
            $check             = Form::hasNoMissingData($couponError);
            $invalidCouponCode = 0;
            \executeHook(\HOOK_WARENKORB_PAGE_KUPONANNEHMEN_PLAUSI, [
                'error'        => &$couponError,
                'nReturnValue' => &$check
            ]);
            if ($check) {
                if ($coupon->cKuponTyp === Kupon::TYPE_STANDARD) {
                    $coupon->accept();
                    \executeHook(\HOOK_WARENKORB_PAGE_KUPONANNEHMEN);
                } elseif (!empty($coupon->kKupon) && $coupon->cKuponTyp === Kupon::TYPE_SHIPPING) {
                    $cart->loescheSpezialPos(\C_WARENKORBPOS_TYP_KUPON);
                    $_SESSION['oVersandfreiKupon'] = $coupon;
                    $this->alertService->addSuccess(
                        Shop::Lang()->get('couponSucc1') . ' '
                        . \trim(\str_replace(';', ', ', $coupon->cLieferlaender), ', '),
                        'shippingFreeSuccess'
                    );
                }
            }
        }

        $this->smarty->assign(
            'invalidCouponCode',
            Kupon::mapCouponErrorMessage($couponError['ungueltig'] ?? $invalidCouponCode)
        );
    }
}
