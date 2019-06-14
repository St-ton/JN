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
    {button name="Wunschliste"
        type="submit"
        class="{$classes|default:''} wishlist badge badge-circle-1 action-tip-animation-b {if $isOnWishList}on-list{/if}"
        title="{lang key='addToWishlist' section='productDetails'}"
        data=["toggle"=>"tooltip", "placement"=>"top", "wl-pos" => $wishlistPos, "product-id-wl" => $Artikel->kArtikel]
    }
        <span class="far fa-heart"></span>
    {/button}
    <div class="action-tip-animation">{lang key='onWishlist'}</div>
    <div class="action-tip-animation">{lang key='notOnWishlist'}</div>
    {input type="hidden" name="wlPos" value=$wishlistPos}
{/block}
