{block name='checkout-step3-shipping-options'}
    {row}
        {col cols=12 lg=9}
            {if !isset($Versandarten)}
                {block name='checkout-step3-shipping-options-alert'}
                    {alert variant="danger"}{lang key='noShippingMethodsAvailable' section='checkout'}{/alert}
                {/block}
            {else}
                {block name='checkout-step3-shipping-options-form'}
                    {form method="post" action="{get_static_route id='bestellvorgang.php'}" class="form jtl-validate mb-5"}
                        {block name='checkout-step3-shipping-options-fieldset-shipping-payment'}
                            <fieldset id="checkout-shipping-payment" class="mb-5">
                                {block name='checkout-step3-shipping-options-legend-shipping-options'}
                                    <div class="h2">{lang key='shippingOptions'}</div>
                                {/block}
                                {block name='checkout-step3-shipping-options-shipping-address-link'}
                                    <div class="mb-3">
                                        {lang key='shippingTo' section='checkout'}: {$Lieferadresse->cStrasse} {$Lieferadresse->cHausnummer}, {$Lieferadresse->cPLZ} {$Lieferadresse->cOrt}, {$Lieferadresse->cLand}
                                        {button href="{get_static_route id='bestellvorgang.php'}?editLieferadresse=1"
                                            variant="link"
                                            size="sm"
                                            class="font-size-sm"
                                        }
                                            <span class="text-decoration-underline">{lang key='change'}</span>
                                            <span class="ml-1 fa fa-pencil-alt"></span>
                                        {/button}
                                    </div>
                                {/block}
                                {block name='checkout-step3-shipping-options-shipping-address-hr'}
                                    <hr class="my-3">
                                {/block}
                                {block name='checkout-step3-shipping-options-shipping-options'}
                                    <div class="mb-3 form-group">
                                        {radiogroup stacked=true class='radio-w-100'}
                                            {foreach $Versandarten as $versandart}
                                                {block name='checkout-step3-shipping-options-shipment'}
                                                        {radio
                                                            name="Versandart"
                                                            value=$versandart->kVersandart
                                                            id="del{$versandart->kVersandart}"
                                                            checked=($Versandarten|@count == 1 || $AktiveVersandart == $versandart->kVersandart)
                                                            required=($versandart@first)
                                                            class="justify-content-between"
                                                        }
                                                            {formrow class="content"}
                                                                {block name='checkout-step3-shipping-options-shipping-option-title'}
                                                                    {col cols=12 sm=5 class='title'}
                                                                        {$versandart->angezeigterName|trans}
                                                                        {if !empty($versandart->angezeigterHinweistext|trans)}
                                                                            <div>
                                                                                <small>{$versandart->angezeigterHinweistext|trans}</small>
                                                                            </div>
                                                                        {/if}
                                                                    {/col}
                                                                {/block}
                                                                {block name='checkout-step3-shipping-options-shipping-option-info'}
                                                                    {col cols=12 sm=3}<small class="desc text-info">{$versandart->cLieferdauer|trans}</small>{/col}
                                                                {/block}
                                                                {block name='checkout-step3-shipping-options-shipping-option-price'}
                                                                    {col cols=12 sm=4 class='font-bold'}
                                                                        {$versandart->cPreisLocalized}
                                                                        {if !empty($versandart->Zuschlag->fZuschlag)}
                                                                            <div>
                                                                                <small>
                                                                                    ({$versandart->Zuschlag->angezeigterName|trans} +{$versandart->Zuschlag->cPreisLocalized})
                                                                                </small>
                                                                            </div>
                                                                        {/if}
                                                                    {/col}
                                                                {/block}
                                                            {/formrow}
                                                            <span class="btn-block">
                                                                {if isset($versandart->specificShippingcosts_arr)}
                                                                    {foreach $versandart->specificShippingcosts_arr as $specificShippingcosts}
                                                                        {block name='checkout-step3-shipping-options-shipping-option-cost'}
                                                                            {row}
                                                                                {col cols=8}
                                                                                    <ul>
                                                                                        <li>
                                                                                            <small>{$specificShippingcosts->cName|trans}</small>
                                                                                        </li>
                                                                                    </ul>
                                                                                {/col}
                                                                                {col cols=4}
                                                                                    <small>
                                                                                        {$specificShippingcosts->cPreisLocalized}
                                                                                    </small>
                                                                                {/col}
                                                                            {/row}
                                                                        {/block}
                                                                    {/foreach}
                                                                {/if}
                                                                {if !empty($versandart->cLieferdauer|trans) && $Einstellungen.global.global_versandermittlung_lieferdauer_anzeigen === 'Y'}
                                                                    {block name='checkout-step3-shipping-options-shipping-option-shipping-time'}
                                                                        <small>{lang key='shippingTimeLP'}
                                                                            : {$versandart->cLieferdauer|trans}
                                                                        </small>
                                                                    {/block}
                                                                {/if}
                                                            </span>
                                                        {/radio}
                                                {/block}
                                            {/foreach}
                                        {/radiogroup}
                                    </div>
                                {/block}
                            </fieldset>
                        {/block}
                        {block name='checkout-step3-shipping-options-fieldset-payment'}
                            <fieldset id="fieldset-payment" class="mb-5">
                                {block name='checkout-step3-shipping-options-legend-payment'}
                                    <div class="h2">{lang key='paymentOptions'}</div>
                                {/block}
                                <hr class="my-3">
                                {$step4_payment_content}
                            </fieldset>
                        {/block}
                        {if isset($Verpackungsarten) && $Verpackungsarten|@count > 0}
                            {block name='checkout-step3-shipping-options-fieldset-packaging-types'}
                                <fieldset class="mb-5">
                                    {block name='checkout-step3-shipping-options-legend-packaging-types'}
                                        <div class="h2">{lang section='checkout' key='additionalPackaging'}</div>
                                    {/block}
                                    {block name='checkout-step3-shipping-options-legend-packaging-types-hr'}
                                        <hr class="my-3">
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
                                                <strong class="ml-3 float-right">
                                                            {if $oVerpackung->nKostenfrei == 1}{lang key='ExemptFromCharges'}{else}{$oVerpackung->fBruttoLocalized}{/if}
                                                        </strong>
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
                                {row class='mt-5'}
                                    {col cols=12 md=5 class='ml-auto order-1 order-md-2'}
                                        {input type="hidden" name="versandartwahl" value="1"}
                                        {input type="hidden" name="zahlungsartwahl" value="1"}
                                        {button type="submit" variant="primary" class="submit_once d-none mb-3" block=true}
                                            {lang key='continueOrder' section='account data'}
                                        {/button}
                                    {/col}
                                    {col cols=12 md=4 class='order-2 order-md-1'}
                                        {button block=true type="link" href="{get_static_route id='bestellvorgang.php'}?editRechnungsadresse=1" variant="outline-primary"}
                                            {lang key='back'}
                                        {/button}
                                    {/col}
                                {/row}
                            {/block}
                        {/if}
                    {/form}
                {/block}
            {/if}
        {/col}
    {/row}
    {if isset($smarty.get.editZahlungsart)}
        {block name='checkout-step3-shipping-options-script-scroll'}
            {inline_script}<script>
                $(document).ready(function () {
                    $.evo.extended().smoothScrollToAnchor('#fieldset-payment');
                });
            </script>{/inline_script}
        {/block}
    {/if}
{/block}
