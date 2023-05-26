{block name='account-retouren-form'}
    {block name='account-retoure-form-form'}
        {row}
            {col cols=12 md=6 class='retouren-form-wrapper'}
                {form method="post" id='retouren' action="#" class="jtl-validate" slide=true}
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
                                                        <option value="{$lfa->kLieferadresse}" {if $countryISO === $land->getISO()}selected="selected"{/if}>{$land->getName()}</option>
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
                            {col md=12 xl=6 class="checkout-button-row-submit mb-3"}
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
            {col cols=12 md=6 class='retouren-wrapper'}
                {block name='account-retoure-form-form-retoure-wrapper'}
                    <table id="retouren-liste" class="table display compact">
                        <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
                        {block name='account-retouren-form-form-retoure'}
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
                                        <div id="retoureAdditional{$rma->kRetoure}" class="collapse mt-3">
                                            {block name='account-retoure-include-inc-positions'}
                                                {if $rma->PositionenArr|count > 0}
                                                    {block name='account-retoure-include-inc-positions-table'}
                                                        <table class="table dropdown-cart-items">
                                                            <tbody>
                                                            {block name='account-retoure-include-inc-positions-table-item'}
                                                                {foreach $rma->PositionenArr as $oPosition}
                                                                    {$oPosition->fuelleArtikel()}
                                                                    <tr>
                                                                        <td>
                                                                            {formrow}
                                                                                {block name='account-retoure-include-inc-positions-table-item-image'}
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
                                                                                {block name='account-retoure-include-inc-positions-table-item-desc'}
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
                                                                                    {/col}
                                                                                {/block}
                                                                            {/formrow}
                                                                        </td>
                                                                        {block name='account-retoure-include-inc-positions-table-item-price'}
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
                                        data=["toggle"=> "collapse", "target"=>"#retoureAdditional{$rma->kRetoure}"]}
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
            {/col}
        {/row}
    {/block}
    {block name='account-retoure-form-script'}
        {inline_script}<script>
            $(document).ready(function () {
                let tableID = '#retouren-liste';
                $(tableID).DataTable( {
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
                    lengthMenu: [ [3, 5, 10], [3, 5, 10] ],
                    pageLength: 3,
                    order: [0, 'desc'],
                    initComplete: function (settings, json) {
                        $('.dataTables_filter input[type=search]').removeClass('form-control-sm');
                        $('.dataTables_length select').removeClass('custom-select-sm form-control-sm');
                    },
                    drawCallback: function( settings ) {
                        $('table.dataTable thead').remove();
                    },
                } );
            });
        </script>{/inline_script}
    {/block}
{/block}
