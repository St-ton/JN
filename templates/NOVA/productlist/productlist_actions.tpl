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
                        {block name='productlist-productlist-actions-include-comparelist-button'}
                            {include file='snippets/comparelist_button.tpl' classes='circle-small'}
                        {/block}
                    {/if}
                    {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y' && $Einstellungen.artikeluebersicht.artikeluebersicht_wunschzettel_anzeigen === 'Y'}
                        {block name='productlist-productlist-actions-include-wishlist-button'}
                            {include file='snippets/wishlist_button.tpl' classes='circle-small'}
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
