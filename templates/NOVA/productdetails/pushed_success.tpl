{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-pushed-success'}
    <div id="pushed-success" {if $card}role="alert" class="card shadow-sm alert alert-dismissable p-0 mb-5"{/if}>
        {if isset($zuletztInWarenkorbGelegterArtikel)}
            {assign var=pushedArtikel value=$zuletztInWarenkorbGelegterArtikel}
        {else}
            {assign var=pushedArtikel value=$Artikel}
        {/if}
        {assign var=showXSellingCart value=isset($Xselling->Kauf) && count($Xselling->Kauf->Artikel) > 0}
        {if $card}
            {block name='productdetails-pushed-success-cart-note-heading'}
                <div class="text-center card-header alert-success">
                    {if isset($cartNote)}
                        {$cartNote}
                    {/if}
                </div>
            {/block}
            <div class="card-body">
        {/if}

        {row}
            {block name='productdetails-pushed-success-product-cell'}
                {col cols=12 md="{if $showXSellingCart}6{else}12{/if}" class="mb-3"}
                    {block name='productdetails-pushed-success-product-cell-content'}
                        <div class="product-cell productbox-inner{if isset($class)} {$class}{/if}">
                            {row}
                                {col cols=12}
                                    {block name='productdetails-pushed-success-product-cell-subheading'}
                                        <div class="productbox-title subheadline">{$pushedArtikel->cName}</div>
                                    {/block}
                                {/col}
                                {col cols=4}
                                    {block name='productdetails-pushed-success-product-cell-image'}
                                        {counter assign=imgcounter print=0}
                                        {image lazy=true webp=true
                                            src=$pushedArtikel->getImage(\JTL\Media\Image::SIZE_XS)
                                            srcset="{$pushedArtikel->getImage(\JTL\Media\Image::SIZE_XS)} {$Einstellungen.bilder.bilder_artikel_mini_breite}w,
                                                {$pushedArtikel->getImage(\JTL\Media\Image::SIZE_SM)} {$Einstellungen.bilder.bilder_artikel_klein_breite}w,
                                                {$pushedArtikel->getImage(\JTL\Media\Image::SIZE_MD)} {$Einstellungen.bilder.bilder_artikel_normal_breite}w"
                                            alt="{if isset($pushedArtikel->Bilder[0]->cAltAttribut)}{$pushedArtikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html'}{else}{$pushedArtikel->cName}{/if}"
                                            id="image{$pushedArtikel->kArtikel}_{$imgcounter}"
                                            sizes="auto"
                                            class="image mb-3" fluid=true
                                        }
                                    {/block}
                                {/col}
                                {col}
                                    {block name='productdetails-pushed-success-product-cell-details'}
                                        {row}
                                            {col cols=12}
                                                <dl class="form-row">
                                                    <dt class="col-6">{lang key='productNo'}:</dt>
                                                    <dd class="col-6">{$pushedArtikel->cArtNr}</dd>
                                                    {if !empty($pushedArtikel->cHersteller)}
                                                        <dt class="col-6">{lang key='manufacturer' section='productDetails'}:</dt>
                                                        <dd class="col-6">{$pushedArtikel->cHersteller}</dd>
                                                    {/if}
                                                    {if !empty($pushedArtikel->oMerkmale_arr)}
                                                        <dt class="col-6">{lang key='variationsIn' section='productOverview'}:</dt>
                                                        <dd class="col-6 attr-characteristic">
                                                            {block name='productdetails-pushed-success-characteristics'}
                                                                {foreach $pushedArtikel->oMerkmale_arr as $oMerkmal}
                                                                    {$oMerkmal->cName}
                                                                    {if $oMerkmal@index === 10 && !$oMerkmal@last}&hellip;{break}{/if}
                                                                    {if !$oMerkmal@last}, {/if}
                                                                {/foreach}
                                                            {/block}
                                                        </dd>
                                                    {/if}
                                                    {if isset($pushedArtikel->dMHD) && isset($pushedArtikel->dMHD_de)}
                                                        <dt class="col-6">{lang key='productMHDTool'}:</dt>
                                                        <dd class="col-6">{$pushedArtikel->dMHD_de}</dd>
                                                    {/if}
                                                    {if $Einstellungen.artikeluebersicht.artikeluebersicht_gewicht_anzeigen === 'Y' && isset($pushedArtikel->cGewicht) && $pushedArtikel->fGewicht > 0}
                                                        <dt class="col-6">{lang key='shippingWeight'}:</dt>
                                                        <dd class="col-6">{$pushedArtikel->cGewicht} {lang key='weightUnit'}</dd>
                                                    {/if}
                                                    {if $Einstellungen.artikeluebersicht.artikeluebersicht_artikelgewicht_anzeigen === 'Y' && isset($pushedArtikel->cArtikelgewicht) && $pushedArtikel->fArtikelgewicht > 0}
                                                        <dt class="col-6">{lang key='productWeight'}:</dt>
                                                        <dd class="col-6">{$pushedArtikel->cArtikelgewicht} {lang key='weightUnit'}</dd>
                                                    {/if}
                                                    {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y' && (int)$pushedArtikel->fDurchschnittsBewertung !== 0}
                                                        <dt class="col-6">{lang key='ratingAverage'}:</dt>
                                                        <dd class="col-6">
                                                            {block name='productdetails-pushed-success-include-rating'}
                                                                {include file='productdetails/rating.tpl' stars=$pushedArtikel->fDurchschnittsBewertung}
                                                            {/block}
                                                        </dd>
                                                    {/if}
                                                </dl>
                                            {/col}
                                        {/row}
                                    {/block}
                                {/col}
                            {/row}
                        </div>
                    {/block}
                    {block name='productdetails-pushed-success-product-cell-links'}
                        {row}
                            {col cols=12 md=6}
                                {link href="{get_static_route id='warenkorb.php'}" class="btn btn-outline-primary btn-basket btn-block mb-3"}
                                    <i class="fas fa-shopping-cart"></i> {lang key='gotoBasket'}
                                {/link}
                            {/col}
                            {col cols=12 md=6}
                                {link href=$pushedArtikel->cURLFull
                                    class="btn btn-primary btn-block"
                                    data=["dismiss"=>"{if !$card}modal{else}alert{/if}"]
                                    aria=["label"=>"Close"]}
                                    <i class="fa fa-arrow-circle-right"></i> {lang key='continueShopping' section='checkout'}
                                {/link}
                            {/col}
                        {/row}
                    {/block}
                {/col}
            {/block}
            {block name='productdetails-pushed-success-x-sell'}
                {if $showXSellingCart}
                    {col cols=6 class="d-none d-md-block border-left"}
                        {row}
                            {col cols=12}
                                {block name='productdetails-pushed-success-x-sell-heading'}
                                    <div class="productbox-title subheadline">{lang key='customerWhoBoughtXBoughtAlsoY' section='productDetails'}</div>
                                {/block}
                            {/col}
                            {col cols=12}
                                {block name='productdetails-pushed-success-include-product-slider'}
                                    {include file='snippets/product_slider.tpl' id='' productlist=$Xselling->Kauf->Artikel title='' tplscope='half'}
                                {/block}
                            {/col}
                        {/row}
                    {/col}
                {/if}
            {/block}
        {/row}
        {if $card}</div>{/if}
    </div>
{/block}
