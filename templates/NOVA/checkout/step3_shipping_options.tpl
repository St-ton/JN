{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{row}
    {col cols=12}
        {if !isset($Versandarten)}
            {alert variant="danger"}{lang key='noShippingMethodsAvailable' section='checkout'}{/alert}
        {else}
            {form method="post" action="{get_static_route id='bestellvorgang.php'}" class="form evo-validate"}
                <fieldset id="checkout-shipping-payment">
                    <legend>{lang key='shippingOptions'}</legend>
                    <div class="mb-3 form-group">
                        {radiogroup}
                            {foreach $Versandarten as $versandart}
                            <div id="shipment_{$versandart->kVersandart}">
                                {radio
                                    name="Versandart"
                                    value=$versandart->kVersandart
                                    id="del{$versandart->kVersandart}"
                                    checked=($Versandarten|@count == 1 || $AktiveVersandart == $versandart->kVersandart)
                                    required=($versandart@first)
                                    class="w-100"
                                }
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
                                {/radio}
                            </div>
                            {/foreach}
                        {/radiogroup}
                    </div>
                </fieldset>

                {if isset($Verpackungsarten) && $Verpackungsarten|@count > 0}
                    <fieldset>
                        <legend>{lang section='checkout' key='additionalPackaging'}</legend>
                        {row class="mb-3 form-group"}
                            {checkboxgroup}
                                {foreach $Verpackungsarten as $oVerpackung}
                                {col cols=12 id="packaging_{$oVerpackung->kVerpackung}"}
                                    {checkbox
                                        name="kVerpackung[]"
                                        value=$oVerpackung->kVerpackung
                                        id="pac{$oVerpackung->kVerpackung}"
                                        checked=(isset($oVerpackung->bWarenkorbAktiv) && $oVerpackung->bWarenkorbAktiv === true || (isset($AktiveVerpackung[$oVerpackung->kVerpackung]) && $AktiveVerpackung[$oVerpackung->kVerpackung] === 1))
                                    }
                                        <span class="content">
                                            <span class="title">{$oVerpackung->cName}</span>
                                        </span>
                                        <span class="badge badge-pill badge-primary float-right">
                                            {if $oVerpackung->nKostenfrei == 1}{lang key='ExemptFromCharges'}{else}{$oVerpackung->fBruttoLocalized}{/if}
                                        </span>
                                        <span class="btn-block">
                                            <small>{$oVerpackung->cBeschreibung}</small>
                                        </span>
                                    {/checkbox}
                                {/col}
                                {/foreach}
                            {/checkboxgroup}
                        {/row}
                    </fieldset>
                {/if}
                <fieldset id="fieldset-payment">
                    <legend>{lang key='paymentOptions'}</legend>
                    {$step4_payment_content}
                </fieldset>
                {if isset($Versandarten)}
                <div class="text-right">
                    {input type="hidden" name="versandartwahl" value="1"}
                    {input type="hidden" name="zahlungsartwahl" value="1"}
                    {button type="submit" variant="primary" class="submit_once d-none"}
                        {lang key='continueOrder' section='account data'}
                    {/button}
                </div>
                {/if}
            {/form}
        {/if}
    {/col}
{/row}
{if isset($smarty.get.editZahlungsart)}
{literal}
    <script type="text/javascript">
        $(document).ready(function () {
            $.evo.extended().smoothScrollToAnchor('#fieldset-payment');
        });
    </script>
{/literal}
{/if}
