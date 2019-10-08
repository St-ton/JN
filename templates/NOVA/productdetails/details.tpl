{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-details'}
    {*{has_boxes position='left' assign='hasLeftBox'}*}
    {$hasLeftBox = false}
    {container}
        {if isset($bWarenkorbHinzugefuegt) && $bWarenkorbHinzugefuegt}
            {block name='productdetails-details-include-pushed-success'}
                {include file='productdetails/pushed_success.tpl' card=true}
            {/block}
        {else}
            {block name='productdetails-details-alert-product-note'}
                {$alertList->displayAlertByKey('productNote')}
            {/block}
        {/if}
    {/container}
    {block name='productdetails-details-form'}
        {opcMountPoint id='opc_before_buy_form'}
        {container}
            {form id="buy_form" action=$Artikel->cURLFull class="evo-validate"}
                {row id="product-offer"}
                    {block name='productdetails-details-include-image'}
                        {col cols=12 lg=6 class="product-gallery"}
                            {opcMountPoint id='opc_before_gallery'}
                            {include file='productdetails/image.tpl'}
                            {opcMountPoint id='opc_after_gallery'}
                            {*{image src=$Artikel->Bilder[0]->cURLNormal fluid=true class="mx-auto d-block" alt="Responsive image"}*}
                        {/col}
                    {/block}
                    {col cols=12 lg=6 class="product-info"}
                        {block name='productdetails-details-info'}
                        <div class="product-info-inner">
                            <div class="product-headline">
                                {block name='productdetails-details-info-product-title'}
                                    {opcMountPoint id='opc_before_headline'}
                                    <h1 class="product-title h2" itemprop="name">{$Artikel->cName}</h1>
                                {/block}
                            </div>
                            {block name='productdetails-details-info-essential-wrapper'}
                            {if ($Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0) || isset($Artikel->cArtNr)}
                                {if ($Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0)}
                                    {block name='productdetails-details-info-rating-wrapper'}
                                        <div class="rating-wrapper" itemprop="aggregateRating" itemscope="true" itemtype="http://schema.org/AggregateRating">
                                            <meta itemprop="ratingValue" content="{$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt}"/>
                                            <meta itemprop="bestRating" content="5"/>
                                            <meta itemprop="worstRating" content="1"/>
                                            <meta itemprop="reviewCount" content="{$Artikel->Bewertungen->oBewertungGesamt->nAnzahl}"/>
                                            {block name='productdetails-details-include-rating'}
                                                {link href="{$Artikel->cURLFull}#tab-votes"
                                                    id="jump-to-votes-tab"
                                                    class="d-print-none text-decoration-none"
                                                    aria=["label"=>{lang key='Votes'}]
                                                }
                                                    {include file='productdetails/rating.tpl' stars=$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt total=$Artikel->Bewertungen->oBewertungGesamt->nAnzahl}
                                                    ({$Artikel->Bewertungen->oBewertungGesamt->nAnzahl} {lang key='rating'})
                                                {/link}
                                            {/block}
                                        </div>
                                    {/block}
                                {/if}
                                {block name='productdetails-details-info-essential'}
                                    <ul class="list-unstyled my-5">
                                        {if isset($Artikel->cArtNr)}
                                            <li class='product-sku'>
                                                <span class="font-weight-bold">
                                                    {lang key='sortProductno'}:
                                                </span>
                                                <span itemprop="sku">{$Artikel->cArtNr}</span>
                                            </li>
                                        {/if}
                                        {if isset($Artikel->dMHD) && isset($Artikel->dMHD_de)}
                                            <li>
                                                <span class="font-weight-bold" title="{lang key='productMHDTool'}">
                                                    {lang key='productMHD'}:
                                                </span>
                                                <span itemprop="best-before">{$Artikel->dMHD_de}</span>
                                            </li>
                                        {/if}

                                        {if !empty($Artikel->cBarcode)}
                                            <li>
                                                <span class="font-weight-bold">{lang key='ean'}:</span>
                                                <span itemprop="{if $Artikel->cBarcode|count_characters === 8}gtin8{else}gtin13{/if}">{$Artikel->cBarcode}</span>
                                            </li>
                                        {/if}
                                        {if !empty($Artikel->cISBN)
                                        && ($Einstellungen.artikeldetails.isbn_display === 'D'
                                        || $Einstellungen.artikeldetails.isbn_display === 'DL')}
                                            <li>
                                                <span class="font-weight-bold">{lang key='isbn'}:</span>
                                                <span itemprop="gtin13">{$Artikel->cISBN}</span>
                                            </li>
                                        {/if}
                                        {block name='productdetails-details-info-category-wrapper'}
                                            {if $Einstellungen.artikeldetails.artikeldetails_kategorie_anzeigen === 'Y'}
                                                {block name='productdetails-details-info-category'}
                                                    <li class="product-category word-break">
                                                        <span class="font-weight-bold">{lang key='category'}: </span>
                                                        {assign var=cidx value=($Brotnavi|@count)-2}
                                                        <a href="{$Brotnavi[$cidx]->getURLFull()}" itemprop="category">{$Brotnavi[$cidx]->getName()}</a>
                                                    </li>
                                                {/block}
                                            {/if}
                                        {/block}
                                        {block name='productdetails-details-info-manufacturer-wrapper'}
                                            {if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'N' && isset($Artikel->cHersteller)}
                                                {block name='productdetails-details-product-info-manufacturer'}
                                                    <li itemprop="brand" itemscope="true" itemtype="http://schema.org/Organization">
                                                        <span class="font-weight-bold">{lang key='manufacturers'}:</span>
                                                        <a href="{$Artikel->cHerstellerSeo}"{if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'B'} data-toggle="tooltip" data-placement="left" title="{$Artikel->cHersteller}"{/if} itemprop="url">
                                                            {if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'Y' && (!empty($Artikel->cBildpfad_thersteller) || $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen === 'B') && isset($Artikel->cHerstellerBildKlein)}
                                                                {image src=$Artikel->cHerstellerBildURLKlein alt=$Artikel->cHersteller class="img-sm"}
                                                                <meta itemprop="image" content="{$Artikel->cHerstellerBildURLKlein}">
                                                            {/if}
                                                            {if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'B'}
                                                                <span itemprop="name">{$Artikel->cHersteller}</span>
                                                            {/if}
                                                        </a>
                                                    </li>
                                                {/block}
                                            {/if}
                                        {/block}
                                        {if !empty($Artikel->cUNNummer) && !empty($Artikel->cGefahrnr)
                                        && ($Einstellungen.artikeldetails.adr_hazard_display === 'D'
                                        || $Einstellungen.artikeldetails.adr_hazard_display === 'DL')}
                                            {block name='productdetails-details-hazard-info'}
                                                <li>
                                                    <div class="title text-muted">{lang key='adrHazardSign'}:
                                                        <table class="adr-table">
                                                            <tr>
                                                                <td>{$Artikel->cGefahrnr}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>{$Artikel->cUNNummer}</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </li>
                                            {/block}
                                        {/if}
                                    </ul>
                                {/block}
                            {/if}
                            {/block}

                            {block name='productdetails-details-info-description-wrapper'}
                            {if $Einstellungen.artikeldetails.artikeldetails_kurzbeschreibung_anzeigen === 'Y' && $Artikel->cKurzBeschreibung}
                                {block name='productdetails-details-info-description'}
                                    {opcMountPoint id='opc_before_short_desc'}
                                    <div class="shortdesc mb-2 d-none d-md-block" itemprop="description">
                                        {$Artikel->cKurzBeschreibung}
                                    </div>
                                {/block}
                            {/if}
                            {opcMountPoint id='opc_after_short_desc'}
                            {/block}

                            <div class="product-offer mb-5" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                                {block name='productdetails-details-info-hidden'}
                                    <link itemprop="businessFunction" href="http://purl.org/goodrelations/v1#Sell" />

                                    {if !($Artikel->nIstVater)}
                                        <link itemprop="url" href="{$Artikel->cURLFull}" />
                                    {/if}
                                    {input type="hidden" name="inWarenkorb" value="1"}
                                    {if $Artikel->kArtikelVariKombi > 0}
                                        {input type="hidden" name="aK" value=$Artikel->kArtikelVariKombi}
                                    {/if}
                                    {if isset($Artikel->kVariKindArtikel)}
                                        {input type="hidden" name="VariKindArtikel" value=$Artikel->kVariKindArtikel}
                                    {/if}
                                    {if isset($smarty.get.ek)}
                                        {input type="hidden" name="ek" value=$smarty.get.ek|intval}
                                    {/if}
                                    {input type="hidden" name="AktuellerkArtikel" class="current_article" name="a" value=$Artikel->kArtikel}
                                    {input type="hidden" name="wke" value="1"}
                                    {input type="hidden" name="show" value="1"}
                                    {input type="hidden" name="kKundengruppe" value=$smarty.session.Kundengruppe->getID()}
                                    {input type="hidden" name="kSprache" value=$smarty.session.kSprache}
                                {/block}
                                {block name='productdetails-details-include-variation'}
                                    <!-- VARIATIONEN -->
                                    {include file='productdetails/variation.tpl' simple=$Artikel->isSimpleVariation showMatrix=$showMatrix}
                                {/block}

                                {row}
                                    {block name='productdetails-details-include-price'}
                                        {col}
                                            {include file='productdetails/price.tpl' Artikel=$Artikel tplscope='detail' priceLarge=true}
                                        {/col}
                                    {/block}
                                    {block name='productdetails-details-include-stock'}
                                        {col cols=12}
                                            {row class="border-top border-bottom align-items-end no-gutters {if !isset($availability) && !isset($shippingTime)}py-3 mt-5 px-lg-3 mb-4{/if}"}
                                                {col}
                                                    {include file='productdetails/stock.tpl'}
                                                {/col}
                                                {col class="col-auto ml-auto"}
                                                    {if $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'P'}
                                                        <button type="button" id="z{$Artikel->kArtikel}" class="btn btn-link popup-dep question p-0" title="{lang key='productQuestion' section='productDetails'}">
                                                            <span class="fa fa-question-circle"></span>
                                                            <span class="hidden-xs hidden-sm">{lang key='productQuestion' section='productDetails'}</span>
                                                        </button>
                                                    {/if}
                                                {/col}
                                            {/row}
                                        {/col}
                                    {/block}
                                {/row}
                                {*UPLOADS product-specific files, e.g. for customization*}
                                {block name='productdetails-details-include-uploads'}
                                    {include file="snippets/uploads.tpl" tplscope='product'}
                                {/block}
                                {*WARENKORB anzeigen wenn keine variationen mehr auf lager sind?!*}
                                {if !$Artikel->bHasKonfig}
                                    {block name='productdetails-details-include-basket'}
                                        {include file='productdetails/basket.tpl'}
                                    {/block}
                                {/if}
                            </div>
                        </div>{* /product-info-inner *}
                        {/block}{* productdetails-info *}
                        {opcMountPoint id='opc_after_product_info'}
                    {/col}
                    {if $Artikel->bHasKonfig}
                        {block name='productdetails-details-include-config-container'}
                            {col}
                                {row id="product-configurator"}
                                    {include file='productdetails/config_container.tpl'}
                                {/row}
                            {/col}
                        {/block}
                    {/if}
                {/row}
                {block name='productdetails-details-include-matrix'}
                    {include file='productdetails/matrix.tpl'}
                {/block}
            {/form}
        {/container}
    {/block}

    {block name='productdetails-details-content-not-quickview'}
        {block name='productdetails-details-include-tabs'}
            {include file='productdetails/tabs.tpl'}
        {/block}

        {*SLIDERS*}
        {if isset($Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen) && $Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen === 'Y' && isset($Artikel->oStueckliste_arr) && $Artikel->oStueckliste_arr|@count > 0
        || isset($Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen) && $Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen === 'Y' && isset($Artikel->oProduktBundle_arr) && $Artikel->oProduktBundle_arr|@count > 0
        || isset($Xselling->Standard->XSellGruppen) && count($Xselling->Standard->XSellGruppen) > 0
        || isset($Xselling->Kauf->Artikel) && count($Xselling->Kauf->Artikel) > 0
        || isset($oAehnlicheArtikel_arr) && count($oAehnlicheArtikel_arr) > 0}
            {container}
                {if isset($Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen) && $Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen === 'Y' && isset($Artikel->oStueckliste_arr) && $Artikel->oStueckliste_arr|@count > 0}
                    {block name='productdetails-details-include-product-slider-partslist'}
                        <div class="partslist">
                            {lang key='listOfItems' section='global' assign='slidertitle'}
                            {include file='snippets/product_slider.tpl' id='slider-partslist' productlist=$Artikel->oStueckliste_arr title=$slidertitle showPartsList=true}
                        </div>
                    {/block}
                {/if}

                {if isset($Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen) && $Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen === 'Y' && isset($Artikel->oProduktBundle_arr) && $Artikel->oProduktBundle_arr|@count > 0}
                    {block name='productdetails-details-include-bundle'}
                        <div class="bundle">
                            {include file='productdetails/bundle.tpl' ProductKey=$Artikel->kArtikel Products=$Artikel->oProduktBundle_arr ProduktBundle=$Artikel->oProduktBundlePrice ProductMain=$Artikel->oProduktBundleMain}
                        </div>
                    {/block}
                {/if}
            {/container}
            {if isset($Xselling->Standard) || isset($Xselling->Kauf) || isset($oAehnlicheArtikel_arr)}
                <div class="recommendations d-print-none">
                    {block name='productdetails-details-recommendations'}
                        {if isset($Xselling->Standard->XSellGruppen) && count($Xselling->Standard->XSellGruppen) > 0}
                            {foreach $Xselling->Standard->XSellGruppen as $Gruppe}
                                {include file='snippets/product_slider.tpl' class='x-supplies' id='slider-xsell-group-'|cat:$Gruppe@iteration productlist=$Gruppe->Artikel title=$Gruppe->Name}
                            {/foreach}
                        {/if}

                        {if isset($Xselling->Kauf->Artikel) && count($Xselling->Kauf->Artikel) > 0}
                            {lang key='customerWhoBoughtXBoughtAlsoY' section='productDetails' assign='slidertitle'}
                            {include file='snippets/product_slider.tpl' class='x-sell' id='slider-xsell' productlist=$Xselling->Kauf->Artikel title=$slidertitle}
                        {/if}

                        {if isset($oAehnlicheArtikel_arr) && count($oAehnlicheArtikel_arr) > 0}
                            {lang key='RelatedProducts' section='productDetails' assign='slidertitle'}
                            {include file='snippets/product_slider.tpl' class='x-related' id='slider-related' productlist=$oAehnlicheArtikel_arr title=$slidertitle}
                        {/if}
                    {/block}
                </div>
            {/if}
        {/if}
        {block name='productdetails-details-include-popups'}
            <div id="article_popups">
                {include file='productdetails/popups.tpl'}
            </div>
        {/block}
    {/block}
{/block}
