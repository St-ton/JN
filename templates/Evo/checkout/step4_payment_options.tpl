{if !empty($hinweis)}
    <div class="alert alert-danger">{$hinweis}</div>
{/if}

<div class="row">
    <div class="col-xs-12 col-md-10 col-md-offset-1">
        {block name="checkout-payment-options"}
        <div class="well panel-wrap">
            <div class="panel panel-default" id="order-payment-options">
                <div class="panel-heading">
                    <h3 class="panel-title">{block name="checkout-payment-options-title"}{lang key="paymentOptions" section="global"}{/block}</h3>
                </div>
                <div class="panel-body">
                    {block name="checkout-payment-options-body"}
                    <form id="zahlung" method="post" action="bestellvorgang.php" class="form">
                        {$jtl_token}
                        <fieldset>
                            {if empty($cFehler)}
                                <div class="alert alert-info">{lang key="paymentOptionsDesc" section="shipping payment"}</div>
                            {else}
                                <div class="alert alert-danger">{$cFehler}</div>
                            {/if}
                            <ul class="list-group">
                                {foreach name=paymentmethod from=$Zahlungsarten item=zahlungsart}
                                    <li id="{$zahlungsart->cModulId}" class="list-group-item">
                                        <div class="radio">
                                            <label for="payment{$zahlungsart->kZahlungsart}" class="btn-block">
                                                <input name="Zahlungsart" value="{$zahlungsart->kZahlungsart}" type="radio" id="payment{$zahlungsart->kZahlungsart}"{if $Zahlungsarten|@count == 1} checked{/if}{if $smarty.foreach.paymentmethod.first} required{/if}>
                                                {if $zahlungsart->cBild}
                                                    <img src="{$zahlungsart->cBild}" alt="{$zahlungsart->angezeigterName|trans}" class="vmiddle">
                                                {else}
                                                    <strong>{$zahlungsart->angezeigterName|trans}</strong>
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
                                                    <p class="small text-muted">{$zahlungsart->cHinweisText|trans}</p>
                                                {/if}
                                            </label>
                                        </div>
                                    </li>
                                {/foreach}
                            </ul>

                            {if isset($oTrustedShops->oKaeuferschutzProdukte->item) && $oTrustedShops->oKaeuferschutzProdukte->item|@count > 0 && $Einstellungen.trustedshops.trustedshops_nutzen === 'Y'}
                                <hr>
                                <div id="ts-buyerprotection">
                                    <div class="row">
                                        <div class="col-xs-10">
                                            {if $oTrustedShops->oKaeuferschutzProdukte->item|@count > 1}
                                                <div class="checkbox">
                                                    <label>
                                                        <input name="bTS" type="checkbox" value="1">
                                                        <strong>{lang key="trustedShopsBuyerProtection" section="global"} ({lang key="trustedShopsRecommended" section="global"})</strong>
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
                                                    <label>
                                                        <input name="bTS" type="checkbox" value="1">
                                                        <strong>{lang key="trustedShopsBuyerProtection" section="global"} {lang key="trustedShopsTo" section="global"} {$oTrustedShops->oKaeuferschutzProdukte->item[0]->protectedAmountDecimalLocalized}
                                                        ({$oTrustedShops->oKaeuferschutzProdukte->item[0]->grossFeeLocalized} {$oTrustedShops->oKaeuferschutzProdukte->item[0]->cFeeTxt}
                                                        )</strong>
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
                        </fieldset>
                        <input type="submit" value="{lang key="continueOrder" section="account data"}" class="btn btn-primary submit" />
                    </form>
                    {/block}
                </div>
            </div>
        </div>
        {/block}
    </div>
</div>