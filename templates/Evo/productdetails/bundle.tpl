{if !empty($Products)}
    <form action="{if !empty($ProductMain->cURLFull)}{$ProductMain->cURLFull}{else}index.php{/if}" method="post" id="form_bundles" class="evo-validate">
        <div class="panel panel-default">
            <input type="hidden" name="a" value="{$ProductMain->kArtikel}" />
            <input type="hidden" name="addproductbundle" value="1" />
            <input type="hidden" name="aBundle" value="{$ProductKey}" />
            {block name='productdetails-bundle'}{* for additional hidden inputs use block prepend *}
            <div class="panel-heading">
                <h5 class="panel-title">{lang key='buyProductBundle' section='productDetails'}</h5>
            </div>
            <div class="panel-body row">
                <div class="col-xs-12 col-md-8">
                    <ul class="list-inline bundle-list">
                        {foreach $Products as $bundleProduct}
                            <li>
                                <a href="{$bundleProduct->cURLFull}">
                                    <img src="{if $bundleProduct->Bilder[0]->cURLKlein}{$bundleProduct->Bilder[0]->cURLKlein}{else}{$smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN}{/if}"
                                         alt="{$bundleProduct->cName}"
                                         title="{$bundleProduct->cName}" />
                                </a>
                            </li>
                            {if !$bundleProduct@last}
                                <li>
                                    <span class="fa fa-plus"></span>
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                </div>
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
                            <button name="inWarenkorb" type="submit" value="{lang key='addAllToCart'}" class="submit btn btn-default">{lang key='addAllToCart'}</button>
                        </p>
                    {/if}
                </div>
                <div class="col-xs-12">
                    <ul>
                        {foreach $Products as $Product}
                            <li>
                                {foreach $ProductMain->oStueckliste_arr as $bundleProduct}
                                    {if $bundleProduct->kArtikel == $Product->kArtikel}
                                        <span class="article-bundle-info">
                                            <span class="bundle-amount">{$bundleProduct->fAnzahl_stueckliste}</span> <span class="bundle-times">x</span>
                                        </span>
                                        {break}
                                    {/if}
                                {/foreach}
                                <a href="{$Product->cURLFull}">{$Product->cName}</a>
                                <strong class="price price-xs">{$Product->Preise->cVKLocalized[0]}</strong>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            </div>
            {/block}
        </div>
    </form>
{/if}
