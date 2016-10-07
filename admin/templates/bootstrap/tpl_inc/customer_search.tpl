{*
    Parameters:
        cPart - control the kind of output
            'customerlist' = output markup for the result list
                oKunde_arr         - array of undecoded customer data
                kKundeSelected_arr - array of selected customer keys
            'fullcustomer' = output markup for a fully decoded customer
                oKunde - complete Kunde instance
            unset          = output  the main dialog
                kKundeSelected_arr - array of initially selected customer keys
                onSave             - JS callback function name for save button click
                onCancel           - JS callback function name for cancel button click
*}

{if isset($cPart) && $cPart === 'customerlist'}
    {foreach $oKunde_arr as $oKunde}
        <a class="list-group-item {if in_array($oKunde->kKunde, $kKundeSelected_arr)}active{/if}"
           onclick="selectCustomer({$oKunde->kKunde}, !isSelected({$oKunde->kKunde}))" id="customer-{$oKunde->kKunde}"
           style="cursor: pointer;">
            <p class="list-group-item-text">
                {$oKunde->cVorname|htmlentities} {$oKunde->cNachname|htmlentities}
                <em>({$oKunde->cMail|htmlentities})</em>
            </p>
            <p class="list-group-item-text">{$oKunde->cStrasse|htmlentities}
                {$oKunde->cHausnummer}, {$oKunde->cPLZ} {$oKunde->cOrt|htmlentities}
            </p>
        </a>
    {/foreach}
{else}
    <script>
        var searchString            = '';
        var lastSearchString        = '';
        var selectedCustomers       = [{','|implode:$kKundeSelected_arr}];
        var shownCustomers          = [];
        var runningRequests         = [];
        var backupSelectedCustomers = selectedCustomers.slice();

        $(function () {
            runningRequests.push(xajax_getCustomerList('', selectedCustomers));
            $('#customer-search-modal').on('hide.bs.modal', function () {
                killAllRunningRequests();
                $('#customer-search-input').val('');
                runningRequests.push(xajax_getCustomerList('', selectedCustomers));
            }).on('show.bs.modal', function () {
                backupSelectedCustomers = selectedCustomers.slice();
            });
            $('#save-customer-selection').click(function () {
                {if isset($onSave)}
                    window['{$onSave}'] ();
                {/if}
            });
            $('#cancel-customer-selection').click(function () {
                selectedCustomers = backupSelectedCustomers.slice();
                {if isset($onCancel)}
                    window['{$onCancel}'] ();
                {/if}
            });
        });

        function onChangeCustomerSearchInput (searchInput)
        {
            searchString = $(searchInput).val();

            if (searchString !== lastSearchString) {
                lastSearchString = searchString;
                killAllRunningRequests();
                runningRequests.push(xajax_getCustomerList(searchString, selectedCustomers));
            }
        }

        function isSelected (kKunde)
        {
            return selectedCustomers.indexOf(kKunde) != -1;
        }

        function killAllRunningRequests ()
        {
            runningRequests.forEach(function (request) { xajax.abortRequest(request); });
            runningRequests = [];
        }

        function selectCustomer (kKunde, selected)
        {
            if (selected) {
                $('#customer-' + kKunde).addClass('active');
                selectedCustomers.push(kKunde);
            } else {
                $('#customer-' + kKunde).removeClass('active');
                selectedCustomers.splice(selectedCustomers.indexOf(kKunde), 1);
            }
            if ($('#customer-search-input').val() === '') {
                $('#customer-list-title').html('Alle ausgew&auml;hlten Kunden: ' + selectedCustomers.length);
            }
        }

        function selectAllShownCustomers (selected)
        {
            shownCustomers.forEach(function (kKunde) {
                selectCustomer(kKunde, selected);
            });
        }

        function onResetSearchInput ()
        {
            $('#customer-search-input').val('');
            onChangeCustomerSearchInput('#customer-search-input');
        }
    </script>
    <div class="modal fade" id="customer-search-modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <i class="fa fa-times"></i>
                    </button>
                    <h4 class="modal-title">Kunden ausw&auml;hlen</h4>
                </div>
                <div class="modal-body">
                    <div class="input-group">
                        <label for="customer-search-input" class="sr-only">
                            Suche nach Vornamen, E-Mail-Adresse, Wohnort oder Postleitzahl:
                        </label>
                        <input type="text" class="form-control" id="customer-search-input"
                               placeholder="Suche nach Vornamen, E-Mail-Adresse, Wohnort oder Postleitzahl"
                               onkeyup="onChangeCustomerSearchInput(this)" autocomplete="off">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default" onclick="onResetSearchInput();">
                                <i class="fa fa-eraser"></i>
                            </button>
                        </span>
                    </div>
                    <h5 id="customer-list-title">Suchergebnisse</h5>
                    <div class="list-group" id="customer-search-result-list" style="max-height:500px;overflow:auto;"></div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-primary" id="select-all-customers"
                                onclick="selectAllShownCustomers(true);">
                            <i class="fa fa-check-square-o"></i>
                            Alle ausw&auml;hlen
                        </button>
                        <button type="button" class="btn btn-xs btn-danger" id="unselect-all-customers"
                                onclick="selectAllShownCustomers(false);">
                            <i class="fa fa-square-o"></i>
                            Alle abw&auml;hlen
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-danger" data-dismiss="modal"
                                id="cancel-customer-selection">
                            <i class="fa fa-times"></i>
                            {#cancel#}
                        </button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal"
                                id="save-customer-selection">
                            <i class="fa fa-save"></i>
                            {#save#}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}

