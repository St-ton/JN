{if !empty($hinweis)}
    <div class="alert alert-danger">
        {$hinweis}
    </div>
{/if}
<div class="row">
    <div class="col-xs-12 col-md-10 col-md-offset-1">
        {block name="checkout-shipping-options"}
        <div class="well panel-wrap">
            <div class="panel panel-default" id="order-enter-shipping-options">
                <div class="panel-heading">
                    <h3 class="panel-title">{block name="checkout-shipping-options-title"}{lang key="shippingOptions" section="global"}{/block}</h3>
                </div>
                <div class="panel-body">
                    {block name="checkout-shipping-options-body"}
                    <form method="post" action="bestellvorgang.php" class="form">
                        {$jtl_token}
                        <fieldset>
                            {if count($Versandarten) < 1}
                                <div class="alert alert-danger">{lang key="noShippingMethodsAvailable" section="checkout"}</div>
                            {else}
                                <div class="alert alert-info">{lang key="shippingOptionsDesc" section="shipping payment"}</div>
                            {/if}

                            <ul class="list-group">
                                {foreach name=shipment from=$Versandarten item=versandart}
                                    <li id="shipment_{$versandart->kVersandart}" class="list-group-item">
                                        <div class="radio">
                                            <label for="del{$versandart->kVersandart}" class="btn-block">
                                                <input name="Versandart" value="{$versandart->kVersandart}" type="radio" id="del{$versandart->kVersandart}"{if $Versandarten|@count == 1} checked{/if}{if $smarty.foreach.shipment.first} required{/if}>
                                                &nbsp;{if $versandart->cBild}
                                                    <img src="{$versandart->cBild}" alt="{$versandart->angezeigterName|trans}">
                                                {else}
                                                    <strong>{$versandart->angezeigterName|trans}</strong>
                                                {/if}
                                                <span class="badge pull-right">{$versandart->cPreisLocalized}</span>{if $versandart->angezeigterHinweistext|has_trans}
                                                    <p>
                                                        <small>{$versandart->angezeigterHinweistext|trans}</small>
                                                    </p>
                                                {/if}
                                                {if !empty($versandart->Zuschlag->fZuschlag)}
                                                    <p>
                                                        <small>{$versandart->Zuschlag->angezeigterName|trans}
                                                            (+{$versandart->Zuschlag->cPreisLocalized})
                                                        </small>
                                                    </p>
                                                {/if}

                                                {if $versandart->cLieferdauer|has_trans && $Einstellungen.global.global_versandermittlung_lieferdauer_anzeigen === 'Y'}
                                                    <p>
                                                        <small>{lang key="shippingTimeLP" section="global"}
                                                            : {$versandart->cLieferdauer|trans}</small>
                                                    </p>
                                                {/if}
                                            </label>
                                        </div>
                                    </li>
                                {/foreach}
                            </ul>

                            {if $Verpackungsarten|@count > 0}
                                <div class="form-group">
                                    {foreach name=zusatzverpackungen from=$Verpackungsarten item=oVerpackung}
                                        <div class="checkbox">
                                            <label for="pac{$oVerpackung->kVerpackung}">
                                                <input name="kVerpackung[]" type="checkbox" value="{$oVerpackung->kVerpackung}" id="pac{$oVerpackung->kVerpackung}" />{$oVerpackung->cName}
                                                <p>
                                                    <small>{$oVerpackung->cBeschreibung}</small>
                                                </p>
                                            </label>
                                            &nbsp;<span class="label label-default">
                                   {if $oVerpackung->nKostenfrei == 1}{lang key="ExemptFromCharges" section="global"}{else}{$oVerpackung->fBruttoLocalized}{/if}
                                   </span>
                                        </div>
                                    {/foreach}
                                </div>
                            {/if}
                        </fieldset>

                        <input type="hidden" name="versandartwahl" value="1" />

                        <input type="submit" value="{lang key="continueOrder" section="account data"}" class="submit btn btn-primary" />
                    </form>
                    {/block}
                </div>
            </div>
        </div>
        {/block}
    </div>
</div>