{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}

{if !empty($smarty.session.Wunschliste->CWunschlistePos_arr)}
    {navitem
        id="shop-nav-wish"
        tag="div"
        router-tag="a"
        router-class="link_to_wishlist{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'popup'} popup{/if}"
        href="{get_static_route id='wunschliste.php'}"
        target="{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'blank'}_blank{/if}"
        title="{lang key='goToWishlist'}"
        class="d-none d-md-flex{if $nSeitenTyp === $smarty.const.PAGE_WUNSCHLISTE} active{/if}"
    }
        <span class="fas fa-heart"></span>
        <sup>
            {badge pill=true variant="primary"}{$smarty.session.Wunschliste->CWunschlistePos_arr|count}{/badge}
        </sup>
    {/navitem}
{/if}
