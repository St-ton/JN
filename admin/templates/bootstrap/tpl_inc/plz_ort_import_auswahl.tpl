{config_load file="$lang.conf" section='plz_ort_import'}
<div id="modalSelect" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2>{__('plz_ort_import_select')}</h2>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fal fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                {if isset($oLand_arr) && count($oLand_arr) > 0}
                <table class="table">
                    <thead>
                        <tr>
                            <th>{__('iso')}</th>
                            <th>{__('country')}</th>
                            <th>{__('date')}</th>
                            <th>{__('size')}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $oLand_arr as $oLand}
                        <tr>
                            <td>{$oLand->cISO}</td>
                            <td>{$oLand->cDeutsch}</td>
                            <td>{$oLand->cDate}</td>
                            <td>{$oLand->cSize}</td>
                            <td><a href="#" data-callback="plz_ort_import" data-ref="{$oLand->cURL}"><i class="fa fa-download"></i></a></td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
                {else}
                <div class="alert alert-warning"><i class="fal fa-exclamation-triangle"></i> {__('plz_ort_import_select_failed')}</div>
                {/if}
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-close"></i> {__('cancel')}</a>
            </div>
        </div>
    </div>
</div>