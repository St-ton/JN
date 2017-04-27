{if !empty($hinweis)}
    <div class="alert alert-danger">
        {$hinweis}
    </div>
{/if}
<div class="row">
    <div class="col-xs-12">
        <form method="post" action="{get_static_route id='bestellvorgang.php'}" class="form">
            {$jtl_token}
            <fieldset id="checkout-shipping-payment">
                {*{$Versandarten|@var_dump}*}
                {if !isset($Versandarten)}
                    <div class="alert alert-danger">{lang key="noShippingMethodsAvailable" section="checkout"}</div>
                {else}
                    <legend>{lang section='global' key='shippingOptions'}</legend>
                    <div class="row bottom15">
                        {foreach name=shipment from=$Versandarten item=versandart}
                            <div id="shipment_{$versandart->kVersandart}" class="col-xs-12">
                                <div class=" radio ">
                                    <label for="del{$versandart->kVersandart}" class="btn-block">



                                            <input name="Versandart" value="{$versandart->kVersandart}" type="radio" class="radio-checkbox" id="del{$versandart->kVersandart}"{if $Versandarten|@count == 1 || $smarty.session.Versandart->kVersandart == $versandart->kVersandart} checked{/if}{if $smarty.foreach.shipment.first} required{/if}>
                                            <span class="radio-icon"><i class="fa"></i></span>

                                            <span class="content" style="width: 130px;">
                                                    <span class="title">{$versandart->angezeigterName|trans}</span>
                                                    <small class="desc text-info">{$versandart->cLieferdauer|trans}</small>
                                                </span>

                                            {if $versandart->cBild}
                                                <img src="{$versandart->cBild}" alt="{$versandart->angezeigterName|trans}" style="margin:0 20px">
                                            {/if}

                                            <span class="content text-muted" style="max-width: 480px;font-size:0.85em">
                                                    {$versandart->angezeigterHinweistext|trans}
                                                </span>

                                        <div class="hidden">
                                            <span class="badge pull-right">{$versandart->cPreisLocalized}</span>
                                        </div>

                                        {if isset($versandart->specificShippingcosts_arr)}
                                            {foreach name=specificShippingcosts from=$versandart->specificShippingcosts_arr item=specificShippingcosts}
                                                <div class="row">
                                                    <div class="col-xs-8 col-md-9 col-lg-9">
                                                        <ul>
                                                            <li>
                                                                <small>{$specificShippingcosts->cName|trans}</small>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-xs-4 col-md-3 col-lg-3 text-right">
                                                        <small>
                                                            {$specificShippingcosts->cPreisLocalized}
                                                        </small>
                                                    </div>
                                                </div>
                                            {/foreach}
                                        {/if}
                                        {if !empty($versandart->Zuschlag->fZuschlag)}
                                            <p>
                                                <small>{$versandart->Zuschlag->angezeigterName|trans}
                                                    (+{$versandart->Zuschlag->cPreisLocalized})
                                                </small>
                                            </p>
                                        {/if}
                                        {if !empty($versandart->cLieferdauer|trans) && $Einstellungen.global.global_versandermittlung_lieferdauer_anzeigen === 'Y'}
                                            <p>
                                                <small>{lang key="shippingTimeLP" section="global"}
                                                    : {$versandart->cLieferdauer|trans}</small>
                                            </p>
                                        {/if}
                                    </label>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                {/if}
                {if isset($Verpackungsarten) && $Verpackungsarten|@count > 0}
                    <legend>Zusatzverpackung</legend>
                    <div class="form-group bottom15">
                        {foreach name=zusatzverpackungen from=$Verpackungsarten item=oVerpackung}
                            <div class="checkbox">
                                <label for="pac{$oVerpackung->kVerpackung}">
                                    <input name="kVerpackung[]" type="checkbox" value="{$oVerpackung->kVerpackung}" id="pac{$oVerpackung->kVerpackung}" />{$oVerpackung->cName}
                                    <p>
                                        <small>{$oVerpackung->cBeschreibung}</small>
                                    </p>
                                </label>
                                 <span class="label label-default">
                                    {if $oVerpackung->nKostenfrei == 1}{lang key="ExemptFromCharges" section="global"}{else}{$oVerpackung->fBruttoLocalized}{/if}
                                </span>
                            </div>
                        {/foreach}
                    </div>
                {/if}
            </fieldset>
            <fieldset id="fieldset-payment">
                {*{$Zahlungsarten|@var_dump}*}
                {if !empty($cFehler)}
                    <div class="alert alert-danger">{$cFehler}</div>
                {/if}
                <legend>{lang section='global' key='paymentOptions'}</legend>
                <div class="row">
                    {foreach name=paymentmethod from=$Zahlungsarten item=zahlungsart}
                        <div id="{$zahlungsart->cModulId}" class="col-xs-12">
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
                        </div>
                    {/foreach}
                </div>

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
            {if isset($Versandarten)}
                <div class="text-right">
                    <input type="hidden" name="versandartwahl" value="1" />
                    <input type="submit" value="{lang key="continueOrder" section="account data"}" class="submit btn btn-lg submit-once btn-primary" />
                </div>
            {/if}
        </form>
    </div>
</div>