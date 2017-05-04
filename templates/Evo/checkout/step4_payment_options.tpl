{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 * This file is for compatibility with 3-step checkout (will be replaced by payment plugins)
 *}
<div class="row bottom15 form-group">
    {foreach name=paymentmethod from=$Zahlungsarten item=zahlungsart}
        <div id="{$zahlungsart->cModulId}" class="col-xs-12">
            <div class="radio">
                <label for="payment{$zahlungsart->kZahlungsart}" class="btn-block">
                    <input name="Zahlungsart" value="{$zahlungsart->kZahlungsart}" class="radio-checkbox" type="radio" id="payment{$zahlungsart->kZahlungsart}"{if $AktiveZahlungsart === $zahlungsart->kZahlungsart || $Zahlungsarten|@count == 1} checked{/if}{if $smarty.foreach.paymentmethod.first} required{/if}>
                    <span class="control-label label-default">
                        {if $zahlungsart->cBild}
                            <img src="{$zahlungsart->cBild}" alt="{$zahlungsart->angezeigterName|trans}" class="img-responsive-width img-sm">
                        {else}
                            <span class="content">
                                <span class="title">{$zahlungsart->angezeigterName|trans}</span>
                            </span>
                        {/if}
                        {if $zahlungsart->fAufpreis != 0}
                            <span class="badge pull-right">
                                {if $zahlungsart->cGebuehrname|has_trans}
                                    <span>{$zahlungsart->cGebuehrname|trans} </span>
                                {/if}
                                {$zahlungsart->cPreisLocalized}
                            </span>
                        {/if}
                        {if $zahlungsart->cHinweisText|has_trans}
                            <span class="btn-block">
                                <small>{$zahlungsart->cHinweisText|trans}</small>
                            </span>
                        {/if}
                    </span>
                </label>
            </div>
        </div>
    {/foreach}
</div>

{if isset($oTrustedShops->oKaeuferschutzProdukte->item) && $oTrustedShops->oKaeuferschutzProdukte->item|@count > 0 && $Einstellungen.trustedshops.trustedshops_nutzen === 'Y'}
<hr>
<div id="ts-buyerprotection">
    <div class="row bottom15">
        <div class="col-xs-10">
            {if $oTrustedShops->oKaeuferschutzProdukte->item|@count > 1}
                <div class="checkbox">
                    <label for="trusted_bTS" class="btn-block">
                        <input id="trusted_bTS" name="bTS" type="checkbox" value="1">
                        <span class="control-label label-default">
                            <span class="content">
                                <span class="title">{lang key="trustedShopsBuyerProtection" section="global"} ({lang key="trustedShopsRecommended" section="global"})</span>
                            </span>
                        </span>
                    </label>
                </div>

                <select name="cKaeuferschutzProdukt" class="form-control">
                    {foreach name=kaeuferschutzprodukte from=$oTrustedShops->oKaeuferschutzProdukte->item item=oItem}
                        <option value="{$oItem->tsProductID}"{if $oTrustedShops->cVorausgewaehltesProdukt == $oItem->tsProductID} selected{/if}>{lang key="trustedShopsBuyerProtection" section="global"} {lang key="trustedShopsTo" section="global"} {$oItem->protectedAmountDecimalLocalized}
                            ({$oItem->grossFeeLocalized} {$oItem->cFeeTxt})
                        </option>
                    {/foreach}
                </select>
            {elseif $oTrustedShops->oKaeuferschutzProdukte->item|@count == 1}
                <div class="checkbox">
                    <label for="trusted_bTS" class="btn-block">
                        <input id="trusted_bTS" name="bTS" type="checkbox" value="1">
                        <span class="control-label label-default">
                            <span class="content">
                                <span class="title">{lang key="trustedShopsBuyerProtection" section="global"} {lang key="trustedShopsTo" section="global"} {$oTrustedShops->oKaeuferschutzProdukte->item[0]->protectedAmountDecimalLocalized}
                                    ({$oTrustedShops->oKaeuferschutzProdukte->item[0]->grossFeeLocalized} {$oTrustedShops->oKaeuferschutzProdukte->item[0]->cFeeTxt})
                                </span>
                            </span>
                        </span>
                    </label>
                </div>
                <input name="cKaeuferschutzProdukt" type="hidden" value="{$oTrustedShops->oKaeuferschutzProdukte->item[0]->tsProductID}">
            {/if}
            <p class="small text-muted top10">
                {assign var=cISOSprache value=$oTrustedShops->cISOSprache}
                {if !empty($oTrustedShops->cBoxText[$cISOSprache])}
                    {$oTrustedShops->cBoxText[$cISOSprache]}
                {else}
                    {assign var=cISOSprache value='default'}
                    {$oTrustedShops->cBoxText[$cISOSprache]}
                {/if}
            </p>
        </div>
        <div class="col-xs-2">
            <a href="{$oTrustedShops->cLogoURL}" target="_blank"><img src="{$URL_SHOP}/{$PFAD_GFX_TRUSTEDSHOPS}ts_logo.jpg" alt="" class="img-responsive"></a>
        </div>
    </div>
</div>
{/if}
<input type="hidden" name="zahlungsartwahl" value="1" />