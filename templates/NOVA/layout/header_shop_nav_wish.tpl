{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-header-shop-nav-wish'}
    {if !empty($wishlists)}
        {$wlCount = 0}
        {if isset($smarty.session.Wunschliste->CWunschlistePos_arr)}
            {$wlCount = $smarty.session.Wunschliste->CWunschlistePos_arr|count}
        {/if}
        {navitem tag="li"
            aria=['expanded' => 'false']
            data=['toggle' => 'collapse', 'target' => '#nav-wishlist-collapse']
            id='shop-nav-wish'
            class="d-none d-md-flex{if $nSeitenTyp === $smarty.const.PAGE_WUNSCHLISTE} active{/if}"
        }
            <i class="fas fa-heart position-relative">
                <span id="badge-wl-count" class="fa-sup {if $wlCount === 0} d-none{/if}" title="{$wlCount}">
                    {$wlCount}
                </span>
            </i>
        {/navitem}
        {collapse id="nav-wishlist-collapse" tag="div"  data=["parent"=>"#main-nav-wrapper"] class="mt-md-2 py-0 w-100"}
            <div id="wishlist-dropdown-container">
                {block name='layout-header-shop-nav-wish-include-wishlist-dropdown'}
                    {include file='snippets/wishlist_dropdown.tpl'}
                {/block}
            </div>
        {/collapse}
    {/if}
{/block}
