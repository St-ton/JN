{block name='checkout-inc-shipping-address'}
    {assign var=fehlendeAngabenShipping value=$fehlendeAngaben.shippingAddress|default:null}
    {assign var=showShippingAddress value=(isset($Lieferadresse) || !empty($kLieferadresse) || isset($forceDeliveryAddress))}
    {row class="inc-shipping-address"}
    {col cols=12}
    {block name='checkout-inc-shipping-address-checkbox-equals'}
        <div class="form-group checkbox control-toggle">
            {input type="hidden" name="shipping_address" value="1"}
            {checkbox id="checkout_register_shipping_address"
            name="shipping_address" value="0" checked=!$showShippingAddress
            data=["toggle"=>"collapse", "target"=>"#select-shipping-address"]
            class="{if isset($forceDeliveryAddress)}d-none{/if}"
            }
            {lang key='shippingAdressEqualBillingAdress' section='account data'}
            {/checkbox}
        </div>
    {/block}
    {/col}
    {col cols=12}
    {block name='checkout-inc-shipping-address-shipping-address'}
        <div id="select-shipping-address" class="select-shipping-address collapse collapse-non-validate{if $showShippingAddress} show{/if}" aria-expanded="{if $showShippingAddress}true{else}false{/if}">
            {block name='checkout-inc-shipping-address-shipping-address-body'}
                {if JTL\Session\Frontend::getCustomer()->getID() > 0 && isset($Lieferadressen) && $Lieferadressen|count > 0}
                    {row}
                    {col cols=12 md=4}
                    {block name='checkout-inc-shipping-address-legend-address'}
                        <div class="h3">{lang key='deviatingDeliveryAddress' section='account data'}</div>
                    {/block}
                    {/col}
                    {col md=8}
                    {block name='checkout-inc-shipping-address-fieldset-address'}
                        <table id="shipping-address-templates" class="table table-hover display compact" style="width:100%">
                            <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $Lieferadressen as $adresse}
                                <tr>
                                    <td>
                                        <label class="btn-block no-caret text-wrap" for="delivery{$adresse->kLieferadresse}" data-toggle="collapse" data-target="#register_shipping_address.show">
                                            {radio name="kLieferadresse" value=$adresse->kLieferadresse id="delivery{$adresse->kLieferadresse}" checked=($kLieferadresse == $adresse->kLieferadresse || $adresse->nIstStandardLieferadresse == 1)}

                                            {/radio}
                                        </label>
                                    </td>
                                    <td></td>
                                    <td>
                                        {if $adresse->cFirma}{$adresse->cFirma}<br />{/if}
                                        <strong>{$adresse->cVorname} {$adresse->cNachname}</strong><br />
                                        {$adresse->cStrasse} {$adresse->cHausnummer}<br />
                                        {$adresse->cPLZ} {$adresse->cOrt}<br />
                                    </td>
                                    <td>
                                        {$adresse->cTitel}
                                    </td>
                                    <td>
                                        {$adresse->cBundesland}
                                    </td>
                                    <td>
                                        {$adresse->cAdressZusatz}
                                    </td>
                                    <td class="text-right">
                                        {link href="{get_static_route id='jtl.php' params=['editLieferadresse' => 1, 'editAddress' => {$adresse->kLieferadresse}, 'fromCheckout'=>1]}" class="btn btn-outline-primary btn-sm" alt="Adresse bearbeiten"}
                                            <span class="fas fa-pencil-alt"></span>
                                        {/link}
                                    </td>
                                    <td>
                                        <span class="invisible">
                                            {$adresse->nIstStandardLieferadresse}
                                        </span>
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    {/block}
                    {block name='checkout-inc-shipping-address-new-address-wrapper'}
                        <div class="new-shipping-address">
                        {block name='checkout-inc-shipping-address-fieldset-new-address'}
                            <label class="btn-block" for="delivery_new" data-toggle="collapse" data-target="#register_shipping_address:not(.show)">
                                {radio name="kLieferadresse" value="-1" id="delivery_new" checked=($kLieferadresse == -1) required=true aria-required=true}
                                    <span class="control-label label-default">{lang key='createNewShippingAdress' section='account data'}</span>
                                {/radio}
                            </label>
                        {/block}
                        {block name='checkout-inc-shipping-address-fieldset-register'}
                            <fieldset id="register_shipping_address" class="checkout-register-shipping-address collapse collapse-non-validate {if $kLieferadresse == -1} show{/if}" aria-expanded="{if $kLieferadresse == -1}true{else}false{/if}">
                                {block name='checkout-inc-shipping-address-legend-register'}
                                    <legend>{lang key='createNewShippingAdress' section='account data'}</legend>
                                {/block}
                                {block name='checkout-inc-shipping-address-include-customer-shipping-address'}
                                    {include file='checkout/customer_shipping_address.tpl' prefix="register" fehlendeAngaben=$fehlendeAngabenShipping}
                                {/block}
                                {block name='checkout-inc-shipping-address-include-customer-shipping-contact'}
                                    {include file='checkout/customer_shipping_contact.tpl' prefix="register" fehlendeAngaben=$fehlendeAngabenShipping}
                                {/block}
                            </fieldset>
                        {/block}
                        </div>
                    {/block}
                    {/col}
                    {/row}
                {else}
                    {row}
                    {col cols=12 md=4}
                    {block name='checkout-inc-shipping-address-legend-register-first'}
                        <div class="h3">{lang key='createNewShippingAdress' section='account data'}</div>
                    {/block}
                    {/col}
                    {col md=8}
                    {block name='checkout-inc-shipping-address-include-customer-shipping-address-first'}
                        {include file='checkout/customer_shipping_address.tpl' prefix="register" fehlendeAngaben=$fehlendeAngabenShipping}
                    {/block}
                    {block name='checkout-inc-shipping-address-include-customer-shipping-contact-first'}
                        {include file='checkout/customer_shipping_contact.tpl' prefix="register" fehlendeAngaben=$fehlendeAngabenShipping}
                    {/block}
                    {/col}
                    {/row}
                {/if}
            {/block}
        </div>
    {/block}
    {/col}
    {/row}
    {if isset($smarty.get.editLieferadresse) || $step === 'Lieferadresse'}
        {block name='checkout-inc-shipping-address-script-show-shipping-address'}
            {inline_script}<script>
                $(window).on('load', function () {
                    var $registerShippingAddress = $('#checkout_register_shipping_address');
                    if ($registerShippingAddress.prop('checked')) {
                        $registerShippingAddress.click();
                    }
                    $.evo.extended().smoothScrollToAnchor('#checkout_register_shipping_address');
                });
            </script>{/inline_script}
        {/block}
    {/if}
{/block}

<script>
    $(document).ready(function () {
        function format(d) {
            return (
                '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">' +
                '<tr>' +
                '<td>Titel:</td>' +
                '<td>' +
                d.titel +
                '</td>' +
                '</tr>' +
                '<tr>' +
                '<td>Adresszusatz:</td>' +
                '<td>' +
                d.adresszusatz +
                '</td>' +
                '</tr>' +
                '<tr>' +
                '<td>Bundesland:</td>' +
                '<td>' +
                d.bundesland +
                '</td>' +
                '</tr>' +
                '</table>'
            );
        }

        var table = $('#shipping-address-templates').DataTable( {
            language: {
                "lengthMenu":        "{lang key='lengthMenu' section='datatables'}",
                "info":              "{lang key='info' section='datatables'}",
                "infoEmpty":         "{lang key='infoEmpty' section='datatables'}",
                "infoFiltered":      "{lang key='infoFiltered' section='datatables'}",
                "search":            "",
                "searchPlaceholder": "{lang key='search' section='datatables'}",
                "zeroRecords":       "{lang key='zeroRecords' section='datatables'}",
                "paginate": {
                    "first":    "{lang key='paginatefirst' section='datatables'}",
                    "last":     "{lang key='paginatelast' section='datatables'}",
                    "next":     "{lang key='paginatenext' section='datatables'}",
                    "previous": "{lang key='paginateprevious' section='datatables'}"
                }
            },
            columns: [
                { data: 'select' },
               /* {
                    className: 'dt-control',
                    orderable: false,
                    data: null,
                    defaultContent: '',
                }, */
                { data: 'address' },
                { data: 'titel' },
                { data: 'bundesland' },
                { data: 'adresszusatz' },
                { data: 'buttons' },
                { data: 'sort' }
            ],
            select: {
                style: 'single'
            },
            columnDefs: [
                {
                    target: 3,
                    visible: false,
                },{
                    target: 4,
                    visible: false,
                },{
                    target: 5,
                    visible: false,
                },{
                    target: 7,
                    visible: false,
                }
            ],
            lengthMenu: [ [5, 10, 25, 50, -1], [5, 10, 25, 50, "{lang key='showAll'}"] ],
            pageLength: 5,
            order: [6, 'desc'],
            initComplete: function (settings, json) {
                $('.dataTables_filter input[type=search]').removeClass('form-control-sm');
                $('.dataTables_length select').removeClass('custom-select-sm form-control-sm');
            },
            drawCallback: function( settings ) {
                $('table.dataTable thead').remove();
            },
        } );

        $('#shipping-address-templates tbody').on('click', 'td.dt-control', function () {
            let tr = $(this).closest('tr'),
                row = table.row(tr);
            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Open this row
                row.child(format(row.data())).show();
                tr.addClass('shown');
            }
        });
    });
</script>
