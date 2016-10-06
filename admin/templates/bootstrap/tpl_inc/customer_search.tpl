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
*}

{if isset($cPart) && $cPart === 'customerlist'}
    {foreach $oKunde_arr as $oKunde}
        <a class="list-group-item {if in_array($oKunde->kKunde, $kKundeSelected_arr)}active{/if}"
                onclick="selectCustomer({$oKunde->kKunde}, !isSelected({$oKunde->kKunde}))" id="customer-{$oKunde->kKunde}">
            <p class="list-group-item-text">{$oKunde->cVorname|htmlentities} ... <em>({$oKunde->cMail|htmlentities})</em></p>
            <p class="list-group-item-text">... {$oKunde->cPLZ} {$oKunde->cOrt|htmlentities}</p>
        </a>
    {/foreach}
{elseif isset($cPart) && $cPart === 'fullcustomer'}
    <p class="list-group-item-text">{$oKunde->cVorname|htmlentities} {$oKunde->cNachname|htmlentities} <em>({$oKunde->cMail|htmlentities})</em></p>
    <p class="list-group-item-text">{$oKunde->cStrasse|htmlentities} {$oKunde->cHausnummer}, {$oKunde->cPLZ} {$oKunde->cOrt|htmlentities}</p>
{else}
    <script>
        var searchString      = '';
        var lastSearchString  = '';
        var selectedCustomers = [{','|implode:$kKundeSelected_arr}];
        var shownCustomers    = [];
        var runningRequests   = [];

        $(function () {
            runningRequests.push(xajax_getCustomerList('', selectedCustomers));
            $('#customer-search-modal').on('hide.bs.modal', function () {
                killAllRunningRequests();
                $('#customer-search-input').val('');
                runningRequests.push(xajax_getCustomerList('', selectedCustomers));
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
        }

        function selectAllShownCustomers (selected)
        {
            shownCustomers.forEach(function (kKunde) {
                selectCustomer(kKunde, selected);
            });
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
                    <div class="form-group">
                        <label for="customer-search-input" class="sr-only">
                            Suche nach E-Mail-Adresse oder Vornamen:
                        </label>
                        <input type="text" class="form-control" id="customer-search-input"
                               placeholder="Suchen nach E-Mail-Adresse oder Vornamen"
                               onkeyup="onChangeCustomerSearchInput(this)" autocomplete="off">
                    </div>
                    <h5 id="customer-list-title">Suchergebnisse</h5>
                    <div class="list-group" id="customer-search-result-list"></div>
                    <button type="button" class="btn btn-primary" id="select-all-customers"
                            onclick="selectAllShownCustomers(true);">
                        Alle ausw&auml;hlen
                    </button>
                    <button type="button" class="btn btn-danger" id="unselect-all-customers"
                            onclick="selectAllShownCustomers(false);">
                        Alle abw&auml;hlen
                    </button>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{#cancel#}</button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal"
                                id="save-customer-selection">{#save#}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}

