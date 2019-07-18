{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-shipping-calculator'}
    {block name='snippets-shipping-calculator-form'}
        <div id="shipping-estimate-form" class="mb-5">
            {block name='snippets-shipping-calculator-form-content'}
                {block name='snippets-shipping-calculator-estimate'}
                    <div class="h4">{lang key='estimateShippingCostsTo' section='checkout'}:</div>
                {/block}
                {block name='snippets-shipping-calculator-estimate-main'}
                    <div class="form-row">
                        {block name='snippets-shipping-calculator-countries'}
                            {col cols=12 md=6 class="mb-3"}
                                {select name="land" id="country" placeholder=""}
                                    {foreach $laender as $land}
                                        <option value="{$land->getISO()}" {if ($Einstellungen.kunden.kundenregistrierung_standardland === $land->getISO() && (!isset($smarty.session.Kunde->cLand) || !$smarty.session.Kunde->cLand)) || (isset($smarty.session.Kunde->cLand) && $smarty.session.Kunde->cLand==$land->getISO())}selected{/if}>{$land->getName()}</option>
                                    {/foreach}
                                {/select}
                            {/col}
                        {/block}
                        {block name='snippets-shipping-calculator-submit'}
                            {col cols=12 md=6}
                                {inputgroup label-for="plz" label="{lang key='plz' section='forgot password'}"}
                                    {input type="text" name="plz" size="8" maxlength="8" value="{if isset($smarty.session.Kunde->cPLZ)}{$smarty.session.Kunde->cPLZ}{elseif isset($VersandPLZ)}{$VersandPLZ}{/if}" id="plz" placeholder="{lang key='plz' section='forgot password'}"}
                                    {inputgroupaddon append=true}
                                        {button name="versandrechnerBTN" type="submit"}{lang key='estimateShipping' section='checkout'}{/button}
                                    {/inputgroupaddon}
                                {/inputgroup}
                            {/col}
                        {/block}
                    </div>
                {/block}
            {/block}
        </div>
    {/block}
    <div id="shipping-estimated">
        {block name='snippets-shipping-calculator-content'}
            {if !empty($ArtikelabhaengigeVersandarten)}
                {block name='snippets-shipping-calculator-table-artikelabhaengig'}
                    <table class="table table-striped">
                        <caption>{lang key='productShippingDesc' section='checkout'}</caption>
                        <tbody>
                            {foreach $ArtikelabhaengigeVersandarten as $artikelversand}
                                <tr>
                                    <td>{$artikelversand->cName|trans}</td>
                                    <td class="text-right">
                                        <strong>{$artikelversand->cPreisLocalized}</strong>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                {/block}
            {/if}
            {if !empty($Versandarten)}
                {block name='snippets-shipping-calculator-shipping-methods'}
                    <table class="table table-striped">
                        <caption>{lang key='shippingMethods'}</caption>
                        <tbody>
                            {foreach $Versandarten as $versandart}
                                <tr id="shipment_{$versandart->kVersandart}">
                                    <td>
                                        {if $versandart->cBild}
                                            {image src=$versandart->cBild alt="{$versandart->angezeigterName|trans}"}
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
                                                {lang key='shippingTimeLP'}: {$versandart->cLieferdauer|trans}
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
                {/block}
                {block name='snippets-shipping-calculator-link'}
                    {if isset($checkout) && $checkout}
                        {$link = {get_static_route id='warenkorb.php'}}
                    {else}
                        {$link = $ShopURL|cat:'/?s='|cat:$Link->getID()}
                    {/if}
                {/block}
            {else}
                {block name='snippets-shipping-calculator-no-shipping-available'}
                    {row}
                        {col}
                            {lang key='noShippingAvailable' section='checkout'}
                        {/col}
                    {/row}
                {/block}
            {/if}
        {/block}
    </div>
    {block name='snippets-shipping-calculator-hr-end'}
        <hr class="my-4">
    {/block}
{/block}
