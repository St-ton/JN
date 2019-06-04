{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='boxes-box-last-seen'}
    {lang key='lastViewed' assign='boxtitle'}
    {card class="box box-last-seen mb-7" id="sidebox{$oBox->getID()}" title="{$boxtitle}"}
        {block name='boxes-box-last-seen-content'}
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
                                {include file='snippets/image.tpl' src=$product->Bilder[0]->cURLNormal alt=$alt}
                            {/link}
                        {/col}
                        {col cols=8}
                            {link class="last-seen-link" href=$product->cURLFull}
                                {$product->cKurzbezeichnung}
                            {/link}
                            {include file='productdetails/price.tpl' Artikel=$product tplscope='gallery'}
                        {/col}
                    {/row}
                    {if !$product@last}
                        <hr class="my-3">
                    {/if}
                {/block}
            {/foreach}
        {/block}
    {/card}
{/block}
