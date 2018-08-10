<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Mapper;

/**
 * Class PageTypeToPageName
 * @package Mapper
 */
class PageTypeToPageName
{
    /**
     * @param int $type
     * @return string
     */
    public function map(int $type): string
    {
        switch ($type) {
            case \PAGE_STARTSEITE:
            case \PAGE_VERSAND:
            case \PAGE_WRB:
            case \PAGE_AGB:
            case \PAGE_TAGGING:
            case \PAGE_LIVESUCHE:
            case \PAGE_DATENSCHUTZ:
            case \PAGE_HERSTELLER:
            case \PAGE_SITEMAP:
            case \PAGE_GRATISGESCHENK:
            case \PAGE_AUSWAHLASSISTENT:
            case \PAGE_EIGENE:
                return 'SEITE';
            case \PAGE_MEINKONTO:
            case \PAGE_LOGIN:
                return 'MEIN KONTO';
            case \PAGE_REGISTRIERUNG:
                return 'REGISTRIEREN';
            case \PAGE_WARENKORB:
                return 'WARENKORB';
            case \PAGE_PASSWORTVERGESSEN:
                return 'PASSWORT VERGESSEN';
            case \PAGE_KONTAKT:
                return 'KONTAKT';
            case \PAGE_NEWSLETTER:
            case \PAGE_NEWSLETTERARCHIV:
                return 'NEWSLETTER';
            case \PAGE_NEWS:
            case \PAGE_NEWSARCHIV:
                return 'NEWS';
            case \PAGE_NEWSMONAT:
                return 'NEWSMONAT';
            case \PAGE_NEWSKATEGORIE:
                return 'NEWSKATEGORIE';
            case \PAGE_NEWSDETAIL:
                return 'NEWSDETAIL';
            case \PAGE_UMFRAGE:
                return 'UMFRAGE';
            case \PAGE_PLUGIN:
                return 'PLUGIN';
            case \PAGE_404:
                return '404';
            case \PAGE_BESTELLVORGANG:
            case \PAGE_BESTELLABSCHLUSS:
                return 'BESTELLVORGANG';
            case \PAGE_WUNSCHLISTE:
                return 'WUNSCHLISTE';
            case \PAGE_VERGLEICHSLISTE:
                return 'VERGLEICHSLISTE';
            case \PAGE_ARTIKEL:
                return 'ARTIKEL';
            case \PAGE_ARTIKELLISTE:
                return 'ARTIKEL';
            default;
                return '';
        }
    }
}
