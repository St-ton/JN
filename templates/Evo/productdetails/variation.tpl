{if isset($Artikel->Variationen) && $Artikel->Variationen|@count > 0 && !$showMatrix}
    {assign var="VariationsSource" value="Variationen"}
    {if isset($ohneFreifeld) && $ohneFreifeld}
        {assign var="VariationsSource" value="VariationenOhneFreifeld"}
    {/if}
    {assign var="oVariationKombi_arr" value=$Artikel->getChildVariations()}
    <div class="variations {if $simple}simple{else}switch{/if}-variations top15 row">
        <div class="col-xs-12">
            <dl>
            {foreach name=Variationen from=$Artikel->$VariationsSource key=i item=Variation}
            {strip}
                {if !isset($smallView) || !$smallView}
                <dt>{$Variation->cName}{if $Variation->cTyp === 'IMGSWATCHES'} <span class="swatches-selected text-muted" data-id="{$Variation->kEigenschaft}"></span>{/if}</dt>
                {/if}
                <dd class="form-group{if $Variation->cTyp !== 'FREIFELD' && !$showMatrix} required{/if}">
                    {if $Variation->cTyp === 'SELECTBOX'}
                        {block name="productdetails-info-variation-select"}
                        <select class="form-control" title="{if isset($smallView) && $smallView}{$Variation->cName} - {/if}{lang key="pleaseChooseVariation" section="productDetails"}" name="eigenschaftwert[{$Variation->kEigenschaft}]"{if !$showMatrix} required{/if}>
                            {foreach name=Variationswerte from=$Variation->Werte key=y item=Variationswert}
                                {assign var="bSelected" value=false}
                                {if isset($oVariationKombi_arr[$Variationswert->kEigenschaft])}
                                    {assign var="bSelected" value=in_array($Variationswert->kEigenschaftWert, $oVariationKombi_arr[$Variationswert->kEigenschaft])}
                                {/if}
                                {if isset($oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft])}
                                    {assign var="bSelected" value=$Variationswert->kEigenschaftWert == $oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft]->kEigenschaftWert}
                                {/if}
                                {if ($Artikel->kVaterArtikel > 0 || $Artikel->nIstVater == 1) && $Artikel->nVariationOhneFreifeldAnzahl == 1 &&
                                $Einstellungen.global.artikeldetails_variationswertlager == 3 &&
                                !empty($Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar) && $Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar == 1}
                                {else}
                                    {include file="productdetails/variation_value.tpl" assign="cVariationsWert"}
                                    <option value="{$Variationswert->kEigenschaftWert}" class="variation"
                                            data-type="option"
                                            data-original="{$Variationswert->cName}"
                                            data-key="{$Variationswert->kEigenschaft}"
                                            data-value="{$Variationswert->kEigenschaftWert}"
                                            data-content="{$cVariationsWert|escape:'html'}"
                                            {if !empty($Variationswert->cBildPfadMini)}
                                                data-list='{prepare_image_details item=$Variationswert json=true}'
                                                data-title='{$Variationswert->cName}'
                                            {/if}
                                            {if isset($Variationswert->oVariationsKombi)}
                                                data-ref="{$Variationswert->oVariationsKombi->kArtikel}"
                                            {/if}
                                            {if $bSelected} selected="selected"{/if}>
                                        {$cVariationsWert|trim}
                                    </option>
                                {/if}
                            {/foreach}
                        </select>
                        {/block}
                    {elseif $Variation->cTyp === 'RADIO'}
                        {foreach name=Variationswerte from=$Variation->Werte key=y item=Variationswert}
                            {assign var="bSelected" value=false}
                            {if isset($oVariationKombi_arr[$Variationswert->kEigenschaft])}
                               {assign var="bSelected" value=in_array($Variationswert->kEigenschaftWert, $oVariationKombi_arr[$Variationswert->kEigenschaft])}
                            {/if}
                            {if isset($oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft])}
                                {assign var="bSelected" value=$Variationswert->kEigenschaftWert == $oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft]->kEigenschaftWert}
                            {/if}
                            {if ($Artikel->kVaterArtikel > 0 || $Artikel->nIstVater == 1) && $Artikel->nVariationOhneFreifeldAnzahl == 1 &&
                            $Einstellungen.global.artikeldetails_variationswertlager == 3 &&
                            !empty($Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar) && $Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar == 1}
                            {else}
                                {block name="productdetails-info-variation-radio"}
                                <label class="variation" for="vt{$Variationswert->kEigenschaftWert}"
                                       data-type="radio"
                                       data-original="{$Variationswert->cName}"
                                       data-key="{$Variationswert->kEigenschaft}"
                                       data-value="{$Variationswert->kEigenschaftWert}"
                                       {if !empty($Variationswert->cBildPfadMini)}
                                            data-list='{prepare_image_details item=$Variationswert json=true}'
                                            data-title='{$Variationswert->cName}'
                                       {/if}
                                       {if isset($Variationswert->oVariationsKombi)}
                                            data-ref="{$Variationswert->oVariationsKombi->kArtikel}"
                                       {/if}>
                                    <input type="radio"
                                           name="eigenschaftwert[{$Variation->kEigenschaft}]"
                                           id="vt{$Variationswert->kEigenschaftWert}"
                                           value="{$Variationswert->kEigenschaftWert}"
                                           {if $bSelected}checked="checked"{/if}
                                           {if $smarty.foreach.Variationswerte.index === 0 && !$showMatrix} required{/if}
                                           >
                                    {include file="productdetails/variation_value.tpl"}
                                </label>
                                {/block}
                            {/if}
                        {/foreach}
                    {elseif $Variation->cTyp === 'IMGSWATCHES' || $Variation->cTyp === 'TEXTSWATCHES'}
                        <div class="btn-group swatches {$Variation->cTyp|lower}">
                            {foreach name=Variationswerte from=$Variation->Werte key=y item=Variationswert}
                                {assign var="bSelected" value=false}
                                {if isset($oVariationKombi_arr[$Variationswert->kEigenschaft])}
                                    {assign var="bSelected" value=in_array($Variationswert->kEigenschaftWert, $oVariationKombi_arr[$Variationswert->kEigenschaft])}
                                {/if}
                                {if isset($oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft])}
                                    {assign var="bSelected" value=($Variationswert->kEigenschaftWert == $oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft]->kEigenschaftWert)}
                                {/if}
                                {if ($Artikel->kVaterArtikel > 0 || $Artikel->nIstVater == 1) && $Artikel->nVariationOhneFreifeldAnzahl == 1 &&
                                $Einstellungen.global.artikeldetails_variationswertlager == 3 &&
                                !empty($Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar) && $Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar == 1}
                                    {* /do nothing *}
                                {else}
                                    {block name="productdetails-info-variation-swatch"}
                                    <label class="variation block btn btn-default{if $bSelected} active{/if}"
                                            data-type="swatch"
                                            data-original="{$Variationswert->cName}"
                                            data-key="{$Variationswert->kEigenschaft}"
                                            data-value="{$Variationswert->kEigenschaftWert}"
                                            for="vt{$Variationswert->kEigenschaftWert}"
                                            {if !empty($Variationswert->cBildPfadMini)}
                                                data-list='{prepare_image_details item=$Variationswert json=true}'
                                                data-title='{$Variationswert->cName}'
                                            {/if}
                                            {if isset($Variationswert->oVariationsKombi)}
                                                data-ref="{$Variationswert->oVariationsKombi->kArtikel}"
                                            {/if}>
                                        <input type="radio"
                                               class="control-hidden"
                                               name="eigenschaftwert[{$Variation->kEigenschaft}]"
                                               id="vt{$Variationswert->kEigenschaftWert}"
                                               value="{$Variationswert->kEigenschaftWert}"
                                               {if $bSelected}checked="checked"{/if}
                                               {if $smarty.foreach.Variationswerte.index === 0 && !$showMatrix} required{/if}
                                               />
                                       <span class="label-variation">
                                            {if !empty($Variationswert->cBildPfadMini)}
                                                <img src="{$Variationswert->cBildPfadMini}" alt="{$Variationswert->cName|escape:'quotes'}"
                                                     data-list='{prepare_image_details item=$Variationswert json=true}'
                                                     title="{$Variationswert->cName}" />
                                            {else}
                                                {$Variationswert->cName}
                                            {/if}
                                        </span>
                                        {include file="productdetails/variation_value.tpl" hideVariationValue=true}
                                    </label>
                                    {/block}
                                {/if}
                            {/foreach}
                        </div>
                    {elseif $Variation->cTyp === 'FREIFELD' || $Variation->cTyp === 'PFLICHT-FREIFELD'}
                        {block name="productdetails-info-variation-text"}
                        <input type="text"
                           class="form-control"
                           name="eigenschaftwert[{$Variation->kEigenschaft}]"
                           value="{if isset($oEigenschaftWertEdit_arr[$Variation->kEigenschaft])}{$oEigenschaftWertEdit_arr[$Variation->kEigenschaft]->cEigenschaftWertNameLocalized}{/if}"
                           data-key="{$Variation->kEigenschaft}"{if $Variation->cTyp === 'PFLICHT-FREIFELD'} required{/if}>
                        {/block}
                    {/if}
                </dd>
            {/strip}
            {/foreach}
            </dl>
        </div>
    </div>
{/if}