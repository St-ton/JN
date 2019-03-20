<script type="text/javascript">
    {literal}
    function confirmDelete(cName) {
        return confirm('{/literal}{__('deleteShippingMethod')}{literal}"' + cName + '"?');
    }
    {/literal}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=__('shippingmethods') cBeschreibung=__('isleListsHint') cDokuURL=__('shippingmethodsURL')}

<div id="content" class="container-fluid">
    <div>
        <ul>
            <li class="btn btn-primary dropdown">
                <a href="#" class="dropdown-toggle parent" data-toggle="dropdown">
                    <i class="fa fa-plus"></i> Neue Versandart anlegen
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                {foreach $versandberechnungen as $versandberechnung}
                    <li>
                        <form name="versandart_neu" method="post" action="versandarten.php">
                            {$jtl_token}
                            <input type="hidden" name="neu" value="1" />
                            <input type="hidden" id="l{$versandberechnung@index}" name="kVersandberechnung" value="{$versandberechnung->kVersandberechnung}" {if $versandberechnung@index == 0}checked="checked"{/if} />
                            <button type="submit" class="btn btn-link">{$versandberechnung->cName}</button>
                        </form>
                    </li>
                {/foreach}
                </ul>
            </li>
        </ul>
    </div>
    <table class="list table">
        <thead>
        <tr>
            <th>{__('shippingTypeName')}</th>
            <th>{__('shippingclasses')}</th>
            <th>{__('customerclass')}</th>
            <th>{__('paymentMethods')}</th>
            <th>Versandpreis</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {foreach $versandarten as $versandart}
            <tr>
                <td>{$versandart->cName}<br />
                    {foreach $versandart->land_arr as $land}
                        <a href="versandarten.php?zuschlag=1&kVersandart={$versandart->kVersandart}&cISO={$land}&token={$smarty.session.jtl_token}"><span class="label label-{if isset($versandart->zuschlag_arr[$land])}success{else}default{/if}">{$land}</span></a>
                    {/foreach}
                </td>
                <td>
                    {if $versandart->versandklassen|@count == 1 && $versandart->versandklassen[0] === 'Alle'}
                        {$versandart->versandklassen[0]}
                    {else}
                        {foreach $versandart->versandklassen as $versandklasse}
                            [{$versandklasse}] &nbsp;
                        {/foreach}
                    {/if}
                </td>
                <td>
                    {foreach $versandart->cKundengruppenName_arr as $cKundengruppenName}
                        {$cKundengruppenName}
                    {/foreach}
                </td>
                <td>
                    {foreach $versandart->versandartzahlungsarten as $zahlungsart}
                        {$zahlungsart->zahlungsart->cName}{if isset($zahlungsart->zahlungsart->cAnbieter) &&
                    $zahlungsart->zahlungsart->cAnbieter|strlen > 0} ({$zahlungsart->zahlungsart->cAnbieter}){/if} {if $zahlungsart->fAufpreis!=0}{if $zahlungsart->cAufpreisTyp != "%"}{getCurrencyConversionSmarty fPreisBrutto=$zahlungsart->fAufpreis bSteuer=false}{else}{$zahlungsart->fAufpreis}%{/if}{/if}
                        <br />
                    {/foreach}
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
                <td>
                    <form method="post" action="versandarten.php">
                        {$jtl_token}
                        <div class="btn-group">
                            <button name="edit" value="{$versandart->kVersandart}" class="btn btn-link"><i class="fa fa-edit"></i></button>
                            <button name="clone" value="{$versandart->kVersandart}" class="btn btn-link clone"><i class="fa fa-clone"></i></button>
                            <button name="del" value="{$versandart->kVersandart}" class="btn btn-link" onclick="return confirmDelete('{$versandart->cName}');"><i class="fa fa-trash"></i></button>
                        </div>
                    </form>
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>