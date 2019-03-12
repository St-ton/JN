{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{assign var=modal value=isset($smarty.get.quickView) && $smarty.get.quickView == 1}
{if isset($Artikel->Variationen) && $Artikel->Variationen|@count > 0 && !$showMatrix}
    {assign var=VariationsSource value="Variationen"}
    {if isset($ohneFreifeld) && $ohneFreifeld}
        {assign var=VariationsSource value="VariationenOhneFreifeld"}
    {/if}
    {assign var=oVariationKombi_arr value=$Artikel->getChildVariations()}
    {row}
        {col class="updatingStockInfo text-center d-none"}
            <i class="fa fa-spinner fa-spin" title="{lang key='updatingStockInformation' section='productDetails'}"></i>
        {/col}
    {/row}
    {row class="variations {if $simple}simple{else}switch{/if}-variations mb-4"}
        {col}
            <dl>
            {foreach name=Variationen from=$Artikel->$VariationsSource key=i item=Variation}
            {strip}
                {if !isset($smallView) || !$smallView}
                <dt>{$Variation->cName}{if $Variation->cTyp === 'IMGSWATCHES'} <span class="swatches-selected text-muted" data-id="{$Variation->kEigenschaft}"></span>{/if}</dt>
                {/if}
                <dd class="form-group">
                    {if $Variation->cTyp === 'SELECTBOX'}
                        {block name='productdetails-info-variation-select'}
                        {select title="{if isset($smallView) && $smallView}{$Variation->cName} - {/if}{lang key='pleaseChooseVariation' section='productDetails'}" name="eigenschaftwert[{$Variation->kEigenschaft}]" required=!$showMatrix}
                            {foreach name=Variationswerte from=$Variation->Werte key=y item=Variationswert}
                                {assign var=bSelected value=false}
                                {if isset($oVariationKombi_arr[$Variationswert->kEigenschaft])}
                                    {assign var=bSelected value=in_array($Variationswert->kEigenschaftWert, $oVariationKombi_arr[$Variationswert->kEigenschaft])}
                                {/if}
                                {if isset($oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft])}
                                    {assign var=bSelected value=$Variationswert->kEigenschaftWert == $oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft]->kEigenschaftWert}
                                {/if}
                                {if ($Artikel->kVaterArtikel > 0 || $Artikel->nIstVater == 1) && $Artikel->nVariationOhneFreifeldAnzahl == 1 &&
                                $Einstellungen.global.artikeldetails_variationswertlager == 3 &&
                                !empty($Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar) && $Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar == 1}
                                {else}
                                    {include file='productdetails/variation_value.tpl' assign='cVariationsWert'}
                                    <option value="{$Variationswert->kEigenschaftWert}" class="variation"
                                            data-type="option"
                                            data-original="{$Variationswert->cName}"
                                            data-key="{$Variationswert->kEigenschaft}"
                                            data-value="{$Variationswert->kEigenschaftWert}"
                                            data-content="{$cVariationsWert|escape:'html'}{if $Variationswert->notExists}<span class='badge badge-danger badge-not-available'> {lang key='notAvailableInSelection'}</span>{elseif !$Variationswert->inStock}<span class='badge badge-default badge-not-available'>{lang key='ampelRot'}</span>{/if}"
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
                        {/select}
                        {/block}
                    {elseif $Variation->cTyp === 'RADIO'}
                        {foreach name=Variationswerte from=$Variation->Werte key=y item=Variationswert}
                            {assign var=bSelected value=false}
                            {if isset($oVariationKombi_arr[$Variationswert->kEigenschaft])}
                               {assign var=bSelected value=in_array($Variationswert->kEigenschaftWert, $oVariationKombi_arr[$Variationswert->kEigenschaft])}
                            {/if}
                            {if isset($oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft])}
                                {assign var=bSelected value=$Variationswert->kEigenschaftWert == $oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft]->kEigenschaftWert}
                            {/if}
                            {if ($Artikel->kVaterArtikel > 0 || $Artikel->nIstVater == 1) && $Artikel->nVariationOhneFreifeldAnzahl == 1 &&
                            $Einstellungen.global.artikeldetails_variationswertlager == 3 &&
                            !empty($Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar) && $Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar == 1}
                            {else}
                                {block name='productdetails-info-variation-radio'}
                                    <div class="custom-control custom-radio mb-1">
                                        <input type="radio"
                                            class="custom-control-input"
                                            name="eigenschaftwert[{$Variation->kEigenschaft}]"
                                            id="{if $modal}modal-{elseif isset($smallView) && $smallView}a-{$Artikel->kArtikel}{/if}vt{$Variationswert->kEigenschaftWert}"
                                            value="{$Variationswert->kEigenschaftWert}"
                                            {if $bSelected}checked="checked"{/if}
                                            {if $smarty.foreach.Variationswerte.index === 0 && !$showMatrix} required{/if}
                                        >
                                        <label class="variation custom-control-label" for="{if $modal}modal-{elseif isset($smallView) && $smallView}a-{$Artikel->kArtikel}{/if}vt{$Variationswert->kEigenschaftWert}"
                                               data-type="radio"
                                               data-original="{$Variationswert->cName}"
                                               data-key="{$Variationswert->kEigenschaft}"
                                               data-value="{$Variationswert->kEigenschaftWert}"
                                               {if !empty($Variationswert->cBildPfadMini)}
                                                    data-list='{prepare_image_details item=$Variationswert json=true}'
                                                    data-title='{$Variationswert->cName}{if $Variationswert->notExists} - {lang key='notAvailableInSelection'}{elseif !$Variationswert->inStock} - {lang key='ampelRot'}{/if}'
                                               {/if}
                                               {if !$Variationswert->inStock}
                                                    data-stock="out-of-stock"
                                               {/if}
                                               {if isset($Variationswert->oVariationsKombi)}
                                                    data-ref="{$Variationswert->oVariationsKombi->kArtikel}"
                                               {/if}>

                                            {include file="productdetails/variation_value.tpl"}{if $Variationswert->notExists}<span class='badge badge-danger badge-not-available'> {lang key='notAvailableInSelection'}</span>{elseif !$Variationswert->inStock}<span class='badge badge-danger badge-not-available'>{lang key='ampelRot'}</span>{/if}
                                        </label>
                                    </div>
                                {/block}
                            {/if}
                        {/foreach}
                    {elseif $Variation->cTyp === 'IMGSWATCHES' || $Variation->cTyp === 'TEXTSWATCHES'}
                        <div class="btn-group swatches {$Variation->cTyp|lower}">
                            {foreach name=Variationswerte from=$Variation->Werte key=y item=Variationswert}
                                {assign var=bSelected value=false}
                                {if isset($oVariationKombi_arr[$Variationswert->kEigenschaft])}
                                    {assign var=bSelected value=in_array($Variationswert->kEigenschaftWert, $oVariationKombi_arr[$Variationswert->kEigenschaft])}
                                {/if}
                                {if isset($oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft])}
                                    {assign var=bSelected value=($Variationswert->kEigenschaftWert == $oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft]->kEigenschaftWert)}
                                {/if}
                                {if ($Artikel->kVaterArtikel > 0 || $Artikel->nIstVater == 1) && $Artikel->nVariationOhneFreifeldAnzahl == 1 &&
                                $Einstellungen.global.artikeldetails_variationswertlager == 3 &&
                                !empty($Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar) && $Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar == 1}
                                    {* /do nothing *}
                                {else}
                                    {block name='productdetails-info-variation-swatch'}
                                    <label class="variation block btn btn-secondary{if $bSelected} active{/if}{if $Variationswert->notExists} not-available{/if}"
                                            data-type="swatch"
                                            data-original="{$Variationswert->cName}"
                                            data-key="{$Variationswert->kEigenschaft}"
                                            data-value="{$Variationswert->kEigenschaftWert}"
                                            for="{if $modal}modal-{elseif isset($smallView) && $smallView}a-{$Artikel->kArtikel}{/if}vt{$Variationswert->kEigenschaftWert}"
                                            {if !empty($Variationswert->cBildPfadMini)}
                                                data-list='{prepare_image_details item=$Variationswert json=true}'
                                            {/if}
                                            {if $Variationswert->notExists}
                                                title="{lang key='notAvailableInSelection'}"
                                                data-title="{$Variationswert->cName} - {lang key='notAvailableInSelection'}"
                                                data-toggle="tooltip"
                                            {elseif $Variationswert->inStock}
                                                data-title="{$Variationswert->cName}"
                                            {else}
                                                title="{lang key='ampelRot'}"
                                                data-title="{$Variationswert->cName} - {lang key='ampelRot'}"
                                                data-toggle="tooltip"
                                                data-stock="out-of-stock"
                                            {/if}
                                            {if isset($Variationswert->oVariationsKombi)}
                                                data-ref="{$Variationswert->oVariationsKombi->kArtikel}"
                                            {/if}>
                                        <input type="radio"
                                               class="control-hidden"
                                               name="eigenschaftwert[{$Variation->kEigenschaft}]"
                                               id="{if $modal}modal-{elseif isset($smallView) && $smallView}a-{$Artikel->kArtikel}{/if}vt{$Variationswert->kEigenschaftWert}"
                                               value="{$Variationswert->kEigenschaftWert}"
                                               {if $bSelected}checked="checked"{/if}
                                               {if $smarty.foreach.Variationswerte.index === 0 && !$showMatrix} required{/if}
                                               />
                                       <span class="label-variation">
                                            {if !empty($Variationswert->cBildPfadMiniFull)}
                                                {image src=$Variationswert->cBildPfadMiniFull alt=$Variationswert->cName|escape:'quotes'
                                                     data=['list' => "{prepare_image_details item=$Variationswert json=true}"]
                                                     title=$Variationswert->cName}
                                            {else}
                                                {$Variationswert->cName}
                                            {/if}
                                        </span>
                                        {include file='productdetails/variation_value.tpl' hideVariationValue=true}
                                    </label>
                                    {/block}
                                {/if}
                            {/foreach}
                        </div>
                    {elseif $Variation->cTyp === 'FREIFELD' || $Variation->cTyp === 'PFLICHT-FREIFELD'}
                        {block name='productdetails-info-variation-text'}
                        {input name='eigenschaftwert['|cat:$Variation->kEigenschaft|cat:']'
                           value=$oEigenschaftWertEdit_arr[$Variation->kEigenschaft]->cEigenschaftWertNameLocalized|default:''
                           data=['key' => $Variation->kEigenschaft] required=$Variation->cTyp === 'PFLICHT-FREIFELD'}
                        {/block}
                    {/if}
                </dd>
            {/strip}
            {/foreach}
            </dl>
        {/col}
    {/row}
{/if}
