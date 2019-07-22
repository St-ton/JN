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
                <div class="subheading1">{__('shopContent')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="item">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="cOption_arr[]" value="artikel" tabindex="3" id="Artikel" />
                        <label class="custom-control-label" for="Artikel">{__('deleteProductCategory')}</label>
                    </div>
                </div>
                <div class="item">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="cOption_arr[]" value="news" tabindex="4" id="News" />
                        <label class="custom-control-label" for="News">{__('deleteNews')}</label>
                    </div>
                </div>
                <div class="item">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="cOption_arr[]" value="bestseller" tabindex="5" id="Bestseller" />
                        <label class="custom-control-label" for="Bestseller">{__('deleteBestseller')}</label>
                    </div>
                </div>
                <div class="item">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="cOption_arr[]" value="besucherstatistiken" tabindex="6" id="Besucherstatistiken" />
                        <label class="custom-control-label" for="Besucherstatistiken">{__('deleteVisitorStatistics')}</label>
                    </div>
                </div>
                <div class="item">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="cOption_arr[]" value="preisverlaeufe" tabindex="8" id="Preisverlaufe" />
                        <label class="custom-control-label" for="Preisverlaufe">{__('deletePriceStatistics')}</label>
                    </div>
                </div>
                <div class="item">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="cOption_arr[]" value="umfragen" tabindex="9" id="Umfragen" />
                        <label class="custom-control-label" for="Umfragen">{__('deletePolls')}</label>
                    </div>
                </div>
                <div class="item">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="cOption_arr[]" value="verfuegbarkeitsbenachrichtigungen" tabindex="10" id="Verfugbarkeitsbenachrichtigungen" />
                        <label class="custom-control-label" for="Verfugbarkeitsbenachrichtigungen">{__('deleteAvailabilityNotifications')}</label>
                    </div>
                </div>
                <div class="item">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="cOption_arr[]" value="revisions" tabindex="11" id="Revisions" />
                        <label class="custom-control-label" for="Revisions">{__('deleteRevisions')}</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('userGeneratedContent')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="item">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="cOption_arr[]" value="suchanfragen" tabindex="11" id="Suchanfragen" />
                        <label class="custom-control-label" for="Suchanfragen">{__('deleteSearch')}</label>
                    </div>
                </div>
                <div class="item">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="cOption_arr[]" value="tags" tabindex="12" id="Tags" />
                        <label class="custom-control-label" for="Tags">{__('deleteTags')}</label>
                    </div>
                </div>
                <div class="item">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="cOption_arr[]" value="bewertungen" tabindex="13" id="Bewertungen" />
                        <label class="custom-control-label" for="Bewertungen">{__('deleteRatings')}</label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('customersOrdersCoupons')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="item">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="cOption_arr[]" value="shopkunden" tabindex="14" id="Shopkunden" />
                        <label class="custom-control-label" for="Shopkunden">{__('deleteCustomers')}</label>
                    </div>
                </div>
                <div class="item">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="cOption_arr[]" value="kwerbenk" tabindex="14" id="KwerbenK" />
                        <label class="custom-control-label" for="KwerbenK">{__('deleteCustomersRecruitCustomers')}</label>
                    </div>
                </div>
                <div class="item">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="cOption_arr[]" value="bestellungen" tabindex="15" id="Bestellungen" />
                        <label class="custom-control-label" for="Bestellungen">{__('deleteOrders')}</label>
                    </div>
                </div>
                <div class="item">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="cOption_arr[]" value="kupons" tabindex="15" id="Kupons" />
                        <label class="custom-control-label" for="Kupons">{__('deleteCoupons')}</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto mb-3">
                    <div class="checkbox hide">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" id="backupDone" type="checkbox" value="" />
                            <label class="custom-control-label" for="backupDone">{__('yesBackupDone')}</label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-auto">
                    <button disabled="true" type="button" value="{__('shopResetButton')}" data-toggle="modal" data-target=".zuruecksetzen-modal" class="btn btn-danger btn-block">
                        <i class="fa fa-exclamation-triangle"></i> {__('shopResetButton')}
                    </button>
                </div>
            </div>
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
