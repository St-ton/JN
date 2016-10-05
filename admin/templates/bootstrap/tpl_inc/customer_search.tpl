{*
    Parameters:
        cPart -
            'customerlist' - markup for the result list
            'fullcustomer' - markup for a fully decoded customer
            unset - modal dialog
*}

{if isset($cPart) && $cPart === 'customerlist'}
    {foreach $oKunde_arr as $oKunde}
        <button class="list-group-item {if in_array($oKunde->kKunde, $kKundeSelected_arr)}active{/if}"
                onclick="onClickCustomer(this, {$oKunde->kKunde})" id="customer-{$oKunde->kKunde}">
            <p class="list-group-item-text">{$oKunde->cVorname|htmlentities} ... <em>({$oKunde->cMail|htmlentities})</em></p>
            <p class="list-group-item-text">{$oKunde->cPLZ} {$oKunde->cOrt|htmlentities}</p>
        </button>
    {/foreach}
{elseif isset($cPart) && $cPart === 'fullcustomer'}
    <p class="list-group-item-text">{$oKunde->cVorname|htmlentities} {$oKunde->cNachname|htmlentities} <em>({$oKunde->cMail|htmlentities})</em></p>
    <p class="list-group-item-text">{$oKunde->cStrasse|htmlentities} {$oKunde->cHausnummer}, {$oKunde->cPLZ} {$oKunde->cOrt|htmlentities}</p>
{else}
    <script>
        var searchString      = '';
        var lastSearchString  = '';
        var selectedCustomers = [3,6,9];
        var runningRequests   = [];

        $(function () {
            runningRequests.push(xajax_getCustomerList('', selectedCustomers));
        });

        function onChangeCustomerSearchInput (searchInput)
        {
            searchString = $(searchInput).val();

            if (searchString !== lastSearchString) {
                runningRequests.forEach(function (request) { xajax.abortRequest(request); });
                runningRequests = [];
                lastSearchString = searchString;
                runningRequests.push(xajax_getCustomerList(searchString, selectedCustomers));
            }
        }

        function onClickCustomer (item, kKunde)
        {
            var $item = $(item);

            if ($item.hasClass('active')) {
                $item.removeClass('active');
                selectedCustomers.splice(selectedCustomers.indexOf(kKunde), 1);
            } else {
                $item.addClass('active');
                selectedCustomers.push(kKunde);
            }
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
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{#cancel#}</button>
                        <button type="button" class="btn btn-primary">{#save#}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}

