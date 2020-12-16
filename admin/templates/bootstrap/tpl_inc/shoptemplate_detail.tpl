<form action="shoptemplate.php" method="post" enctype="multipart/form-data" id="form_settings">
    {$jtl_token}
    <div id="settings" class="settings">
        {if $template->getType() === 'admin' || ($template->getType() !== 'mobil' && $template->isResponsive())}
            <input type="hidden" name="eTyp" value="{if !empty($template->getType())}{$template->getType()}{else}standard{/if}" />
        {else}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('mobile')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    {if $template->getType() === 'mobil' && $template->isResponsive()}
                        <div class="alert alert-warning">{__('warning_responsive_mobile')}</div>
                    {/if}
                    <div class="item form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="eTyp">{__('standardTemplateMobil')}</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select class="custom-select" name="eTyp" id="eTyp">
                                <option value="standard" {if $template->getType() === 'standard'}selected="selected"{/if}>
                                    {__('optimizeBrowser')}
                                </option>
                                <option value="mobil" {if $template->getType() === 'mobil'}selected="selected"{/if}>
                                    {__('optimizeMobile')}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        {foreach $templateConfig as $section}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__($section->name)}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="row">
                        {foreach $section->settings as $setting}
                            {if $setting->key === 'theme_default' && isset($themePreviews) && $themePreviews !== null}
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
                                <input type="hidden" name="cSektion[]" value="{$section->key}" />
                                <input type="hidden" name="cName[]" value="{$setting->key}" />
                                <div class="item form-group form-row align-items-center">
                                    {if $setting->isEditable}
                                        <label class="col col-sm-4 col-form-label text-sm-right" for="{$section->key}-{$setting->key}">{__($setting->name)}:</label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            {if $setting->cType === 'select'}
                                                <select class="custom-select" name="cWert[]" id="{$section->key}-{$setting->key}">
                                                    {foreach $setting->options as $option}
                                                        <option value="{$option->value}" {if $option->value == $setting->value}selected="selected"{/if}>{__($option->name)}</option>
                                                    {/foreach}
                                                </select>
                                            {elseif $setting->cType === 'optgroup'}
                                                <select class="custom-select" name="cWert[]" id="{$section->key}-{$setting->key}">
                                                    {foreach $setting->optGroups as $oOptgroup}
                                                        <optgroup label="{__($oOptgroup->name)}">
                                                            {foreach $oOptgroup->values as $option}
                                                                <option value="{$option->value}" {if $option->value == $setting->value}selected="selected"{/if}>{__($option->name)}</option>
                                                            {/foreach}
                                                        </optgroup>
                                                    {/foreach}
                                                </select>
                                            {elseif $setting->cType === 'colorpicker'}
                                                {include file='snippets/colorpicker.tpl'
                                                cpID="{$section->key}-{$setting->key}"
                                                cpName="cWert[]"
                                                cpValue=$setting->value}
                                            {elseif $setting->cType === 'number'}
                                                <div class="input-group form-counter">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                                            <span class="fas fa-minus"></span>
                                                        </button>
                                                    </div>
                                                    <input class="form-control" type="number" name="cWert[]" id="{$section->key}-{$setting->key}" value="{$setting->value|escape:'html'}" placeholder="{__($setting->cPlaceholder)}" />
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                                            <span class="fas fa-plus"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            {elseif $setting->cType === 'text' || $setting->cType === 'float'}
                                                <input class="form-control" type="text" name="cWert[]" id="{$section->key}-{$setting->key}" value="{$setting->value|escape:'html'}" placeholder="{__($setting->cPlaceholder)}" />
                                            {elseif $setting->cType === 'textarea' }
                                                <div class="form-group">
                                                        <textarea style="resize:{if isset($setting->textareaAttributes.Resizable)}{$setting->textareaAttributes.Resizable}{/if};max-width:800%;width:100%;border:none"
                                                                  name="cWert[]"
                                                                  cols="{if isset($setting->textareaAttributes.Cols)}{$setting->textareaAttributes.Cols}{/if}"
                                                                  rows="{if isset($setting->textareaAttributes.Rows)}{$setting->textareaAttributes.Rows}{/if}"
                                                                  id="{$section->key}-{$setting->key}"
                                                                  placeholder="{__($setting->cPlaceholder)}"
                                                        >{$setting->cTextAreaValue|escape:'html'}
                                                        </textarea>
                                                </div>
                                            {elseif $setting->cType === 'password'}
                                                <div class="form-group">
                                                    <input type="{$setting->cType}" size="32" name="cWert[]" value="{$setting->value}" id="pf_first" class="form-control">
                                                </div>
                                            {elseif $setting->cType === 'upload' && isset($setting->rawAttributes.target)}
                                                <input type="hidden" name="cWert[]" value="upload-{$setting@iteration}" />
                                            {include file='tpl_inc/fileupload.tpl'
                                                fileID="tpl-upload-{$setting@iteration}"
                                                fileName="upload-{$setting@iteration}"
                                                fileDeleteUrl="{$adminURL}/shoptemplate.php?token={$smarty.session.jtl_token}"
                                                fileExtraData='{id:1}'
                                                fileMaxSize="{if !empty($setting->rawAttributes.maxFileSize)}{$setting->rawAttributes.maxFileSize}{else}1000{/if}"
                                                fileAllowedExtensions="{if !empty($setting->rawAttributes.allowedFileExtensions)}{$setting->rawAttributes.allowedFileExtensions}{/if}"
                                                fileInitialPreview="[
                                                    {if !empty($setting->value)}
                                                        '<img src=\"{$shopURL}/templates/{$template->getDir()}/{$setting->rawAttributes.target}{$setting->value}?v={$smarty.now}\" class=\"file-preview-image\"/>'
                                                    {/if}
                                                ]"
                                                fileInitialPreviewConfig="[{
                                                    url: '{$adminURL}/shoptemplate.php',
                                                    extra: {
                                                            upload: '{$template->getDir()}/{$setting->rawAttributes.target}{$setting->value}',
                                                            id:     'upload-{$setting@iteration}',
                                                            token:  '{$smarty.session.jtl_token}',
                                                            cName:  '{$setting->key}'
                                                    }
                                                }]"
                                            }
                                            {/if}
                                        </div>
                                    {else}
                                        <input type="hidden" name="cWert[]" value="{$setting->value|escape:'html'}" />
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
                    <a class="btn btn-outline-primary btn-block" href="shoptemplate.php">
                        {__('cancelWithIcon')}
                    </a>
                </div>
                <div class="col-sm-6 col-xl-auto">
                    {if isset($smarty.get.activate)}
                        <input type="hidden" name="activate" value="1" />
                        <input type="hidden" name="action" value="activate" />
                    {else}
                        <input type="hidden" name="action" value="save-config" />
                    {/if}
                    <input type="hidden" name="type" value="settings" />
                    <input type="hidden" name="dir" value="{$template->getDir()}" />
                    <input type="hidden" name="admin" value="0" />
                    <button type="submit" class="btn btn-primary btn-block">
                        {if isset($smarty.get.activate)}<i class="fa fa-share"></i> {__('activateTemplate')}{else}{__('saveWithIcon')}{/if}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
