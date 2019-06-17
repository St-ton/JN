{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productlist-productlist-actions'}
    {form action="#" method="post" class="product-actions actions-small d-none d-md-flex" data=["toggle" => "product-actions"]}
        {buttongroup class="actions btn-group-justified d-flex" size="sm" aria=["label" => "..."] role="group"}
            {block name='productlist-productlist-actions-buttons'}
                {if !($Artikel->nIstVater && $Artikel->kVaterArtikel === 0)}
                    {if $Einstellungen.artikeluebersicht.artikeluebersicht_vergleichsliste_anzeigen === 'Y'}
                        {block name='productlist-productlist-actions-comparelist'}
                            {button name="Vergleichsliste" type="submit" class="circle-small compare badge badge-circle"
                                title={lang key='addToCompare' section='productOverview'}
                                data=["toggle"=>"tooltip", "placement"=>"top"]
                            }
                                <span class="far fa-list-alt"></span>
                            {/button}
                        {/block}
                    {/if}
                    {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y' && $Einstellungen.artikeluebersicht.artikeluebersicht_wunschzettel_anzeigen === 'Y'}
                        {block name='productlist-productlist-actions-wishlist'}
                            {button name="Wunschliste" type="submit" class="circle-small wishlist badge badge-circle"
                                title={lang key='addToWishlist' section='productDetails'}
                                data=["toggle"=>"tooltip", "placement"=>"top"]
                            }
                                <span class="far fa-heart"></span>
                            {/button}
                        {/block}
                    {/if}
                {/if}
            {/block}
        {/buttongroup}
        {block name='productlist-productlist-actions-input-hidden'}
            {input type="hidden" name="a" value="{if !empty({$Artikel->kVariKindArtikel})}{$Artikel->kVariKindArtikel}{else}{$Artikel->kArtikel}{/if}"}
        {/block}
    {/form}
{/block}
