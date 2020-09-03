{if empty($Versandarten)}
    {block name='shipping-estimate-form'}
        <div class="panel panel-default" id="shipping-estimate-form">
            <div class="panel-heading">
                <div class="panel-title">
                    {block name='shipping-estimate-form-title'}{lang key='estimateShippingCostsTo' section='checkout'}{/block}
                </div>
            </div>
            <div class="panel-body">
                {block name='shipping-estimate-form-body'}
                    <div class="form-inline">
                        <label for="country">{lang key='country' section='account data'}</label>
                        <select name="land" id="country" class="form-control">
                            {foreach $laender as $land}
                                <option value="{$land->getISO()}" {if $shippingCountry === $land->getISO()}selected{/if}>
                                    {$land->getName()}
                                </option>
                            {/foreach}
                        </select>
                        &nbsp;
                        <label class="sr-only" for="plz">{lang key='plz' section='forgot password'}</label>
                        <span class="input-group">
                            <input type="text" name="plz" size="8" maxlength="8" value="{if isset($smarty.session.Kunde->cPLZ)}{$smarty.session.Kunde->cPLZ}{/if}" id="plz" class="form-control" placeholder="{lang key='plz' section='forgot password'}">
                            <span class="input-group-btn">
                                <button name="versandrechnerBTN" class="btn btn-default" type="submit">{lang key='estimateShipping' section='checkout'}</button>
                            </span>
                        </span>
                    </div>
                {/block}
            </div>
        </div>
    {/block}
{else}
    {block name='shipping-estimated'}
        <div class="panel panel-default" id="shipping-estimated">
            <div class="panel-heading">
                <h4 class="panel-title">{block name='shipping-estimated-title'}{lang key='estimateShippingCostsTo' section='checkout'} {$Versandland}, {lang key='plz' section='forgot password'} {$VersandPLZ}{/block}</h4>
            </div>
            <div class="panel-body">
                {block name='shipping-estimated-body'}
                    {if count($ArtikelabhaengigeVersandarten) > 0}
                        <table class="table table-striped">
                            <caption>{lang key='productShippingDesc' section='checkout'}:</caption>
                            <tbody>
                                {foreach $ArtikelabhaengigeVersandarten as $artikelversand}
                                    <tr>
                                        <td>{$artikelversand->cName|trans}</td>
                                        <td class="text-right"><strong>{$artikelversand->cPreisLocalized}</strong>
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    {/if}
                    {if !empty($Versandarten)}
                        <table class="table table-striped">
                            <caption>{lang key='shippingMethods' section='global'}:</caption>
                            <tbody>
                                {foreach $Versandarten as $versandart}
                                    <tr id="shipment_{$versandart->kVersandart}">
                                        <td>
                                            {if $versandart->cBild}
                                                <img src="{$versandart->cBild}" alt="{$versandart->angezeigterName|trans}">
                                            {else}
                                                {$versandart->angezeigterName|trans}
                                            {/if}
                                            {if $versandart->angezeigterHinweistext|trans}
                                                <p class="small">
                                                    {$versandart->angezeigterHinweistext|trans}
                                                </p>
                                            {/if}
                                            {if isset($versandart->Zuschlag) && $versandart->Zuschlag->fZuschlag != 0}
                                                <p class="small">
                                                    {$versandart->Zuschlag->angezeigterName|trans}
                                                        (+{$versandart->Zuschlag->cPreisLocalized})
                                                </p>
                                            {/if}
                                            {if $versandart->cLieferdauer|trans && $Einstellungen.global.global_versandermittlung_lieferdauer_anzeigen === 'Y'}
                                                <p class="small">
                                                    {lang key='shippingTimeLP' section='global'}: {$versandart->cLieferdauer|trans}
                                                </p>
                                            {/if}
                                        </td>
                                        <td class="text-right">
                                            <strong>
                                                {$versandart->cPreisLocalized}
                                            </strong>
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                        {if isset($checkout) && $checkout}
                            {$link = {get_static_route id='warenkorb.php'}}
                        {else}
                            {$link = $ShopURL|cat:'/?s='|cat:$Link->getID()}
                        {/if}
                        <a href="{$link}" class="btn btn-default">{lang key='newEstimation' section='checkout'}</a>
                    {else}
                        <div class="row">
                            {lang key='noShippingAvailable' section='checkout'}
                        </div>
                    {/if}
                {/block}
            </div>
        </div>
    {/block}
{/if}
