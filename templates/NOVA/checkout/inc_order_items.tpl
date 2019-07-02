{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-inc-order-items'}
    {input type="submit" name="fake" class="d-none"}
        {if $Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'Y'
            && $tplscope === 'cart'}
            {$headcols=8}{$headsm=6}
        {elseif $Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'Y'
            && $tplscope !== 'cart'}
            {$headcols=10}{$headsm=8}
        {elseif ($Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'N')
            && $tplscope === 'cart'}
            {$headcols=8}{$headsm=7}
        {elseif ($Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'N')
            && $tplscope !== 'cart'}
            {$headcols=10}{$headsm=9}
        {/if}
    {block name='checkout-inc-order-items-order-items'}
        {foreach $smarty.session.Warenkorb->PositionenArr as $oPosition}
            {if !$oPosition->istKonfigKind()}
                {row class="type-{$oPosition->nPosTyp}"}
                    {block name='checkout-inc-order-items-image'}
                        {if $Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'Y' && !empty($oPosition->Artikel->cVorschaubild)}
                            {col cols=2 class="d-none d-sm-block text-center vcenter"}
                                {link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|trans}
                                    {image src=$oPosition->Artikel->cVorschaubild alt=$oPosition->cName|trans fluid=true}
                                {/link}
                            {/col}
                        {elseif $Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'Y'}
                            {col cols=2 class="d-none d-sm-block"}{/col}
                        {/if}
                    {/block}
                    {block name='checkout-inc-order-items-items-main-content'}
                        {col cols=$headcols sm=$headsm}
                            {if $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_ARTIKEL}
                                {block name='checkout-inc-order-items-price-single'}
                                    <p>{link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|trans}{$oPosition->cName|trans}{/link}</p>
                                    {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen === 'Y'}
                                        {if $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_ARTIKEL}
                                            {if !$oPosition->istKonfigVater()}
                                                <p>{$oPosition->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}</p>
                                            {/if}
                                        {/if}
                                    {/if}
                                {/block}
                                {block name='checkout-inc-order-items-product-data'}
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
                                {block name='checkout-inc-order-items-is-not-product'}
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
                                {block name='checkout-inc-order-items-product-cofig-items'}
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
                                {block name='checkout-inc-order-items-product-partlist-items'}
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

                            {block name='checkout-inc-order-items-quantity'}
                                {if $tplscope === 'cart'}
                                    {if $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_ARTIKEL}
                                        {if !isset($Einstellungen.template.theme.qty_modify_dropdown) || $Einstellungen.template.theme.qty_modify_dropdown === 'Y'}
                                            {if $oPosition->istKonfigVater()}
                                                <div class="qty-wrapper">
                                                    {$oPosition->nAnzahl|replace_delim} {if !empty($oPosition->Artikel->cEinheit)}{$oPosition->Artikel->cEinheit}{/if}
                                                    {link class="btn btn-light configurepos ml-3"
                                                        href="index.php?a={$oPosition->kArtikel}&ek={$oPosition@index}"}
                                                        <i class="fa fa-cogs"></i><span class="d-none d-md-inline-flex ml-1">{lang key='configure'}</span>
                                                    {/link}
                                                </div>
                                            {else}
                                                <div class="qty-wrapper dropdown">
                                                    {inputgroup id="quantity-grp{$oPosition@index}" class="choose_quantity"}
                                                        {input type="{if $oPosition->Artikel->cTeilbar === 'Y' && $oPosition->Artikel->fAbnahmeintervall == 0}text{else}number{/if}"
                                                            min="{if $oPosition->Artikel->fMindestbestellmenge}{$oPosition->Artikel->fMindestbestellmenge}{else}0{/if}"
                                                            required=($oPosition->Artikel->fAbnahmeintervall > 0)
                                                            step="{if $oPosition->Artikel->fAbnahmeintervall > 0}{$oPosition->Artikel->fAbnahmeintervall}{/if}"
                                                            id="quantity[{$oPosition@index}]" class="quantity text-right" name="anzahl[{$oPosition@index}]"
                                                            aria=["label"=>"{lang key='quantity'}"]
                                                            value=$oPosition->nAnzahl
                                                            data=["decimals"=>{getDecimalLength quantity=$oPosition->Artikel->fAbnahmeintervall}]
                                                        }
                                                        {inputgroupaddon append=true}
                                                            {if $oPosition->cEinheit}
                                                                {inputgrouptext class="unit form-control"}
                                                                    {$oPosition->cEinheit}
                                                                {/inputgrouptext}
                                                            {/if}
                                                        {/inputgroupaddon}
                                                    {/inputgroup}
                                                </div>
                                            {/if}
                                        {else}
                                            {if $oPosition->istKonfigVater()}
                                                <div class="qty-wrapper">
                                                    {buttongroup vertical=true id="quantity-grp{$oPosition@index}"}
                                                        {input name="anzahl[{$oPosition@index}]" type="text" class="text-center" value=$oPosition->nAnzahl readonly=true}
                                                        {link class="btn btn-light configurepos ml-3"
                                                            href="index.php?a={$oPosition->kArtikel}&ek={$oPosition@index}"}
                                                            <span class="d-none d-sm-block d-md-none"><i class="fa fa-cogs"></i></span>
                                                            <span class="d-sm-none d-md-block">{lang key='configure'}</span>
                                                        {/link}
                                                    {/buttongroup}
                                                </div>
                                            {else}
                                                <div class="p-3">
                                                    {buttongroup vertical=true id="quantity-grp{$oPosition@index}"}
                                                        {input name="anzahl[{$oPosition@index}]" id="quantity{$oPosition@index}" class="quantity text-right" size="3" value=$oPosition->nAnzahl}
                                                        {if $oPosition->Artikel->cEinheit}
                                                            {button class="unit d-none d-sm-block " disabled=true}{$oPosition->Artikel->cEinheit}{/button}
                                                        {/if}
                                                        {button type="submit" title="{lang key='refresh' section='checkout'}"}<i class="fa fa-sync"></i>{/button}
                                                    {/buttongroup}
                                                </div>
                                            {/if}
                                        {/if}
                                    {elseif $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_GRATISGESCHENK}
                                        {input name="anzahl[{$oPosition@index}]" type="hidden" value="1"}

                                    {/if}
                                {else}
                                    <div class="text-muted small">
                                        <strong>{lang key='quantity'}: </strong>{$oPosition->nAnzahl|replace_delim} {if !empty($oPosition->Artikel->cEinheit)}{$oPosition->Artikel->cEinheit}{/if}
                                    </div>
                                {/if}
                            {/block}
                        {/col}
                    {/block}
                    {block name='checkout-inc-order-items-order-items-price-net'}
                        {col cols=2 class="price-col text-right"}
                            <strong class="price_overall">
                                {if $oPosition->istKonfigVater()}
                                    {$oPosition->cKonfigpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                {else}
                                    {$oPosition->cGesamtpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                {/if}
                            </strong>
                        {/col}
                    {/block}

                    {if $tplscope === 'cart'}
                        {block name='checkout-inc-order-items-cart-submit'}
                            {col cols=2 class="delitem-col text-right"}
                            {if $oPosition->nPosTyp == $C_WARENKORBPOS_TYP_ARTIKEL}
                                {button type="submit" variant="light" size="sm" class="droppos" name="dropPos" value=$oPosition@index title="{lang key='delete'}"}<span class="fa fa-trash"></span>{/button}
                            {/if}
                            {/col}
                        {/block}
                    {/if}
                {/row}
                {block name='checkout-inc-order-items-items-bottom-hr'}
                    <hr class="my-3">
                {/block}
            {/if}
        {/foreach}
    {/block}
    {block name='checkout-inc-order-items-price-tax'}
        {if $NettoPreise}
            {block name='checkout-inc-order-items-price-net'}
                {row class="total-net border-bottom"}
                    {col class="text-right" cols=6}
                        <span class="price_label"><strong>{lang key='totalSum'} ({lang key='net'}):</strong></span>
                    {/col}
                    {col class="text-right price-col" cols=6}
                        <strong class="price total-sum">{$WarensummeLocalized[$NettoPreise]}</strong>
                    {/col}
                {/row}
            {/block}
        {/if}

        {if $Einstellungen.global.global_steuerpos_anzeigen !== 'N' && $Steuerpositionen|@count > 0}
            {block name='checkout-inc-order-items-tax'}
                {foreach $Steuerpositionen as $Steuerposition}
                    {row class="tax border-bottom"}
                        {col class="text-right" cols=6}
                            <span class="tax_label">{$Steuerposition->cName}:</span>
                        {/col}
                        {col class="text-right price-col" cols=6}
                            <span class="tax_label">{$Steuerposition->cPreisLocalized}</span>
                        {/col}
                    {/row}
                {/foreach}
            {/block}
        {/if}

        {if isset($smarty.session.Bestellung->GuthabenNutzen) && $smarty.session.Bestellung->GuthabenNutzen == 1}
            {block name='checkout-inc-order-items-credit'}
                 {row class="customer-credit border-bottom"}
                     {col class="text-right" cols=6}
                        {lang key='useCredit' section='account data'}
                     {/col}
                     {col class="text-right" cols=6}
                         {$smarty.session.Bestellung->GutscheinLocalized}
                     {/col}
                 {/row}
            {/block}
        {/if}
        {block name='checkout-inc-order-items-price-sticky'}
            {row class="total bg-info border-top border-bottom position-sticky"}
                {col class="text-right" cols=6}
                    <span class="price_label"><strong>{lang key='totalSum'}:</strong></span>
                {/col}
                {col class="text-right price-col" cols=6}
                    <strong class="price total-sum">{$WarensummeLocalized[0]}</strong>
                {/col}
            {/row}
        {/block}
    {/block}
    {block name='checkout-inc-order-items-shipping'}
        {if isset($FavourableShipping)}
            {if $NettoPreise}
                {$shippingCosts = "`$FavourableShipping->cPriceLocalized[$NettoPreise]` {lang key='plus' section='basket'} {lang key='vat' section='productDetails'}"}
            {else}
                {$shippingCosts = $FavourableShipping->cPriceLocalized[$NettoPreise]}
            {/if}
            {row class="shipping-costs text-right"}
               {col cols=12}
                    <small>{lang|sprintf:$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL():$shippingCosts:$FavourableShipping->cCountryCode key='shippingInformationSpecific' section='basket'}</small>
                {/col}
            {/row}
        {elseif empty($FavourableShipping) && empty($smarty.session.Versandart)}
            {row class="shipping-costs text-right"}
                {col cols=12}
                    <small>{lang|sprintf:$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL() key='shippingInformation' section='basket'}</small>
                {/col}
            {/row}
        {/if}
    {/block}
{/block}
