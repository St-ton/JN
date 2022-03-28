{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('plz_ort_import') cBeschreibung=__('plz_ort_importDesc')}
<div id="content">
    <div class="card">
        <form id="importForm" action="plz_ort_import.php">
            {$jtl_token}
            <div class="card-header">
                <div class="subheading1">{__('plz_ort_available')}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{__('iso')}</th>
                                <th>{__('country')}</th>
                                <th>{__('continent')}</th>
                                <th>{__('entries')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $oPlzOrt_arr as $oPlzOrt}
                                <tr>
                                    <td>{$oPlzOrt->cLandISO}</td>
                                    <td>{$oPlzOrt->cDeutsch}</td>
                                    <td>{$oPlzOrt->cKontinent}</td>
                                    <td>{$oPlzOrt->nPLZOrte|number_format:0:',':'.'}</td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        {include file='tpl_inc/csv_import_btn.tpl' importerId="plz"}
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
