{block name='account-order-item-return'}
    <div class="order-items card-table">
        {block name='accountorder-item-return-items'}
            {foreach $Bestellung->Positionen as $oPosition}
                {if $oPosition->nPosTyp == $smarty.const.C_WARENKORBPOS_TYP_ARTIKEL}
                    {if !(is_string($oPosition->cUnique) && !empty($oPosition->cUnique) && (int)$oPosition->kKonfigitem > 0)}
                        {*!istKonfigKind()*}
                        {row class="type-{$oPosition->nPosTyp} order-item"}
                            {block name='accountorder-item-return-items-data'}
                                {col cols=12 md=6 lg=6 class='mb-2 mb-lg-0'}
                                    {row}
                                        {col cols=3 md=4 class='order-item-image-wrapper'}
                                            {if !empty($oPosition->Artikel->cVorschaubildURL)}
                                                {block name='accountorder-item-return-image'}
                                                    {link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|trans|escape:'html'}
                                                        {include file='snippets/image.tpl'
                                                            item=$oPosition->Artikel
                                                            sizes='(min-width: 992px) 10vw, (min-width: 768px) 17vw, 25vw'
                                                            lazy=!$oPosition@first
                                                            square=false
                                                            alt=$oPosition->cName|trans|escape:'html'
                                                        }
                                                    {/link}
                                                {/block}
                                            {/if}
                                        {/col}
                                        {col md=8}
                                            {block name='accountorder-item-return-details'}
                                                {block name='accountorder-item-return-link'}
                                                    {link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|trans|escape:'html'}{$oPosition->cName|trans}{/link}
                                                {/block}
                                                <ul class="list-unstyled text-muted-util small item-detail-list">
                                                    {block name='accountorder-item-return-sku'}
                                                        <li class="sku">{lang key='productNo' section='global'}: {$oPosition->Artikel->cArtNr}</li>
                                                    {/block}
                                                    {if isset($oPosition->Artikel->dMHD, $oPosition->Artikel->dMHD_de)}
                                                        {block name='accountorder-item-return-mhd'}
                                                            <li title="{lang key='productMHDTool' section='global'}" class="best-before">
                                                                {lang key='productMHD' section='global'}:{$oPosition->Artikel->dMHD_de}
                                                            </li>
                                                        {/block}
                                                    {/if}
                                                    {if $oPosition->Artikel->cLocalizedVPE && $oPosition->Artikel->cVPE !== 'N'}
                                                        {block name='accountorder-item-return-base-price'}
                                                            <li class="baseprice">{lang key='basePrice' section='global'}:{$oPosition->Artikel->cLocalizedVPE[$NettoPreise]}
                                                            </li>
                                                        {/block}
                                                    {/if}
                                                    {if $Einstellungen.kaufabwicklung.warenkorb_varianten_varikombi_anzeigen === 'Y' && isset($oPosition->WarenkorbPosEigenschaftArr) && !empty($oPosition->WarenkorbPosEigenschaftArr)}
                                                        {block name='accountorder-item-return-variations'}
                                                            {foreach $oPosition->WarenkorbPosEigenschaftArr as $Variation}
                                                                <li class="variation">
                                                                    {$Variation->cEigenschaftName|trans}: {$Variation->cEigenschaftWertName|trans}
                                                                    {if !empty($Variation->cAufpreisLocalized[$NettoPreise])}&raquo;
                                                                        {if substr($Variation->cAufpreisLocalized[$NettoPreise], 0, 1) !== '-'}+{/if}{$Variation->cAufpreisLocalized[$NettoPreise]}
                                                                    {/if}
                                                                </li>
                                                            {/foreach}
                                                        {/block}
                                                    {/if}
                                                    {if !empty($oPosition->cHinweis)}
                                                        {block name='accountorder-item-return-notice'}
                                                            <li class="text-info notice">{$oPosition->cHinweis}</li>
                                                        {/block}
                                                    {/if}

                                                    {* Buttonloesung eindeutige Merkmale *}
                                                    {if $oPosition->Artikel->cHersteller && $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen != "N"}
                                                        {block name='accountorder-item-return-manufacturer'}
                                                            <li class="manufacturer">
                                                                {lang key='manufacturer' section='productDetails'}:
                                                                <span class="values">
                                                                    {$oPosition->Artikel->cHersteller}
                                                                </span>
                                                            </li>
                                                        {/block}
                                                    {/if}

                                                    {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelmerkmale == 'Y' && !empty($oPosition->Artikel->oMerkmale_arr)}
                                                        {block name='accountorder-item-return-characteristics'}
                                                            {foreach $oPosition->Artikel->oMerkmale_arr as $characteristic}
                                                                <li class="characteristic">
                                                                    {$characteristic->getName()}:
                                                                    <span class="values">
                                                                        {foreach $characteristic->getCharacteristicValues() as $characteristicValue}
                                                                            {if !$characteristicValue@first}, {/if}
                                                                            {$characteristicValue->getValue()}
                                                                        {/foreach}
                                                                    </span>
                                                                </li>
                                                            {/foreach}
                                                        {/block}
                                                    {/if}

                                                    {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelattribute == 'Y' && !empty($oPosition->Artikel->Attribute)}
                                                        {block name='accountorder-item-return-attributes'}
                                                            {foreach $oPosition->Artikel->Attribute as $oAttribute_arr}
                                                                <li class="attribute">
                                                                    {$oAttribute_arr->cName}:
                                                                    <span class="values">
                                                                        {$oAttribute_arr->cWert}
                                                                    </span>
                                                                </li>
                                                            {/foreach}
                                                        {/block}
                                                    {/if}

                                                    {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelkurzbeschreibung == 'Y' && $oPosition->Artikel->cKurzBeschreibung !== null && $oPosition->Artikel->cKurzBeschreibung|strlen > 0}
                                                        {block name='accountorder-item-return-short-desc'}
                                                            <li class="shortdescription">{$oPosition->Artikel->cKurzBeschreibung}</li>
                                                        {/block}
                                                    {/if}
                                                    {block name='accountorder-item-return-delivery-status'}
                                                        <li class="delivery-status">{lang key='orderStatus' section='login'}:
                                                            <strong>
                                                                {if $oPosition->bAusgeliefert}
                                                                    {lang key='statusShipped' section='order'}
                                                                {elseif $oPosition->nAusgeliefert > 0}
                                                                    {if $oPosition->cUnique|strlen == 0}{lang key='statusShipped' section='order'}:
                                                                    {$oPosition->nAusgeliefertGesamt}{else}
                                                                        {lang key='statusPartialShipped' section='order'}
                                                                    {/if}
                                                                {else}
                                                                    {lang key='notShippedYet' section='login'}
                                                                {/if}
                                                            </strong>
                                                        </li>
                                                    {/block}
                                                    {if $oPosition->nAusgeliefertGesamt > 0}
                                                        {block name='accountorder-item-return-quantity-bought'}
                                                            <li>
                                                            {lang key='rma_qty_sent' section='rma'}:
                                                                <strong>{$oPosition->nAusgeliefertGesamt}</strong>
                                                            </li>
                                                        {/block}
                                                    {/if}
                                                    {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen === 'Y'}
                                                        {block name='accountorder-item-return-price-single-price'}
                                                            <li>
                                                                {lang key='price'}: 
                                                                <strong>
                                                                    {if !(is_string($oPosition->cUnique) && !empty($oPosition->cUnique) && (int)$oPosition->kKonfigitem === 0)}
                                                                        {*!istKonfigVater()*}
                                                                        {$oPosition->cEinzelpreisLocalized[$NettoPreise]}
                                                                    {else}
                                                                        {$oPosition->cKonfigeinzelpreisLocalized[$NettoPreise]}
                                                                    {/if}
                                                                </strong>
                                                            </li>
                                                        {/block}
                                                    {/if}
                                                    {block name='accountorder-item-return-price-overall'}
                                                        <li>
                                                        {lang key='priceForAll' section='productDetails'}: 
                                                            <strong class="price_overall">
                                                                {if is_string($oPosition->cUnique) && !empty($oPosition->cUnique) && (int)$oPosition->kKonfigitem === 0}
                                                                    {$oPosition->cKonfigpreisLocalized[$NettoPreise]}
                                                                {else}
                                                                    {$oPosition->cGesamtpreisLocalized[$NettoPreise]}
                                                                {/if}
                                                            </strong>
                                                        </li>
                                                    {/block}
                                                </ul>
                                            {/block}

                                            {if is_string($oPosition->cUnique) && !empty($oPosition->cUnique) && (int)$oPosition->kKonfigitem === 0}
                                                {*istKonfigVater()*}
                                                {block name='accountorder-item-return-config-items'}
                                                    <ul class="config-items text-muted-util small">
                                                        {foreach $Bestellung->Positionen as $KonfigPos}
                                                            {if $oPosition->cUnique == $KonfigPos->cUnique && $KonfigPos->kKonfigitem > 0}
                                                                <li>
                                                                    <span
                                                                        class="qty">{if !(is_string($oPosition->cUnique) && !empty($oPosition->cUnique) && (int)$oPosition->kKonfigitem === 0)}{$KonfigPos->nAnzahlEinzel}{else}1{/if}x</span>
                                                                    {$KonfigPos->cName|trans} &raquo;<br />
                                                                    <span class="price_value">
                                                                        {if substr($KonfigPos->cEinzelpreisLocalized[$NettoPreise], 0, 1) !== '-'}+{/if}{$KonfigPos->cEinzelpreisLocalized[$NettoPreise]}
                                                                        {lang key='pricePerUnit' section='checkout'}
                                                                    </span>
                                                                </li>
                                                            {/if}
                                                        {/foreach}
                                                    </ul>
                                                {/block}
                                            {/if}
                                        {/col}
                                    {/row}
                                {/col}
                            {/block}
                            {if $oPosition->nAusgeliefertGesamt > 0}
                                {col class='qty-col text-right-util' lg=6 md=6 cols=12}
                                    {row}
                                        {block name='accountorder-item-return-price'}
                                            {block name='accountorder-item-return-price-qty'}
                                                {col class='qty-col text-right-util' lg=6 md=6 cols=12}
                                                    <div class="qty-wrapper dropdown max-w-md">
                                                        {inputgroup id="quantity-grp{$oPosition@index}" class="form-counter choose_quantity"}
                                                            {inputgroupprepend}
                                                                {button variant="" class="btn-decrement"
                                                                    data=["count-down"=>""]
                                                                    aria=["label"=>{lang key='decreaseQuantity' section='aria'}]}
                                                                    <span class="fas fa-minus"></span>
                                                                {/button}
                                                            {/inputgroupprepend}
                                                            {*$oPosition->nAusgeliefertGesamt oder nur nAusgeliefert ?*}
                                                            {input type="number"
                                                                min="0"
                                                                max=$oPosition->nAusgeliefertGesamt|default:''
                                                                required=false
                                                                step="{if $oPosition->Artikel->cTeilbar === 'Y' && $oPosition->Artikel->fAbnahmeintervall == 0}any{elseif $oPosition->Artikel->fAbnahmeintervall > 0}{$oPosition->Artikel->fAbnahmeintervall}{else}1{/if}"
                                                                id="quantity[{$oPosition@index}]" class="quantity" name="anzahl[{$oPosition@index}]"
                                                                aria=["label"=>"{lang key='quantity'}"]
                                                                value=0
                                                                data=[
                                                                    "decimals"=>{getDecimalLength quantity=$oPosition->Artikel->fAbnahmeintervall},
                                                                    "product-id"=>"{if isset($oPosition->Artikel->kVariKindArtikel)}{$oPosition->Artikel->kVariKindArtikel}{else}{$oPosition->Artikel->kArtikel}{/if}"
                                                                ]
                                                            }
                                                            {inputgroupappend}
                                                                {button variant="" class="btn-increment"
                                                                    data=["count-up"=>""]
                                                                    aria=["label"=>{lang key='increaseQuantity' section='aria'}]}
                                                                    <span class="fas fa-plus"></span>
                                                                {/button}
                                                            {/inputgroupappend}
                                                        {/inputgroup}
                                                    </div>

                                                    {if !empty($oPosition->Artikel->cEinheit)}{if preg_match("/(\d)/", $oPosition->Artikel->cEinheit)} x{/if}
                                                    {$oPosition->Artikel->cEinheit} {/if}
                                                {/col}
                                            {/block}
                                        {/block}

                                        {block name='accountorder-item-return-return'}
                                            {col class='return-col text-nowrap-util text-right-util mt-3 mt-md-0' lg=6 md=6 cols=12}
                                                {select name="kGrund[{$oPosition@index}]" class="custom-select" aria=["label"=>"{lang key='rma_reason' section='rma'}"]}
                                                    <option value="-1" selected>{lang key='rma_reason' section='rma'}</option>
                                                    <option value="1">{lang key='rma_reason_defect' section='rma'}</option>
                                                    <option value="2">{lang key='rma_reason_dont_like' section='rma'}</option>
                                                    <option value="3">{lang key='rma_reason_missing_parts' section='rma'}</option>
                                                {/select}
                                            {/col}
                                        {/block}

                                        {block name='accountorder-item-return-return'}
                                            {col class='return-col text-nowrap-util text-right-util mt-3' lg=12 md=12 cols=12}
                                                {formgroup
                                                    id="commentText"
                                                    class="{if $nPlausiValue_arr.cKommentar > 0} has-error{/if}"
                                                    label="<strong>{lang key='rma_reason' section='rma'}</strong>"
                                                    label-for="comment-text"
                                                    label-class="commentForm"
                                                }
                                                    {if false}
                                                        <div class="form-error-msg"><i class="fas fa-exclamation-triangle"></i>
                                                            {lang key='fillOut' section='global'}
                                                        </div>
                                                    {/if}
                                                    {textarea id="comment-text" name="cKommentar[{$oPosition@index}]"}{/textarea}
                                                {/formgroup}
                                            {/col}
                                        {/block}

                                    {/row}
                                {/col}
                            {/if}
                        {/row}
                        {block name='accountorder-item-return-last-hr'}
                            <hr>
                        {/block}
                    {/if}
                {/if}
            {/foreach}
        {/block}
    </div>
{/block}