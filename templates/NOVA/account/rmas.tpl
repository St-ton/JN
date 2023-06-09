{block name='account-rmas-form'}
    {block name='account-rma-form-form'}
        {row}
            {col cols=12 md=12 class='rmas-wrapper'}
                {block name='account-rma-form-form-rma-wrapper'}
                    {card no-body=true class="account-rma-shipping-addresses"}
                        {cardheader}
                            {block name='account-rma-shipping-addresses-header'}
                                {row class="align-items-center-util"}
                                    {col}
                                        <span class="h3">
                                            {lang key='myReturns' section='rma'}
                                        </span>
                                    {/col}
                                {/row}
                            {/block}
                        {/cardheader}
                        {cardbody}
                            {block name='account-rma-shipping-addresses-body'}
                                <table id="rmas-liste" class="table display compact stripe">
                                    <thead>
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {block name='account-rmas-form-form-rma'}
                                        {foreach $Retouren as $rma}
                                            {$rma->fuelleRetoure()}
                                            <tr>
                                                <td class="d-none">{$rma->dErstellt}</td>
                                                <td>
                                                    <div class="d-block font-weight-bold">
                                                        <span class="far fa-calendar mr-2"></span>{$rma->ErstelltDatum}
                                                    </div>
                                                    {if isset($rma->Lieferadresse)}
                                                        {if $rma->Lieferadresse->cFirma}{$rma->Lieferadresse->cFirma}<br />{/if}
                                                        {$rma->Lieferadresse->cStrasse} {$rma->Lieferadresse->cHausnummer}<br />
                                                        {$rma->Lieferadresse->cPLZ} {$rma->Lieferadresse->cOrt}
                                                    {/if}
                                                    <div id="rmaAdditional{$rma->kRetoure}" class="collapse mt-3">
                                                        {block name='account-rma-include-inc-positions'}
                                                            {if $rma->PositionenArr|count > 0}
                                                                {block name='account-rma-include-inc-positions-table'}
                                                                    <table class="table dropdown-cart-items">
                                                                        <tbody>
                                                                        {block name='account-rma-include-inc-positions-table-item'}
                                                                            {foreach $rma->PositionenArr as $oPosition}
                                                                                {$oPosition->fuelleArtikel()}
                                                                                <tr>
                                                                                    <td>
                                                                                        {formrow}
                                                                                            {block name='account-rma-include-inc-positions-table-item-image'}
                                                                                                {if isset($oPosition->Artikel)}
                                                                                                    {col class="col-auto"}
                                                                                                    {link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|trans|escape:'html'}
                                                                                                        {include file='snippets/image.tpl'
                                                                                                        fluid=false
                                                                                                        item=$oPosition->Artikel
                                                                                                        square=false
                                                                                                        srcSize='xs'
                                                                                                        sizes='50px'
                                                                                                        class='rma-img'}
                                                                                                    {/link}
                                                                                                    {/col}
                                                                                                {/if}
                                                                                            {/block}
                                                                                            {block name='account-rma-include-inc-positions-table-item-desc'}
                                                                                                {col class="col-auto"}
                                                                                                    {$oPosition->nAnzahl|replace_delim}x
                                                                                                {/col}
                                                                                                {col}
                                                                                                    {if isset($oPosition->Artikel)}
                                                                                                        {link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|trans|escape:'html'}
                                                                                                            {$oPosition->cName|trans}
                                                                                                        {/link}
                                                                                                    {else}
                                                                                                        {$oPosition->cName|trans}
                                                                                                    {/if}
                                                                                                    <small class="text-muted-util d-block">
                                                                                                        {lang key='orderNo' section='login'}: {$oPosition->cBestellNr}
                                                                                                    </small>
                                                                                                {/col}
                                                                                            {/block}
                                                                                        {/formrow}
                                                                                    </td>
                                                                                    {block name='account-rma-include-inc-positions-table-item-price'}
                                                                                        <td class="text-right-util text-nowrap-util">
                                                                                            {$oPosition->Preis}
                                                                                        </td>
                                                                                    {/block}
                                                                                </tr>
                                                                            {/foreach}
                                                                        {/block}
                                                                        </tbody>
                                                                    </table>
                                                                {/block}
                                                            {else}
                                                                {lang key='noDataAvailable'}
                                                            {/if}
                                                        {/block}
                                                    </div>
                                                    {button variant="link" class="btn-show-more"
                                                    data=["toggle"=> "collapse", "target"=>"#rmaAdditional{$rma->kRetoure}"]}
                                                    {lang key='showPositions' section='rma'}
                                                    {/button}
                                                </td>
                                                <td class="text-right">
                                                    {buttongroup}
                                                        <button type="button" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="{lang key='editAddress' section='account data'}" onclick="location.href='#'">
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
                        {/cardbody}
                    {/card}
                {/block}
            {/col}
        {/row}
    {/block}
    {block name='account-rmas-form-script'}
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
                    lengthMenu: [ [rows, rows*2, rows*3, rows*6, rows*10], [rows, rows*2, rows*3, rows*6, rows*10] ],
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
                initDataTable($('#rmas-liste'));
            });
        </script>{/inline_script}
    {/block}
{/block}
