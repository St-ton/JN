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
                                <div class="col-sm-12 col-md-4 dataTable-custom-filter">
                                    <label>
                                        {select name="bestellnummer" aria=["label"=>"Bestellnummer"]
                                        class="custom-select custom-select-sm form-control form-control-sm"}
                                            <option value="" selected>Alle Bestellungen</option>
                                            {foreach $retournierbareBestellungen as $rBestellung}
                                                <option value="{$rBestellung}">{$rBestellung}</option>
                                            {/foreach}
                                        {/select}
                                    </label>
                                </div>
                                <table id="returnable-items" class="table display compact">
                                    <thead>
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {block name='account-retouren-returnable-items'}
                                        {foreach $retournierbareArtikel as $rArtikel}
                                            <tr>
                                                <td class="d-none">{$rArtikel->cBestellNr}</td>
                                                <td class="product">
                                                    <div class="d-flex flex-wrap">
                                                        <div class="d-flex flex-nowrap flex-grow-1">
                                                            <div class="d-block">
                                                                {image lazy=true webp=true fluid=true
                                                                src=$rArtikel->Artikel->Bilder[0]->cURLKlein|default:$smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN
                                                                alt=$rArtikel->cName
                                                                class="img-aspect-ratio product-thumbnail pr-2"}
                                                            </div>

                                                            <div class="d-flex flex-nowrap flex-grow-1 flex-column">
                                                                <div class="d-inline-flex flex-nowrap justify-content-between">
                                                                    <span class="font-weight-bold">{$rArtikel->cName}</span>
                                                                    <div class="custom-control custom-switch">
                                                                        <input type='checkbox'
                                                                               class='custom-control-input ra-switch'
                                                                               id="switch-{$rArtikel->kLieferscheinPos}"
                                                                               name="returnItem"
                                                                               {if false}checked{/if}
                                                                               aria-label="Lorem ipsum">
                                                                        <label class="custom-control-label"
                                                                               for="switch-{$rArtikel->kLieferscheinPos}">
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <small class="text-muted-util d-block">
                                                                    Bestellnummer: {$rArtikel->cBestellNr}<br>
                                                                    Referenz: {link href=$rArtikel->cBestellNr}
                                                                        {$rArtikel->cArtNr}
                                                                    {/link}<br>
                                                                    {$rArtikel->fAnzahl} {$rArtikel->cEinheit|default:''} x {$rArtikel->Preis}
                                                                </small>
                                                            </div>
                                                        </div>

                                                        <div class="d-none retoureFormPositions flex-wrap mt-2 w-100">
                                                            <div class="qty-wrapper max-w-sm mr-2 mb-2">
                                                                {inputgroup id="quantity-grp{$rArtikel->kLieferscheinPos}" class="form-counter choose_quantity"}
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
                                                                min="1"
                                                                max="{$rArtikel->fAnzahl}"
                                                                id="qty-{$rArtikel->kLieferscheinPos}" class="quantity" name="quantity[]"
                                                                aria=["label"=>"{lang key='quantity'}"]
                                                                value=$rArtikel->fAnzahl
                                                                data=[
                                                                "decimals" => {getDecimalLength quantity=$rArtikel->Artikel->fAbnahmeintervall},
                                                                "product-id" => "{if isset($rArtikel->Artikel->kVariKindArtikel)}{$rArtikel->Artikel->kVariKindArtikel}{else}{$rArtikel->Artikel->kArtikel}{/if}",
                                                                "lid" => {$rArtikel->kLieferscheinPos}
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

                                                            <div class="mr-2 mb-2">
                                                                {select aria=["label"=>""]
                                                                name="reason[]"
                                                                data=[
                                                                "lid" => "{$rArtikel->kLieferscheinPos}"
                                                                ]
                                                                class="custom-select form-control"}
                                                                    <option value="-1" selected>Grund</option>
                                                                    <option value="0">Artikel defekt</option>
                                                                    <option value="1">Artikel nicht geliefert</option>
                                                                    <option value="2">Sonstiges</option>
                                                                {/select}
                                                            </div>

                                                            <div class="flex-grow-1 mr-2 mb-2">
                                                                {textarea name="comment[]"
                                                                data=[
                                                                "lid" => "{$rArtikel->kLieferscheinPos}"
                                                                ]
                                                                rows=1}{/textarea}
                                                            </div>
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

                {form method="post" id='retoure' action="#" class="jtl-validate mt-3" slide=true}
                    {block name='account-retoure-form-include-customer-retouren'}
                        {block name='checkout-customer-shipping-address'}
                            <fieldset>
                                {formrow}
                                    {col cols=12}
                                        {block name='checkout-customer-shipping-address-country'}
                                            {formgroup label="Abholadrese" label-for="shippingAdress"}
                                                {select name="shippingAdress" id="shippingAdress" class="custom-select"
                                                autocomplete="shipping Adress"}
                                                    <option value="" selected disabled>Abholadrese</option>
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
                        { data: 'product' }
                    ],
                    lengthMenu: [ [rows, rows*2, rows*3, rows*6, rows*10], [rows, rows*2, rows*3, rows*6, rows*10] ],
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

            function toggleProduct(artikel, active) {
                if (active) {
                    artikel.closest('tr').find('.retoureFormPositions').removeClass("d-none").addClass('d-flex');
                } else {
                    artikel.closest('tr').find('.retoureFormPositions').removeClass("d-flex").addClass('d-none');
                }
            }

            function setListenerForToggles(id='.ra-switch') {
                $(id).off('change').on('change', function () {
                    toggleProduct($(this), $(this).prop('checked'));
                });
            }

            $(document).ready(function () {
                const customFilter = $('.dataTable-custom-filter select[name="bestellnummer"]');

                // Filter by order id
                $.fn.dataTable.ext.search.push(function (settings, data) {
                    let orderId = customFilter.val(),
                        orderIds = data[0] || '';

                    return orderId === orderIds || orderId === '';
                });

                const table = initDataTable('#returnable-items');

                // Set toggle listener again when table redraws
                table.on('draw', function () {
                    setListenerForToggles();
                });

                customFilter.on('change', function () {
                    table.draw();
                });

                setListenerForToggles();

                $('.qty-wrapper .btn-decrement, .qty-wrapper .btn-increment').on('click', function () {
                    let input = $(this).closest('.qty-wrapper').find('input.quantity'),
                        step = parseFloat(input.attr('step')),
                        min = parseFloat(input.attr('min')),
                        max = parseFloat(input.attr('max')),
                        val = parseFloat(input.val());
                    if ($(this).hasClass('btn-increment')) {
                        val += step;
                        if (val > max) {
                            val = max;
                            eModal.alert({
                                message: '{lang key='maxAnzahlText' section='rma'}',
                                title: '{lang key='maxAnzahlTitle' section='rma'}',
                                keyboard: true,
                                tabindex: -1,
                                buttons: false
                            });
                        }
                    } else {
                        val -= step;
                        val = val < min ? min : val;
                    }
                    input.val(val);
                });

                $('#retoure').on('submit', function (e) {
                    e.preventDefault();
                    let inputs = [];
                    table.rows().every(function () {
                        if ($(this.node()).find('input[name="returnItem"]').prop('checked')) {
                            $(this.node()).find('[data-lid]').each(function () {
                                inputs.push(
                                    {
                                        name: $(this).attr('name'),
                                        value: {
                                            lid: $(this).attr('data-lid'),
                                            val: $(this).val(),
                                        }
                                    }
                                );
                            });
                        }
                    });
                    let formData = $(this).serializeArray().concat(inputs);
                    console.log(formData);
                });
            });
        </script>{/inline_script}
    {/block}
{/block}
