{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='shopzuruecksetzen'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('shopResetTitle') cBeschreibung=__('shopResetDesc') cDokuURL=__('shopResetURL')}
{literal}
    <script>
        $(document).ready(function(){
            $('input[type="checkbox"]').on('change', function(){
                var itemsChecked = '';
                $('input[type="checkbox"]:checked').next().each(function(i){
                    itemsChecked += $(this).prev().val();
                });
                if (itemsChecked === 'artikel' || itemsChecked === '') {
                    $('#warningZuruecksetzen, #messageDataGetsLost').addClass('hide');
                    $('button[data-target=".zuruecksetzen-modal"]').prop('disabled', itemsChecked === '');
                    $('#backupDone').closest('div.checkbox').addClass('hide');
                } else {
                    $('#warningZuruecksetzen, #messageDataGetsLost').removeClass('hide');
                    $('#backupDone').closest('div.checkbox').removeClass('hide');
                    $('button[data-target=".zuruecksetzen-modal"]').prop('disabled', !$("#backupDone").is(':checked'));
                }
            });
            $('#backupDone').on('change', function(){
                if (this.checked) {
                    $('button[data-target=".zuruecksetzen-modal"]').prop('disabled', false);
                } else {
                    $('button[data-target=".zuruecksetzen-modal"]').prop('disabled', true);
                }
            });
            $('#submitZuruecksetzen').on('click', function(){
                $('#formZuruecksetzen').submit();
            });
            $('button[data-target=".zuruecksetzen-modal"]').on('click', function(){
                var itemsToDelete = '';
                $('input[type="checkbox"]:checked').next().each(function(i){
                    itemsToDelete += '<li class="list-group-item list-group-item-warning">' + $(this).text() + '</li>';
                });
                $('.zuruecksetzen-modal .modal-body').html('<ul class="list-group">' + itemsToDelete + '</ul>');
            });
        });
    </script>
{/literal}
<div id="warningZuruecksetzen" class="alert alert-warning hide" >
    <h3>{__('dangerStrong')}</h3>
    <p>{__('warningDeleteNotRestoreableData')}</p>
</div>
<div id="content" class="container-fluid settings">
    <form id="formZuruecksetzen" name="login" method="post" action="shopzuruecksetzen.php">
        {$jtl_token}
        <input type="hidden" name="zuruecksetzen" value="1" />

        <div class="card">
            <div class="card-header">
                <div class="card-title">{__('shopContent')}</div>
            </div>
            <div class="card-body">
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="artikel" tabindex="3" id="Artikel" />
                    <label for="Artikel">{__('deleteProductCategory')}</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="news" tabindex="4" id="News" />
                    <label for="News">{__('deleteNews')}</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="bestseller" tabindex="5" id="Bestseller" />
                    <label for="Bestseller">{__('deleteBestseller')}</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="besucherstatistiken" tabindex="6" id="Besucherstatistiken" />
                    <label for="Besucherstatistiken">{__('deleteVisitorStatistics')}</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="preisverlaeufe" tabindex="8" id="Preisverlaufe" />
                    <label for="Preisverlaufe">{__('deletePriceStatistics')}</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="umfragen" tabindex="9" id="Umfragen" />
                    <label for="Umfragen">{__('deletePolls')}</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="verfuegbarkeitsbenachrichtigungen" tabindex="10" id="Verfugbarkeitsbenachrichtigungen" />
                    <label for="Verfugbarkeitsbenachrichtigungen">{__('deleteAvailabilityNotifications')}</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="revisions" tabindex="11" id="Revisions" />
                    <label for="Revisions">{__('deleteRevisions')}</label>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">{__('userGeneratedContent')}</div>
            </div>
            <div class="card-body">
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="suchanfragen" tabindex="11" id="Suchanfragen" />
                    <label for="Suchanfragen">{__('deleteSearch')}</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="tags" tabindex="12" id="Tags" />
                    <label for="Tags">{__('deleteTags')}</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="bewertungen" tabindex="13" id="Bewertungen" />
                    <label for="Bewertungen">{__('deleteRatings')}</label>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <div class="card-title">{__('customersOrdersCoupons')}</div>
            </div>
            <div class="card-body">
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="shopkunden" tabindex="14" id="Shopkunden" />
                    <label for="Shopkunden">{__('deleteCustomers')}</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="kwerbenk" tabindex="14" id="KwerbenK" />
                    <label for="KwerbenK">{__('deleteCustomersRecruitCustomers')}</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="bestellungen" tabindex="15" id="Bestellungen" />
                    <label for="Bestellungen">{__('deleteOrders')}</label>
                </div>
                <div class="item">
                    <input type="checkbox" name="cOption_arr[]" value="kupons" tabindex="15" id="Kupons" />
                    <label for="Kupons">{__('deleteCoupons')}</label>
                </div>
            </div>
        </div>
        <div class="save_wrapper">
            <div class="checkbox hide">
                <label><input id="backupDone" type="checkbox" value="" />{__('yesBackupDone')}</label>
            </div>
            <button disabled="true" type="button" value="{__('shopResetButton')}" data-toggle="modal" data-target=".zuruecksetzen-modal" class="btn btn-danger"><i class="fa fa-exclamation-triangle"></i> {__('shopResetButton')}</button>
        </div>
    </form>
</div>
<div class="modal zuruecksetzen-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{__('followingWillBeDeleted')}</h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <p>{__('sureContinue')}</p>
                <button type="button" id="submitZuruecksetzen" class="btn btn-danger">{__('shopResetButton')}</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">{__('cancel')}</button>
            </div>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
