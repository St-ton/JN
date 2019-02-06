<script type="text/javascript">
    {literal}
    function confirmDelete(cName) {
        return confirm('{/literal}{__('deleteShippingMethod')}{literal}"' + cName + '"?');
    }
    {/literal}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=__('shippingmethods') cBeschreibung=__('isleListsHint') cDokuURL=__('shippingmethodsURL')}
<div id="content" class="container-fluid">
    {foreach $versandarten as $versandart}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{$versandart->cName}</h3>
            </div>
            <table class="table table-list">
                <tbody>
                    <tr>
                        <td style="width:160px">{__('shippingTypeName')}</td>
                        <td>
                            {foreach $versandart->oVersandartSprachen_arr as $oVersandartSprachen}
                                {$oVersandartSprachen->cName}{if !$oVersandartSprachen@last}, {/if}
                            {/foreach}
                        </td>
                    </tr>
                    <tr>
                        <td>{__('countries')}</td>
                        <td>
                            {foreach $versandart->land_arr as $land}
                                <a href="versandarten.php?zuschlag=1&kVersandart={$versandart->kVersandart}&cISO={$land}&token={$smarty.session.jtl_token}"><span class="label label-{if isset($versandart->zuschlag_arr[$land])}success{else}default{/if}">{$land}</span></a>
                            {/foreach}
                        </td>
                    </tr>
                    <tr>
                        <td>{__('shippingclasses')}</td>
                        <td>
                            {if $versandart->versandklassen|@count == 1 && $versandart->versandklassen[0] === 'Alle'}
                                {$versandart->versandklassen[0]}
                            {else}
                                {foreach $versandart->versandklassen as $versandklasse}
                                    [{$versandklasse}] &nbsp;
                                {/foreach}
                            {/if}
                        </td>
                    </tr>
                    <tr>
                        <td>{__('customerclass')}</td>
                        <td>
                            {foreach $versandart->cKundengruppenName_arr as $cKundengruppenName}
                                {$cKundengruppenName}
                            {/foreach}
                        </td>
                    </tr>
                    <tr>
                        <td>{__('taxshippingcosts')}</td>
                        <td>{if $versandart->eSteuer === 'netto'}{__('net')}{else}{__('gross')}{/if}</td>
                    </tr>
                    <tr>
                        <td>{__('shippingtime')}</td>
                        <td>{$versandart->nMinLiefertage} - {$versandart->nMaxLiefertage} {__('days')}</td>
                    </tr>
                    <tr>
                        <td>{__('paymentMethods')}</td>
                        <td>
                            {foreach $versandart->versandartzahlungsarten as $zahlungsart}
                                {$zahlungsart->zahlungsart->cName}{if isset($zahlungsart->zahlungsart->cAnbieter) &&
                                    $zahlungsart->zahlungsart->cAnbieter|strlen > 0} ({$zahlungsart->zahlungsart->cAnbieter}){/if} {if $zahlungsart->fAufpreis!=0}{if $zahlungsart->cAufpreisTyp != "%"}{getCurrencyConversionSmarty fPreisBrutto=$zahlungsart->fAufpreis bSteuer=false}{else}{$zahlungsart->fAufpreis}%{/if}{/if}
                                <br />
                            {/foreach}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {if $versandart->versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl' || $versandart->versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl' || $versandart->versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl'}
                                {__('priceScale')}
                            {elseif $versandart->versandberechnung->cModulId === 'vm_versandkosten_pauschale_jtl'}
                                {__('shippingPrice')}
                            {/if}
                        </td>
                        <td>
                            {if $versandart->versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl' || $versandart->versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl' || $versandart->versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl'}
                                {foreach $versandart->versandartstaffeln as $versandartstaffel}
                                    {if $versandartstaffel->fBis != 999999999}
                                        {__('upTo')} {$versandartstaffel->fBis} {$versandart->einheit} {getCurrencyConversionSmarty fPreisBrutto=$versandartstaffel->fPreis bSteuer=false}
                                        <br />
                                    {/if}
                                {/foreach}
                            {elseif $versandart->versandberechnung->cModulId === 'vm_versandkosten_pauschale_jtl'}
                                {getCurrencyConversionSmarty fPreisBrutto=$versandart->fPreis bSteuer=false}
                            {/if}
                        </td>
                    </tr>
                    {if $versandart->fVersandkostenfreiAbX>0}
                        <tr>
                            <td>{__('freeFrom')}</td>
                            <td>{getCurrencyConversionSmarty fPreisBrutto=$versandart->fVersandkostenfreiAbX bSteuer=false} ({if $versandart->eSteuer === 'netto'}{__('net')}{else}{__('gross')}{/if})</td>
                        </tr>
                    {/if}
                    {if $versandart->fDeckelung>0}
                        <tr>
                            <td>{__('maxCostsUpTo')}</td>
                            <td>{getCurrencyConversionSmarty fPreisBrutto=$versandart->fDeckelung bSteuer=false}</td>
                        </tr>
                    {/if}
                </tbody>
            </table>
            <div class="panel-footer">
                <form method="post" action="versandarten.php">
                    {$jtl_token}
                    <div class="btn-group">
                        <button name="edit" value="{$versandart->kVersandart}" class="btn btn-primary"><i class="fa fa-edit"></i> {__('edit')}</button>
                        <button name="clone" value="{$versandart->kVersandart}" class="btn btn-default clone">{__('duplicate')}</button>
                        <button name="del" value="{$versandart->kVersandart}" class="btn btn-danger" onclick="return confirmDelete('{$versandart->cName}');"><i class="fa fa-trash"></i> {__('delete')}</button>
                    </div>
                </form>
            </div>
        </div>
    {/foreach}

    <div id="settings">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{__('createShippingMethod')}</h3>
            </div>
            <form name="versandart_neu" method="post" action="versandarten.php">
                {$jtl_token}
                <div class="panel-body">
                    <input type="hidden" name="neu" value="1" />
                    {foreach $versandberechnungen as $versandberechnung}
                        <div class="item">
                            <div class="for">
                                <input type="radio" id="l{$versandberechnung@index}" name="kVersandberechnung" value="{$versandberechnung->kVersandberechnung}" {if $versandberechnung@index == 0}checked="checked"{/if} />
                                <label for="l{$versandberechnung@index}">{$versandberechnung->cName}</label>
                            </div>
                        </div>
                    {/foreach}
                </div>
                <div class="panel-footer">
                    <button type="submit" value="{__('createShippingMethod')}" class="btn btn-primary"><i class="fa fa-share"></i> {__('createShippingMethod')}</button>
                </div>
            </form>
        </div>
    </div>
</div>