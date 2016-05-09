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
            <table class="table table-striped">
                <tr>
                    <td colspan="2">
                        <b>{lang key="estimateShippingCostsTo" section="checkout"} {$Versandland}, {lang key="plz" section="forgot password"} {$VersandPLZ}</b>
                    </td>
                </tr>
                {if isset($ArtikelabhaengigeVersandarten) && $ArtikelabhaengigeVersandarten|@count > 0}
                    <tr>
                        <td colspan="2">{lang key="productShippingDesc" section="checkout"}:</td>
                    </tr>
                    {foreach name=artikelversandliste from=$ArtikelabhaengigeVersandarten item=artikelversand}
                        <tr>
                            <td>
                                {$artikelversand->cName|trans}
                            </td>
                            <td>
                                <b>{$artikelversand->cPreisLocalized}</b>
                            </td>
                        </tr>
                    {/foreach}
                {/if}
                {if isset($Versandarten) && $Versandarten|@count > 0}
                    {foreach name=versand from=$Versandarten item=versandart}
                        <tr>
                            <td>
                                {if !empty($versandart->cBild)}
                                    <img src="{$versandart->cBild}" alt="{$versandart->angezeigterName|trans}" />
                                {else}
                                    {$versandart->angezeigterName|trans}
                                {/if}
                                {if $versandart->angezeigterHinweistext|has_trans}
                                    <p>
                                        <small>{$versandart->angezeigterHinweistext|trans}</small>
                                    </p>
                                {/if}
                                {if !empty($versandart->Zuschlag->fZuschlag)}
                                    <br>
                                    <span class="small">{$versandart->Zuschlag->angezeigterName|trans} (+{$versandart->Zuschlag->cPreisLocalized})</span>
                                {/if}
                                {if $versandart->cLieferdauer|has_trans && $Einstellungen.global.global_versandermittlung_lieferdauer_anzeigen === 'Y'}
                                    <br>
                                    <span class="small">{lang key="shippingTimeLP" section="global"}: {$versandart->cLieferdauer|trans}</span>
                                {/if}
                            </td>
                            <td>
                                {if $versandart->fEndpreis == 0}
                                    <b>{lang key="freeshipping" section="global"}</b>
                                {else}
                                    <b>{$versandart->cPreisLocalized}</b>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="2">{lang key="noShippingAvailable" section="checkout"}</td>
                    </tr>
                {/if}
            </table>
            <a href="navi.php?s={$Link->kLink}" class="btn btn-primary">{lang key="newEstimation" section="checkout"}</a>
        {/if}
    </form>
{else}
    {lang key="estimateShippingCostsNote" section="global"}
{/if}