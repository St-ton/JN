{block name='account-rmas-form'}
    {block name='account-rma-form-form'}
        {row}
            {col cols=12 md=12 class='rmas-form-wrapper'}
                {block name='account-my-account-rma'}
                    {card no-body=true class="rma-positions"}
                        {cardheader}
                            {block name='rma-positions-header'}
                                {row class="align-items-center-util"}
                                    {col}
                                        <span class="h3">
                                            {lang key='addPositions' section='rma'}
                                        </span>
                                    {/col}
                                {/row}
                            {/block}
                        {/cardheader}
                        {cardbody}
                            {block name='rma-positions-body'}
                                <div class="col-sm-12 col-md-4 dataTable-custom-filter">
                                    <label>
                                        {select name="orders" aria=["label"=>"Bestellnummer"]
                                        class="custom-select custom-select-sm form-control form-control-sm"}
                                            <option value="" selected>{lang key='allOrders' section='rma'}</option>
                                            {foreach $returnableOrders as $order}
                                                <option value="{$order}">{$order}</option>
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
                                    {block name='account-rmas-returnable-items'}
                                        {foreach $returnableProducts as $product}
                                            <tr>
                                                <td class="d-none">{$product->orderID}</td>
                                                <td class="product">
                                                    <div class="d-flex flex-wrap">
                                                        <div class="d-flex flex-nowrap flex-grow-1">
                                                            <div class="d-block">
                                                                {image lazy=true webp=true fluid=true
                                                                src=$product->Artikel->Bilder[0]->cURLKlein|default:$smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN
                                                                alt=$product->name
                                                                class="img-aspect-ratio product-thumbnail pr-2"}
                                                            </div>

                                                            <div class="d-flex flex-nowrap flex-grow-1 flex-column">
                                                                <div class="d-inline-flex flex-nowrap justify-content-between">
                                                                    <span class="font-weight-bold">{$product->name}</span>
                                                                    <div class="custom-control custom-switch">
                                                                        <input type='checkbox'
                                                                               class='custom-control-input ra-switch'
                                                                               id="switch-{$product->shippingNotePosID}"
                                                                               name="returnItem"
                                                                               {if false}checked{/if}
                                                                               aria-label="Lorem ipsum">
                                                                        <label class="custom-control-label"
                                                                               for="switch-{$product->shippingNotePosID}">
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <small class="text-muted-util d-block">
                                                                    {lang key='orderNo' section='login'}: {$product->orderID}<br>
                                                                    {lang key='productNo'}: {link href=$product->orderID}
                                                                        {$product->productNR}
                                                                    {/link}<br>
                                                                    {$product->quantity} {$product->unit|default:''} x {$product->unitPriceNet}
                                                                </small>
                                                            </div>
                                                        </div>

                                                        <div class="d-none rmaFormPositions flex-wrap mt-2 w-100">
                                                            <div class="qty-wrapper max-w-sm mr-2 mb-2">
                                                                {inputgroup id="quantity-grp{$product->shippingNotePosID}" class="form-counter choose_quantity"}
                                                                {inputgroupprepend}
                                                                {button variant="" class="btn-decrement"
                                                                data=["count-down"=>""]
                                                                aria=["label"=>{lang key='decreaseQuantity' section='aria'}]}
                                                                    <span class="fas fa-minus"></span>
                                                                {/button}
                                                                {/inputgroupprepend}
                                                                {input type="number"
                                                                required=($product->Artikel->fAbnahmeintervall > 0)
                                                                step="{if $product->Artikel->cTeilbar === 'Y' && $product->Artikel->fAbnahmeintervall == 0}any{elseif $product->Artikel->fAbnahmeintervall > 0}{$product->Artikel->fAbnahmeintervall}{else}1{/if}"
                                                                min="1"
                                                                max="{$product->quantity}"
                                                                id="qty-{$product->shippingNotePosID}" class="quantity" name="quantity[]"
                                                                aria=["label"=>"{lang key='quantity'}"]
                                                                value=$product->quantity
                                                                data=[
                                                                "decimals" => {getDecimalLength quantity=$product->Artikel->fAbnahmeintervall},
                                                                "product-id" => "{if isset($product->Artikel->kVariKindArtikel)}{$product->Artikel->kVariKindArtikel}{else}{$product->Artikel->kArtikel}{/if}",
                                                                "snposid" => {$product->shippingNotePosID}
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
                                                                "snposid" => "{$product->shippingNotePosID}"
                                                                ]
                                                                class="custom-select form-control"}
                                                                    <option value="-1" selected>{lang key='rma_comment_choose' section='rma'}</option>
                                                                    <option value="0">Artikel defekt</option>
                                                                    <option value="1">Artikel nicht geliefert</option>
                                                                    <option value="2">Sonstiges</option>
                                                                {/select}
                                                            </div>

                                                            <div class="flex-grow-1 mr-2 mb-2">
                                                                {textarea name="comment[]"
                                                                data=[
                                                                "snposid" => "{$product->shippingNotePosID}"
                                                                ]
                                                                rows=1
                                                                maxlength="255"
                                                                placeholder="{lang key='comment' section='productDetails'}"}{/textarea}
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

                {form method="post" id='rma' action="#" class="jtl-validate mt-3" slide=true}
                    {block name='account-rma-form-include-customer-rmas'}
                        {block name='checkout-customer-shipping-address'}
                            <fieldset>
                                {formrow}
                                    {col cols=12}
                                        {block name='checkout-customer-shipping-address-country'}
                                            {formgroup label="{lang key='pickupAddress' section='rma'}" label-for="shippingAdress"}
                                                {select name="shippingAdress" id="shippingAdress" class="custom-select"
                                                autocomplete="shipping Adress"}
                                                    {foreach $shippingAddresses as $sa}
                                                        <option value="{$sa->kLieferadresse}"{if $sa->nIstStandardLieferadresse == 1} selected{/if}>
                                                            {if $sa->cFirma}{$sa->cFirma}, {/if}
                                                            {$sa->cStrasse} {$sa->cHausnummer},
                                                            {$sa->cPLZ} {$sa->cOrt}
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
                    {block name='account-rma-form-form-submit'}
                        {row class='btn-row'}
                            {col md=12 xl=12 class="checkout-button-row-submit mb-3"}
                                {button type="submit" value="1" block=true variant="primary"}
                                    {lang key='continueOrder' section='account data'}
                                {/button}
                            {/col}
                        {/row}
                    {/block}
                {/form}
            {/col}
        {/row}
    {/block}
    {block name='account-rma-form-script'}
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

            function setListenerForToggles(id='.ra-switch') {
                $(id).off('change').on('change', function () {
                    if ($(this).prop('checked')) {
                        $(this).closest('tr').find('.rmaFormPositions').removeClass("d-none").addClass('d-flex');
                    } else {
                        $(this).closest('tr').find('.rmaFormPositions').removeClass("d-flex").addClass('d-none');
                    }
                });
            }

            function setListenerForQuantities() {
                $('.qty-wrapper .btn-decrement, .qty-wrapper .btn-increment').off('click').on('click', function () {
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
            }

            $(document).ready(function () {
                const customFilter = $('.dataTable-custom-filter select[name="orders"]');

                // Filter by order id
                $.fn.dataTable.ext.search.push(function (settings, data) {
                    let orderID = customFilter.val(),
                        orderIDs = data[0] || '';

                    return orderID === orderIDs || orderID === '';
                });

                const table = initDataTable('#returnable-items');

                // Set toggle listener again when table redraws
                table.on('draw', function () {
                    setListenerForToggles();
                    setListenerForQuantities();
                });

                customFilter.on('change', function () {
                    table.draw();
                });

                setListenerForToggles();
                setListenerForQuantities();

                $('#rma').on('submit', function (e) {
                    e.preventDefault();
                    let inputs = [];
                    table.rows().every(function () {
                        if ($(this.node()).find('input[name="returnItem"]').prop('checked')) {
                            $(this.node()).find('[data-snposid]').each(function () {
                                inputs.push(
                                    {
                                        name: $(this).attr('name'),
                                        value: {
                                            shippingNotePosID: $(this).attr('data-snposid'),
                                            value: $(this).val()
                                        }
                                    }
                                );
                            });
                        }
                    });
                    if (inputs.length === 0) {
                        eModal.alert({
                            message: '{lang key='noItemsSelectedText' section='rma'}',
                            title: '{lang key='noItemsSelectedTitle' section='rma'}',
                            keyboard: true,
                            tabindex: -1,
                            buttons: false
                        });
                        return;
                    }
                    let formData = $(this).serializeArray().concat(inputs);
                    console.log(formData);
                });
            });
        </script>{/inline_script}
    {/block}
{/block}
