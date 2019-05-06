<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mapper;

/**
 * Class PageTypeToPageNiceName
 * @package JTL\Mapper
 */
class PageTypeToPageNiceName
{
    /**
     * @param int $type
     * @return string
     */
    public function mapPageTypeToPageNiceName(int $type): string
    {
        switch ($type) {
            case \PAGE_STARTSEITE:
                return __('pageHome');
            case \PAGE_VERSAND:
                return __('pageShipping');
            case \PAGE_WRB:
                return __('pageWRB');
            case \PAGE_AGB:
                return __('pageAGB');
            case \PAGE_LIVESUCHE:
                return __('pageLiveSearch');
            case \PAGE_DATENSCHUTZ:
                return __('pageDataProtection');
            case \PAGE_HERSTELLER:
                return __('pageManufacturer');
            case \PAGE_SITEMAP:
                return __('pageSitemap');
            case \PAGE_GRATISGESCHENK:
                return __('pageGifts');
            case \PAGE_AUSWAHLASSISTENT:
                return __('pageSelectionAssitant');
            case \PAGE_EIGENE:
                return __('pageCustom');
            case \PAGE_MEINKONTO:
                return __('pageAccount');
            case \PAGE_LOGIN:
                return __('pageLogin');
            case \PAGE_REGISTRIERUNG:
                return __('pageRegister');
            case \PAGE_WARENKORB:
                return __('pageCart');
            case \PAGE_PASSWORTVERGESSEN:
                return __('pageForgotPassword');
            case \PAGE_KONTAKT:
                return __('pageContact');
            case \PAGE_NEWSLETTER:
                return __('pageNewsletter');
            case \PAGE_NEWSLETTERARCHIV:
                return __('pageNewsletterArchive');
            case \PAGE_NEWS:
                return __('pageNews');
            case \PAGE_NEWSMONAT:
                return __('pageNewsMonth');
            case \PAGE_NEWSKATEGORIE:
                return __('pageNewsCategory');
            case \PAGE_NEWSDETAIL:
                return __('pageNewsDetail');
            case \PAGE_UMFRAGE:
                return __('pagePoll');
            case \PAGE_PLUGIN:
                return __('pagePlugin');
            case \PAGE_404:
                return __('page404');
            case \PAGE_BESTELLVORGANG:
                return __('pageOrderProcess');
            case \PAGE_BESTELLABSCHLUSS:
                return __('pageOrderFinalize');
            case \PAGE_WUNSCHLISTE:
                return __('pageWishList');
            case \PAGE_VERGLEICHSLISTE:
                return __('pageCompareList');
            case \PAGE_ARTIKEL:
                return __('pageProduct');
            case \PAGE_ARTIKELLISTE:
                return __('pageProductList');
            case \PAGE_BEWERTUNG:
                return __('pageRating');
            case \PAGE_WARTUNG:
                return __('pageMaintenance');
            case \PAGE_BESTELLSTATUS:
                return __('pageOrderStatus');
            case \PAGE_UNBEKANNT:
                return __('pageUnknown');
            default:
                return '';
        }
    }
}
