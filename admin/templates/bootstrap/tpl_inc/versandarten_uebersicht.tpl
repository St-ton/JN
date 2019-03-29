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
                    <i class="fa fa-plus"></i> {__('createShippingMethod')}
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
            <th>{__('shippingPrice')}</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {foreach $versandarten as $versandart}
            <tr>
                <td>{$versandart->cName}<br />
                    {foreach $versandart->land_arr as $land}
                        <a href="#"
                           data-toggle="modal"
                           data-target="#zuschlagliste-modal"
                           data-shipping-method="{$versandart->kVersandart}"
                           data-iso="{$land}"
                           data-shipping-method-name="{$versandart->cName}"
                        >
                            <span class="label label-{if isset($versandart->zuschlag_arr[$land])}success{else}default{/if}">{$land}</span>
                        </a>
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
<div class="modal fade" id="zuschlagliste-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fa fa-times"></i>
                </button>
                <h4 class="modal-title">{__('surchargeListFor')} <span id="surcharge-modal-title"></span></h4>
            </div>
            <div class="modal-body">
                <form id="zuschlag-new" method="post" action="versandarten.php">
                    {$jtl_token}
                    <input type="hidden" name="neuerZuschlag" value="1" />
                    <input type="hidden" name="kVersandart" value="0" />
                    <input type="hidden" name="cISO" value="" />
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{__('createNewList')}</h3>
                        </div>
                        <div class="panel-body">
                            <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cName">{__('isleList')}</label>
                        </span>
                                <input class="form-control" type="text" id="cName" name="cName" value="{if isset($oVersandzuschlag->cName)}{$oVersandzuschlag->cName}{/if}" tabindex="1" required/>
                            </div>
                            {foreach $sprachen as $sprache}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="cName_{$sprache->cISO}">{__('showedName')} ({$sprache->cNameDeutsch})</label>
                                    </span>
                                    <input class="form-control" type="text" id="cName_{$sprache->cISO}" name="cName_{$sprache->cISO}" value=""/>
                                </div>
                            {/foreach}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="fZuschlag">{__('additionalFee')} ({__('amount')})</label>
                                </span>
                                <input type="text" id="fZuschlag" name="fZuschlag" value="" class="form-control price_large" required>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <div class="btn-group">
                                <button id="zuschlag-new-submit" type="submit" value="" class="btn btn-primary">
                                    <i class="fa fa-save"></i> {__('createNew')}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="zuschlaglisten">

                </div>
                <button type="button" class="btn btn-danger" data-dismiss="modal" id="zuschlagliste-cancel-btn">
                    <i class="fa fa-times"></i>
                    {__('cancel')}
                </button>
            </div>
        </div>
    </div>
</div>

{literal}
<script>
    $(document).ready(function () {
        activateAjaxLoadingSpinner();
        $('a[data-target="#zuschlagliste-modal"]').click(function () {
            $('#zuschlaglisten').html('');
            $('#surcharge-modal-title').html($(this).data('shipping-method-name') + ', ' + $(this).data('iso'));
            $('#zuschlag-new input[name="kVersandart"').val($(this).data('shipping-method'));
            $('#zuschlag-new input[name="cISO"').val($(this).data('iso'));
            ioCall('getZuschlagsListen', [$(this).data('shipping-method'), $(this).data('iso')], function (data) {
                $('#zuschlaglisten').html(data.body);
            });
        });
        $('#zuschlag-new-submit').click(function(e){
            e.preventDefault();
            ioCall('createZuschlagsListe', [$('#zuschlag-new').serializeArray()], function (data) {
                $('#zuschlaglisten').html(data.body);
            });
        });
    });
</script>
{/literal}
