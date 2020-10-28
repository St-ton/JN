{block name='boxes-box-last-seen'}
    {lang key='lastViewed' assign='boxtitle'}
    {card class="box box-last-seen box-normal" id="sidebox{$oBox->getID()}"}
        {block name='boxes-box-last-seen-content'}
            {block name='boxes-box-last-seen-title'}
                <div class="productlist-filter-headline">
                    {$boxtitle}
                </div>
            {/block}
            {foreach $oBox->getProducts() as $product}
                <div class="box-last-seen-item">
                    {block name='boxes-box-last-seen-image-link'}
                        <div class="productbox productbox-row productbox-sidebar">
                            <div class="productbox-inner">
                                {formrow}
                                    {col md=4 lg=6 xl=3}
                                        {link class="image-wrapper" href=$product->cURLFull}
                                            {if isset($product->Bilder[0]->cAltAttribut)}
                                                {assign var=alt value=$product->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html'}
                                            {else}
                                                {assign var=alt value=$product->cName}
                                            {/if}
                                            <div class="square-image square">
                                                <div class="inner">
                                                    {image fluid=true webp=true lazy=true
                                                    alt=$alt
                                                    src=$product->Bilder[0]->cURLKlein
                                                    srcset="{$product->Bilder[0]->cURLMini} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                        {$product->Bilder[0]->cURLKlein} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                        {$product->Bilder[0]->cURLNormal} {$Einstellungen.bilder.bilder_artikel_normal_breite}w"
                                                    sizes="auto"}
                                                </div>
                                            </div>
                                        {/link}
                                    {/col}
                                    {col class="col-md"}
                                        {link class="productbox-title" href=$product->cURLFull}
                                            {$product->cKurzbezeichnung}
                                        {/link}
                                        {include file='productdetails/price.tpl' Artikel=$product tplscope='box'}
                                    {/col}
                                {/formrow}
                            </div>
                        </div>
                    {/block}
                </div>
            {/foreach}
        {/block}
    {/card}
{/block}
