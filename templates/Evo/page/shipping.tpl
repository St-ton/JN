{if !empty($hinweis)}
    <div class="alert alert-info">
        {$hinweis}
    </div>
{/if}
{if !empty($fehler)}
    <div class="alert alert-danger">
        {$fehler}
    </div>
{/if}

{if isset($Einstellungen.global.global_versandermittlung_anzeigen) && $Einstellungen.global.global_versandermittlung_anzeigen === 'Y' && isset($smarty.session.Warenkorb->PositionenArr) && $smarty.session.Warenkorb->PositionenArr|@count > 0}
    <form method="post" action="navi.php{if $bExclusive}?exclusive_content=1{/if}" class="form form-inline">
        {$jtl_token}
        <input type="hidden" name="s" value="{$Link->kLink}">
        {if !isset($Versandarten)}
            {if !empty($MsgWarning)}
                <div class="alert alert-danger">{$MsgWarning}</div>
            {/if}
            <p>
                <label for="shipping-country">{lang key="estimateShippingCostsTo" section="checkout"}</label>
                <select id="shipping-country" name="land" class="form-control">
                    {foreach name=land from=$laender item=land}
                        <option value="{$land->cISO}" {if ($Einstellungen.kunden.kundenregistrierung_standardland==$land->cISO && empty($smarty.session.Kunde->cLand)) || (!empty($smarty.session.Kunde->cLand) && $smarty.session.Kunde->cLand == $land->cISO)}selected{/if}>{$land->cName}</option>
                    {/foreach}
                </select>
                <label for="shipping-plz">{lang key="plz" section="forgot password"}:</label>
                <input id="shipping-plz" type="text" name="plz" maxlength="20" class="form-control" value="{if isset($smarty.session.Kunde->cPLZ)}{$smarty.session.Kunde->cPLZ}{/if}">
                &nbsp;<input type="submit" value="{lang key="estimateShipping" section="checkout"}" class="btn btn-primary">
            </p>
        {else}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div>
                        <b>{lang key="estimateShippingCostsTo" section="checkout"} {$Versandland}, {lang key="plz" section="forgot password"} {$VersandPLZ}</b>
                    </div>
                </div>
                <div class="panel-body">
                    {if isset($Versandarten) && $Versandarten|@count > 0}
                        {foreach name=versand from=$Versandarten item=versandart}
                                <div class="row">
                                    <div class="col-xs-8 col-md-10 col-lg-10">
                                        {if !empty($versandart->cBild)}
                                            <img src="{$versandart->cBild}" alt="{$versandart->angezeigterName|trans}" />
                                        {else}
                                            <p>
                                                {$versandart->angezeigterName|trans}
                                            </p>
                                        {/if}
                                    </div>
                                    <div class="col-xs-4 col-md-2 col-lg-2">
                                        <b><small>{$versandart->cPreisLocalized}</small></b>
                                    </div>
                                </div>
                                {if isset($versandart->specificShippingcosts_arr)}
                                    {foreach name=specificShippingcosts from=$versandart->specificShippingcosts_arr item=specificShippingcosts}
                                        <div class="row">
                                            <div class="col-xs-8 col-md-10 col-lg-10">
                                                <ul>
                                                    <li>
                                                        <small>{$specificShippingcosts->cName|trans}</small>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-xs-4 col-md-2 col-lg-2">
                                                <small>
                                                    {$specificShippingcosts->cPreisLocalized}
                                                </small>
                                            </div>
                                        </div>
                                    {/foreach}
                                {/if}

                            {if !empty($versandart->angezeigterHinweistext|trans) && $versandart->angezeigterHinweistext|has_trans}
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
                            {if !empty($versandart->cLieferdauer|trans) && $Einstellungen.global.global_versandermittlung_lieferdauer_anzeigen === 'Y'}
                                <p>
                                    <small>{lang key="shippingTimeLP" section="global"}: {$versandart->cLieferdauer|trans}</small>
                                </p>
                            {/if}
                            {if !$smarty.foreach.versand.last}
                            <hr>
                            {/if}
                        {/foreach}
                    {else}
                        <div class="row">
                           {lang key="noShippingAvailable" section="checkout"}
                        </div>
                    {/if}
                </div>
            </div>

            <a href="navi.php?s={$Link->kLink}" class="btn btn-primary">{lang key="newEstimation" section="checkout"}</a>
        {/if}
    </form>
{else}
    {lang key="estimateShippingCostsNote" section="global"}
{/if}