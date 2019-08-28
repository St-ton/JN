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
        <div class="wishlist-icon-dropdown">
            {navitem tag="div"
                aria=['expanded' => 'false']
                data=['toggle' => 'collapse', 'target' => '#nav-wishlist-collapse']
                id='shop-nav-wish'
                class="d-none d-md-flex{if $nSeitenTyp === $smarty.const.PAGE_WUNSCHLISTE} active{/if}"
            }
                <i class='fas fa-heart'></i>
                <sup>
                    {badge pill=true
                        variant="primary"
                        class="{if $wlCount === 0} d-none{/if}"
                        id="badge-wl-count"
                    }
                    {$wlCount}
                    {/badge}
                </sup>
            {/navitem}
            {collapse id="nav-wishlist-collapse" tag="div"  data=["parent"=>"#main-nav-wrapper"] class="mt-md-2 w-100"}
                <div id="wishlist-dropdown-container" class="p-3">
                    {block name='layout-header-shop-nav-wish-include-wishlist-dropdown'}
                        {include file='snippets/wishlist_dropdown.tpl'}
                    {/block}
                </div>
            {/collapse}
        </div>
    {/if}
{/block}
