{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-last-seen'}
    {lang key='lastViewed' assign='boxtitle'}
    {card class="box box-last-seen mb-4" id="sidebox{$oBox->getID()}"}
        {block name='boxes-box-last-seen-content'}
            {block name='boxes-box-last-seen-title'}
                <div class="productlist-filter-headline">
                    {$boxtitle}
                </div>
            {/block}
            {foreach $oBox->getProducts() as $product}
                {block name='boxes-box-last-seen-image-link'}
                    {row}
                        {col cols=4}
                            {link class="image-wrapper" href=$product->cURLFull}
                                {if isset($product->Bilder[0]->cAltAttribut)}
                                    {assign var=alt value=$product->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html'}
                                {else}
                                    {assign var=alt value=$product->cName}
                                {/if}
                            {image fluid=true webp=true lazy=true
                                alt=$alt
                                src=$product->Bilder[0]->cURLKlein
                                srcset="{$product->Bilder[0]->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                    {$product->Bilder[0]->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                    {$product->Bilder[0]->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w"
                                sizes="auto"
                            }
                            {/link}
                        {/col}
                        {col cols=8}
                            {link class="last-seen-link" href=$product->cURLFull}
                                {$product->cKurzbezeichnung}
                            {/link}
                            {include file='productdetails/price.tpl' Artikel=$product tplscope='box'}
                        {/col}
                    {/row}
                    {if !$product@last}
                        {block name='boxes-box-last-seen-hr'}
                            <hr class="my-3">
                        {/block}
                    {/if}
                {/block}
            {/foreach}
        {/block}
    {/card}
{/block}
