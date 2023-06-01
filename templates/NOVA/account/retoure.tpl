{block name='account-retouren-form'}
    {block name='account-retoure-form-form'}
        {form method="post"
        action="{if isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}{$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL()}{else}{$ShopURL}/{/if}{if $bExclusive}?exclusive_content=1{/if}"
        class="jtl-validate shipping-calculator-form row"
        id="shipping-calculator-form"
        slide=true}
            {input type="hidden" name="s" value=$Link->getID()}
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
                                <div class="col-sm-12 col-md-4 dataTable-custom-filter">
                                    {select name="bestellnummer" aria=["label"=>"Bestellnummer"]
                                    class="custom-select custom-select-sm form-control form-control-sm"}
                                        <option value="" selected>Alle Bestellungen</option>
                                        {foreach $retournierbareBestellungen as $rBestellung}
                                            <option value="{$rBestellung}">{$rBestellung}</option>
                                        {/foreach}
                                    {/select}
                                </div>
                                <table id="returnable-items" class="table display compact">
                                    <thead>
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {block name='account-retouren-returnable-items'}
                                        {foreach $retournierbareArtikel as $rArtikel}
                                            <tr data-id="ra-{$rArtikel->cBestellNr}-{$rArtikel->kArtikel}">
                                                <td class="d-none">{$rArtikel->cBestellNr}</td>
                                                <td class="product-thumbnail">
                                                    {include file='snippets/image.tpl'
                                                    item=$rArtikel->Artikel
                                                    square=false
                                                    srcSize='sm'
                                                    sizes='80px'
                                                    width='80'
                                                    height='80'
                                                    class='img-aspect-ratio'
                                                    alt=$rArtikel->cName}
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap">
                                                        <div class="flex-grow-1">
                                                            <span class="font-weight-bold">{$rArtikel->cName}</span>
                                                            <small class="text-muted-util d-block">
                                                                Bestellnummer: {$rArtikel->cBestellNr}<br>
                                                                {$rArtikel->fAnzahl} {$rArtikel->cEinheit|default:''} x {$rArtikel->Preis}
                                                            </small>
                                                        </div>
                                                        <div class="d-none formFields flex-wrap">
                                                            <div class="qty-wrapper dropdown max-w-sm mr-2 mb-2">
                                                                {inputgroup id="quantity-grp{$rArtikel->cBestellNr}-{$rArtikel->kArtikel}" class="form-counter choose_quantity"}
                                                                {inputgroupprepend}
                                                                {button variant="" class="btn-decrement"
                                                                data=["count-down"=>""]
                                                                aria=["label"=>{lang key='decreaseQuantity' section='aria'}]}
                                                                    <span class="fas fa-minus"></span>
                                                                {/button}
                                                                {/inputgroupprepend}
                                                                {input type="number"
                                                                required=($rArtikel->Artikel->fAbnahmeintervall > 0)
                                                                step="{if $rArtikel->Artikel->cTeilbar === 'Y' && $oPosition->Artikel->fAbnahmeintervall == 0}any{elseif $rArtikel->Artikel->fAbnahmeintervall > 0}{$rArtikel->Artikel->fAbnahmeintervall}{else}1{/if}"
                                                                id="quantity[{$rArtikel->cBestellNr}-{$rArtikel->kArtikel}]" class="quantity" name="anzahl[{$rArtikel->cBestellNr}-{$rArtikel->kArtikel}]"
                                                                aria=["label"=>"{lang key='quantity'}"]
                                                                value=$rArtikel->fAnzahl
                                                                data=[
                                                                "decimals"=>{getDecimalLength quantity=$rArtikel->Artikel->fAbnahmeintervall},
                                                                "product-id"=>"{if isset($rArtikel->Artikel->kVariKindArtikel)}{$rArtikel->Artikel->kVariKindArtikel}{else}{$rArtikel->Artikel->kArtikel}{/if}"
                                                                ]
                                                                }
                                                                {inputgroupappend}
                                                                {button variant="" class="btn-increment"
                                                                data=["count-up"=>""]
                                                                aria=["label"=>{lang key='increaseQuantity' section='aria'}]}
                                                                    <span class="fas fa-plus"></span>
                                                                {/button}
                                                                {/inputgroupappend}
                                                                {/inputgroup}
                                                            </div>

                                                            <div class="flex-fill mr-2 mb-2">
                                                                {select name="" aria=["label"=>""]
                                                                class="custom-select form-control"}
                                                                    <option value="-1" selected>Grund</option>
                                                                    <option value="0">Artikel defekt</option>
                                                                    <option value="1">Artikel nicht geliefert</option>
                                                                    <option value="2">Sonstiges</option>
                                                                {/select}
                                                            </div>

                                                            <div class="flex-fill mr-2 mb-2">
                                                                {textarea id="" name="cKommentar"}{/textarea}
                                                            </div>
                                                        </div>

                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <div class="d-inline-flex flex-nowrap">
                                                        <div class="custom-control custom-switch">
                                                            <input type='checkbox'
                                                                   class='custom-control-input ra-switch'
                                                                   id="ra-{$rArtikel->cBestellNr}-{$rArtikel->kArtikel}"
                                                                   data-ra-id="{$rArtikel->kArtikel}"
                                                                   data-rb-id="{$rArtikel->cBestellNr}"
                                                               {if false}checked{/if}
                                                               aria-label="Lorem ipsum">
                                                            <label class="custom-control-label"
                                                                   for="ra-{$rArtikel->cBestellNr}-{$rArtikel->kArtikel}">
                                                            </label>
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
        {/form}
    {/block}
    {block name='account-retoure-form-script'}
        {inline_script}<script>
            function initDataTable(tableID, rows = 5) {
                let table = $(tableID);
                return table.DataTable( {
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
                        { data: 'image' },
                        { data: 'product' },
                        { data: 'buttons' }
                    ],
                    lengthMenu: [ [rows, rows*2, rows*3], [rows, rows*2, rows*3] ],
                    pageLength: rows,
                    order: [0, 'desc'],
                    initComplete: function (settings, json) {
                        table.find('.dataTables_filter input[type=search]').removeClass('form-control-sm');
                        table.find('.dataTables_length select').removeClass('custom-select-sm form-control-sm');
                        $(tableID + '_wrapper').find('> .row > .col-md-6').removeClass('col-md-6').addClass('col-md-4');
                        $('.dataTable-custom-filter').prependTo($(tableID + '_wrapper > .row:first-child'));
                    },
                    drawCallback: function( settings ) {
                        table.find('thead').remove();
                    },
                } );
            }

            function toggleArtikel(artikel, active) {
                let raID = artikel.data('ra-id');
                let rbID = artikel.data('rb-id');
                if (active) {
                    $('*[data-id="ra-' + rbID + '-' + raID + '"] .formFields').removeClass("d-none").addClass("d-flex");
                } else {
                    $('*[data-id="ra-' + rbID + '-' + raID + '"] .formFields').removeClass("d-flex").addClass("d-none");
                }
            }

            $(document).ready(function () {
                let orderID = $('.dataTable-custom-filter select[name="bestellnummer"]');

                $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                    var bNummer = orderID.val();
                    var bestellnummern = data[0] || '';

                    if (bNummer === bestellnummern || bNummer === '') {
                        return true;
                    }

                    return false;
                });

                let table = initDataTable('#returnable-items');

                orderID.on('change', function () {
                    table.draw();
                });

                $('.ra-switch').on('change', function () {
                    toggleArtikel($(this), $(this).prop('checked'));
                });
            });
        </script>{/inline_script}
    {/block}
{/block}
