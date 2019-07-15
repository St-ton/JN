{config_load file="$lang.conf" section='plz_ort_import'}

<div class="table-responsive">
<table class="table">
    <thead>
        <tr>
            <th>{__('iso')}</th>
            <th>{__('country')}</th>
            <th>{__('continent')}</th>
            <th>{__('entries')}</th>
            <th>{__('action')}</th>
        </tr>
    </thead>
    <tbody>
{foreach $oPlzOrt_arr as $oPlzOrt}
    <tr>
        <td>{$oPlzOrt->cLandISO}</td>
        <td>{$oPlzOrt->cDeutsch}</td>
        <td>{$oPlzOrt->cKontinent}</td>
        <td>{$oPlzOrt->nPLZOrte|number_format:0:',':'.'}</td>
        <td>
            {if isset($oPlzOrt->nBackup) && $oPlzOrt->nBackup > 0}<a title="{__('plz_ort_import_reset_desc')}" href="#" data-callback="plz_ort_import_reset" data-ref="{$oPlzOrt->cLandISO}"><i class="fa fa-history"></i></a> {/if}
            {if isset($oPlzOrt->cImportFile)} <a title="{__('plz_ort_import_refresh_desc')}" href="#" data-callback="plz_ort_import_refresh" data-ref="{$oPlzOrt->cImportFile}"><i class="fa fa-download"></i></a>{/if}
        </td>
    </tr>
{/foreach}
    </tbody>
</table>