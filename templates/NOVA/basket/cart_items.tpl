{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='basket-cart-items'}
    {input type="submit" name="fake" class="d-none"}
    {if $Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'Y'}
        {$itemInfoCols=3}
        {$cols=9}
    {else}
        {$itemInfoCols=5}
        {$cols=12}
    {/if}
    {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen !== 'Y'}
        {$itemInfoCols=$itemInfoCols+2}
    {/if}
    {block name='basket-cart-items-order-items'}
        {block name='basket-cart-items-order-items-header'}
            {row class="text-accent d-none d-xl-flex pb-3"}
                {if $Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'Y'}
                    {col cols=2}{/col}
                {/if}
                {col cols=$itemInfoCols}
                    <span class="text-accent">{lang key='product'}</span>
                {/col}
                {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen === 'Y'}
                    {col cols=2}
                        <span class="text-accent">{lang key="pricePerUnit" section="productDetails"}</span>
                    {/col}
                {/if}
                {col cols=3 class="text-center"}
                    <span class="text-accent">{lang key="quantity" section="checkout"}</span>
                {/col}
                {col cols=2 class="text-right"}
                    <span class="text-accent">{lang key="price"}</span>
                {/col}
                {col cols=12}
                    <hr>
                {/col}
            {/row}
        {/block}
        {block name='basket-cart-items-order-items-main'}
        {foreach $smarty.session.Warenkorb->PositionenArr as $oPosition}
            {if !$oPosition->istKonfigKind()}
                {row class="type-{$oPosition->nPosTyp} pb-3"}
                    {block name='basket-cart-items-image'}
                        {if $Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'Y'}
                            {col cols=3 xl=2 class="h-100"}
                                {if !empty($oPosition->Artikel->cVorschaubild)}
                                    {link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|trans}
                                        {image lazy=true webp=true
                                            src=$oPosition->Artikel->cVorschaubild
                                            alt=$oPosition->cName|trans
                                            fluid=true
                                        }
                                    {/link}
                                {/if}
                            {/col}
                        {/if}
                    {/block}
                    {block name='basket-cart-items-items-main-content'}
                        {col cols=$cols xl=$itemInfoCols class="ml-auto"}
                        {if $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_ARTIKEL}
                            <p>{link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|trans}{$oPosition->cName|trans}{/link}</p>
                            {block name='basket-cart-items-product-data'}
                                <ul class="list-unstyled text-muted small">
                                    <li class="sku"><strong>{lang key='productNo'}:</strong> {$oPosition->Artikel->cArtNr}</li>
                                    {if isset($oPosition->Artikel->dMHD) && isset($oPosition->Artikel->dMHD_de) && $oPosition->Artikel->dMHD_de !== null}
                                        <li title="{lang key='productMHDTool'}" class="best-before">
                                            <strong>{lang key='productMHD'}:</strong> {$oPosition->Artikel->dMHD_de}
                                        </li>
                                    {/if}
                                    {if $oPosition->Artikel->cLocalizedVPE && $oPosition->Artikel->cVPE !== 'N'}
                                        <li class="baseprice"><strong>{lang key='basePrice'}:</strong> {$oPosition->Artikel->cLocalizedVPE[$NettoPreise]}</li>
                                    {/if}
                                    {if $Einstellungen.kaufabwicklung.warenkorb_varianten_varikombi_anzeigen === 'Y' && isset($oPosition->WarenkorbPosEigenschaftArr) && !empty($oPosition->WarenkorbPosEigenschaftArr)}
                                        {foreach $oPosition->WarenkorbPosEigenschaftArr as $Variation}
                                            <li class="variation">
                                                <strong>{$Variation->cEigenschaftName|trans}:</strong> {$Variation->cEigenschaftWertName|trans}
                                            </li>
                                        {/foreach}
                                    {/if}
                                    {if $Einstellungen.kaufabwicklung.bestellvorgang_lieferstatus_anzeigen === 'Y' && $oPosition->cLieferstatus|trans}
                                        <li class="delivery-status"><strong>{lang key='deliveryStatus'}:</strong> {$oPosition->cLieferstatus|trans}</li>
                                    {/if}
                                    {if !empty($oPosition->cHinweis)}
                                        <li class="text-info notice">{$oPosition->cHinweis}</li>
                                    {/if}

                                    {* Buttonloesung eindeutige Merkmale *}
                                    {if $oPosition->Artikel->cHersteller && $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen != "N"}
                                        <li class="manufacturer">
                                            <strong>{lang key='manufacturer' section='productDetails'}</strong>:
                                            <span class="values">
                                               {$oPosition->Artikel->cHersteller}
                                            </span>
                                        </li>
                                    {/if}

                                    {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelmerkmale == 'Y' && !empty($oPosition->Artikel->oMerkmale_arr)}
                                        {foreach $oPosition->Artikel->oMerkmale_arr as $oMerkmale_arr}
                                            <li class="characteristic">
                                                <strong>{$oMerkmale_arr->cName}</strong>:
                                                <span class="values">
                                                    {foreach $oMerkmale_arr->oMerkmalWert_arr as $oWert}
                                                        {if !$oWert@first}, {/if}
                                                        {$oWert->cWert}
                                                    {/foreach}
                                                </span>
                                            </li>
                                        {/foreach}
                                    {/if}

                                    {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelattribute == 'Y' && !empty($oPosition->Artikel->Attribute)}
                                        {foreach $oPosition->Artikel->Attribute as $oAttribute_arr}
                                            <li class="attribute">
                                                <strong>{$oAttribute_arr->cName}</strong>:
                                                <span class="values">
                                                    {$oAttribute_arr->cWert}
                                                </span>
                                            </li>
                                        {/foreach}
                                    {/if}

                                    {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelkurzbeschreibung == 'Y' && $oPosition->Artikel->cKurzBeschreibung|strlen > 0}
                                        <li class="shortdescription">{$oPosition->Artikel->cKurzBeschreibung}</li>
                                    {/if}

                                    {if isset($oPosition->Artikel->cGewicht) && $Einstellungen.artikeldetails.artikeldetails_gewicht_anzeigen === 'Y' && $oPosition->Artikel->fGewicht > 0}
                                        <li class="weight">
                                            <strong>{lang key='shippingWeight'}: </strong>
                                            <span class="value">{$oPosition->Artikel->cGewicht} {lang key='weightUnit'}</span>
                                        </li>
                                    {/if}
                                </ul>
                            {/block}
                        {else}
                            {block name='basket-cart-items-is-not-product'}
                                {$oPosition->cName|trans}{if isset($oPosition->discountForArticle)}{$oPosition->discountForArticle|trans}{/if}
                                {if isset($oPosition->cArticleNameAffix)}
                                    {if is_array($oPosition->cArticleNameAffix)}
                                        <ul class="small text-muted">
                                            {foreach $oPosition->cArticleNameAffix as $cArticleNameAffix}
                                                <li>{$cArticleNameAffix|trans}</li>
                                            {/foreach}
                                        </ul>
                                    {else}
                                        <ul class="small text-muted">
                                            <li>{$oPosition->cArticleNameAffix|trans}</li>
                                        </ul>
                                    {/if}
                                {/if}
                                {if !empty($oPosition->cHinweis)}
                                    <small class="text-info notice">{$oPosition->cHinweis}</small>
                                {/if}
                            {/block}
                        {/if}

                        {if $oPosition->istKonfigVater()}
                            {block name='basket-cart-items-product-cofig-items'}
                                <ul class="config-items text-muted small">
                                    {$labeled=false}
                                    {foreach $smarty.session.Warenkorb->PositionenArr as $KonfigPos}
                                        {block name='product-config-item'}
                                            {if $oPosition->cUnique == $KonfigPos->cUnique && $KonfigPos->kKonfigitem > 0
                                            && !$KonfigPos->isIgnoreMultiplier()}
                                                <li>
                                                    <span class="qty">{if !$KonfigPos->istKonfigVater()}{$KonfigPos->nAnzahlEinzel}{else}1{/if}x</span>
                                                    {$KonfigPos->cName|trans} &raquo;
                                                    <span class="price_value">
                                                        {$KonfigPos->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                                        {lang key='pricePerUnit' section='checkout'}
                                                    </span>
                                                </li>
                                            {elseif $oPosition->cUnique == $KonfigPos->cUnique && $KonfigPos->kKonfigitem > 0
                                            && $KonfigPos->isIgnoreMultiplier()}
                                                {if !$labeled}
                                                    <strong>{lang key='one-off' section='checkout'}</strong>
                                                    {$labeled=true}
                                                {/if}
                                                <li>
                                                    <span class="qty">{if !$KonfigPos->istKonfigVater()}{$KonfigPos->nAnzahlEinzel}{else}1{/if}x</span>
                                                    {$KonfigPos->cName|trans} &raquo;
                                                    <span class="price_value">
                                                        {$KonfigPos->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                                        {lang key='pricePerUnit' section='checkout'}
                                                    </span>
                                                </li>
                                            {/if}
                                        {/block}
                                    {/foreach}
                                </ul>
                            {/block}
                        {/if}

                        {if !empty($oPosition->Artikel->kStueckliste) && !empty($oPosition->Artikel->oStueckliste_arr)}
                            {block name='basket-cart-items-product-partlist-items'}
                                <ul class="partlist-items text-muted small">
                                    {foreach $oPosition->Artikel->oStueckliste_arr as $partListItem}
                                        <li>
                                            <span class="qty">{$partListItem->fAnzahl_stueckliste}x</span>
                                            {$partListItem->cName|trans}
                                        </li>
                                    {/foreach}
                                </ul>
                            {/block}
                        {/if}
                        {/col}

                        {block name='basket-cart-items-price-single'}
                            {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen === 'Y'}
                                {col cols=$cols xl=2 class="ml-auto text-nowrap mb-3 mb-xl-0"}
                                {if $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_ARTIKEL}
                                    {if !$oPosition->istKonfigVater()}
                                        <span class="mr-3 d-inline-flex d-xl-none">{lang key="pricePerUnit" section="productDetails"}:</span>{$oPosition->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                    {/if}
                                {/if}
                                {/col}
                            {/if}
                        {/block}

                        {col cols=$cols xl=3 class="ml-auto text-center mb-4 mb-xl-0 text-nowrap"}
                        {block name='basket-cart-items-quantity'}
                            {if $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_ARTIKEL}
                                {if $oPosition->istKonfigVater()}
                                    <div class="qty-wrapper max-w-sm">
                                        {$oPosition->nAnzahl|replace_delim} {if !empty($oPosition->Artikel->cEinheit)}{$oPosition->Artikel->cEinheit}{/if}
                                        {link class="btn btn-outline-secondary configurepos ml-3"
                                        href="index.php?a={$oPosition->kArtikel}&ek={$oPosition@index}"}
                                            <i class="fa fa-cogs"></i><span class="ml-1">{lang key='configure'}</span>
                                        {/link}
                                    </div>
                                {else}
                                    <div class="qty-wrapper dropdown max-w-sm">
                                        {inputgroup id="quantity-grp{$oPosition@index}" class="choose_quantity"}
                                        {input type="{if $oPosition->Artikel->cTeilbar === 'Y' && $oPosition->Artikel->fAbnahmeintervall == 0}text{else}number{/if}"
                                            min="{if $oPosition->Artikel->fMindestbestellmenge}{$oPosition->Artikel->fMindestbestellmenge}{else}0{/if}"
                                            required=($oPosition->Artikel->fAbnahmeintervall > 0)
                                            step="{if $oPosition->Artikel->fAbnahmeintervall > 0}{$oPosition->Artikel->fAbnahmeintervall}{/if}"
                                            id="quantity[{$oPosition@index}]" class="quantity" name="anzahl[{$oPosition@index}]"
                                            aria=["label"=>"{lang key='quantity'}"]
                                            value=$oPosition->nAnzahl
                                            data=["decimals"=>{getDecimalLength quantity=$oPosition->Artikel->fAbnahmeintervall}]
                                        }
                                        {/inputgroup}
                                    </div>
                                {/if}
                            {elseif $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_GRATISGESCHENK}
                                {input name="anzahl[{$oPosition@index}]" type="hidden" value="1"}
                            {/if}
                        {/block}
                        {/col}
                    {/block}
                    {block name='basket-cart-items-order-items-price-net'}
                        {col cols=$cols xl=2 class="price-col ml-auto text-nowrap text-accent text-xl-right"}
                            <span class="price_overall text-accent">
                                {if $oPosition->istKonfigVater()}
                                    {$oPosition->cKonfigpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                {else}
                                    {$oPosition->cGesamtpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                {/if}
                            </span>
                        {/col}
                    {/block}
                    {block name='basket-cart-items-cart-submit'}
                        {if $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_ARTIKEL
                        || $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_GRATISGESCHENK
                        }
                            {col cols=$cols xl=10 class='mt-4 ml-auto' data=['toggle'=>'product-actions']}
                                {if $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_ARTIKEL}
                                    <div class="btn-scale-small d-inline-block">
                                        {include file='snippets/wishlist_button.tpl' Artikel=$oPosition->Artikel}
                                    </div>
                                    <span class="mx-2">|</span>
                                {/if}
                                {button type="submit"
                                variant="link"
                                size="sm"
                                class="pl-0 droppos border-0"
                                name="dropPos"
                                value=$oPosition@index
                                title="{lang key='delete'}"}
                                    <span class="fa fa-trash "></span> <span>{lang key='delete'}</span>
                                {/button}
                            {/col}
                        {/if}
                    {/block}
                    {block name='basket-cart-items-items-bottom-hr'}
                        {col cols=12}
                            <hr>
                        {/col}
                    {/block}
                {/row}
            {/if}
        {/foreach}
        {/block}
    {/block}
{/block}
