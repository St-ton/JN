{block name='account-rma-form'}
    {row class="rma-step-1"}
        {col cols=12 md=5 lg=4 class='rma-positions-wrapper order-md-2'}
            {card no-body=true class="rma-step-1 sticky-card"}
                <div id="rmaStickyPositions">
                    <div class="rmaPosContainer">
                        {include file='account/rma_positions.tpl' rmaPositions=$rma->getPositions()
                        rmaTotal=$rma->getPriceLocalized()}
                    </div>
                </div>

                {form method="post" id='rma' action="#" class="jtl-validate card p-2" slide=true}
                    {block name='account-rma-form-include-customer-rmas'}
                        {block name='checkout-customer-shipping-address'}
                            {formgroup label="{lang key='pickupAddress' section='rma'}"
                            label-for="pickupAddress"}
                                <div class="input-group">
                                    {select name="pickupAddress" id="pickupAddress" class="custom-select"
                                    autocomplete="shipping Adress"}
                                        {include file='account/pickupaddress/form_option.tpl' pkAddresses=$shippingAddresses}
                                    {/select}
                                    <div class="input-group-append">
                                        {block name='account-rma-form-form-submit'}
                                            {button type="submit" value="1" block=true variant="primary"}
                                                {lang key='continueOrder' section='account data'}
                                            {/button}
                                        {/block}
                                    </div>
                                </div>
                            {/formgroup}
                        {/block}
                    {/block}
                {/form}
            {/card}
        {/col}
        {col cols=12 md=7 lg=8 class='rma-form-wrapper order-md-1'}
            {block name='account-my-account-rma'}
                {card no-body=true id="rma-positions"}
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
                            <a href="#" class="w-100 select_all">
                                SELECT ALL
                            </a>
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
                                        {assign var=rmaPos value=$rma->getPos($product->shippingNotePosID)}
                                        <tr>
                                            <td class="d-none">{$product->orderNo}</td>
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
                                                                           {if $rmaPos->getID() > 0}checked{/if}
                                                                           aria-label="Lorem ipsum">
                                                                    <label class="custom-control-label"
                                                                           for="switch-{$product->shippingNotePosID}">
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <small class="text-muted-util d-block">
                                                                {lang key='orderNo' section='login'}: {$product->orderNo}<br>
                                                                {lang key='productNo'}: {link
                                                                    href=$product->Artikel->cURLFull target="_blank"}
                                                                    {$product->productNR}
                                                                {/link}<br>
                                                                {if $product->property->name !== ''
                                                                    && $product->property->value !== ''}
                                                                    {$product->property->name}: {$product->property->value}<br>
                                                                {/if}
                                                                {$product->quantity} {$product->unit|default:''} x {$product->unitPriceNetLocalized}
                                                            </small>
                                                        </div>
                                                    </div>

                                                    <div class="{if $rmaPos->getID() > 0}d-flex {else}d-none {/if}rmaFormPositions flex-wrap mt-2 w-100">
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
                                                            id="qty-{$product->shippingNotePosID}" class="quantity" name="quantity"
                                                            aria=["label"=>"{lang key='quantity'}"]
                                                            value="{if $rmaPos->getID() > 0}{$rmaPos->getQuantity()}{else}{$product->quantity}{/if}"
                                                            data=[
                                                                "snposid" => {$product->shippingNotePosID},
                                                                "decimals" => {$product->Artikel->fAbnahmeintervall}
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
                                                            name="reason"
                                                            data=["snposid" => "{$product->shippingNotePosID}"]
                                                            class="custom-select form-control"}
                                                                <option value="-1"{if $rmaPos->getID() === 0} selected{/if}>{lang key='rma_comment_choose' section='rma'}</option>
                                                                {foreach $reasons as $reason}
                                                                    <option value="{$reason->reasonID}"{if $rmaPos->getReasonID() === $reason->reasonID} selected{/if}>{$reason->title}</option>
                                                                {/foreach}
                                                            {/select}
                                                        </div>

                                                        <div class="flex-grow-1 mr-2 mb-2">
                                                            {textarea name="comment"
                                                            data=["snposid" => "{$product->shippingNotePosID}"]
                                                            rows=1
                                                            maxlength="255"
                                                            placeholder="{lang key='comment' section='productDetails'}"}
                                                                {if $rmaPos->getComment() !== null}
                                                                    {$rmaPos->getComment()}
                                                                {/if}
                                                            {/textarea}
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

        {/col}
    {/row}

    {row class="rma-step-2 d-none"}
        {col}
            {block name='rma-summary-body'}
                {block name='account-rma-summary'}
                    <div id="rma-summary"></div>
                {/block}
            {/block}
        {/col}
    {/row}
{/block}
{block name='account-rma-form-pickup-address-modal'}
    {modal id="pickupAddressModal" class="fade" title={lang key='newPickupAddress' section='rma'}}
        {include file='account/shipping_address_form.tpl' isModal=true LieferLaender=$shippingCountries}
    {/modal}
{/block}
{block name='account-rma-form-script'}
    {inline_script}<script>
        var rmaID = parseInt("{$rma->getID()}"),
            formData = [],
            updPosRequest,
            goToStep = 1;

        function initDataTable(tableID, rows = 5) {
            let $table = $(tableID);
            return $table.DataTable( {
                language: {
                    "lengthMenu":        "_MENU_",
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
                    let $tableWrapper = $('#rma-positions');

                    $tableWrapper.find('.dataTable-custom-filter')
                        .removeClass('col-sm-12 col-md-6').addClass('col-8 col-sm-8 col-md-8 col-lg-4');

                    $tableWrapper.find('.dataTables_length').parent()
                        .removeClass('col-sm-12 col-md-6').addClass('col-4 col-sm-4 col-md-4 col-lg-2');

                    $tableWrapper.find('.dataTables_filter').parent()
                        .removeClass('col-sm-12 col-md-6').addClass('col-sm-12 col-md-12 col-lg-6');

                    $tableWrapper.find('.custom-select').addClass('w-100');
                    $tableWrapper.find('.dataTable-custom-filter').prependTo($tableWrapper.find('.dataTables_wrapper .row:first-child'));
                },
                drawCallback: function( settings ) {
                    $table.find('thead').remove();
                },
            } );
        }

        function setListenerForToggles() {
            $('.ra-switch').off('change').on('change', function () {
                if ($(this).prop('checked')) {
                    $(this).closest('tr').find('.rmaFormPositions').removeClass("d-none").addClass('d-flex');
                } else {
                    $(this).closest('tr').find('.rmaFormPositions').removeClass("d-flex").addClass('d-none');
                }
                $('#rma').submit();
            });
        }

        function setListenerForQuantities() {
            $('.qty-wrapper .btn-decrement, .qty-wrapper .btn-increment').off('click').on('click', function () {
                let input = $(this).closest('.qty-wrapper').find('input.quantity'),
                    step = input.attr('step') > 0 ? parseFloat(input.attr('step')) : 1,
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
                        return;
                    }
                } else {
                    val -= step;
                    val = val < min ? min : val;
                }
                input.val(val.toFixed(input.data('decimals')).replace(',', '.'));
                $('#rma').submit();
            });
        }

        function setListenerForListExpander() {
            $('.list-compressed a.listExpander').off('click').on('click', function (e) {
                e.preventDefault();
                $('.list-compressed').toggleClass('open');
            });
        }

        function setListenerForBackButton() {
            $('#goBackOneStep').off('click').on('click', function (e) {
                e.preventDefault();
                step(1);
            });
        }

        function showMinItemsAlert() {
            eModal.alert({
                message: '{lang key='noItemsSelectedText' section='rma'}',
                title: '{lang key='noItemsSelectedTitle' section='rma'}',
                keyboard: true,
                tabindex: -1,
                buttons: false
            });
        }

        function step(goTo) {
            if (goTo === 1) {
                $('.rma-step-1').removeClass('d-none');
                $('.rma-step-2').addClass('d-none');
                setListenerForListExpander();
            } else if (goTo === 2) {
                $('.rma-step-2').removeClass('d-none');
                $('.rma-step-1').addClass('d-none');
                setListenerForBackButton();
            }
        }

        $(document).ready(function () {
            const customFilter = $('.dataTable-custom-filter select[name="orders"]');
            setListenerForListExpander();

            // Filter by order id
            $.fn.dataTable.ext.search.push(function (settings, data) {
                let orderNo = customFilter.val(),
                    orderNos = data[0] || '';

                return orderNo === orderNos || orderNo === '';
            });

            const $table = initDataTable('#returnable-items');

            // Set toggle listener again when table redraws
            $table.on('draw', function () {
                setListenerForToggles();
                setListenerForQuantities();
            });

            customFilter.on('change', function () {
                $table.draw();
            });

            setListenerForToggles();
            setListenerForQuantities();

            $('#lieferadressen').on('submit', function (e) {
                e.preventDefault();
                $('#lieferadressen button[type="submit"]')
                    .addClass('isLoading').attr('disabled', true);
                let formData = $(this).serializeArray();
                $.evo.io().request(
                    {
                        'name': 'createShippingAddress',
                        'params': [formData]
                    },
                    { },
                    function (error, data) {
                        if (error) {
                            return;
                        }
                        if (data['response']['result'] === false) {
                            alert(data['response']['msg']);
                        } else {
                            $('#pickupAddress').html(data['response']['options']);
                            $('#lieferadressen button[type="submit"]')
                                .removeClass('isLoading').attr('disabled', false);
                            $('#pickupAddressModal').modal('hide');
                            $('#rma button[type="submit"]').trigger('click');
                        }
                    }
                );
            });

            $('#rma button[type="submit"]').on('click', function (e) {
                e.preventDefault();
                if ($('#pickupAddress').val() === "-1") {
                    $('#pickupAddressModal').modal('show');
                } else {
                    if (formData.filter(e => e.name === 'quantity').length === 0) {
                        showMinItemsAlert();
                        return;
                    }
                    goToStep = 2;
                    $('#rma').submit();
                }
            });

            $('#rma').on('submit', function (e) {
                e.preventDefault();
                formData = $(this).serializeArray();
                let inputs = [];

                $table.rows().every(function () {
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

                if (inputs.length > 0) {
                    formData = formData.concat(inputs);
                }

                // Cancel AJAX request if it is still running
                if (updPosRequest !== undefined) {
                    updPosRequest.abort();
                }

                $('#rmaStickyPositions').addClass('loadingAJAX');

                updPosRequest = $.evo.io().request(
                        {
                            'name': (goToStep === 2) ? 'rmaSummary' : 'rmaPositions',
                            'params': [formData]
                        },
                    { },
                    function (error, data) {
                        $('#rmaStickyPositions').removeClass('loadingAJAX');
                        if (error) {
                            return;
                        }
                        if (data['response']['result'] === false) {
                            alert(data['response']['msg']);
                        } else {
                            if (goToStep === 1) {
                                $('#rma-summary').html('');
                                $('#rmaStickyPositions .rmaPosContainer').html(data['response']['html']);
                                step(1);
                            } else if (goToStep === 2) {
                                $('#rma-summary').html(data['response']['html']);
                                step(2);
                                goToStep = 1;
                            }
                        }
                    }
                );
            });
            $('.select_all').on('click', function (e) {
                e.preventDefault();
                $('.ra-switch').prop('checked', true);
            });
        });
    </script>{/inline_script}
{/block}