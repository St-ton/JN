{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='shoptemplate'}
{assign var=cBeschreibung value=__('shoptemplatesDesc')}
{if isset($oEinstellungenXML) && $oEinstellungenXML}
    {assign var=cTitel value={__('settings')}|cat:': '|cat:$oTemplate->cName}
    {if !empty($oTemplate->cDokuURL)}
        {assign var=cDokuURL value=$oTemplate->cDokuURL}
    {else}
        {assign var=cDokuURL value=__('shoptemplateURL')}
    {/if}
{else}
    {assign var=cTitel value=__('shoptemplates')}
    {assign var=cDokuURL value=__('shoptemplateURL')}
{/if}
{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=$cBeschreibung cDokuURL=$cDokuURL}
{*workaround: no async uploads (the fileinput option uploadAsync does not work correctly... *}
<style>.fileinput-upload-button, .kv-file-upload{ldelim}display:none!important;{rdelim}</style>
<div id="content">
{if isset($oEinstellungenXML) && $oEinstellungenXML}
    <form action="shoptemplate.php" method="post" enctype="multipart/form-data" id="form_settings">
        {$jtl_token}
        <div id="settings" class="settings">
            {if isset($oTemplate->eTyp) && ($oTemplate->eTyp === 'admin' || ($oTemplate->eTyp !== 'mobil' && $oTemplate->bResponsive))}
                <input type="hidden" name="eTyp" value="{if !empty($oTemplate->eTyp)}{$oTemplate->eTyp}{else}standard{/if}" />
            {else}
                <div class="card">
                    <div class="card-header">
                        <div class="subheading1">{__('mobile')}</div>
                        <hr class="mb-n3">
                    </div>
                    <div class="card-body">
                        {if $oTemplate->eTyp === 'mobil' && $oTemplate->bResponsive}
                            <div class="alert alert-warning">{__('warning_responsive_mobile')}</div>
                        {/if}
                        <div class="item form-group form-row align-items-center">
                            <label class="col col-sm-4 col-form-label text-sm-right" for="eTyp">{__('standardTemplateMobil')}</label>
                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                <select class="custom-select" name="eTyp" id="eTyp">
                                    <option value="standard" {if $oTemplate->eTyp === 'standard'}selected="selected"{/if}>
                                        {__('optimizeBrowser')}
                                    </option>
                                    <option value="mobil" {if $oTemplate->eTyp === 'mobil'}selected="selected"{/if}>
                                        {__('optimizeMobile')}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}

            {foreach $oEinstellungenXML as $oSection}
                <div class="card">
                    <div class="card-header">
                        <div class="subheading1">{$oSection->cName}</div>
                        <hr class="mb-n3">
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {foreach $oSection->oSettings_arr as $oSetting}
                                {if $oSetting->cKey === 'theme_default' && isset($themePreviews) && $themePreviews !== null}
                                    <div class="col-sm-8 ml-auto">
                                        <div class="item form-group form-row align-items-center" id="theme-preview-wrap" style="display: none;">
                                            <span class="input-group-addon"><strong>{__('preview')}</strong></span>
                                            <img id="theme-preview" alt="" />
                                        </div>
                                        <script type="text/javascript">
                                            var previewJSON = {$themePreviewsJSON};
                                            {literal}
                                            setPreviewImage = function () {
                                                var currentTheme = $('#theme-theme_default').val(),
                                                    previewImage = $('#theme-preview'),
                                                    previewImageWrap = $('#theme-preview-wrap');
                                                if (typeof previewJSON[currentTheme] !== 'undefined') {
                                                    previewImage.attr('src', previewJSON[currentTheme]);
                                                    previewImageWrap.show();
                                                } else {
                                                    previewImageWrap.hide();
                                                }
                                            };
                                            $(document).ready(function () {
                                                setPreviewImage();
                                                $('#theme-theme_default').on('change', function () {
                                                    setPreviewImage();
                                                });
                                            });
                                            {/literal}
                                        </script>
                                    </div>
                                {/if}
                                <div class="col-xs-12 col-md-12">
                                    <input type="hidden" name="cSektion[]" value="{$oSection->cKey}" />
                                    <input type="hidden" name="cName[]" value="{$oSetting->cKey}" />
                                    <div class="item form-group form-row align-items-center">
                                        {if $oSetting->bEditable}
                                            <label class="col col-sm-4 col-form-label text-sm-right" for="{$oSection->cKey}-{$oSetting->cKey}">{$oSetting->cName}:</label>
                                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                                {if $oSetting->cType === 'select'}
                                                    <select class="custom-select" name="cWert[]" id="{$oSection->cKey}-{$oSetting->cKey}">
                                                        {foreach $oSetting->oOptions_arr as $oOption}
                                                            <option value="{$oOption->cValue}" {if $oOption->cValue == $oSetting->cValue}selected="selected"{/if}>{$oOption->cName}</option>
                                                        {/foreach}
                                                    </select>
                                                {elseif $oSetting->cType === 'optgroup'}
                                                    <select class="custom-select" name="cWert[]" id="{$oSection->cKey}-{$oSetting->cKey}">
                                                        {foreach $oSetting->oOptgroup_arr as $oOptgroup}
                                                            <optgroup label="{$oOptgroup->cName}">
                                                            {foreach $oOptgroup->oValues_arr as $oOption}
                                                                <option value="{$oOption->cValue}" {if $oOption->cValue == $oSetting->cValue}selected="selected"{/if}>{$oOption->cName}</option>
                                                            {/foreach}
                                                            </optgroup>
                                                        {/foreach}
                                                    </select>
                                                {elseif $oSetting->cType === 'colorpicker'}
                                                    <div id="{$oSection->cKey}-{$oSetting->cKey}" style="display:inline-block">
                                                        <div style="background-color: {$oSetting->cValue}" class="colorSelector"></div>
                                                    </div>
                                                    <input type="hidden" name="cWert[]" class="{$oSection->cKey}-{$oSetting->cKey}_data" value="{$oSetting->cValue}" />
                                                    <script type="text/javascript">
                                                        $('#{$oSection->cKey}-{$oSetting->cKey}').ColorPicker({ldelim}
                                                            color:    '{$oSetting->cValue}',
                                                            onShow:   function (colpkr) {ldelim}
                                                                $(colpkr).fadeIn(500);
                                                                return false;
                                                                {rdelim},
                                                            onHide:   function (colpkr) {ldelim}
                                                                $(colpkr).fadeOut(500);
                                                                return false;
                                                                {rdelim},
                                                            onChange: function (hsb, hex, rgb) {ldelim}
                                                                $('#{$oSection->cKey}-{$oSetting->cKey} div').css('backgroundColor', '#' + hex);
                                                                $('.{$oSection->cKey}-{$oSetting->cKey}_data').val('#' + hex);
                                                                {rdelim}
                                                            {rdelim});
                                                    </script>
                                                {elseif $oSetting->cType === 'number'}
                                                    <div class="input-group form-counter">
                                                        <div class="input-group-prepend">
                                                            <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                                                <span class="fas fa-minus"></span>
                                                            </button>
                                                        </div>
                                                        <input class="form-control" type="number" name="cWert[]" id="{$oSection->cKey}-{$oSetting->cKey}" value="{$oSetting->cValue|escape:'html'}" placeholder="{$oSetting->cPlaceholder}" />
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                                                <span class="fas fa-plus"></span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                     {elseif $oSetting->cType === 'text' || $oSetting->cType === 'float'}
                                                    <input class="form-control" type="text" name="cWert[]" id="{$oSection->cKey}-{$oSetting->cKey}" value="{$oSetting->cValue|escape:'html'}" placeholder="{$oSetting->cPlaceholder}" />
                                                {elseif $oSetting->cType === 'textarea' }
                                                    <div class="form-group">
                                                        <textarea style="resize:{if isset($oSetting->vTextAreaAttr_arr.Resizable)}{$oSetting->vTextAreaAttr_arr.Resizable}{/if};max-width:800%;width:100%;border:none"
                                                                  name="cWert[]"
                                                                  cols="{if isset($oSetting->vTextAreaAttr_arr.Cols)}{$oSetting->vTextAreaAttr_arr.Cols}{/if}"
                                                                  rows="{if isset($oSetting->vTextAreaAttr_arr.Rows)}{$oSetting->vTextAreaAttr_arr.Rows}{/if}"
                                                                  id="{$oSection->cKey}-{$oSetting->cKey}"
                                                                  placeholder="{$oSetting->cPlaceholder}"
                                                                  >{$oSetting->cTextAreaValue|escape:'html'}
                                                        </textarea>
                                                    </div>
                                                {elseif $oSetting->cType === 'password'}
                                                    <div class="form-group">
                                                        <input type="{$oSetting->cType}" size="32" name="cWert[]" value="{$oSetting->cValue}" id="pf_first" class="form-control">
                                                    </div>
                                                {elseif $oSetting->cType === 'upload' && isset($oSetting->rawAttributes.target)}
                                                    <div class="template-upload">
                                                        <input name="upload-{$oSetting@iteration}"
                                                               id="tpl-upload-{$oSetting@iteration}" type="file"
                                                               class="file"
                                                               accept="{if !empty($oSetting->rawAttributes.accept)}{$oSetting->rawAttributes.accept}{else}image/*{/if}">
                                                    </div>
                                                    <input type="hidden" name="cWert[]" value="upload-{$oSetting@iteration}" />
                                                    <script>
                                                        $('#tpl-upload-{$oSetting@iteration}').fileinput({ldelim}
                                                            uploadAsync: false,
                                                            uploadExtraData: {ldelim}id:1{rdelim},
                                                            uploadUrl: '{$shopURL}/{$PFAD_ADMIN}shoptemplate.php?token={$smarty.session.jtl_token}',
                                                            allowedFileExtensions : {if !empty($oSetting->rawAttributes.allowedFileExtensions)}{$oSetting->rawAttributes.allowedFileExtensions}{else}['jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp']{/if},
                                                            overwriteInitial: true,
                                                            browseOnZoneClick:     true,
                                                            cancelClass: 'btn btn-outline-primary',
                                                            fileActionSettings:    {
                                                                showZoom:   false,
                                                                showDrag:   false,
                                                                showRemove: false,
                                                            },
                                                            deleteUrl: '{$shopURL}/{$PFAD_ADMIN}shoptemplate.php?token={$smarty.session.jtl_token}',
                                                            initialPreviewCount: 1,
                                                            showPreview: true,
                                                            language: '{$language|mb_substr:0:2}',
                                                            maxFileSize: {if !empty($oSetting->rawAttributes.maxFileSize)}{$oSetting->rawAttributes.maxFileSize}{else}1000{/if},
                                                            maxFilesNum: 1{if !empty($oSetting->cValue)}, initialPreview: [
                                                                '<img src="{$shopURL}/templates/{$oTemplate->cOrdner}/{$oSetting->rawAttributes.target}{$oSetting->cValue}?v={$smarty.now}" class="file-preview-image" alt="" title="" />'
                                                            ]{/if},
                                                            initialPreviewConfig: [
                                                                {ldelim}
                                                                    url: '{$shopURL}/{$PFAD_ADMIN}shoptemplate.php',
                                                                    extra: {ldelim}
                                                                            upload: '{$oTemplate->cOrdner}/{$oSetting->rawAttributes.target}{$oSetting->cValue}',
                                                                            id: 'upload-{$oSetting@iteration}',
                                                                            token : '{$smarty.session.jtl_token}',
                                                                            cName : '{$oSetting->cKey}'
                                                                           {rdelim}
                                                                {rdelim}
                                                            ]
                                                        {rdelim}).on('fileuploaded', function(event, data) {ldelim}
                                                            if (data.response.status === 'OK') {ldelim}
                                                                $('#logo-upload-success').show().removeClass('hidden');
                                                                $('.kv-upload-progress').addClass('hide');
                                                            {rdelim} else {ldelim}
                                                                $('#logo-upload-error').show().removeClass('hidden');
                                                            {rdelim}
                                                        {rdelim});
                                                    </script>
                                                {/if}
                                            </div>
                                        {else}
                                            <input type="hidden" name="cWert[]" value="{$oSetting->cValue|escape:'html'}" />
                                        {/if}
                                    </div>
                                </div>
                            {/foreach}
                       </div>{* /row *}
                    </div>
                </div>
            {/foreach}
            <div class="save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        {if isset($smarty.get.activate)}<input type="hidden" name="activate" value="1" />{/if}
                        <input type="hidden" name="type" value="settings" />
                        <input type="hidden" name="ordner" value="{$oTemplate->cOrdner}" />
                        <input type="hidden" name="admin" value="{$admin}" />
                        <button type="submit" class="btn btn-primary btn-block">
                            {if isset($smarty.get.activate)}<i class="fa fa-share"></i> {__('activateTemplate')}{else}{__('saveWithIcon')}{/if}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
{else}
    <div class="card">
        <div class="table-responsive card-body">
            <table class="table">
                <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th class="text-center">{__('status')}</th>
                    <th class="text-center">{__('version')}</th>
                    <th class="text-center">{__('shopversion')}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                {foreach $oTemplate_arr as $oTemplate}
                    <tr>
                        <td class="text-vcenter text-center" width="140">
                            <div class="thumb-box thumb-sm">
                                <div class="thumb" style="background-image:url({if $oTemplate->cPreview|strlen > 0}{$shopURL}/templates/{$oTemplate->cOrdner}/{$oTemplate->cPreview}{else}{$shopURL}/gfx/keinBild.gif{/if})"></div>
                            </div>
                        </td>
                        <td>
                            <ul class="list-unstyled">
                                <li>
                                    <h3 style="margin:0">{$oTemplate->cName}</h3>
                                    {if !empty($oTemplate->cDescription)}
                                        <div class="small">{$oTemplate->cDescription}</div>
                                    {/if}
                                    <span class="label label-default">
                                     <i class="far fa-folder" aria-hidden="true"></i> {$oTemplate->cOrdner}
                                    </span>
                                    {if $oTemplate->bChild === true}<span class="label label-info"><i class="fa fa-level-up" aria-hidden="true"></i> <abbr title="{{__('inheritsFrom')}|sprintf:{$oTemplate->cParent}}">{$oTemplate->cParent}</abbr></span>{/if}

                                    {if isset($oStoredTemplate_arr[$oTemplate->cOrdner])}
                                        {foreach $oStoredTemplate_arr[$oTemplate->cOrdner] as $oStored}
                                            <span class="label label-warning"><i class="fal fa-info-circle" aria-hidden="true"></i> <abbr title="{__('originalExists')} ({$oStored->cVersion})">{$oStored->cVersion}</abbr></span>
                                        {/foreach}
                                    {/if}
                                    <div class="font-size-sm">
                                        {if !empty($oTemplate->cURL)}<a href="{$oTemplate->cURL}" target="_blank">{/if}
                                            {$oTemplate->cAuthor}
                                        {if !empty($oTemplate->cURL)}</a>{/if}
                                    </div>
                                </li>
                            </ul>
                        </td>
                        <td class="text-vcenter text-center">
                            {if !empty($oTemplate->bHasError) && $oTemplate->bHasError === true}
                                <h4 class="label-wrap">
                                    <span class="label label-danger">{__('faulty')}</span>
                                </h4>
                            {elseif $oTemplate->bAktiv}
                                <h4 class="label-wrap">
                                    <span class="label label-success">{__('activated')} {if $oTemplate->eTyp === 'mobil'} ({__('mobileDevices')}{/if}</span>
                                </h4>
                            {/if}
                            {if !empty($oTemplate->checksums)}
                                {if $oTemplate->checksums === true}
                                    <span class="badge badge-success">{__('unmodified')}</span>
                                {else}
                                    <span class="badge badge-warning cursor-pointer" title="{__('tplChecksums')}"
                                        data-toggle="modal" data-target="#tplModal{$oTemplate->cName}">{__('modified')}</span>
                                    <div class="modal fade" id="tplModal{$oTemplate->cName}" tabindex="-1" role="dialog"
                                        aria-labelledby="tplModal{$oTemplate->cName}Label" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                    <h5 class="modal-title" id="tplModal{$oTemplate->cName}Label">
                                                        {$oTemplate->cName} – {__('modifiedFiles')} ({$oTemplate->checksums|count})
                                                    </h5>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="table-responsive table-">
                                                        <table class="table table-sm table-borderless">
                                                            <thead>
                                                            <tr>
                                                                <th class="text-left">{__('file')}</th>
                                                                <th class="text-right">{__('lastModified')}</th>
                                                            </tr>
                                                            </thead>
                                                            {foreach $oTemplate->checksums as $file}
                                                                <tr>
                                                                    <td class="text-left">{$file->name}</td>
                                                                    <td class="text-right">
                                                                        <small class="text-muted">{$file->lastModified}</small>
                                                                    </td>
                                                                </tr>
                                                            {/foreach}
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="modal-footer text-right">
                                                    <button type="button" class="btn btn-primary" data-dismiss="modal">{__('close')}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/if}
                            {/if}
                        </td>
                        <td class="text-vcenter text-center">
                            {$oTemplate->cVersion}
                        </td>
                        <td class="text-vcenter text-center">
                            {$oTemplate->cShopVersion}
                        </td>
                        <td class="text-vcenter text-center">
                            {if !empty($oTemplate->bHasError) && $oTemplate->bHasError === true}
                                <span class="error"><strong>{__('danger')}:</strong><br />{__('parentTemplateMissing')}.</span>
                            {else}
                                {if !$oTemplate->bAktiv}
                                    {if $oTemplate->bEinstellungen}
                                        <a class="btn btn-primary" href="shoptemplate.php?settings={$oTemplate->cOrdner}&activate=1&token={$smarty.session.jtl_token}"><i class="fal fa-share"></i> {__('activate')}</a>
                                    {else}
                                        <a class="btn btn-primary" href="shoptemplate.php?switch={$oTemplate->cOrdner}&token={$smarty.session.jtl_token}"><i class="fal fa-share"></i> {__('activate')}</a>
                                    {/if}
                                {else}
                                    {if $oTemplate->bEinstellungen}
                                        <a class="btn btn-outline-primary" href="shoptemplate.php?settings={$oTemplate->cOrdner}&token={$smarty.session.jtl_token}"><i class="fal fa-edit"></i> {__('settings')}</a>
                                    {/if}
                                {/if}
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{/if}
</div>
{include file='tpl_inc/footer.tpl'}
