{include file='tpl_inc/seite_header.tpl' cTitel=__('exportformats')}
{literal}
    <script type="text/javascript">
        $(document).ready(function () {
            $('#nAlleXStunden').on('change', function () {
                var val = $(this).val(),
                    customFieldWrapper = $('#custom-freq-input-wrapper');
                if (val === 'custom') {
                    customFieldWrapper.removeClass('d-none')
                        .find('input').attr('name', 'nAlleXStundenCustom');
                } else {
                    customFieldWrapper.addClass('d-none')
                        .find('input').attr('name', '');
                }
            });
        });
    </script>
{/literal}
<div id="content" class="container-fluid2">
    <form name="exportformat_queue" method="post" action="{$adminURL}{$route}">
        {$jtl_token}
        {$cronID = $cron->cronID|default:0}
        <input type="hidden" name="erstellen_eintragen" value="1" />
        {if $cronID > 0}
            <input type="hidden" name="kCron" value="{$cronID}" />
        {/if}
        {if count($exportFormats) > 0}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{if $cronID > 0}{__('save')}{else}{__('exportformatAdd')}{/if}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body" id="formtable">
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="kExportformat">{__('exportformats')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select name="kExportformat" id="kExportformat" class="custom-select">
                                <option value="-1"></option>
                                {foreach $exportFormats as $format}
                                    <option value="{$format->getId()}"{if (isset($error->kExportformat) && $error->kExportformat == $format->getId()) || (isset($cron->foreignKeyID) && $cron->foreignKeyID == $format->getId())} selected{/if}>{$format->getName()}
                                        ({$format->getLanguage()->getLocalizedName()} / {$format->getCurrency()->getName()}
                                        / {$format->getCustomerGroup()->getName()})
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="dStart">{__('exportformatStart')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input id="dStart" name="dStart" type="text" class="form-control" value="{if isset($error->dStart) && $error->dStart|strlen > 0}{$error->dStart}{elseif isset($cron->dStart_de) && $cron->dStart_de|strlen > 0}{$cron->dStart_de}{else}{$smarty.now|date_format:'d.m.Y H:i'}{/if}" />
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="nAlleXStunden">{__('exportformatEveryXHour')}:</label>
                        {assign var=showCustomInput value=false}
                        {assign var=customInput value=''}
                        {if isset($error->nAlleXStunden)}
                            {assign var=customInput value=$error->nAlleXStunden}
                            {assign var=showCustomInput value=!in_array($error->nAlleXStunden, [24, 48, 168])}
                        {elseif isset($cron->frequency)}
                            {assign var=customInput value=$cron->frequency}
                            {assign var=showCustomInput value=!in_array($cron->frequency, [24, 48, 168])}
                        {/if}
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="nAlleXStunden" name="nAlleXStunden" class="custom-select">
                                <option value="24"{if (isset($error->nAlleXStunden) && $error->nAlleXStunden === 24) || (isset($cron->frequency) && $cron->frequency === 24)} selected{/if}>
                                    24 {__('hours')}
                                </option>
                                <option value="48"{if (isset($error->nAlleXStunden) && $error->nAlleXStunden === 48) || (isset($cron->frequency) && $cron->frequency === 48)} selected{/if}>
                                    48 {__('hours')}
                                </option>
                                <option value="168"{if (isset($error->nAlleXStunden) && $error->nAlleXStunden === 168) || (isset($cron->frequency) && $cron->frequency === 168)} selected{/if}>
                                    1 {__('week')}
                                </option>
                                <option value="custom" id="custom-freq"{if $showCustomInput} selected{/if}>
                                    {__('own')}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div id="custom-freq-input-wrapper" class="form-group form-row align-items-center{if !$showCustomInput} d-none{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="nAlleXStundenCustom"></label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="number"
                                   min="1"
                                   value="{$customInput}"
                                   class="form-control"
                                   name="{if $showCustomInput}nAlleXStundenCustom{/if}" id="custom-freq-input"/>
                        </div>
                    </div>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <a class="btn btn-outline-primary btn-block" href="{$adminURL}{$route}">
                                {__('cancelWithIcon')}
                            </a>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button name="action[erstellen_eintragen]" type="submit" value="1" class="btn btn-primary btn-block">
                                <i class="fa fa-save"></i> {if $cronID > 0}{__('save')}{else}{__('exportformatAdd')}{/if}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        {else}
            <div class="alert alert-info">{__('exportformatNoFormat')}</div>
        {/if}
    </form>
</div>
