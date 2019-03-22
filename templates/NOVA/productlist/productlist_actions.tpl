{form action="" method="post" class="product-actions actions-small d-none d-md-flex" data=["toggle" => "product-actions"]}
    {buttongroup class="actions btn-group-justified d-flex" size="sm" aria=["label" => "..."] role="group"}
        {block name='product-actions'}
            {if !($Artikel->nIstVater && $Artikel->kVaterArtikel === 0)}
                {if $Einstellungen.artikeluebersicht.artikeluebersicht_vergleichsliste_anzeigen === 'Y'}
                    {button name="Vergleichsliste" type="submit" class="circle-small compare badge badge-circle"
                        title={lang key='addToCompare' section='productOverview'}
                        data=["toggle"=>"tooltip", "placement"=>"top"]
                    }
                        <span class="far fa-list-alt"></span>
                    {/button}
                {/if}
                {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y' && $Einstellungen.artikeluebersicht.artikeluebersicht_wunschzettel_anzeigen === 'Y'}
                    {button name="Wunschliste" type="submit" class="circle-small wishlist badge badge-circle"
                        title={lang key='addToWishlist' section='productDetails'}
                        data=["toggle"=>"tooltip", "placement"=>"top"]
                    }
                        <span class="far fa-heart"></span>
                    {/button}
                {/if}
            {/if}
            {if $Einstellungen.template.productlist.quickview_productlist === 'Y' && !$Artikel->bHasKonfig}
                {button name="quickview" class="circle-small quickview badge badge-circle"
                    title="{lang key='downloadPreview' section='productDownloads'} {$Artikel->cName}"
                    data=["toggle"=>"tooltip", "placement"=>"top", "src"=>"{$Artikel->cURLFull}", "target"=>"buy_form_{$Artikel->kArtikel}"]
                }
                    <span class="far fa-eye"></span>
                {/button}
            {/if}
        {/block}
    {/buttongroup}
    {input type="hidden" name="a" value="{if !empty({$Artikel->kVariKindArtikel})}{$Artikel->kVariKindArtikel}{else}{$Artikel->kArtikel}{/if}"}
{/form}