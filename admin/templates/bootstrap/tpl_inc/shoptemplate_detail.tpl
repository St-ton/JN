<form action="{$adminURL}{$route}" method="post" enctype="multipart/form-data" id="form_settings">
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
                    {if $section->key === 'header'}
                        <style>
                            .preset-button {
                                border: 5px solid transparent;
                                max-width: 222px;
                            }
                            .preset-button.selected { border: 5px solid; }
                        </style>
                        <script>
                            {literal}
                            $(document).ready(function(){
                                let $menuTemplate       = $('#header_menu_template'),
                                    $allSettings        = $('[id^="header_"]'),
                                    settingPrefix       = 'header_',
                                    settingPrefixID     = '#' + settingPrefix,
                                    menuTemplateCurrent = $menuTemplate.val(),
                                    settings = {
                                        menu_single_row: {},
                                        menu_multiple_rows: {disableOptionWhen: {
                                                'menu_single_row': 'N'
                                            }
                                        },
                                        menu_center: {disableOptionWhen: {
                                                'menu_multiple_rows':'scroll',
                                                'menu_single_row': 'N'
                                            }
                                        },
                                        menu_scroll: { disableOptionWhen: {
                                                'menu_single_row': 'N'
                                            }
                                        },
                                        menu_logoheight: {},
                                        menu_logo_centered: { disableOptionWhen: {
                                                'menu_single_row': 'N'
                                            }
                                        },
                                        menu_search_width: { disableOptionWhen: {
                                                'menu_single_row': 'N'
                                            }
                                        },
                                        menu_search_position: { disableOptionWhen: {
                                                'menu_single_row': 'N'
                                            }
                                        },
                                        header_full_width: {},
                                        menu_show_topbar: {},
                                };
                                let presets = [
                                    {
                                        name: 'headerStandard',
                                        settings: {
                                            menu_single_row: 'N',
                                            menu_multiple_rows: 'multiple',
                                            menu_center: 'center',
                                            menu_scroll: 'menu',
                                            menu_logoheight: '49',
                                            menu_logo_centered: 'Y',
                                            menu_search_width: '0',
                                            menu_search_position: 'right',
                                            header_full_width: 'N',
                                            menu_show_topbar: 'Y',
                                        }
                                    },
                                    {
                                        name: 'headerLogo',
                                        settings: {
                                            menu_single_row: 'Y',
                                            menu_multiple_rows: 'scroll',
                                            menu_center: 'center',
                                            menu_scroll: 'menu',
                                            menu_logoheight: '110',
                                            menu_logo_centered: 'Y',
                                            menu_search_width: '240',
                                            menu_search_position: 'right',
                                            header_full_width: 'N',
                                            menu_show_topbar: 'Y',
                                        }
                                    },
                                    {
                                        name: 'headerSingle',
                                        settings: {
                                            menu_single_row: 'Y',
                                            menu_multiple_rows: 'scroll',
                                            menu_center: 'center',
                                            menu_scroll: 'menu',
                                            menu_logoheight: '80',
                                            menu_logo_centered: 'N',
                                            menu_search_width: '0',
                                            menu_search_position: 'right',
                                            header_full_width: 'N',
                                            menu_show_topbar: 'Y',
                                        }
                                    },
                                    {
                                        name: 'headerBoxed',
                                        settings: {
                                            menu_single_row: 'Y',
                                            menu_multiple_rows: 'multiple',
                                            menu_center: 'space-between',
                                            menu_scroll: 'menu',
                                            menu_logoheight: '80',
                                            menu_logo_centered: 'N',
                                            menu_search_width: '500',
                                            menu_search_position: 'left',
                                            header_full_width: 'B',
                                            menu_show_topbar: 'Y',
                                        }
                                    },
                                    {
                                        name: 'headerTopbar',
                                        settings: {
                                            menu_single_row: 'Y',
                                            menu_multiple_rows: 'scroll',
                                            menu_center: 'center',
                                            menu_scroll: 'all',
                                            menu_logoheight: '80',
                                            menu_logo_centered: 'N',
                                            menu_search_width: '700',
                                            menu_search_position: 'left',
                                            header_full_width: 'Y',
                                            menu_show_topbar: 'N',
                                        }
                                    },
                                ];
                                $.each(presets, function (key, value) {
                                    let isSelected = value.name === menuTemplateCurrent ? 'selected' : '';
                                    $('#preset-items').append(
                                        '<div class="col col-auto">' +
                                        '<img src="{/literal}{$templateBaseURL}{literal}gfx/header/' + value.name + '.png" id="' + value.name + '" class="preset-button ' + isSelected + '"/>'  +
                                        '</div>')
                                });
                                let $presetButtons = $('.preset-button');
                                $presetButtons.on('click', function () {
                                    setSettings($(this).prop('id'));
                                });
                                $menuTemplate.on('change', function () {
                                    setSettings($(this).val());
                                });
                                $('[id^=' + settingPrefix).on('change', function() {
                                    disableSettings();
                                });
                                //TODO: Generate disabled messages from javascript settings object above
                                $allSettings.on('change', function () {
                                        if (!$menuTemplate.is(this)) {
                                            $presetButtons.removeClass('selected');
                                            $menuTemplate.val('headerCustom');
                                        }
                                    });
                                $allSettings.parent().prop('toggle', 'tooltip');
                                $allSettings.parent().prop('title', '{/literal}{__('tooltipWithoutMenuSingleRow')}{literal}');
                                $(settingPrefixID + 'menu_center').parent().prop('title', '{/literal}{__('tooltipDisabledMenuCenter')}{literal}')
                                function disableSettings() {
                                    $.each(settings, function (key, value) {
                                        if (value.disableOptionWhen !== undefined) {
                                            $.each(value.disableOptionWhen, function (keyOption, valueOption) {
                                                let $settingID = $(settingPrefixID + key);
                                                if ($(settingPrefixID + keyOption).val() === valueOption) {
                                                    $settingID.prop('disabled', true).css('pointer-events', 'none');
                                                    $settingID.parent().tooltip('enable');
                                                    return false;
                                                } else {
                                                    $settingID.prop('disabled', false).css('pointer-events', 'initial');
                                                    $settingID.parent().tooltip('disable');
                                                }
                                            });
                                        }
                                    });
                                }
                                function setSettings(presetId) {
                                    $.each(presets, function (key, value) {
                                        if (presetId === value.name) {
                                            $.each(value.settings, function (presetKey, presetValue) {
                                                $(settingPrefixID + presetKey).val(presetValue);
                                            });
                                        }
                                    });

                                    $menuTemplate.val(presetId);
                                    disableSettings();

                                    $presetButtons.removeClass('selected');
                                    $('#' + presetId).addClass('selected');
                                }
                                disableSettings();

                                $('.fa-desktop')
                                    .prop('toggle', 'tooltip')
                                    .prop('title', '{/literal}{__('tooltipDesktop')}{literal}')
                                    .tooltip('enable');
                                $('.fa-mobile-alt')
                                    .prop('toggle', 'tooltip')
                                    .prop('title', '{/literal}{__('tooltipMobile')}{literal}')
                                    .tooltip('enable');

                                $('#form_settings').on('submit', function () {
                                    $allSettings.prop('disabled', false);
                                });
                            });
                            {/literal}
                        </script>

                        <div id="preset-wrapper">
                            <div id="preset-description">
                                {__('chooseLayout')}
                            </div>
                            <div id="preset-items" class="row mt-3 mb-4">

                            </div>
                        </div>
                    <a class="btn btn-primary mb-5" data-toggle="collapse" href="#header-settings" aria-controls="header-settings">
                        {__('buttonCustomLayout')}
                    </a>
                    <div class="collapse" id="header-settings">
                    {elseif $section->key === 'customsass'}
                        <div class="underline-links mb-5">{__('Custom Sass Description')}</div>
                    {elseif $section->key === 'colors'}
                        <div id="color-info" class="mb-5 text-warning">{__('The color settings might not work as expected in this theme.')}</div>
                    {/if}
                    <div class="row">
                        {foreach $section->settings as $setting}
                            {if !empty($setting->rawAttributes.Subheader)}
                            <div class="col-11 ml-auto">
                                <div class="subheading1 mb-2">{__($setting->rawAttributes.Subheader)}</div>
                                <hr>
                            </div>
                            {/if}
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
                            {if $setting->key === 'theme_default'}
                                <script>
                                    $(document).ready(function () {
                                        $('#theme_theme_default').on('change', function () {
                                            if ($(this).val() === 'blackline') {
                                                $('#color-info').show();
                                            } else {
                                                $('#color-info').hide();
                                            }
                                        }).change();
                                    });
                                </script>
                            {/if}
                            <div class="col-xs-12 col-md-12 {if !empty($setting->rawAttributes.MarginBottom)}mb-5{/if}">
                                <div class="item form-group form-row align-items-center">
                                    {if $setting->isEditable}
                                        <label class="col col-sm-4 col-form-label text-sm-right" for="{$setting->elementID}">
                                            {if $setting->key === 'use_minify' && $action === 'setPreview'}
                                                <span class="badge badge-warning">{__('Might not work correctly in preview mode')}</span>
                                            {/if}
                                            {__($setting->name)}:
                                        </label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 {if $setting->cType === 'number'}config-type-number{/if}">
                                            {if $setting->cType === 'select'}
                                                {include file='tpl_inc/option_select.tpl' setting=$setting section=$section}
                                            {elseif $setting->cType === 'optgroup'}
                                                {include file='tpl_inc/option_optgroup.tpl' setting=$setting section=$section}
                                            {elseif $setting->cType === 'colorpicker'}
                                                {include file='snippets/colorpicker.tpl'
                                                cpID="{$setting->elementID}"
                                                cpName="{$setting->elementID}"
                                                useAlpha=true
                                                cpValue=$setting->value}
                                            {elseif $setting->cType === 'number'}
                                                {include file='tpl_inc/option_number.tpl' setting=$setting section=$section}
                                            {elseif $setting->cType === 'radio'}
                                                {include file='tpl_inc/option_radio.tpl' setting=$setting section=$section}
                                            {elseif $setting->cType === 'textarea' }
                                                {include file='tpl_inc/option_textarea.tpl' setting=$setting section=$section}
                                            {elseif $setting->cType === 'upload' && isset($setting->rawAttributes.target)}
                                                {include file='tpl_inc/option_upload.tpl' setting=$setting section=$section iteration=$setting@iteration}
                                            {else}
                                                {include file='tpl_inc/option_generic.tpl' setting=$setting section=$section iteration=$setting@iteration}
                                            {/if}
                                        </div>
                                    {else}
                                        <input type="hidden" name="{$setting->elementID}" value="{$setting->value|escape:'html'}" />
                                    {/if}
                                </div>
                            </div>
                        {/foreach}
                    </div>{* /row *}
                    {if $section->key === 'header'}
                        </div>
                    {/if}
                </div>
            </div>
        {/foreach}
        <div class="save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <a class="btn btn-outline-primary btn-block" href="{$adminURL}{$route}">
                        {__('cancelWithIcon')}
                    </a>
                </div>
                <div class="col-sm-6 col-xl-auto">
                    {include file='snippets/buttons/saveAndContinueButton.tpl'}
                </div>
                <div class="col-sm-6 col-xl-auto">
                    {if isset($smarty.get.activate)}
                        <input type="hidden" name="activate" value="1" />
                    {/if}
                    <input type="hidden" name="type" value="settings" />
                    <input type="hidden" name="dir" value="{$template->getDir()}" />
                    <input type="hidden" name="admin" value="0" />
                    <button type="submit" class="btn btn-primary btn-block" name="action" value="{if isset($smarty.get.activate)}activate{else}save-config{/if}">
                        {if isset($smarty.get.activate)}<i class="fa fa-share"></i> {__('activateTemplate')}{else}{__('saveWithIcon')}{/if}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
