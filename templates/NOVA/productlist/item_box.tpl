{block name='productlist-item-box'}
    {if $Einstellungen.template.productlist.variation_select_productlist_gallery === 'N'}
        {assign var=hasOnlyListableVariations value=0}
        {$showVariationCollapse = false}
    {else}
        {hasOnlyListableVariations artikel=$Artikel
            maxVariationCount=$Einstellungen.template.productlist.variation_select_productlist_gallery
            maxWerteCount=$Einstellungen.template.productlist.variation_max_werte_productlist_gallery
            assign='hasOnlyListableVariations'}
        {$showVariationCollapse = ($hasOnlyListableVariations > 0 && $Artikel->nIstVater && !$Artikel->bHasKonfig && $Artikel->kEigenschaftKombi === 0 &&
        empty($Artikel->FunktionsAttribute[\FKT_ATTRIBUT_NO_GAL_VAR_PREVIEW]) &&
    $Artikel->nVariationOhneFreifeldAnzahl <= 2 &&
    ($Artikel->Variationen[0]->cTyp === 'IMGSWATCHES' || $Artikel->Variationen[0]->cTyp === 'TEXTSWATCHES' || $Artikel->Variationen[0]->cTyp === 'SELECTBOX') &&
    (!isset($Artikel->Variationen[1]) || ($Artikel->Variationen[1]->cTyp === 'IMGSWATCHES' || $Artikel->Variationen[1]->cTyp === 'TEXTSWATCHES' || $Artikel->Variationen[1]->cTyp === 'SELECTBOX')))}
    {/if}
    <div id="{$idPrefix|default:''}result-wrapper_buy_form_{$Artikel->kArtikel}" data-wrapper="true"
         class="productbox productbox-column {if !empty($hasOnlyListableVariations) && empty($Artikel->FunktionsAttribute[\FKT_ATTRIBUT_NO_GAL_VAR_PREVIEW])}productbox-show-variations {/if} productbox-hover{if isset($class)} {$class}{/if} {if $showVariationCollapse}show-variation-collapse{/if}">
        {block name='productlist-item-box-include-productlist-actions'}
            <div class="productbox-quick-actions productbox-onhover d-none d-md-flex">
                {include file='productlist/productlist_actions.tpl'}
            </div>
        {/block}

        {form id="{$idPrefix|default:''}buy_form_{$Artikel->kArtikel}"
        action=$ShopURL class="form form-basket jtl-validate"
        data=["toggle" => "basket-add"]}
        {input type="hidden" name="a" value="{if !empty({$Artikel->kVariKindArtikel})}{$Artikel->kVariKindArtikel}{else}{$Artikel->kArtikel}{/if}"}
        <div class="productbox-inner">
            {row}
                {col cols=12}
                    <div class="productbox-image" data-target="#variations-collapse-{$Artikel->kArtikel}">
                        {if isset($Artikel->Bilder[0]->cAltAttribut)}
                            {assign var=alt value=$Artikel->Bilder[0]->cAltAttribut}
                        {else}
                            {assign var=alt value=$Artikel->cName}
                        {/if}
                        {block name='productlist-item-box-image'}
                            {counter assign=imgcounter print=0}

                            {block name='productlist-item-box-include-ribbon'}
                                {include file='snippets/ribbon.tpl'}
                            {/block}
                            <div class="productbox-images list-gallery">
                                {link href=$Artikel->cURLFull}
                                    {block name="productlist-item-list-image"}
                                        {strip}
                                            {$image = $Artikel->Bilder[0]}
                                            <div class="productbox-image square square-image first-wrapper">
                                                <div class="inner">
                                                    {image alt=$alt|truncate:60 fluid=true webp=true lazy=true
                                                        src="{$image->cURLKlein}"
                                                        srcset="
                                                            {$image->cURLMini} {$image->imageSizes->xs->size->width}w,
                                                            {$image->cURLKlein} {$image->imageSizes->sm->size->width}w,
                                                            {$image->cURLNormal} {$image->imageSizes->md->size->width}w"
                                                        sizes = '(min-width: 1300px) 25vw, (min-width: 992px) 34vw, 50vw'
                                                        data=["id"  => $imgcounter]
                                                        class="{if !$isMobile && !empty($Artikel->Bilder[1])} first{/if}"
                                                        fluid=true
                                                    }
                                                </div>
                                            </div>
                                            {if !$isMobile && !empty($Artikel->Bilder[1])}
                                                <div class="productbox-image square square-image second-wrapper">
                                                    <div class="inner">
                                                    {$image = $Artikel->Bilder[1]}
                                                    {if isset($image->cAltAttribut)}
                                                        {$alt=$image->cAltAttribut}
                                                    {else}
                                                        {$alt=$Artikel->cName}
                                                    {/if}
                                                    {image alt=$alt|truncate:60 fluid=true webp=true lazy=true
                                                        src="{$image->cURLKlein}"
                                                        srcset="
                                                            {$image->cURLMini} {$image->imageSizes->xs->size->width}w,
                                                            {$image->cURLKlein} {$image->imageSizes->sm->size->width}w,
                                                            {$image->cURLNormal} {$image->imageSizes->md->size->width}w"
                                                        sizes = '(min-width: 1300px) 25vw, (min-width: 992px) 34vw, 50vw'
                                                        data=["id"  => $imgcounter|cat:"_2nd"]
                                                        class='second'
                                                    }
                                                    </div>
                                                </div>
                                            {/if}
                                        {/strip}
                                    {/block}
                                {/link}
                                {if !empty($Artikel->Bilder[0]->cURLNormal)}
                                    <meta itemprop="image" content="{$Artikel->Bilder[0]->cURLNormal}">
                                {/if}
                            </div>
                        {/block}
                    </div>
                {/col}
            {if $showVariationCollapse}
                    {col cols=12 class='productbox-variations'}
                    {block name='productlist-item-box-form-variations'}
                        <div class="productbox-onhover collapse" id="variations-collapse-{$Artikel->kArtikel}">
                            {block name='productlist-item-box-form-include-variation'}
                                {include file='productlist/variation_gallery.tpl'
                                simple=$Artikel->isSimpleVariation showMatrix=false
                                smallView=true ohneFreifeld=($hasOnlyListableVariations == 2)}
                            {/block}
                        </div>
                    {/block}
                    {/col}
                {/if}
                {col cols=12}
                    {block name='productlist-item-box-caption'}
                        {block name='productlist-item-box-caption-short-desc'}
                            <div class="productbox-title" itemprop="name">
                                {link href=$Artikel->cURLFull class="text-clamp-2"}
                                    {$Artikel->cKurzbezeichnung}
                                {/link}
                            </div>
                        {/block}
                        {block name='productlist-item-box-meta'}
                            {if $Artikel->cName !== $Artikel->cKurzbezeichnung}
                                <meta itemprop="alternateName" content="{$Artikel->cName}">
                            {/if}
                            <meta itemprop="url" content="{$Artikel->cURLFull}">
                        {/block}
                        {block name='productlist-index-include-rating'}
                            {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->fDurchschnittsBewertung > 0}
                                {include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung
                                    link=$Artikel->cURLFull}
                            {/if}
                        {/block}
                        {block name='productlist-index-include-price'}
                            <div itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                                <link itemprop="businessFunction" href="http://purl.org/goodrelations/v1#Sell" />
                                {include file='productdetails/price.tpl' Artikel=$Artikel tplscope=$tplscope}
                            </div>
                        {/block}
                    {/block}
                {/col}
            {/row}
        </div>
        {/form}
    </div>
{/block}
