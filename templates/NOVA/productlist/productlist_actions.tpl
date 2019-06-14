{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var='isOnCompareList' value=false}
{if isset($smarty.session.Vergleichsliste)}
    {foreach $smarty.session.Vergleichsliste->oArtikel_arr as $product}
        {if $product->kArtikel === $Artikel->kArtikel}
            {assign var='isOnCompareList' value=true}
            {break}
        {/if}
    {/foreach}
{/if}
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
{block name='productlist-productlist-actions'}
    {form action="#" method="post" class="product-actions actions-small d-none d-md-flex" data=["toggle" => "product-actions"]}
        {buttongroup class="actions btn-group-justified d-flex" size="sm" aria=["label" => "..."] role="group"}
            {block name='productlist-productlist-actions-buttons'}
                {if !($Artikel->nIstVater && $Artikel->kVaterArtikel === 0)}
                    {if $Einstellungen.artikeluebersicht.artikeluebersicht_vergleichsliste_anzeigen === 'Y'}
                        {block name='productlist-productlist-actions-comparelist'}
                            {button name="Vergleichsliste"
                                type="submit"
                                class="circle-small compare badge badge-circle-1 action-tip-animation-b {if $isOnCompareList}on-list{/if}"
                                title={lang key='addToCompare' section='productOverview'}
                                data=["toggle"=>"tooltip", "placement"=>"top", "product-id-cl" => $Artikel->kArtikel]
                            }
                                <span class="far fa-list-alt"></span>
                            {/button}
                            <div class="action-tip-animation">Auf die Vergleichsliste!</div>
                            <div class="action-tip-animation">Von Vergleichsliste entfernt!</div>
                        {/block}
                    {/if}
                    {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y' && $Einstellungen.artikeluebersicht.artikeluebersicht_wunschzettel_anzeigen === 'Y'}
                        {block name='productlist-productlist-actions-wishlist'}
                            {button name="Wunschliste"
                                type="submit"
                                class="circle-small wishlist badge badge-circle-1 action-tip-animation-b {if $isOnWishList}on-list{/if}"
                                title={lang key='addToWishlist' section='productDetails'}
                                data=["toggle"=>"tooltip", "placement"=>"top", "wl-pos" => $wishlistPos, "product-id-wl" => $Artikel->kArtikel]
                            }
                                <span class="far fa-heart"></span>
                            {/button}
                            <div class="action-tip-animation">Auf den Wunschzettel!</div>
                            <div class="action-tip-animation">Von Wunschzettel entfernt!</div>
                            {input type="hidden" name="wlPos" value=$wishlistPos}
                        {/block}
                    {/if}
                {/if}
                {if $Einstellungen.template.productlist.quickview_productlist === 'Y' && !$Artikel->bHasKonfig}
                    {block name='productlist-productlist-actions-quickview'}
                        {button name="quickview" class="circle-small quickview badge badge-circle"
                            title="{lang key='downloadPreview' section='productDownloads'} {$Artikel->cName}"
                            data=["toggle"=>"tooltip", "placement"=>"top", "src"=>"{$Artikel->cURLFull}", "target"=>"buy_form_{$Artikel->kArtikel}"]
                        }
                            <span class="far fa-eye"></span>
                        {/button}
                    {/block}
                {/if}
            {/block}
        {/buttongroup}
        {block name='productlist-productlist-actions-input-hidden'}
            {input type="hidden" name="a" value="{if !empty({$Artikel->kVariKindArtikel})}{$Artikel->kVariKindArtikel}{else}{$Artikel->kArtikel}{/if}"}
        {/block}
    {/form}
{/block}
