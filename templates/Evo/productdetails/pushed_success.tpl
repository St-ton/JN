<div id="pushed-success" class="alert alert-info panel-wrap{if isset($inline)} no-margin{/if}">
    {if !isset($Artikel) && isset($zuletztInWarenkorbGelegterArtikel)}
        {assign var=Artikel value=$zuletztInWarenkorbGelegterArtikel}
    {/if}
    <div class="panel panel-default clearfix">
        <div class="panel-body">
            <div class="row">
                {assign var="showXSellingCart" value=false}
                {if isset($Xselling->Kauf) && count($Xselling->Kauf->Artikel) > 0}
                    {assign var="showXSellingCart" value=true}
                {/if}
                <div class="col-sm-5{if !$showXSellingCart} col-sm-offset-4{/if} text-center">
                    <h4 class="success-title">{$hinweis}</h4>
                    <div class="product-cell text-center{if isset($class)} {$class}{/if}">
                        <div class="row">
                            <div class="col-xs-4 col-xs-offset-4">
                                {counter assign=imgcounter print=0}
                                <img src="{$Artikel->Bilder[0]->cPfadNormal}" alt="{if isset($Artikel->Bilder[0]->cAltAttribut)}{$Artikel->Bilder[0]->cAltAttribut|strip_tags|escape:"quotes"|truncate:60}{else}{$Artikel->cName}{/if}" id="image{$Artikel->kArtikel}_{$imgcounter}" class="image img-responsive" />
                            </div>
                            <div class="col-xs-12">
                                <div class="caption">
                                    <span class="title">{$Artikel->cName}</span>
                                </div>
                            </div>{* /caption *}
                        </div>
                    </div>{* /product-cell *}
                    <hr>
                    <p class="btn-group btn-group-justified btn-group-full" role="group">
                        <a href="warenkorb.php" class="btn btn-default btn-basket"><i class="fa fa-shopping-cart"></i> {lang key="gotoBasket"}</a>
                        <a href="#" class="btn btn-primary btn-checkout" data-dismiss="{if isset($type)}{$type}{else}modal{/if}" aria-label="Close"><i class="fa fa-arrow-circle-right"></i> {lang key="continueShopping" section="checkout"}</a>
                    </p>
{*
                    <p class="continue-shopping">
                        <a href="bestellvorgang.php">{lang key="checkout" section="basketpreview"}</a>
                    </p>
*}
                </div>
                {if $showXSellingCart}
                    <div class="col-xs-7 recommendations hidden-xs">
                        <h4 class="text-center">{lang key='customerWhoBoughtXBoughtAlsoY' section='productDetails'}</h4>
                        {include file='snippets/product_slider.tpl' id='slider-xsell' productlist=$Xselling->Kauf->Artikel title='' showPanel=false}
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>