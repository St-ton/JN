{block name='account-shipping-address-form'}
    {row class='btn-row'}
        {block name='account-shipping-address-form-form-lieferadressen'}
            {col md=6}
                {form method="post" id='lieferadressen' action="{get_static_route params=['editLieferadresse' => 1]}" class="jtl-validate" slide=true}
                    {block name='account-inc-shipping-address-include-customer-shipping-address-first'}
                        {include file='../checkout/customer_shipping_address.tpl' prefix="register" fehlendeAngaben=null}
                    {/block}
                    {block name='account-address-form-form-submit'}
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
            {col md=6}
                {block name='checkout-inc-shipping-address-fieldset-address'}
                    <table id="lieferadressen-liste" class="table-striped display compact" style="width:100%">
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
                        {foreach $Lieferadressen as $adresse}
                            <tr>
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
                                <td style="max-width: 40px" class="text-right">
                                    {if $adresse->nIstStandardLieferadresse !== 1}
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="{lang key='useAsDefaultShippingAddress' section='account data'}" onclick="location.href='{get_static_route id='jtl.php' params=['editLieferadresse' => 1, 'setAddressAsDefault' => {$adresse->kLieferadresse}]}'">
                                            <span class="fas fa-star"></span>
                                        </button>
                                    {/if}

                                    <button type="button" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="{lang key='editAddress' section='account data'}" onclick="location.href='{get_static_route id='jtl.php' params=['editLieferadresse' => 1, 'editAddress' => {$adresse->kLieferadresse}]}'">
                                        <span class="fas fa-pencil-alt"></span>
                                    </button>

                                    <button type="button" class="btn btn-danger btn-sm delete-popup-modal" data-lieferadresse="{$adresse->kLieferadresse}" data-toggle="tooltip" data-placement="top" title="{lang key='deleteAddress' section='account data'}">
                                        <span class="fas fa-times"></span>
                                    </button>
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
            {/col}
        {/block}
    {/row}
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

        var table = $('#lieferadressen-liste').DataTable( {
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
            lengthMenu: [ [5, 10, 25, 50, -1], [5, 10, 25, 50, "Alle"] ],
            pageLength: 5,
            order: [6, 'desc']
        } );

        $('#lieferadressen-liste tbody').on('click', 'td.dt-control', function () {
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

        $('.delete-popup-modal').on('click',function(){
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

</script>
