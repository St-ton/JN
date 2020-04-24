{block name='productlist-item-box'}
    {if $Einstellungen.template.productlist.variation_select_productlist === 'N' || $Einstellungen.template.productlist.hover_productlist !== 'Y'}
        {assign var=hasOnlyListableVariations value=0}
    {else}
        {hasOnlyListableVariations artikel=$Artikel maxVariationCount=$Einstellungen.template.productlist.variation_select_productlist maxWerteCount=$Einstellungen.template.productlist.variation_max_werte_productlist assign='hasOnlyListableVariations'}
    {/if}
    <div id="result-wrapper_buy_form_{$Artikel->kArtikel}" data-wrapper="true" class="productbox productbox-column {if $Einstellungen.template.productlist.hover_productlist === 'Y'} productbox-hover{/if}{if isset($class)} {$class}{/if}">
        <div class="productbox-inner">
            {row}
                {col cols=12}
                    <div class="productbox-image">
                        {if isset($Artikel->Bilder[0]->cAltAttribut)}
                            {assign var=alt value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html'}
                        {else}
                            {assign var=alt value=$Artikel->cName}
                        {/if}
                        {block name='productlist-item-box-image'}
                            {counter assign=imgcounter print=0}
                            {if isset($Artikel->oSuchspecialBild)}
                                {block name='productlist-item-box-include-ribbon'}
                                    {include file='snippets/ribbon.tpl'}
                                {/block}
                            {/if}
                            <div class="productbox-images {if !$isMobile && !empty($Artikel->Bilder[1])}toggle-image-js{/if} list-gallery">
                                {link href=$Artikel->cURLFull}
                                    {block name="productlist-item-list-image"}
                                        {strip}
                                            {$image = $Artikel->Bilder[0]}
                                            <div class="productbox-image square square-image first-wrapper">
                                                <div class="inner">
                                            {image alt=$alt fluid=true webp=true lazy=true
                                                src="{$image->cURLKlein}"
                                                srcset="{$image->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                         {$image->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                         {$image->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w"
                                                sizes="auto"
                                                data=["id"  => $imgcounter]
                                                class="{if !$isMobile && !empty($Artikel->Bilder[1])} first{/if}"
                                                fluid=true
                                            }</div>
                                            </div>
                                            {if !$isMobile && !empty($Artikel->Bilder[1])}
                                                <div class="productbox-image square square-image d-none second-wrapper">
                                                    <div class="inner">
                                                    {$image = $Artikel->Bilder[1]}
                                                    {image alt=$alt fluid=true webp=true lazy=true
                                                        src="{$image->cURLKlein}"
                                                        srcset="{$image->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                                         {$image->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                                         {$image->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w"
                                                        sizes="auto"
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

                        {if $smarty.session.Kundengruppe->mayViewPrices()
                            && isset($Artikel->SieSparenX)
                            && $Artikel->SieSparenX->anzeigen == 1
                            && $Artikel->SieSparenX->nProzent > 0
                            && !$NettoPreise
                            && $Artikel->taxData['tax'] > 0
                        }
                            {block name='productlist-item-badge-yousave'}
                                <div class="productbox-sale-percentage">
                                    <div class="ribbon ribbon-7 productbox-ribbon">{$Artikel->SieSparenX->nProzent}%</div>
                                </div>
                            {/block}
                        {/if}

                        {block name='productlist-item-box-include-productlist-actions'}
                            <div class="productbox-quick-actions productbox-onhover d-none d-md-flex">
                                {include file='productlist/productlist_actions.tpl'}
                            </div>
                        {/block}
                    </div>
                {/col}
                {col cols=12}
                    {block name='productlist-item-box-caption'}
                        {block name='productlist-item-box-caption-short-desc'}
                            <div class="productbox-title mt-2" itemprop="name">
                                {link href=$Artikel->cURLFull class="text-clamp-2"}
                                    {$Artikel->cKurzbezeichnung}
                                {/link}
                            </div>
                        {/block}
                        {block name='productlist-item-box-meta'}
                            {if $Artikel->cName !== $Artikel->cKurzbezeichnung}<meta itemprop="alternateName" content="{$Artikel->cName}">{/if}
                            <meta itemprop="url" content="{$Artikel->cURLFull}">
                        {/block}
                        {block name='productlist-index-include-rating'}
                            {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->fDurchschnittsBewertung > 0}
                                {include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}<br>
                            {/if}
                        {/block}
                        {block name='productlist-index-include-price'}
                            <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                                <link itemprop="businessFunction" href="http://purl.org/goodrelations/v1#Sell" />
                                {include file='productdetails/price.tpl' Artikel=$Artikel tplscope=$tplscope}
                            </div>
                        {/block}
                    {/block}
                {/col}
            {/row}
        </div>
    </div>
{/block}
