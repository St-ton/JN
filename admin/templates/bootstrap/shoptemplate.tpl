{include file='tpl_inc/header.tpl'}
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
                                                    <input type="hidden" name="cWert[]" value="upload-{$oSetting@iteration}" />
                                                    {include file='tpl_inc/fileupload.tpl'
                                                        fileID="tpl-upload-{$oSetting@iteration}"
                                                        fileName="upload-{$oSetting@iteration}"
                                                        fileDeleteUrl="{$shopURL}/{$PFAD_ADMIN}shoptemplate.php?token={$smarty.session.jtl_token}"
                                                        fileExtraData='{id:1}'
                                                        fileMaxSize="{if !empty($oSetting->rawAttributes.maxFileSize)}{$oSetting->rawAttributes.maxFileSize}{else}1000{/if}"
                                                        fileAllowedExtensions="{if !empty($oSetting->rawAttributes.allowedFileExtensions)}{$oSetting->rawAttributes.allowedFileExtensions}{/if}"
                                                        fileInitialPreview="[
                                                                {if !empty($oSetting->cValue)}
                                                                    '<img src=\"{$shopURL}/templates/{$oTemplate->cOrdner}/{$oSetting->rawAttributes.target}{$oSetting->cValue}?v={$smarty.now}\" class=\"file-preview-image\"/>'
                                                                {/if}
                                                            ]"
                                                        fileInitialPreviewConfig="[
                                                                {
                                                                    url: '{$shopURL}/{$PFAD_ADMIN}shoptemplate.php',
                                                                    extra: {
                                                                            upload: '{$oTemplate->cOrdner}/{$oSetting->rawAttributes.target}{$oSetting->cValue}',
                                                                            id: 'upload-{$oSetting@iteration}',
                                                                            token : '{$smarty.session.jtl_token}',
                                                                            cName : '{$oSetting->cKey}'
                                                                           }
                                                                }
                                                            ]"
                                                    }
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
                        {if isset($smarty.get.activate)}
                            <input type="hidden" name="activate" value="1" />
                            <input type="hidden" name="action" value="activate" />
                        {else}
                            <input type="hidden" name="action" value="save-config" />
                        {/if}
                        <input type="hidden" name="type" value="settings" />
                        <input type="hidden" name="dir" value="{$oTemplate->cOrdner}" />
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
    {include file='tpl_inc/shoptemplate_overview.tpl'}
{/if}
</div>
{include file='tpl_inc/footer.tpl'}
