{config_load file="$lang.conf" section='plz_ort_import'}

<div class="row">
    <div class="col-sm-1"><strong>{__('iso')}</strong></div>
    <div class="col-sm-4"><strong>{__('country')}</strong></div>
    <div class="col-sm-3"><strong>{__('continent')}</strong></div>
    <div class="col-sm-3"><strong>{__('entries')}</strong></div>
    <div class="col-sm-1"><strong>{__('action')}</strong></div>
</div>
{foreach $oPlzOrt_arr as $oPlzOrt}
<div class="row">
    <div class="col-sm-1">{$oPlzOrt->cLandISO}</div>
    <div class="col-sm-4">{$oPlzOrt->cDeutsch}</div>
    <div class="col-sm-3">{$oPlzOrt->cKontinent}</div>
    <div class="col-sm-3">{$oPlzOrt->nPLZOrte|number_format:0:',':'.'}</div>
    <div class="col-sm-1">
        {if isset($oPlzOrt->nBackup) && $oPlzOrt->nBackup > 0}<a title="{__('plz_ort_import_reset_desc')}" href="#" data-callback="plz_ort_import_reset" data-ref="{$oPlzOrt->cLandISO}"><i class="fa fa-history"></i></a> {/if}
        {if isset($oPlzOrt->cImportFile)} <a title="{__('plz_ort_import_refresh_desc')}" href="#" data-callback="plz_ort_import_refresh" data-ref="{$oPlzOrt->cImportFile}"><i class="fa fa-download"></i></a>{/if}
    </div>
</div>
{/foreach}