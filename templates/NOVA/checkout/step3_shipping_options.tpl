{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-step3-shipping-options'}
    {row}
        {col cols=12 lg=9}
            {if !isset($Versandarten)}
                {block name='checkout-step3-shipping-options-alert'}
                    {alert variant="danger"}{lang key='noShippingMethodsAvailable' section='checkout'}{/alert}
                {/block}
            {else}
                {block name='checkout-step3-shipping-options-form'}
                    {form method="post" action="{get_static_route id='bestellvorgang.php'}" class="form evo-validate mb-7"}
                        {block name='checkout-step3-shipping-options-fieldset-shipping-payment'}
                            <fieldset id="checkout-shipping-payment" class="mb-7">
                                {block name='checkout-step3-shipping-options-legend-shipping-options'}
                                    <div class="h2">{lang key='shippingOptions'}</div>
                                {/block}
                                {block name='checkout-step3-shipping-options-shipping-address-link'}
                                    <div class="mb-3">
                                        {lang key='shippingTo' section='checkout'}: {$Lieferadresse->cStrasse}, {$Lieferadresse->cPLZ} {$Lieferadresse->cOrt}, {$Lieferadresse->cLand}
                                        {link href="{get_static_route id='bestellvorgang.php'}?editLieferadresse=1" class="ml-3"}
                                            {lang key='edit' section='global'}
                                        {/link}
                                        <span class="ml-1 fa fa-pencil-alt"></span>
                                    </div>
                                {/block}
                                <hr class="my-3">
                                <div class="mb-3 form-group">
                                    {radiogroup stacked=true}
                                        {foreach $Versandarten as $versandart}
                                            {block name='checkout-step3-shipping-options-shipment'}
                                                <div id="shipment_{$versandart->kVersandart}" class="mb-3">
                                                    {radio
                                                        name="Versandart"
                                                        value=$versandart->kVersandart
                                                        id="del{$versandart->kVersandart}"
                                                        checked=($Versandarten|@count == 1 || $AktiveVersandart == $versandart->kVersandart)
                                                        required=($versandart@first)
                                                        class="justify-content-between"
                                                    }
                                                        <div class="content">
                                                            <span class="title">{$versandart->angezeigterName|trans}</span>
                                                            <small class="desc text-info">{$versandart->cLieferdauer|trans}</small>
                                                            <span class="ml-3 float-right font-weight-bold">{$versandart->cPreisLocalized}</span>
                                                        </div>
                                                        <span class="btn-block">
                                                            {if $versandart->cBild}
                                                                {image fluid=true class="w-20" src=$versandart->cBild alt=$versandart->angezeigterName|trans}
                                                            {/if}
                                                            {if !empty($versandart->angezeigterHinweistext|trans)}
                                                                <span class="text-muted">
                                                                    {$versandart->angezeigterHinweistext|trans}
                                                                </span>
                                                            {/if}

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
                                                                <small>{$versandart->Zuschlag->angezeigterName|trans}
                                                                    (+{$versandart->Zuschlag->cPreisLocalized})
                                                                </small>
                                                            {/if}
                                                            {if !empty($versandart->cLieferdauer|trans) && $Einstellungen.global.global_versandermittlung_lieferdauer_anzeigen === 'Y'}
                                                                <small>{lang key='shippingTimeLP'}
                                                                    : {$versandart->cLieferdauer|trans}</small>
                                                            {/if}
                                                        </span>
                                                    {/radio}
                                                </div>
                                            {/block}
                                        {/foreach}
                                    {/radiogroup}
                                </div>
                            </fieldset>
                        {/block}
                        {block name='checkout-step3-shipping-options-fieldset-payment'}
                            <fieldset id="fieldset-payment" class="mb-7">
                                {block name='checkout-step3-shipping-options-legend-payment'}
                                    <div class="h2">{lang key='paymentOptions'}</div>
                                {/block}
                                <hr class="my-3">
                                {$step4_payment_content}
                            </fieldset>
                        {/block}
                        {if isset($Verpackungsarten) && $Verpackungsarten|@count > 0}
                            {block name='checkout-step3-shipping-options-fieldset-packaging-types'}
                                <fieldset class="mb-7">
                                    {block name='checkout-step3-shipping-options-legend-packaging-types'}
                                        <div class="h2">{lang section='checkout' key='additionalPackaging'}</div>
                                    {/block}
                                    <hr class="my-3">
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
                                                <span class="ml-3 float-right font-weight-bold">
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
                                <div class="mt-4 mb-7">
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
