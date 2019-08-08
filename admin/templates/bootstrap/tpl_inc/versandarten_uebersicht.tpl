<script type="text/javascript">
    {literal}
    function confirmDelete(cName) {
        return confirm('{/literal}{__('deleteShippingMethod')}{literal}"' + cName + '"?');
    }
    {/literal}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=__('shippingmethods') cBeschreibung=__('isleListsHint') cDokuURL=__('shippingmethodsURL')}

<div id="content" class="container-fluid">
    <div class="dropdown">
        <button class="btn btn-primary" type="button" id="versandart" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="fal fa-plus mr-2"></span>{__('createShippingMethod')}
        </button>
        <div class="dropdown-menu" aria-labelledby="versandart">
            {foreach $versandberechnungen as $versandberechnung}
                <a class="dropdown-item">
                    <form name="versandart_neu" method="post" action="versandarten.php">
                        {$jtl_token}
                        <input type="hidden" name="neu" value="1" />
                        <input type="hidden" id="l{$versandberechnung@index}" name="kVersandberechnung" value="{$versandberechnung->kVersandberechnung}" {if $versandberechnung@index == 0}checked="checked"{/if} />
                        <button type="submit" class="btn btn-link">{$versandberechnung->cName}</button>
                    </form>
                </a>
            {/foreach}
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table">
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
                        <td>{$versandart->cName}
                            <span class="d-block">
                            {foreach $versandart->land_arr as $land}
                                <a href="versandarten.php?zuschlag=1&kVersandart={$versandart->kVersandart}&cISO={$land}&token={$smarty.session.jtl_token}">
                                    <span class="small text-muted">
                                        {if isset($versandart->zuschlag_arr[$land])}<u>{$land}*</u>{else}{$land}{/if}
                                    </span>
                                    {if !$land@last},{/if}
                                </a>
                            {/foreach}
                            </span>
                        </td>
                        <td>
                            <ul class="list-unstyled">
                            {if $versandart->versandklassen|@count == 1 && $versandart->versandklassen[0] === 'Alle'}
                                <li><span class="badge badge-primary text-wrap">{__('all')}</span></li>
                            {else}
                                {foreach $versandart->versandklassen as $versandklasse}
                                    <li><span class="badge badge-primary text-wrap">{$versandklasse}</span></li>
                                {/foreach}
                            {/if}
                            </ul>
                        </td>
                        <td>
                            <ul class="list-unstyled">
                            {foreach $versandart->cKundengruppenName_arr as $cKundengruppenName}
                                <li>{$cKundengruppenName}</li>
                            {/foreach}
                            </ul>
                        </td>
                        <td>
                            <ul class="list-unstyled">
                            {foreach $versandart->versandartzahlungsarten as $zahlungsart}
                                <li>
                                    {$zahlungsart->zahlungsart->cName}
                                    {if isset($zahlungsart->zahlungsart->cAnbieter) && $zahlungsart->zahlungsart->cAnbieter|strlen > 0}
                                        ({$zahlungsart->zahlungsart->cAnbieter})
                                    {/if}
                                    {if $zahlungsart->fAufpreis!=0}
                                        {if $zahlungsart->cAufpreisTyp != "%"}
                                            {getCurrencyConversionSmarty fPreisBrutto=$zahlungsart->fAufpreis bSteuer=false}
                                        {else}
                                            {$zahlungsart->fAufpreis}%
                                        {/if}
                                    {/if}
                                </li>
                            {/foreach}
                            </ul>
                        </td>
                        <td>
                            <ul class="list-unstyled">
                            {if $versandart->versandberechnung->cModulId === 'vm_versandberechnung_gewicht_jtl' || $versandart->versandberechnung->cModulId === 'vm_versandberechnung_warenwert_jtl' || $versandart->versandberechnung->cModulId === 'vm_versandberechnung_artikelanzahl_jtl'}
                                {foreach $versandart->versandartstaffeln as $versandartstaffel}
                                    {if $versandartstaffel->fBis != 999999999}
                                        <li>{__('upTo')} {$versandartstaffel->fBis} {$versandart->einheit} {getCurrencyConversionSmarty fPreisBrutto=$versandartstaffel->fPreis bSteuer=false}</li>
                                    {/if}
                                {/foreach}
                            {elseif $versandart->versandberechnung->cModulId === 'vm_versandkosten_pauschale_jtl'}
                                <li>{getCurrencyConversionSmarty fPreisBrutto=$versandart->fPreis bSteuer=false}</li>
                            {/if}
                            </ul>
                        </td>
                        <td>
                            <form method="post" action="versandarten.php">
                                {$jtl_token}
                                <div class="btn-group">
                                    <button name="del"
                                            value="{$versandart->kVersandart}"
                                            class="btn btn-link px-2"
                                            onclick="return confirmDelete('{$versandart->cName}');"
                                            title="{__('delete')}"
                                            data-toggle="tooltip">
										<span class="icon-hover">
											<span class="fal fa-trash-alt"></span>
											<span class="fas fa-trash-alt"></span>
										</span>
                                    </button>
                                    <button name="clone"
                                            value="{$versandart->kVersandart}"
                                            class="btn btn-link px-2"
                                            title="{__('duplicate')}"
                                            data-toggle="tooltip">
										<span class="icon-hover">
											<span class="fal fa-clone"></span>
											<span class="fas fa-clone"></span>
										</span>
                                    </button>
                                    <button name="edit"
                                            value="{$versandart->kVersandart}"
                                            class="btn btn-link px-2"
                                            title="{__('edit')}"
                                            data-toggle="tooltip">
										<span class="icon-hover">
											<span class="fal fa-edit"></span>
											<span class="fas fa-edit"></span>
										</span>
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="zuschlagliste-modal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fa fa-times"></i>
                </button>
                <div class="subheading1">{__('surchargeListFor')} <span id="surcharge-modal-title"></span></div>
                <hr class="mb-3">
            </div>
            <div class="modal-body">
                <div id="new-surcharge-form-wrapper">
                    {include file='snippets/zuschlagliste_form.tpl'}
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
        $('a[data-target="#zuschlagliste-modal"]').on('click', function () {
            $('#zuschlaglisten').html('');
            $('#surcharge-modal-title').html($(this).data('shipping-method-name') + ', ' + $(this).data('iso'));
            $('#zuschlag-new input[name="kVersandart"').val($(this).data('shipping-method'));
            $('#zuschlag-new input[name="cISO"').val($(this).data('iso'));
            ioCall('getZuschlagsListen', [$(this).data('shipping-method'), $(this).data('iso')], function (data) {
                $('#zuschlaglisten').html(data.body);
            });
        });
        $('#zuschlag-new-submit').on('click', function(e){
            e.preventDefault();
            ioCall('saveZuschlagsListe', [$('#zuschlag-new').serializeArray()], function (data) {
                if (data.error) {
                    $('#zuschlag-new').prepend(data.message);
                }
                $('#zuschlaglisten').html(data.surcharges.body);
            });
        });
    });
</script>
{/literal}
