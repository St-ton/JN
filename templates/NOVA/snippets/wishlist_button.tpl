{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var='isOnWishList' value=false}
{assign var='wishlistPos' value=0}
{if isset($smarty.session.Wunschliste)}
    {foreach $smarty.session.Wunschliste->CWunschlistePos_arr as $product}
        {if $product->kArtikel === $Artikel->kArtikel}
            {$isOnWishList=true}
            {$wishlistPos=$product->kWunschlistePos}
            {break}
        {/if}
    {/foreach}
{/if}
{block name='snippets-wishlist-button-main'}
    {if $buttonAndText|default:false}
        {button name="Wunschliste"
            type="submit"
            variant="link"
            class="{$classes|default:''} mr-3 pr-3 p-0 d-block d-lg-inline-block  border-lg-right wishlist action-tip-animation-b {if $isOnWishList}on-list{/if}"
            aria=["label" => {lang key='addToWishlist' section='productDetails'}]
            data=["wl-pos" => $wishlistPos, "product-id-wl" => $Artikel->kArtikel]}
            <span class="d-flex align-items-baseline">
                <span class="{if $isOnWishList}fas{else}far{/if} fa-heart mr-2 wishlist-icon"></span>
                <span class="text-decoration-underline">{lang key='onWishlist'}</span>
            </span>
        {/button}
    {else}
        {button name="Wunschliste"
            type="submit"
            class="{$classes|default:''} wishlist badge badge-circle-1 action-tip-animation-b {if $isOnWishList}on-list{/if}"
            aria=["label" => {lang key='addToWishlist' section='productDetails'}]
            data=["wl-pos" => $wishlistPos, "product-id-wl" => $Artikel->kArtikel]
        }
            <span class="far fa-heart"></span>
        {/button}
    {/if}
    {input type="hidden" name="wlPos" value=$wishlistPos}
{/block}
