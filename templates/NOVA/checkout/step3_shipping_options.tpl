{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-step3-shipping-options'}
    {row}
        {col cols=8}
            {if !isset($Versandarten)}
                {block name='checkout-step3-shipping-options-alert'}
                    {alert variant="danger"}{lang key='noShippingMethodsAvailable' section='checkout'}{/alert}
                {/block}
            {else}
                {block name='checkout-step3-shipping-options-form'}
                    {form method="post" action="{get_static_route id='bestellvorgang.php'}" class="form evo-validate"}
                        {block name='checkout-step3-shipping-options-fieldset-shipping-payment'}
                            <fieldset id="checkout-shipping-payment">
                                {block name='checkout-step3-shipping-options-legend-shipping-options'}
                                    <legend>{lang key='shippingOptions'}</legend>
                                {/block}
                                {block name='checkout-step3-shipping-options-shipping-address-link'}
                                    <span>
                                        {lang key='shippingTo' section='checkout'}:
                                        {link href="{get_static_route id='bestellvorgang.php'}?editLieferadresse=1"}
                                            {$Lieferadresse->cStrasse}, {$Lieferadresse->cPLZ} {$Lieferadresse->cOrt}, {$Lieferadresse->cLand}
                                        {/link}
                                        <span class="ml-1 fa fa-pencil-alt"></span>
                                    </span>
                                    <hr class="mb-3 mt-1">
                                {/block}
                                <div class="mb-3 form-group">
                                    {radiogroup stacked=true}
                                        {foreach $Versandarten as $versandart}
                                            {block name='checkout-step3-shipping-options-shipment'}
                                                <div id="shipment_{$versandart->kVersandart}">
                                                    {radio
                                                        name="Versandart"
                                                        value=$versandart->kVersandart
                                                        id="del{$versandart->kVersandart}"
                                                        checked=($Versandarten|@count == 1 || $AktiveVersandart == $versandart->kVersandart)
                                                        required=($versandart@first)
                                                        class="w-100"
                                                    }
                                                        <div class="row">
                                                            <span class="content">
                                                                <span class="title">{$versandart->angezeigterName|trans}</span>
                                                                <small class="desc text-info">{$versandart->cLieferdauer|trans}</small>
                                                            </span>
                                                            {if $versandart->cBild}
                                                                {image fluid=true class="img-sm" src=$versandart->cBild alt=$versandart->angezeigterName|trans}
                                                            {/if}
                                                                <span class="content text-muted">
                                                                    {$versandart->angezeigterHinweistext|trans}
                                                                </span>
                                                                <span class="badge badge-pill badge-primary float-right">{$versandart->cPreisLocalized}</span>
                                                                {if isset($versandart->specificShippingcosts_arr)}
                                                                    {foreach $versandart->specificShippingcosts_arr as $specificShippingcosts}
                                                                        {block name='checkout-step3-shipping-options-shipping-cost'}
                                                                            {row}
                                                                                {col cols=8 md=9 lg=9}
                                                                                    <ul>
                                                                                        <li>
                                                                                            <small>{$specificShippingcosts->cName|trans}</small>
                                                                                        </li>
                                                                                    </ul>
                                                                                {/col}
                                                                                {col cols=4 md=3 lg=3 cclass="text-right"}
                                                                                    <small>
                                                                                        {$specificShippingcosts->cPreisLocalized}
                                                                                    </small>
                                                                                {/col}
                                                                            {/row}
                                                                        {/block}
                                                                    {/foreach}
                                                                {/if}
                                                                {if !empty($versandart->Zuschlag->fZuschlag)}
                                                                <span class="btn-block">
                                                                    <small>{$versandart->Zuschlag->angezeigterName|trans}
                                                                        (+{$versandart->Zuschlag->cPreisLocalized})
                                                                    </small>
                                                                </span>
                                                                {/if}
                                                                {if !empty($versandart->cLieferdauer|trans) && $Einstellungen.global.global_versandermittlung_lieferdauer_anzeigen === 'Y'}
                                                                <span class="btn-block">
                                                                    <small>{lang key='shippingTimeLP'}
                                                                        : {$versandart->cLieferdauer|trans}</small>
                                                                </span>
                                                                {/if}
                                                        </div>
                                                    {/radio}
                                                </div>
                                            {/block}
                                        {/foreach}
                                    {/radiogroup}
                                </div>
                            </fieldset>
                        {/block}
                        {block name='checkout-step3-shipping-options-fieldset-payment'}
                            <fieldset id="fieldset-payment">
                                {block name='checkout-step3-shipping-options-legend-payment'}
                                    <legend>{lang key='paymentOptions'}</legend>
                                {/block}
                                {$step4_payment_content}
                            </fieldset>
                        {/block}
                        {if isset($Verpackungsarten) && $Verpackungsarten|@count > 0}
                            {block name='checkout-step3-shipping-options-fieldset-packaging-types'}
                                <fieldset>
                                    {block name='checkout-step3-shipping-options-legend-packaging-types'}
                                        <legend>{lang section='checkout' key='additionalPackaging'}</legend>
                                    {/block}
                                    {checkboxgroup stacked=true}
                                    {foreach $Verpackungsarten as $oVerpackung}
                                        {block name='checkout-step3-shipping-options-packaging'}
                                            <div class="mb-3">
                                            {checkbox
                                                name="kVerpackung[]"
                                                value=$oVerpackung->kVerpackung
                                                id="pac{$oVerpackung->kVerpackung}"
                                                checked=(isset($oVerpackung->bWarenkorbAktiv) && $oVerpackung->bWarenkorbAktiv === true || (isset($AktiveVerpackung[$oVerpackung->kVerpackung]) && $AktiveVerpackung[$oVerpackung->kVerpackung] === 1))
                                            }
                                                <span class="content">
                                                            <span class="title">{$oVerpackung->cName}</span>
                                                        </span>
                                                <span class="float-right">
                                                            {if $oVerpackung->nKostenfrei == 1}{lang key='ExemptFromCharges'}{else}{$oVerpackung->fBruttoLocalized}{/if}
                                                        </span>
                                                <span class="btn-block">
                                                            <small>{$oVerpackung->cBeschreibung}</small>
                                                        </span>
                                            {/checkbox}
                                            </div>
                                        {/block}
                                    {/foreach}
                                    {/checkboxgroup}
                                </fieldset>
                            {/block}
                        {/if}
                        {if isset($Versandarten)}
                            {block name='checkout-step3-shipping-options-shipping-type-submit'}
                                <div class="mt-4">
                                    {button type="link" href="{get_static_route id='bestellvorgang.php'}?editRechnungsadresse=1" variant="secondary"}
                                        {lang key='back'}
                                    {/button}
                                    {input type="hidden" name="versandartwahl" value="1"}
                                    {input type="hidden" name="zahlungsartwahl" value="1"}
                                    {button type="submit" variant="primary" class="submit_once d-none float-right"}
                                        {lang key='continueOrder' section='account data'}
                                    {/button}
                                </div>
                            {/block}
                        {/if}
                    {/form}
                {/block}
            {/if}
        {/col}
    {/row}
    {if isset($smarty.get.editZahlungsart)}
        {block name='checkout-step3-shipping-options-script-scroll'}
            {literal}
                <script type="text/javascript">
                    $(document).ready(function () {
                        $.evo.extended().smoothScrollToAnchor('#fieldset-payment');
                    });
                </script>
            {/literal}
        {/block}
    {/if}
{/block}
