{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='account-order-item'}
    <div class="order-items card-table mr-3 ml-3">
        {foreach $Bestellung->Positionen as $oPosition}
            {if !(is_string($oPosition->cUnique) && !empty($oPosition->cUnique) && (int)$oPosition->kKonfigitem > 0)} {*!istKonfigKind()*}
                {row class="type-{$oPosition->nPosTyp}"}
                    {col cols=12 md="{if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen === 'Y'}7{else}9{/if}"}
                        {row}
                            {col cols=3 md=4 class='pr-1 pl-1'}
                                {if !empty($oPosition->Artikel->cVorschaubild)}
                                    {block name='account-order-item-image'}
                                        {link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|trans}
                                            {image src=$oPosition->Artikel->cVorschaubild alt=$oPosition->cName|trans fluid=true}
                                        {/link}
                                    {/block}
                                {/if}
                            {/col}
                            {col md=8}
                            {if $oPosition->nPosTyp == $smarty.const.C_WARENKORBPOS_TYP_ARTIKEL}
                                {block name='account-order-item-details'}
                                {link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|trans}{$oPosition->cName|trans}{/link}
                                <ul class="list-unstyled text-muted small mt-2">
                                    <li class="sku">{lang key='productNo' section='global'}: {$oPosition->Artikel->cArtNr}</li>
                                    {if isset($oPosition->Artikel->dMHD, $oPosition->Artikel->dMHD_de)}
                                        <li title="{lang key='productMHDTool' section='global'}" class="best-before">
                                            {lang key='productMHD' section='global'}:{$oPosition->Artikel->dMHD_de}
                                        </li>
                                    {/if}
                                    {if $oPosition->Artikel->cLocalizedVPE && $oPosition->Artikel->cVPE !== 'N'}
                                        <li class="baseprice">{lang key='basePrice' section='global'}:{$oPosition->Artikel->cLocalizedVPE[$NettoPreise]}</li>
                                    {/if}
                                    {if $Einstellungen.kaufabwicklung.warenkorb_varianten_varikombi_anzeigen === 'Y' && isset($oPosition->WarenkorbPosEigenschaftArr) && !empty($oPosition->WarenkorbPosEigenschaftArr)}
                                        {block name='account-order-item-variations'}
                                            {foreach $oPosition->WarenkorbPosEigenschaftArr as $Variation}
                                                <li class="variation">
                                                    {$Variation->cEigenschaftName|trans}: {$Variation->cEigenschaftWertName|trans} {if !empty($Variation->cAufpreisLocalized[$NettoPreise])}&raquo;
                                                        {if $Variation->cAufpreisLocalized[$NettoPreise]|substr:0:1 !== '-'}+{/if}{$Variation->cAufpreisLocalized[$NettoPreise]} {/if}
                                                </li>
                                            {/foreach}
                                        {/block}
                                    {/if}
                                    {if !empty($oPosition->cHinweis)}
                                        <li class="text-info notice">{$oPosition->cHinweis}</li>
                                    {/if}

                                    {* Buttonloesung eindeutige Merkmale *}
                                    {if $oPosition->Artikel->cHersteller && $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen != "N"}
                                        {block name='account-order-item-manufacturer'}
                                        <li class="manufacturer">
                                            {lang key='manufacturer' section='productDetails'}:
                                            <span class="values">
                                               {$oPosition->Artikel->cHersteller}
                                            </span>
                                        </li>
                                        {/block}
                                    {/if}

                                    {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelmerkmale == 'Y' && !empty($oPosition->Artikel->oMerkmale_arr)}
                                        {block name='account-order-item-characteristics'}
                                            {foreach $oPosition->Artikel->oMerkmale_arr as $oMerkmale_arr}
                                                <li class="characteristic">
                                                    {$oMerkmale_arr->cName}:
                                                    <span class="values">
                                                        {foreach $oMerkmale_arr->oMerkmalWert_arr as $oWert}
                                                            {if !$oWert@first}, {/if}
                                                            {$oWert->cWert}
                                                        {/foreach}
                                                    </span>
                                                </li>
                                            {/foreach}
                                        {/block}
                                    {/if}

                                    {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelattribute == 'Y' && !empty($oPosition->Artikel->Attribute)}
                                        {block name='account-order-item-attributes'}
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

                                    {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelkurzbeschreibung == 'Y' && $oPosition->Artikel->cKurzBeschreibung|strlen > 0}
                                        <li class="shortdescription">{$oPosition->Artikel->cKurzBeschreibung}</li>
                                    {/if}
                                    {block name='account-order-item-delivery-status'}
                                    <li class="delivery-status">{lang key='orderStatus' section='login'}:
                                        <strong>
                                        {if $oPosition->bAusgeliefert}
                                            {lang key='statusShipped' section='order'}
                                        {elseif $oPosition->nAusgeliefert > 0}
                                            {if $oPosition->cUnique|strlen == 0}{lang key='statusShipped' section='order'}: {$oPosition->nAusgeliefertGesamt}{else}{lang key='statusPartialShipped' section='order'}{/if}
                                        {else}
                                            {lang key='notShippedYet' section='login'}
                                        {/if}
                                        </strong>
                                    </li>
                                    {/block}
                                </ul>
                                {/block}
                            {else}
                                {block name='account-order-item-details-not-product'}
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

                            {if is_string($oPosition->cUnique) && !empty($oPosition->cUnique) && (int)$oPosition->kKonfigitem === 0} {*istKonfigVater()*}
                                {block name='account-order-item-config-items'}
                                    <ul class="config-items text-muted small">
                                        {foreach $Bestellung->Positionen as $KonfigPos}
                                            {if $oPosition->cUnique == $KonfigPos->cUnique && $KonfigPos->kKonfigitem > 0}
                                                <li>
                                                    <span class="qty">{if !(is_string($oPosition->cUnique) && !empty($oPosition->cUnique) && (int)$oPosition->kKonfigitem === 0)}{$KonfigPos->nAnzahlEinzel}{else}1{/if}x</span>
                                                    {$KonfigPos->cName|trans} &raquo;<br/>
                                                    <span class="price_value">
                                                        {if $KonfigPos->cEinzelpreisLocalized[$NettoPreise]|substr:0:1 !== '-'}+{/if}{$KonfigPos->cEinzelpreisLocalized[$NettoPreise]}
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

                    {block name='account-order-item-price'}
                    {col class='qty-col text-right' md=1 cols=6}
                        {$oPosition->nAnzahl|replace_delim} {if !empty($oPosition->Artikel->cEinheit)}{$oPosition->Artikel->cEinheit}{/if}x
                    {/col}
                    {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen === 'Y'}
                        {col class='price-col text-right hidden-xs text-nowrap' md=2 cols=3}
                            {if $oPosition->nPosTyp == $smarty.const.C_WARENKORBPOS_TYP_ARTIKEL}
                                {if !(is_string($oPosition->cUnique) && !empty($oPosition->cUnique) && (int)$oPosition->kKonfigitem === 0)} {*!istKonfigVater()*}
                                    {$oPosition->cEinzelpreisLocalized[$NettoPreise]}
                                {else}
                                    {$oPosition->cKonfigeinzelpreisLocalized[$NettoPreise]}
                                {/if}
                            {/if}
                        {/col}
                    {/if}
                    {col class='price-col text-right text-nowrap' md=2 cols=3}
                        <strong class="price_overall">
                            {if is_string($oPosition->cUnique) && !empty($oPosition->cUnique) && (int)$oPosition->kKonfigitem === 0}
                                {$oPosition->cKonfigpreisLocalized[$NettoPreise]}
                            {else}
                                {$oPosition->cGesamtpreisLocalized[$NettoPreise]}
                            {/if}
                        </strong>
                    {/col}
                    {/block}
                {/row}
            {/if}
            {if !$oPosition@last}
                <hr class="my-3">
            {/if}
        {/foreach}
    </div>
{/block}
