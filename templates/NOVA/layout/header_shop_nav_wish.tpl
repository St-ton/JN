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
        <li id='shop-nav-wish'
            class="nav-item dropdown d-none d-md-flex{if $nSeitenTyp === $smarty.const.PAGE_WUNSCHLISTE} active{/if}">
            {link class='nav-link' aria=['expanded' => 'false'] data=['toggle' => 'dropdown']}
                <i class="fas fa-heart position-relative">
                    <span id="badge-wl-count" class="fa-sup {if $wlCount === 0} d-none{/if}" title="{$wlCount}">
                        {$wlCount}
                    </span>
                </i>
            {/link}
            <div id="nav-wishlist-collapse" class="dropdown-menu dropdown-menu-right lg-min-w-lg">
                <div id="wishlist-dropdown-container">
                    {block name='layout-header-shop-nav-wish-include-wishlist-dropdown'}
                        {include file='snippets/wishlist_dropdown.tpl'}
                    {/block}
                </div>
            </div>
        </li>
    {/if}
{/block}
