{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-bundle'}
    {if !empty($Products)}
        {block name='productdetails-bundle-form'}
            {form action="{if !empty($ProductMain->cURLFull)}{$ProductMain->cURLFull}{else}index.php{/if}" method="post" id="form_bundles" class="evo-validate"}
            {block name='productdetails-bundle-hidden-inputs'}
                {input type="hidden" name="a" value=$ProductMain->kArtikel}
                {input type="hidden" name="addproductbundle" value="1"}
                {input type="hidden" name="aBundle" value=$ProductKey}
            {/block}
            {block name='productdetails-bundle-include-product-slider'}
                {include file='snippets/product_slider.tpl' id='slider-partslist' productlist=$ProductMain->oStueckliste_arr title="{lang key='buyProductBundle' section='productDetails'}" showPartsList=true}
            {/block}
            {if $smarty.session.Kundengruppe->mayViewPrices()}
                {block name='productdetails-bundle-form-price'}
                    {row class='mb-7 mt-n2 justify-content-md-center align-items-center'}
                    {col cols=12 md='auto' class='text-center text-md-right mb-2 mb-md-0'}
                        <span class="font-weight-bold">
                                {lang key='priceForAll' section='productDetails'}:
                                <span class="price price-sm">{$ProduktBundle->cPriceLocalized[$NettoPreise]}</span>
                            </span>
                    {if $ProduktBundle->fPriceDiff > 0}
                        <span class="text-warning ml-1">({lang key='youSave' section='productDetails'}: {$ProduktBundle->cPriceDiffLocalized[$NettoPreise]})</span>
                    {/if}
                    {if $ProductMain->cLocalizedVPE}
                        <span class="font-weight-bold">{lang key='basePrice'}: </span>
                        <span>{$ProductMain->cLocalizedVPE[$NettoPreise]}</span>
                    {/if}
                    {/col}
                    {col cols=12 md='auto'}
                    {block name='productdetails-bundle-form-submit'}
                        {button name="inWarenkorb" type="submit" value="1" block=true}
                        {lang key='addAllToCart'}
                        {/button}
                    {/block}
                    {/col}
                    {/row}
                {/block}
            {/if}
            {/form}
        {/block}
    {/if}
{/block}
