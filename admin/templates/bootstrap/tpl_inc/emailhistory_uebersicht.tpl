{include file='tpl_inc/seite_header.tpl' cTitel=__('emailhistory') cBeschreibung=__('emailhistoryDesc') cDokuURL=__('emailhistoryURL')}
<div id="content" class="container-fluid">
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
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">{__('danger')}!</h4>
                        </div>
                        <div class="modal-body">
                            <p>{__('sureEmailDelete')}</p>
                        </div>
                        <div class="modal-footer">
                            <div class="btn-group">
                                <button type="button" class="btn btn-success" name="ok" id="submitForm"><i class="fal fa-check text-success"></i>&nbsp;{__('ok')}</button>
                                <button type="button" class="btn btn-danger" name="cancel" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;{__('cancel')}</button>
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
                <div class="card-body table-responsive">
                    {include file='tpl_inc/pagination.tpl' pagination=$pagination}
                    <table class="list table table-striped">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="tleft">{__('subject')}</th>
                            <th class="tleft">{__('fromname')}</th>
                            <th class="tleft">{__('fromemail')}</th>
                            <th class="tleft">{__('toname')}</th>
                            <th class="tleft">{__('toemail')}</th>
                            <th class="tleft">{__('date')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $oEmailhistory_arr as $oEmailhistory}
                            <tr>
                                <td class="check">
                                    <input type="checkbox" name="kEmailhistory[]" value="{$oEmailhistory->getEmailhistory()}" />
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
                        <tfoot>
                        <tr>
                            <td class="check">
                                <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" /></td>
                            <td colspan="8"><label for="ALLMSGS">{__('globalSelectAll')}</label></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer save-wrapper">
                    <button name="zuruecksetzenBTN" type="submit" class="btn btn-warning"><i class="fas fa-trash-alt"></i> {__('deleteSelected')}</button>
                    <button name="remove_all" type="button" class="btn btn-danger" data-target="#confirmModal" data-toggle="modal"><i class="fas fa-trash-alt"></i> {__('deleteAll')}</button>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info">{__('nodata')}</div>
    {/if}
</div>
