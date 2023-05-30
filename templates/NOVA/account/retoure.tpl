{block name='account-retouren-form'}
    {block name='account-retoure-form-form'}
        {row}
            {col cols=12 md=12 class='retouren-form-wrapper'}
                {block name='account-my-account-rma'}
                    {card no-body=true class="rma-positions"}
                        {cardheader}
                            {block name='rma-positions-header'}
                                {row class="align-items-center-util"}
                                    {col}
                                        <span class="h3">
                                            Positionen hinzufügen
                                        </span>
                                    {/col}
                                {/row}
                            {/block}
                        {/cardheader}
                        {cardbody}
                            {block name='rma-positions-body'}
                                <table id="returnable-items" class="table display compact">
                                    <thead>
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {block name='account-retouren-returnable-items'}
                                        {foreach $retournierbareArtikel as $rArtikel}
                                            <tr>
                                                <td class="d-none">123</td>
                                                <td>
                                                    <div class="d-block font-weight-bold">
                                                        {$rArtikel->cName}
                                                    </div>
                                                    {$rArtikel->Preis}
                                                </td>
                                                <td class="text-right">
                                                    <div class="d-inline-flex flex-nowrap">
                                                        <span data-switch-label-state="default-{$rArtikel->kArtikel}" class="">
                                                            Retournieren
                                                        </span>
                                                        <div class="custom-control custom-switch">
                                                            <input type='checkbox'
                                                               class='custom-control-input ra-switch'
                                                               id="ra-{$rArtikel->kArtikel}"
                                                               data-ra-id="{$rArtikel->kArtikel}"
                                                               {if false}checked{/if}
                                                               aria-label="Lorem ipsum">
                                                            <label class="custom-control-label" for="ra-{$rArtikel->kArtikel}"></label>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        {/foreach}
                                    {/block}
                                    </tbody>
                                </table>
                            {/block}
                        {/cardbody}
                    {/card}
                {/block}

                {form method="post" id='retouren' action="#" class="jtl-validate mt-3" slide=true}
                    {input name="Artikel" type="hidden" value=""}
                    {block name='account-retoure-form-include-customer-retouren'}
                        {block name='checkout-customer-shipping-address'}
                            <fieldset>
                                {formrow}
                                    {col cols=12}
                                        {block name='checkout-customer-shipping-address-country'}
                                            {formgroup label="{lang key='shippingAdress' section='account data'}" label-for="shippingAdress"}
                                                {select name="shippingAdress" id="shippingAdress" class="custom-select" autocomplete="shipping Adress"}
                                                    <option value="" selected disabled>{lang key='shippingAdress' section='account data'}</option>
                                                    {foreach $Lieferadressen as $lfa}
                                                        <option value="{$lfa->kLieferadresse}">
                                                            {if $lfa->cFirma}{$lfa->cFirma}, {/if}
                                                            {$lfa->cStrasse} {$lfa->cHausnummer},
                                                            {$lfa->cPLZ} {$lfa->cOrt}
                                                        </option>
                                                    {/foreach}
                                                {/select}
                                            {/formgroup}
                                        {/block}
                                    {/col}
                                {/formrow}
                            </fieldset>
                        {/block}
                    {/block}
                    {block name='account-retoure-form-form-submit'}
                        {row class='btn-row'}
                            {col md=12 xl=12 class="checkout-button-row-submit mb-3"}
                                {input type="hidden" name="editLieferadresse" value="1"}
                                {input type="hidden" name="editAddress" value="neu"}
                                {button type="submit" value="1" block=true variant="primary"}
                                    {lang key='createRetoure' section='rma'}
                                {/button}
                            {/col}
                        {/row}
                    {/block}
                {/form}
            {/col}
        {/row}
    {/block}
    {block name='account-retoure-form-script'}
        {inline_script}<script>
            function initDataTable(table, rows = 5) {
                table.DataTable( {
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
                        { data: 'sort' },
                        { data: 'address' },
                        { data: 'buttons' }
                    ],
                    lengthMenu: [ [rows, rows*2, rows*3], [rows, rows*2, rows*3] ],
                    pageLength: rows,
                    order: [0, 'desc'],
                    initComplete: function (settings, json) {
                        table.find('.dataTables_filter input[type=search]').removeClass('form-control-sm');
                        table.find('.dataTables_length select').removeClass('custom-select-sm form-control-sm');
                    },
                    drawCallback: function( settings ) {
                        table.find('thead').remove();
                    },
                } );
            }
            $(document).ready(function () {
                initDataTable($('#returnable-items'));
            });
        </script>{/inline_script}
    {/block}
{/block}
