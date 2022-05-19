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
                                {if isset($Lieferadresse->nIstStandardLieferadresse) && $Lieferadresse->nIstStandardLieferadresse === "1"}
                                    {input type="hidden" name="isDefault" value=1}
                                {/if}
                                {if isset($Lieferadresse->kLieferadresse) && !isset($smarty.get.fromCheckout)}
                                    {input type="hidden" name="updateAddress" value=$Lieferadresse->kLieferadresse}
                                    {button type="submit" value="1" block=true variant="primary"}
                                        {lang key='updateAddress' section='account data'}
                                    {/button}
                                {else if !isset($Lieferadresse->kLieferadresse)}
                                    {input type="hidden" name="editAddress" value="neu"}
                                    {button type="submit" value="1" block=true variant="primary"}
                                        {lang key='saveAddress' section='account data'}
                                    {/button}
                                {else if isset($Lieferadresse->kLieferadresse) && isset($smarty.get.fromCheckout)}
                                    {input type="hidden" name="editAddress" value=$Lieferadresse->kLieferadresse}
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

            {*
            {col md=6}
                {block name='checkout-inc-shipping-address-fieldset-address'}
                    {foreach $Lieferadressen as $adresse}
                        {if $adresse->kLieferadresse > 0}
                            {block name='checkout-inc-shipping-address-address'}
                            <div class="card mb-3">
                                {if $adresse->nIstStandardLieferadresse === '1'}
                                    <div class="card-header bg-primary">
                                        <strong>{lang key='defaultShippingAdresses' section='account data'}</strong>
                                    </div>
                                {/if}
                                <div class="card-body">
                                    <span class="control-label label-default">
                                        {if $adresse->cFirma}{$adresse->cFirma}<br />{/if}
                                        {if $adresse->cTitel}{$adresse->cTitel}<br />{/if}
                                        <strong>{$adresse->cVorname} {$adresse->cNachname}</strong><br />
                                        {$adresse->cStrasse} {$adresse->cHausnummer}<br />
                                        {$adresse->cPLZ} {$adresse->cOrt}<br />
                                        {$adresse->angezeigtesLand}
                                    </span>
                                </div>
                                <div class="card-footer text-muted">
                                    {if $adresse->nIstStandardLieferadresse !== '1'}
                                        <div class="control-label label-default">
                                            {link href="{get_static_route id='jtl.php' params=['editLieferadresse' => 1, 'setAddressAsDefault' => {$adresse->kLieferadresse}]}" class="btn btn-primary btn-sm" rel="nofollow" }
                                                {lang key='useAsDefaultShippingAdress' section='account data'}
                                            {/link}
                                        </div>
                                    {/if}
                                    <div class="control-label label-default mt-2">
                                        {link href="{get_static_route id='jtl.php' params=['editLieferadresse' => 1, 'editAddress' => {$adresse->kLieferadresse}]}" class="btn btn-secondary btn-sm" alt="Adresse bearbeiten"}
                                            <span class="fas fa-pencil-alt"></span>
                                            {lang key='editShippingAddress' section='account data'}
                                        {/link}
                                        {link href="{get_static_route id='jtl.php' params=['editLieferadresse' => 1, 'deleteAddress' => {$adresse->kLieferadresse}]}" class="btn btn-danger btn-sm" alt="Adresse löschen"}
                                            <span class="fas fa-times"></span>
                                            {lang key='deleteAddress' section='account data'}
                                        {/link}
                                    </div>
                                </div>
                            </div>
                            {/block}
                        {/if}
                    {/foreach}
                    {block name='account-orders-include-pagination'}
                        {include file='snippets/pagination.tpl' oPagination=$addressPagination cThisUrl='jtl.php' cParam_arr=['editLieferadresse' => 1] parts=['pagi', 'label']}
                    {/block}
                {/block}
            {/col}
            *}
            {col md=6}
                {block name='checkout-inc-shipping-address-fieldset-address'}
                    <table id="example" class="display compact" style="width:100%">
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
                                    {if $adresse->nIstStandardLieferadresse !== '1'}
                                        {link href="{get_static_route id='jtl.php' params=['editLieferadresse' => 1, 'setAddressAsDefault' => {$adresse->kLieferadresse}]}" class="btn btn-primary btn-sm" rel="nofollow" data-toggle="tooltip" data-placement="left" title="Tooltip on left"}
                                            <span class="fas fa-star"></span>
                                        {/link}
                                    {/if}
                                    {link href="{get_static_route id='jtl.php' params=['editLieferadresse' => 1, 'editAddress' => {$adresse->kLieferadresse}]}" class="btn btn-secondary btn-sm" alt="Adresse bearbeiten"}
                                        <span class="fas fa-pencil-alt"></span>
                                    {/link}
                                    {link href="{get_static_route id='jtl.php' params=['editLieferadresse' => 1, 'deleteAddress' => {$adresse->kLieferadresse}]}" class="btn btn-danger btn-sm" alt="Adresse löschen"}
                                        <span class="fas fa-times"></span>
                                    {/link}
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

        var table = $('#example').DataTable( {
            language: {
                url: '{$ShopURL}/{$currentTemplateDir}js/DataTables/de-DE.json'
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

        $('#example tbody').on('click', 'td.dt-control', function () {
            var tr = $(this).closest('tr');
            var row = table.row(tr);

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Open this row
                row.child(format(row.data())).show();
                tr.addClass('shown');
                console.log(row.child);
            }
        });
    });
</script>