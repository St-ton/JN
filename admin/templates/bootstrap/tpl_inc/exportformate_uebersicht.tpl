{include file='tpl_inc/seite_header.tpl' cTitel=__('exportformats') cBeschreibung=__('exportformatsDesc') cDokuURL=__('exportformatsURL')}
<div id="content">
    <script type="text/javascript" src="{$currentTemplateDir}js/jquery.progressbar.js"></script>
    <script type="text/javascript">
        var url = "{$shopURL}/{$PFAD_ADMIN}exportformate.php",
            token = "{$smarty.session.jtl_token}",
            tpl = "{$shopURL}/{$PFAD_ADMIN}{$currentTemplateDir}gfx/jquery";
        {literal}
        $(function () {
            $('#exportall').on('click', function () {
                $('.extract_async').trigger('click');
                return false;
            });
        });

        function init_export(id) {
            $.getJSON(url, {token: token, action: 'export', kExportformat: id, ajax: '1'}, function (cb) {
                do_export(cb);
            });
            return false;
        }

        function do_export(cb) {
            if (typeof cb !== 'object') {
                error_export();
            } else if (cb.bFinished) {
                finish_export(cb);
            } else {
                show_export_info(cb);
                $.getJSON(cb.cURL, {token: token, action: 'export', e: cb.kExportqueue, back: 'admin', ajax: '1', max: cb.nMax}, function (cb) {
                    do_export(cb);
                });
            }
        }

        function error_export(cb) {
            alert('{/literal}{__('errorExport')}{literal}');
        }

        function show_export_info(cb) {
            var elem = '#progress' + cb.kExportformat;
            $(elem).find('p').hide();
            $(elem).find('div').fadeIn();
            $(elem).find('div').progressBar(cb.nCurrent, {
                max:          cb.nMax,
                textFormat:   'fraction',
                steps:        cb.bFirst ? 0 : 20,
                stepDuration: cb.bFirst ? 0 : 20,
                boxImage:     tpl + '/progressbar.gif',
                barImage:     {
                    0: tpl + '/progressbg_red.gif',
                    30: tpl + '/progressbg_orange.gif',
                    50: tpl + '/progressbg_yellow.gif',
                    70: tpl + '/progressbg_green.gif'
                }
            });
        }

        function finish_export(cb) {
            var elem = '#progress' + cb.kExportformat;
            $(elem).find('div').fadeOut(250, function () {
                $('#error-msg-' + cb.kExportformat).remove();
                var text  = $(elem).find('p').html(),
                    error = '';
                if (cb.errorMessage.length > 0) {
                    error = '<span class="red" id="error-msg-' + cb.kExportformat + '"><br>' + cb.errorMessage + '</span>';
                }
                $(elem).find('p').html(text).append(error).fadeIn(1000);
            });
        }
        {/literal}
    </script>

    <div class="card">
        <div class="card-header">
            <div class="subheading1">{__('availableFormats')}</div>
            <hr class="mb-n3">
        </div>
        <div class="table-responsive card-body">
            <table class="table">
                <thead>
                <tr>
                    <th class="text-left">{__('name')}</th>
                    <th class="text-left" style="width:320px">{__('filename')}</th>
                    <th class="text-center">{__('language')}</th>
                    <th class="text-center">{__('currency')}</th>
                    <th class="text-center">{__('customerGroup')}</th>
                    <th class="text-center">{__('lastModified')}</th>
                    <th class="text-center">{__('syntax')}</th>
                    <th class="text-center" width="200">{__('actions')}</th>
                </tr>
                </thead>
                <tbody>
                {foreach $exportformate as $exportformat}
                    {if $exportformat->nSpecial == 0}
                        <tr>
                            <td class="text-left"> {$exportformat->cName}</td>
                            <td class="text-left" id="progress{$exportformat->kExportformat}">
                                <p>{$exportformat->cDateiname}</p>
                                <div></div>
                            </td>
                            <td class="text-center">{$exportformat->Sprache->getLocalizedName()}</td>
                            <td class="text-center">{$exportformat->Waehrung->cName}</td>
                            <td class="text-center">{$exportformat->Kundengruppe->cName}</td>
                            <td class="text-center">{if !empty($exportformat->dZuletztErstellt)}{$exportformat->dZuletztErstellt}{else}-{/if}</td>
                            <td class="text-center">
                                {if (int)$exportformat->nFehlerhaft === 1}
                                    <i class="fal fa-times text-danger"></i>
                                {else}
                                    <i class="fal fa-check text-success"></i>
                                {/if}
                            </td>
                            <td class="text-center">
                                <form method="post" action="exportformate.php">
                                    {$jtl_token}
                                    <input type="hidden" name="kExportformat" value="{$exportformat->kExportformat}" />
                                    <div class="btn-group">
                                        <button name="action" value="delete" class="btn btn-link px-1 remove notext" title="{__('delete')}" onclick="return confirm('{__('sureDeleteFormat')}');">
                                            <span class="icon-hover">
                                                <span class="fal fa-trash-alt"></span>
                                                <span class="fas fa-trash-alt"></span>
                                            </span>
                                        </button>
                                        <button name="action" value="export" class="btn btn-link px-1 extract notext" title="{__('createExportFile')}">
                                            <span class="icon-hover">
                                                <span class="fal fa-plus"></span>
                                                <span class="fas fa-plus"></span>
                                            </span>
                                        </button>
                                        <button name="action" value="download" class="btn btn-link px-1 download notext" title="{__('download')}">
                                            <span class="icon-hover">
                                                <span class="fal fa-download"></span>
                                                <span class="fas fa-download"></span>
                                            </span>
                                        </button>
                                        {if !$exportformat->bPluginContentExtern}
                                            <a href="#" onclick="return init_export('{$exportformat->kExportformat}');" class="btn btn-link px-1 extract_async notext" title="{__('createExportFileAsync')}">
                                                <span class="icon-hover">
                                                    <span class="fal fa-plus-square"></span>
                                                    <span class="fas fa-plus-square"></span>
                                                </span>
                                            </a>
                                        {/if}
                                        <button name="action" value="edit" class="btn btn-link px-1 edit notext" title="{__('edit')}">
                                            <span class="icon-hover">
                                                <span class="fal fa-edit"></span>
                                                <span class="fas fa-edit"></span>
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    {/if}
                {/foreach}
                </tbody>
            </table>
        </div>
        <div class="card-footer save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <a class="btn btn-outline-primary btn-block mb-3" href="#" id="exportall">
                        {__('exportAll')}
                    </a>
                </div>
                <div class="col-sm-6 col-xl-auto">
                    <a class="btn btn-primary btn-block" href="exportformate.php?neuerExport=1&token={$smarty.session.jtl_token}">
                        <i class="fa fa-share"></i> {__('newExportformat')}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
