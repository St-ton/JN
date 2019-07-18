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
                    <th class="tleft">{__('name')}</th>
                    <th class="tleft" style="width:320px">{__('filename')}</th>
                    <th class="tcenter">{__('language')}</th>
                    <th class="tcenter">{__('currency')}</th>
                    <th class="tcenter">{__('customerGroup')}</th>
                    <th class="tcenter">{__('lastModified')}</th>
                    <th class="tcenter">{__('syntax')}</th>
                    <th class="tcenter" width="200">{__('actions')}</th>
                </tr>
                </thead>
                <tbody>
                {foreach $exportformate as $exportformat}
                    {if $exportformat->nSpecial == 0}
                        <tr>
                            <td class="tleft"> {$exportformat->cName}</td>
                            <td class="tleft" id="progress{$exportformat->kExportformat}">
                                <p>{$exportformat->cDateiname}</p>
                                <div></div>
                            </td>
                            <td class="tcenter">{$exportformat->Sprache->getLocalizedName()}</td>
                            <td class="tcenter">{$exportformat->Waehrung->cName}</td>
                            <td class="tcenter">{$exportformat->Kundengruppe->cName}</td>
                            <td class="tcenter">{if !empty($exportformat->dZuletztErstellt)}{$exportformat->dZuletztErstellt}{else}-{/if}</td>
                            <td class="tcenter">
                                {if (int)$exportformat->nFehlerhaft === 1}
                                    <i class="fal fa-times text-danger"></i>
                                {else}
                                    <i class="fal fa-check text-success"></i>
                                {/if}
                            </td>
                            <td class="tcenter">
                                <form method="post" action="exportformate.php">
                                    {$jtl_token}
                                    <input type="hidden" name="kExportformat" value="{$exportformat->kExportformat}" />
                                    <button name="action" value="delete" class="btn btn-danger btn-circle remove notext" title="{__('delete')}" onclick="return confirm('{__('sureDeleteFormat')}');"><i class="fas fa-trash-alt"></i></button>
                                    <button name="action" value="export" class="btn btn-default btn-circle extract notext" title="{__('createExportFile')}"><i class="fal fa-plus"></i></button>
                                    <button name="action" value="download" class="btn btn-default btn-circle download notext" title="{__('download')}"><i class="fa fa-download"></i></button>
                                    {if !$exportformat->bPluginContentExtern}
                                        <a href="#" onclick="return init_export('{$exportformat->kExportformat}');" class="btn btn-default btn-circle extract_async notext" title="{__('createExportFileAsync')}"><i class="fal fa-plus-square"></i></a>
                                    {/if}
                                    <button name="action" value="edit" class="btn btn-primary btn-circle edit notext" title="{__('edit')}"><i class="fal fa-edit"></i></button>
                                </form>
                            </td>
                        </tr>
                    {/if}
                {/foreach}
                </tbody>
            </table>
        </div>
        <div class="card-footer save-wrapper">
            <a class="btn btn-default" href="#" id="exportall">
                {__('exportAll')}
            </a>
            <a class="btn btn-primary" href="exportformate.php?neuerExport=1&token={$smarty.session.jtl_token}">
                <i class="fa fa-share"></i> {__('newExportformat')}
            </a>
        </div>
    </div>
</div>
