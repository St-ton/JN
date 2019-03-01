{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{has_boxes position='left' assign='hasLeftBox'}

{if isset($bWarenkorbHinzugefuegt) && $bWarenkorbHinzugefuegt}
    {include file='productdetails/pushed_success.tpl'}
{else}
    {$alertList->displayAlertByKey('productNote')}
{/if}

{include file='snippets/opc_mount_point.tpl' id='opc_article_content_prepend'}

{form id="buy_form" action=$Artikel->cURLFull class="evo-validate"}
    {row id="product-offer"}
        {col cols=12 md=6 class="product-gallery"}
            {include file='productdetails/image.tpl'}
            {*{image src=$Artikel->Bilder[0]->cURLNormal fluid=true class="mx-auto d-block" alt="Responsive image"}*}
        {/col}
        {col cols=12 md=6 class="product-info"}
            <div class="h1 d-xs-block d-sm-none text-center">{$Artikel->cName}</div>
            {block name='productdetails-info'}
            <div class="product-info-inner">
                <div class="product-headline d-none d-sm-block">
                    {block name='productdetails-info-product-title'}
                        <h1 class="product-title" itemprop="name">{$Artikel->cName}</h1>
                    {/block}
                </div>

                {block name='productdetails-info-essential-wrapper'}
                {if ($Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0) || isset($Artikel->cArtNr)}
                    {row class="info-essential mb-2"}
                        {if ($Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0)}
                            {block name='productdetails-info-rating-wrapper'}
                                {col class="rating-wrapper" itemprop="aggregateRating" itemscope=true itemtype="http://schema.org/AggregateRating"}
                                {*<div class="rating-wrapper">*}
                                    <meta itemprop="ratingValue" content="{$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt}"/>
                                    <meta itemprop="bestRating" content="5"/>
                                    <meta itemprop="worstRating" content="1"/>
                                    <meta itemprop="reviewCount" content="{$Artikel->Bewertungen->oBewertungGesamt->nAnzahl}"/>
                                    {link href="{$Artikel->cURLFull}#tab-votes" id="jump-to-votes-tab" class="d-print-none"}
                                        {include file='productdetails/rating.tpl' stars=$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt total=$Artikel->Bewertungen->oBewertungGesamt->nAnzahl}
                                    {/link}
                                {*</div>*}
                                {/col}
                                {* /rating-wrapper*}
                            {/block}
                        {/if}
                        {block name='productdetails-info-essential'}
                            {if isset($Artikel->cArtNr) || isset($Artikel->dMHD)}
                                {col cols=12}
                                    <p class="text-muted product-sku">{lang key='sortProductno'}: <span
                                                itemprop="sku">{$Artikel->cArtNr}</span></p>
                                    {if isset($Artikel->dMHD) && isset($Artikel->dMHD_de)}
                                        <p title="{lang key='productMHDTool'}"
                                           class="best-before text-muted">{lang key='productMHD'}: <span
                                                    itemprop="best-before">{$Artikel->dMHD_de}</span></p>
                                    {/if}
                                {/col}
                            {/if}
                            {if !empty($Artikel->cISBN)
                            && ($Einstellungen.artikeldetails.isbn_display === 'D'
                            || $Einstellungen.artikeldetails.isbn_display === 'DL')}
                                {col cols=12}
                                    <p class="text-muted">{lang key='isbn'}: <span>{$Artikel->cISBN}</span></p>
                                {/col}
                            {/if}
                            {block name='productdetails-info-category-wrapper'}
                                {if $Einstellungen.artikeldetails.artikeldetails_kategorie_anzeigen === 'Y'}
                                    {block name='productdetails-info-category'}
                                        {col cols=12 class="product-category word-break"}
                                            <span class="text-muted">{lang key='category'}: </span>
                                            {assign var=i_kat value=$Brotnavi|@count}{assign var=i_kat value=$i_kat-1}
                                            <a href="{$Brotnavi[$i_kat]->getURLFull()}" itemprop="category">{$Brotnavi[$i_kat]->getName()}</a>
                                        {/col}
                                    {/block}
                                {/if}
                            {/block}
                            {block name='productdetails-info-manufacturer-wrapper'}
                                {if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'N' && isset($Artikel->cHersteller)}
                                    {block name='product-info-manufacturer'}
                                        {col cols=12 class="small" itemprop="brand" itemscope=true itemtype="http://schema.org/Organization"}
                                            <a href="{$Artikel->cHerstellerSeo}"{if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'B'} data-toggle="tooltip" data-placement="left" title="{$Artikel->cHersteller}"{/if} itemprop="url">
                                                {if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'Y' && (!empty($Artikel->cBildpfad_thersteller) || $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen === 'B') && isset($Artikel->cHerstellerBildKlein)}
                                                    {image src=$Artikel->cHerstellerBildURLKlein alt=$Artikel->cHersteller class="img-sm"}
                                                    <meta itemprop="image" content="{$Artikel->cHerstellerBildURLKlein}">
                                                {/if}
                                                {if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'B'}
                                                    <span itemprop="name">{$Artikel->cHersteller}</span>
                                                {/if}
                                            </a>
                                        {/col}
                                    {/block}
                                {/if}
                            {/block}
                            {if !empty($Artikel->cUNNummer) && !empty($Artikel->cGefahrnr)
                            && ($Einstellungen.artikeldetails.adr_hazard_display === 'D'
                            || $Einstellungen.artikeldetails.adr_hazard_display === 'DL')}
                                {col cols=12}
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
                                {/col}
                            {/if}
                        {/block}
                    {/row}
                {/if}
                {/block}

                {block name='productdetails-info-description-wrapper'}
                {if $Einstellungen.artikeldetails.artikeldetails_kurzbeschreibung_anzeigen === 'Y' && $Artikel->cKurzBeschreibung}
                    {block name='productdetails-info-description'}
                        <div class="shortdesc mb-5 d-none d-md-flex" itemprop="description">
                            {$Artikel->cKurzBeschreibung}
                        </div>
                    {/block}
                {/if}
                {/block}

                <div class="product-offer" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                    <link itemprop="businessFunction" href="http://purl.org/goodrelations/v1#Sell" />
                    {block name='productdetails-info-hidden'}
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
                    {block name='productdetails-info-variation'}
                        <!-- VARIATIONEN -->
                        {include file='productdetails/variation.tpl' simple=$Artikel->isSimpleVariation showMatrix=$showMatrix}
                    {/block}

                    {row class="mb-5"}
                        {block name='productdetails-info-price'}
                            {col cols=12}
                                {include file='productdetails/price.tpl' Artikel=$Artikel tplscope='detail'}
                            {/col}
                        {/block}
                        {block name='productdetails-info-stock'}
                            {col cols=12}
                                {include file='productdetails/stock.tpl'}
                            {/col}
                        {/block}
                    {/row}
                    {*UPLOADS product-specific files, e.g. for customization*}
                    {include file="snippets/uploads.tpl" tplscope='product'}

                    {*WARENKORB anzeigen wenn keine variationen mehr auf lager sind?!*}
                    {if !$Artikel->bHasKonfig}
                        {include file='productdetails/basket.tpl'}
                    {/if}
                    <hr>
                </div>
            </div>{* /product-info-inner *}
            {/block}{* productdetails-info *}
        {/col}
        {if $Artikel->bHasKonfig}
            {block name='productdetails-config'}
                {row id="product-configurator"}
                    {include file='productdetails/config_container.tpl'}
                {/row}
            {/block}
        {/if}
    {/row}
    {block name='details-matrix'}
        {include file='productdetails/matrix.tpl'}
    {/block}
{/form}

{include file='snippets/opc_mount_point.tpl' id='opc_article_content_append'}

{if !isset($smarty.get.quickView) || $smarty.get.quickView != 1}
    <div class="clearfix"></div>

    {block name='details-tabs'}
        {include file='productdetails/tabs.tpl'}
    {/block}

    <div class="clearfix"></div>

    {include file='snippets/opc_mount_point.tpl' id='opc_article_tabs_prepend'}

    {*SLIDERS*}
    {if isset($Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen) && $Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen === 'Y' && isset($Artikel->oStueckliste_arr) && $Artikel->oStueckliste_arr|@count > 0
    || isset($Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen) && $Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen === 'Y' && isset($Artikel->oProduktBundle_arr) && $Artikel->oProduktBundle_arr|@count > 0
    || isset($Xselling->Standard->XSellGruppen) && count($Xselling->Standard->XSellGruppen) > 0
    || isset($Xselling->Kauf->Artikel) && count($Xselling->Kauf->Artikel) > 0
    || isset($oAehnlicheArtikel_arr) && count($oAehnlicheArtikel_arr) > 0}
        <hr>
        {if isset($Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen) && $Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen === 'Y' && isset($Artikel->oStueckliste_arr) && $Artikel->oStueckliste_arr|@count > 0}
            <div class="partslist">
                {lang key='listOfItems' section='global' assign='slidertitle'}
                {include file='snippets/product_slider.tpl' id='slider-partslist' productlist=$Artikel->oStueckliste_arr title=$slidertitle showPartsList=true}
            </div>
        {/if}

        {if isset($Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen) && $Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen === 'Y' && isset($Artikel->oProduktBundle_arr) && $Artikel->oProduktBundle_arr|@count > 0}
            <div class="bundle">
                {include file='productdetails/bundle.tpl' ProductKey=$Artikel->kArtikel Products=$Artikel->oProduktBundle_arr ProduktBundle=$Artikel->oProduktBundlePrice ProductMain=$Artikel->oProduktBundleMain}
            </div>
        {/if}

        {if isset($Xselling->Standard) || isset($Xselling->Kauf) || isset($oAehnlicheArtikel_arr)}
            <div class="recommendations d-print-none">
                {block name='productdetails-recommendations'}
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
    <div id="article_popups">
        {include file='productdetails/popups.tpl'}
    </div>
{/if}
