{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var='isOnWishList' value=false}
{assign var='wishlistPos' value=0}
{if isset($smarty.session.Wunschliste)}
    {foreach $smarty.session.Wunschliste->CWunschlistePos_arr as $product}
        {if $product->kArtikel === $Artikel->kArtikel || $product->kArtikel === $Artikel->kVariKindArtikel}
            {$isOnWishList=true}
            {$wishlistPos=$product->kWunschlistePos}
            {break}
        {/if}
    {/foreach}
{/if}
{block name='snippets-wishlist-button-main'}
    {if $buttonAndText|default:false}
        {block name='snippets-wishlist-button-button-text'}
            {button name="Wunschliste"
                type="submit"
                variant="link"
                class="{$classes|default:''} mr-3 pr-3 p-0 d-block d-lg-inline-block  border-lg-right wishlist action-tip-animation-b {if $isOnWishList}on-list{/if}"
                aria=["label" => {lang key='addToWishlist' section='productDetails'}]
                data=["wl-pos" => $wishlistPos, "product-id-wl" => "{if isset($Artikel->kVariKindArtikel)}{$Artikel->kVariKindArtikel}{else}{$Artikel->kArtikel}{/if}"]}
                <span class="d-flex align-items-baseline">
                    <span class="{if $isOnWishList}fas{else}far{/if} fa-heart mr-2 wishlist-icon"></span>
                    <span class="text-decoration-underline">{lang key='onWishlist'}</span>
                </span>
            {/button}
        {/block}
    {else}
        {block name='snippets-wishlist-button-button'}
            {button name="Wunschliste"
                type="submit"
                class="{$classes|default:''} wishlist badge badge-circle-1 action-tip-animation-b {if $isOnWishList}on-list{/if}"
                aria=["label" => {lang key='addToWishlist' section='productDetails'}]
                data=["wl-pos" => $wishlistPos, "product-id-wl" => "{if isset($Artikel->kVariKindArtikel)}{$Artikel->kVariKindArtikel}{else}{$Artikel->kArtikel}{/if}"]
            }
                <span class="far fa-heart"></span>
            {/button}
        {/block}
    {/if}
    {block name='snippets-wishlist-button-hidden'}
        {input type="hidden" name="wlPos" value=$wishlistPos}
    {/block}
{/block}
