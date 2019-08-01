{include file='tpl_inc/seite_header.tpl' cTitel=__('emailhistory') cBeschreibung=__('emailhistoryDesc') cDokuURL=__('emailhistoryURL')}
<div id="content">
    {if $oEmailhistory_arr|@count > 0 && $oEmailhistory_arr}
        <form name="emailhistory" method="post" action="emailhistory.php">
            {$jtl_token}
            <script>
                {literal}
                $(document).ready(function() {
                    // onclick-handler for the modal-button 'Ok'
                    $('#submitForm').on('click', function() {
                        // we need to add our interest here again (a anonymouse button is not sent)
                        $('form[name$=emailhistory]').append('<input type="hidden" name="remove_all" value="true">');
                        // do the 'submit'
                        $('form[name$=emailhistory]').submit();
                    })
                });
                {/literal}
            </script>
            <div id="confirmModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">
                                <i class="fal fa-times"></i>
                            </button>
                            <h2 class="modal-title">{__('danger')}!</h2>
                        </div>
                        <div class="modal-body py-5">
                            <p>{__('sureEmailDelete')}</p>
                        </div>
                        <div class="modal-footer">
                            <div class="row">
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <button type="button" class="btn btn-outline-primary" name="cancel" data-dismiss="modal">
                                        {__('cancelWithIcon')}
                                    </button>
                                </div>
                                <div class="col-sm-6 col-xl-auto">
                                    <button type="button" class="btn btn-danger" name="ok" id="submitForm"><i class="fas fa-trash-alt"></i> {__('deleteAll')}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input name="a" type="hidden" value="delete" />
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('emailhistory')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    {include file='tpl_inc/pagination.tpl' pagination=$pagination}
                    <div class="table-responsive">
                        <table class="list table table-striped">
                            <thead>
                            <tr>
                                <th></th>
                                <th class="text-left"></th>
                                <th class="text-left">{__('fromname')}</th>
                                <th class="text-left">{__('fromemail')}</th>
                                <th class="text-left">{__('toname')}</th>
                                <th class="text-left">{__('toemail')}</th>
                                <th class="text-left">{__('date')}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $oEmailhistory_arr as $oEmailhistory}
                                <tr>
                                    <td class="check text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" type="checkbox" name="kEmailhistory[]" id="email-history-id-{$oEmailhistory->getEmailhistory()}" value="{$oEmailhistory->getEmailhistory()}" />
                                            <label class="custom-control-label" for="email-history-id-{$oEmailhistory->getEmailhistory()}"></label>
                                        </div>
                                    </td>
                                    <td>{$oEmailhistory->getSubject()}</td>
                                    <td>{$oEmailhistory->getFromName()}</td>
                                    <td>{$oEmailhistory->getFromEmail()}</td>
                                    <td>{$oEmailhistory->getToName()}</td>
                                    <td>{$oEmailhistory->getToEmail()}</td>
                                    <td>{SmartyConvertDate date=$oEmailhistory->getSent()}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="col-sm-6 col-xl-auto text-left mb-3">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" />
                                <label class="custom-control-label" for="ALLMSGS">{__('globalSelectAll')}</label>
                            </div>
                        </div>
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button name="remove_all" type="button" class="btn btn-danger btn-block mb-2" data-target="#confirmModal" data-toggle="modal"><i class="fas fa-trash-alt"></i> {__('deleteAll')}</button>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button name="zuruecksetzenBTN" type="submit" class="btn btn-warning btn-block">
                                <i class="fas fa-trash-alt"></i> {__('deleteSelected')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info">{__('nodata')}</div>
    {/if}
</div>
