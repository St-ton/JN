{block name='productlist-item-slider'}
    {block name='productlist-item-slider-link'}
        {link href=$Artikel->cURLFull}
            <div class="item-slider productbox-image square square-image">
                <div class="inner">
                    {if isset($Artikel->Bilder[0]->cAltAttribut)}
                        {assign var=alt value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html'}
                    {else}
                        {assign var=alt value=$Artikel->cName}
                    {/if}
                    {block name='productlist-item-slider-image'}
                        {image fluid=true webp=true lazy=true
                            alt=$Artikel->cName
                            src=$Artikel->Bilder[0]->cURLKlein
                            srcset="{$Artikel->Bilder[0]->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                {$Artikel->Bilder[0]->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                {$Artikel->Bilder[0]->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w"
                            sizes="auto"
                            class="product-image"}
                    {/block}
                    {if $tplscope !== 'box'}
                        <meta itemprop="image" content="{$Artikel->Bilder[0]->cURLNormal}">
                        <meta itemprop="url" content="{$Artikel->cURLFull}">
                    {/if}
                </div>
            </div>
        {/link}
    {/block}
    {block name='productlist-item-slider-caption'}
        {block name='productlist-item-slider-caption-short-desc'}
            {link href=$Artikel->cURLFull}
                <span class="text-clamp-2">
                    {if isset($showPartsList) && $showPartsList === true && isset($Artikel->fAnzahl_stueckliste)}
                        {block name='productlist-item-slider-caption-bundle'}
                            {$Artikel->fAnzahl_stueckliste}x
                        {/block}
                    {/if}
                    <span {if $tplscope !== 'box'}itemprop="name"{/if}>{$Artikel->cKurzbezeichnung}</span>
                </span>
            {/link}
        {/block}
        {if $tplscope === 'box'}
            {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->fDurchschnittsBewertung > 0}
                {block name='productlist-item-slider-include-rating'}
                    <small>{include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung link=$Artikel->cURLFull}</small>
                {/block}
            {/if}
        {/if}
        {block name='productlist-item-slider-include-price'}
            <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                {include file='productdetails/price.tpl' Artikel=$Artikel tplscope=$tplscope}
            </div>
        {/block}
    {/block}
{/block}
