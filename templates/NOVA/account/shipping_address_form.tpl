{block name='account-shipping-address-form'}
    {block name='account-shipping-address-form-form'}
        {row}
            {col cols=12 md=6 class='shipping-address-form-wrapper'}
                {form method="post" id='lieferadressen' action="{get_static_route params=['editLieferadresse' => 1]}" class="jtl-validate" slide=true}
                    {block name='account-shipping-address-form-include-customer-shipping-address'}
                        {include file='checkout/customer_shipping_address.tpl' prefix="register" fehlendeAngaben=null}
                    {/block}
                    {block name='account-shipping-address-form-form-submit'}
                        {row class='btn-row'}
                            {col md=8 xl=6 class="checkout-button-row-submit"}
                                {input type="hidden" name="editLieferadresse" value="1"}
                                {if isset($Lieferadresse->nIstStandardLieferadresse) && $Lieferadresse->nIstStandardLieferadresse === 1}
                                    {input type="hidden" name="isDefault" value=1}
                                {/if}
                                {if isset($Lieferadresse->kLieferadresse) && !isset($smarty.get.fromCheckout)}
                                    {input type="hidden" name="updateAddress" value=$Lieferadresse->kLieferadresse}
                                    {button type="submit" value="1" block=true variant="primary"}
                                        {lang key='updateAddress' section='account data'}
                                    {/button}
                                {elseif !isset($Lieferadresse->kLieferadresse)}
                                    {input type="hidden" name="editAddress" value="neu"}
                                    {button type="submit" value="1" block=true variant="primary"}
                                        {lang key='saveAddress' section='account data'}
                                    {/button}
                                {elseif isset($Lieferadresse->kLieferadresse) && isset($smarty.get.fromCheckout)}
                                    {input type="hidden" name="updateAddress" value=$Lieferadresse->kLieferadresse}
                                    {input type="hidden" name="backToCheckout" value="1"}
                                    {button type="submit" value="1" block=true variant="primary"}
                                        {lang key='updateAddressBackToCheckout' section='account data'}
                                    {/button}
                                {/if}
                            {/col}
                        {/row}
                    {/block}
                {/form}
            {/col}
            {col cols=12 md=6 class='shipping-addresses-wrapper'}
                {block name='account-shipping-address-form-form-address-wrapper'}
                    <table id="lieferadressen-liste" class="table display compact" style="width:100%">
                        <thead>
                            <tr>
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
                            {block name='account-shipping-address-form-form-addresses'}
                            {foreach $Lieferadressen as $address}
                                <tr>
                                    <td></td>
                                    <td>
                                        {if $address->cFirma}{$address->cFirma}<br />{/if}
                                        <strong>{$address->cVorname} {$address->cNachname}</strong><br />
                                        {$address->cStrasse} {$address->cHausnummer}<br />
                                        {$address->cPLZ} {$address->cOrt}<br />
                                    </td>
                                    <td>
                                        {$address->cTitel}
                                    </td>
                                    <td>
                                        {$address->cBundesland}
                                    </td>
                                    <td>
                                        {$address->cAdressZusatz}
                                    </td>
                                    <td class="text-right">
                                        {buttongroup}
                                            {if $Einstellungen.kaufabwicklung.bestellvorgang_kaufabwicklungsmethode == 'N' && $address->nIstStandardLieferadresse !== 1}
                                                <button type="button" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="{lang key='useAsDefaultShippingAddress' section='account data'}" onclick="location.href='{get_static_route id='jtl.php' params=['editLieferadresse' => 1, 'setAddressAsDefault' => {$address->kLieferadresse}]}'">
                                                    <span class="fas fa-star"></span>
                                                </button>
                                            {/if}

                                            <button type="button" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="{lang key='editAddress' section='account data'}" onclick="location.href='{get_static_route id='jtl.php' params=['editLieferadresse' => 1, 'editAddress' => {$address->kLieferadresse}]}'">
                                                <span class="fas fa-pencil-alt"></span>
                                            </button>

                                            <button type="button" class="btn btn-danger btn-sm delete-popup-modal" data-lieferadresse="{$address->kLieferadresse}" data-toggle="tooltip" data-placement="top" title="{lang key='deleteAddress' section='account data'}">
                                                <span class="fas fa-times"></span>
                                            </button>
                                        {/buttongroup}
                                    </td>
                                    <td>
                                        <span class="invisible">
                                            {$address->nIstStandardLieferadresse}
                                        </span>
                                    </td>
                                </tr>
                            {/foreach}
                            {/block}
                        </tbody>
                    </table>
                {/block}
            {/col}
        {/row}
    {/block}
    {block name='account-shipping-address-form-script'}
        {inline_script}<script>
        $(document).ready(function () {
            function format(d) {
                return (
                    'Weitere Informationen'
                );
            }
            let tableID = '#lieferadressen-liste';
            let table = $(tableID).DataTable( {
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
                    {
                        className: 'dt-control',
                        orderable: false,
                        data: null,
                        defaultContent: '',
                    },
                    { data: 'address' },
                    { data: 'titel' },
                    { data: 'bundesland' },
                    { data: 'adresszusatz' },
                    { data: 'buttons' },
                    { data: 'sort' }
                ],
                columnDefs: [
                    {
                        target: 2,
                        visible: false,
                    },{
                        target: 3,
                        visible: false,
                    },{
                        target: 4,
                        visible: false,
                    },{
                        target: 6,
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

            $(tableID + ' tbody').on('click', 'td.dt-control', function () {
                let tr = $(this).closest('tr'),
                    row = table.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });

            $(tableID + ' tbody').on('click', '.delete-popup-modal',function(){
                let lieferadresse = $(this).data('lieferadresse');

                eModal.addLabel('{lang key='yes' section='global'}', '{lang key='no' section='global'}');
                let options = {
                    message: '{lang key='modalShippingAddressDeletionConfirmation' section='account data'}',
                    label: '{lang key='yes' section='global'}',
                    title: '{lang key='deleteAddress' section='account data'}'
                };
                eModal.confirm(options).then(
                    function() {
                        window.location = "{get_static_route id='jtl.php'}?editLieferadresse=1&deleteAddress="+lieferadresse
                    }
                );
            });
        });
    </script>{/inline_script}
    {/block}
{/block}
