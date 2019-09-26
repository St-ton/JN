{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-bundle'}
{if !empty($Products)}
    {block name='productdetails-bundle-form'}
        {form action="{if !empty($ProductMain->cURLFull)}{$ProductMain->cURLFull}{else}index.php{/if}" method="post" id="form_bundles" class="evo-validate"}
            {card no-body=true}
                {block name='productdetails-bundle-hidden-inputs'}
                    {input type="hidden" name="a" value=$ProductMain->kArtikel}
                    {input type="hidden" name="addproductbundle" value="1"}
                    {input type="hidden" name="aBundle" value=$ProductKey}
                {/block}
                {block name='productdetails-bundle-form-content'}{* for additional hidden inputs use block prepend *}
                    {cardheader}
                        {block name='productdetails-bundle-form-subheading'}
                            <div class="h3 mb-0">{lang key='buyProductBundle' section='productDetails'}</div>
                        {/block}
                    {/cardheader}
                    {cardbody class='row'}
                        {block name='productdetails-bundle-form-body'}
                            <div class="col-xs-12 col-md-8">
                                <ul class="list-inline bundle-list align-items-center d-flex flex-wrap flex-row">
                                    {foreach $Products as $bundleProduct}
                                        <li>
                                            {link href=$bundleProduct->cURLFull}
                                                {image src="{if $bundleProduct->Bilder[0]->cURLKlein}{$bundleProduct->Bilder[0]->cURLKlein}{else}{$smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN}{/if}"
                                                     alt=$bundleProduct->cName
                                                     title=$bundleProduct->cName}
                                            {/link}
                                        </li>
                                        {if !$bundleProduct@last}
                                            <li class="px-2">
                                                <span class="fa fa-plus"></span>
                                            </li>
                                        {/if}
                                    {/foreach}
                                </ul>
                            </div>
                        {/block}
                        {block name='productdetails-bundle-form-price'}
                            <div class="col-xs-12 col-md-4">
                                {if $smarty.session.Kundengruppe->mayViewPrices()}
                                    <p class="bundle-price">
                                        <strong>{lang key='priceForAll' section='productDetails'}:</strong>
                                        <strong class="price price-sm">{$ProduktBundle->cPriceLocalized[$NettoPreise]}</strong>
                                        {if $ProduktBundle->fPriceDiff > 0}
                                            <br />
                                            <span class="label label-warning">{lang key='youSave' section='productDetails'}: {$ProduktBundle->cPriceDiffLocalized[$NettoPreise]}</span>
                                        {/if}
                                        {if $ProductMain->cLocalizedVPE}
                                            <b class="label">{lang key='basePrice'}: </b>
                                            <span class="value">{$ProductMain->cLocalizedVPE[$NettoPreise]}</span>
                                        {/if}
                                    </p>
                                    <p>
                                        {block name='productdetails-bundle-form-submit'}
                                            {button name="inWarenkorb" type="submit" value="1"}{lang key='addAllToCart'}{/button}
                                        {/block}
                                    </p>
                                {/if}
                            </div>
                        {/block}
                        {block name='productdetails-bundle-form-products'}
                            <div class="col-xs-12">
                                <ul>
                                    {foreach $Products as $Product}
                                        <li>
                                            {block name='productdetails-bundle-bindles'}
                                            {foreach $ProductMain->oStueckliste_arr as $bundleProduct}
                                                {if $bundleProduct->kArtikel == $Product->kArtikel}
                                                    <span class="article-bundle-info">
                                                        <span class="bundle-amount">{$bundleProduct->fAnzahl_stueckliste}</span> <span class="bundle-times">x</span>
                                                    </span>
                                                    {break}
                                                {/if}
                                            {/foreach}
                                            {/block}
                                            {block name='productdetails-bundle-link-price'}
                                                {link href=$Product->cURLFull}{$Product->cName}{/link}
                                                <strong class="price price-xs">{$Product->Preise->cVKLocalized[0]}</strong>
                                            {/block}
                                        </li>
                                    {/foreach}
                                </ul>
                            </div>
                        {/block}
                    {/cardbody}
                {/block}
            {/card}
        {/form}
    {/block}
{/if}
{/block}
