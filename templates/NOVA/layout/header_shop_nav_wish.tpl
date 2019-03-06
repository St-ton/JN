{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($wishlists)}
<div class="wishlist-icon-dropdown">
    {navitem tag="div"
        aria=['expanded' => 'false']
        data=['toggle' => 'collapse', 'target' => '#nav-wishlist-collapse']
        id='shop-nav-wish'
        class="btn-link d-none d-md-flex{if $nSeitenTyp === $smarty.const.PAGE_WUNSCHLISTE} active{/if}"
    }

            <i class='fas fa-heart'></i>
            {badge pill=true
                variant="primary"
                class="{if empty($smarty.session.Wunschliste->CWunschlistePos_arr)} d-none{/if}"
                id="badge-wl-count"
            }
                {$smarty.session.Wunschliste->CWunschlistePos_arr|count}
            {/badge}

    {/navitem}
    {collapse id="nav-wishlist-collapse" tag="div"  data=["parent"=>"#evo-main-nav-wrapper"] class="mt-md-2"}
        {dropdownitem tag="div" right=true id="wishlist-dropdown-container"}
            {include file='snippets/wishlist_dropdown.tpl'}
        {/dropdownitem}
    {/collapse}
</div>
{/if}