{block name='account-retouren-form'}
    {block name='account-retoure-form-form'}
        {row}
        {col cols=12 md=6 class='retouren-form-wrapper'}
        {form method="post" id='retouren' action="#" class="jtl-validate" slide=true}
        {block name='account-retoure-form-include-customer-retouren'}

        {/block}
        {block name='account-retoure-form-form-submit'}
            {row class='btn-row'}
            {col md=12 xl=6 class="checkout-button-row-submit mb-3"}
            {input type="hidden" name="editLieferadresse" value="1"}
            {input type="hidden" name="editAddress" value="neu"}
            {button type="submit" value="1" block=true variant="primary"}
            {lang key='saveAddress' section='account data'}
            {/button}
            {/col}
            {/row}
        {/block}
        {/form}
        {/col}
        {col cols=12 md=6 class='retouren-wrapper'}
        {block name='account-retoure-form-form-retoure-wrapper'}
            <table id="retouren-liste" class="table display compact">
                <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                {block name='account-retouren-form-form-retoure'}
                    {foreach $Retouren as $retoure}
                        <tr>
                            <td>
                                {if $retoure->cFirma}<br />{/if}
                                <strong>{if $address->cTitel}{$address->cTitel}{/if} {$address->cVorname} {$address->cNachname}</strong><br />
                                {$address->cStrasse} {$address->cHausnummer}<br />
                                {$address->cPLZ} {$address->cOrt}<br />
                                <div id="retoureAdditional{$retoure->kRetoure}" class="collapse">
                                    {block name='account-retoure-include-inc-positions'}

                                    {/block}
                                </div>
                                {button variant="link" class="btn-show-more"
                                data=["toggle"=> "collapse", "target"=>"#deliveryAdditional{$address->kLieferadresse}"]}
                                {lang  key='showMore'}
                                {/button}
                            </td>
                            <td class="text-right">
                                {buttongroup}
                                    <button type="button" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="{lang key='editAddress' section='account data'}" onclick="location.href='{get_static_route id='jtl.php' params=['editLieferadresse' => 1, 'editAddress' => {$address->kLieferadresse}]}'">
                                        <span class="fas fa-pencil-alt"></span>
                                    </button>
                                {/buttongroup}
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
    {block name='account-retoure-form-script'}
        {inline_script}<script>
            $(document).ready(function () {
                let tableID = '#retouren-liste';
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
                        { data: 'address' },
                        { data: 'buttons' },
                        { data: 'sort' }
                    ],
                    columnDefs: [
                        {
                            targets: [2],
                            visible: false,
                        }
                    ],
                    lengthMenu: [ [5, 10, 25, 50, -1], [5, 10, 25, 50, "{lang key='showAll'}"] ],
                    pageLength: 5,
                    order: [2, 'desc'],
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
