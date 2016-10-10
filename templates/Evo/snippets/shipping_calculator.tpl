{block name="shipping-estimate"}
    {if !isset($Versandarten) || !$Versandarten}
        {block name="shipping-estimate-form"}
            <div class="panel panel-default" id="basket-shipping-estimate-form">
                <div class="panel-heading">
                    <div class="panel-title">
                        {block name="shipping-estimate-form-title"}{lang key="estimateShippingCostsTo" section="checkout"}{/block}
                    </div>
                </div>
                <div class="panel-body">
                    {block name="shipping-estimate-form-body"}
                        <div class="form-inline">
                            <label for="country">{lang key="country" section="account data"}</label>
                            <select name="land" id="country" class="form-control">
                                {foreach name=land from=$laender item=land}
                                    <option value="{$land->cISO}" {if ($Einstellungen.kunden.kundenregistrierung_standardland==$land->cISO && (!isset($smarty.session.Kunde->cLand) || !$smarty.session.Kunde->cLand)) || (isset($smarty.session.Kunde->cLand) && $smarty.session.Kunde->cLand==$land->cISO)}selected{/if}>{$land->cName}</option>
                                {/foreach}
                            </select>
                            &nbsp;
                            <label class="sr-only" for="plz">{lang key="plz" section="forgot password"}</label>
                            <span class="input-group">
                                <input type="text" name="plz" size="8" maxlength="8" value="{if isset($smarty.session.Kunde->cPLZ)}{$smarty.session.Kunde->cPLZ}{/if}" id="plz" class="form-control" placeholder="{lang key="plz" section="forgot password"}">
                                <span class="input-group-btn">
                                    <button name="versandrechnerBTN" class="btn btn-default" type="submit">{lang key="estimateShipping" section="checkout"}</button>
                                </span>
                            </span>
                        </div>
                    {/block}
                </div>
            </div>
        {/block}
    {else}
        {block name="shipping-estimated"}
            <div class="panel panel-default" id="basket-shipping-estimated">
                <div class="panel-heading">
                    <h4 class="panel-title">{block name="shipping-estimated-title"}{lang key="estimateShippingCostsTo" section="checkout"} {$Versandland}, {lang key="plz" section="forgot password"} {$VersandPLZ}{/block}</h4>
                </div>
                <div class="panel-body">
                    {block name="shipping-estimated-body"}
                        {if count($ArtikelabhaengigeVersandarten)>0}
                            <table class="table table-striped">
                                <caption>{lang key="productShippingDesc" section="checkout"}:</caption>
                                {foreach name=artikelversandliste from=$ArtikelabhaengigeVersandarten item=artikelversand}
                                    <tr>
                                        <td>{$artikelversand->cName|trans}</td>
                                        <td class="text-right"><strong>{$artikelversand->cPreisLocalized}</strong>
                                        </td>
                                    </tr>
                                {/foreach}
                            </table>
                        {/if}
                        {if !empty($Versandarten)}
                            <table class="table table-striped">
                                <caption>{lang key="shippingMethods" section="global"}:</caption>
                                {foreach name=versand from=$Versandarten item=versandart}
                                    <tr id="shipment_{$versandart->kVersandart}">
                                        <td>
                                            {if $versandart->cBild}
                                                <img src="{$versandart->cBild}" alt="{$versandart->angezeigterName|trans}">
                                            {else}
                                                {$versandart->angezeigterName|trans}
                                            {/if}
                                            {if $versandart->angezeigterHinweistext|trans}
                                                <p>
                                                    <small>{$versandart->angezeigterHinweistext|trans}</small>
                                                </p>
                                            {/if}
                                            {if isset($versandart->Zuschlag) && $versandart->Zuschlag->fZuschlag != 0}
                                                <p>
                                                    <small>{$versandart->Zuschlag->angezeigterName|trans}
                                                        (+{$versandart->Zuschlag->cPreisLocalized})
                                                    </small>
                                                </p>
                                            {/if}
                                            {if $versandart->cLieferdauer|trans && $Einstellungen.global.global_versandermittlung_lieferdauer_anzeigen === 'Y'}
                                                <p>
                                                    <small>{lang key="shippingTimeLP" section="global"}: {$versandart->cLieferdauer|trans}</small>
                                                </p>
                                            {/if}
                                        </td>
                                        <td class="text-right">
                                            {if $versandart->fEndpreis == 0}
                                                <strong>{lang key="freeshipping" section="global"}</strong>
                                            {else}
                                                <strong>{$versandart->cPreisLocalized}</strong>
                                            {/if}
                                        </td>
                                    </tr>
                                {/foreach}
                            </table>
                            {if isset($checkout) && $checkout}
                                <a href="{get_static_route id='warenkorb.php'}" class="btn btn-default">{lang key="newEstimation" section="checkout"}</a>
                            {else}
                                <a href="navi.php?s={$Link->kLink}" class="btn btn-default">{lang key="newEstimation" section="checkout"}</a>
                            {/if}
                        {else}
                            <div class="row">
                                {lang key="noShippingAvailable" section="checkout"}
                            </div>
                        {/if}
                    {/block}
                </div>
            </div>
        {/block}
        {if !empty($cErrorVersandkosten)}
            <div class="alert alert-info">{$cErrorVersandkosten}</div>
        {/if}
    {/if}
{/block}