{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{navitem
    id="shop-nav-wish"
    tag="div"
    router-tag="a"
    router-class="link_to_wishlist{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'popup'} popup{/if}"
    href="{get_static_route id='wunschliste.php'}"
    target="{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'blank'}_blank{/if}"
    title="{lang key='goToWishlist'}"
    class="d-none d-md-flex mr-2{if $nSeitenTyp === $smarty.const.PAGE_WUNSCHLISTE && !empty($smarty.session.Wunschliste->CWunschlistePos_arr)} active{/if}"
}
    {if !empty($smarty.session.Wunschliste->CWunschlistePos_arr)}
        <span class="fas fa-heart"></span>
        <sup>
            {badge pill=true
                variant="primary"
                class="{if empty($smarty.session.Wunschliste->CWunschlistePos_arr)} d-none{/if}"
                id="badge-wl-count"
            }
                {$smarty.session.Wunschliste->CWunschlistePos_arr|count}
            {/badge}
        </sup>
{/navitem}
{collapse id="nav-wishlist-collapse" tag="div"  data=["parent"=>"#evo-main-nav-wrapper"] class="mt-md-2"}
    {dropdownitem tag="div" right=true id="wishlist-dropdown-container"}
        {include file='snippets/wishlist_dropdown.tpl'}
    {/dropdownitem}
{/collapse}