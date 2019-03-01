{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='sitemapExport'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('sitemapExport') cBeschreibung=__('sitemapExportDesc') cDokuURL=__('sitemapExportURL')}
<div id="content" class="container-fluid">
    <div id="confirmModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{__('danger')}!</h4>
                </div>
                <div class="modal-body">
                    <p>{__('sureDeleteEntries')}</p>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-info" name="cancel" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;{__('cancel')}</button>
                        <button id="formSubmit" type="button" class="btn btn-danger" name="delete" ><i class="fa fa-trash"></i>&nbsp;{__('delete')}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'export'} active{/if}">
            <a data-toggle="tab" role="tab" href="#export">{__('sitemapExport')}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'downloads'} active{/if}">
            <a data-toggle="tab" role="tab" href="#downloads">{__('sitemapDownload')}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'report'} active{/if}">
            <a data-toggle="tab" role="tab" href="#report">{__('sitemapReport')}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{__('settings')}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="export" class="tab-pane fade {if !isset($cTab) || $cTab === 'export'} active in{/if}">
            {if isset($errorNoWrite) && $errorNoWrite|strlen > 0}
                <div class="alert alert-danger">{$errorNoWrite}</div>
            {/if}

            <p><input type="text" readonly="readonly" value="{$URL}" class="form-control" /></p>

            <div class="alert alert-info">
                <p>{__('searchEngines')}</p>
                <p>{__('download')} <a href="{$URL}">{__('xml')}</a></p>
            </div>

            <form action="sitemap.php" method="post">
                {$jtl_token}
                <input type="hidden" name="update" value="1" />
                <input type="hidden" name="tab" value="export" />

                <p class="submit">
                    <button type="submit" value="{__('sitemapExportSubmit')}" class="btn btn-primary"><i class="fa fa-share"></i> {__('sitemapExportSubmit')}</button>
                </p>
            </form>
        </div>
        <div id="downloads" class="tab-pane fade {if isset($cTab) && $cTab === 'downloads'} active in{/if}">
            <div class="panel-heading well well-sm">
                <div class="toolbar well well-sm">
                    <form id="formDeleteSitemapExport" method="post" action="sitemapexport.php" class="form-inline">
                        <div class="form-group">
                            {$jtl_token}
                            <input type="hidden" name="action" value="">
                            <input type="hidden" name="tab" value="downloads">
                            <input type="hidden" name="SitemapDownload_nPage" value="0">
                            <label for="nYear">Jahr</label>
                            <select id="nYear" name="nYear_downloads">
                                {foreach $oSitemapDownloadYears_arr as $oSitemapDownloadYear}
                                    <option value="{$oSitemapDownloadYear->year}"{if isset($nSitemapDownloadYear) && $nSitemapDownloadYear == $oSitemapDownloadYear->year} selected="selected"{/if}>{$oSitemapDownloadYear->year}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="btn-group">
                            <button name="action[year_downloads]" type="submit" value="1" class="btn btn-info"><i class="fa fa-search"></i>&nbsp;{__('show')}</button>
                            <button type="button" class="btn btn-danger"
                                    data-form="#formDeleteSitemapExport" data-action="year_downloads_delete" data-target="#confirmModal" data-toggle="modal" data-title="{__('sitemapDownload')} löschen"><i class="fa fa-trash"></i>&nbsp;{__('delete')}</button>
                        </div>
                    </form>
                </div>
                {include file='tpl_inc/pagination.tpl' oPagination=$oSitemapDownloadPagination cParam_arr=['tab' => 'downloads', 'nYear_downloads' => {$nSitemapDownloadYear}]}
            </div>
            {if isset($oSitemapDownload_arr) && $oSitemapDownload_arr|@count > 0}
                <div class="panel panel-default">
                    <form name="sitemapdownload" method="post" action="sitemapexport.php">
                        {$jtl_token}
                        <input type="hidden" name="download_edit" value="1" />
                        <input type="hidden" name="tab" value="downloads" />
                        <input type="hidden" name="nYear_downloads" value="{$nSitemapDownloadYear}" />
                        <div id="payment">
                            <div id="tabellenBewertung" class="table-responsive">
                                <table class="table table-striped">
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th>{__('sitemapName')}</th>
                                        <th>{__('sitemapBot')}</th>
                                        <th class="text-right">{__('sitemapDate')}</th>
                                    </tr>
                                    {foreach $oSitemapDownload_arr as $oSitemapDownload}
                                        <tr>
                                            <td width="20">
                                                <input name="kSitemapTracker[]" type="checkbox" value="{$oSitemapDownload->kSitemapTracker}">
                                            </td>
                                            <td><a href="{\JTL\Shop::getURL()}/{$oSitemapDownload->cSitemap}" target="_blank">{$oSitemapDownload->cSitemap}</a></td>
                                            <td>
                                                <strong>{__('sitemapIP')}</strong>: {$oSitemapDownload->cIP}<br />
                                                {if $oSitemapDownload->cBot|strlen > 0}
                                                    <strong>{__('sitemapBot')}</strong>: {$oSitemapDownload->cBot}
                                                {else}
                                                    <strong>{__('sitemapUserAgent')}</strong>: <abbr title="{$oSitemapDownload->cUserAgent}">{$oSitemapDownload->cUserAgent|truncate:60}</abbr>
                                                {/if}
                                            </td>
                                            <td class="text-right" width="130">{$oSitemapDownload->dErstellt_DE}</td>
                                        </tr>
                                    {/foreach}
                                    <tr>
                                        <td>
                                            <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                        </td>
                                        <td colspan="6"><label for="ALLMSGS">{__('sitemapSelectAll')}</label></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="panel-footer">
                                <div class="button-group">
                                    <button class="btn btn-danger" name="loeschen" type="submit" value="{__('delete')}"><i class="fa fa-trash"></i> {__('deleteSelected')}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
            {/if}
        </div>
        <div id="report" class="tab-pane fade {if isset($cTab) && $cTab === 'report'} active in{/if}">
            <div class="panel-heading well well-sm">
                <div class="toolbar well well-sm">
                    <form id="formDeleteSitemapReport" method="post" action="sitemapexport.php" class="form-inline">
                        <div class="form-group">
                            {$jtl_token}
                            <input type="hidden" name="action" value="">
                            <input type="hidden" name="tab" value="report">
                            <input type="hidden" name="SitemapReport_nPage" value="0">
                            <label for="nYear">{__('year')}</label>
                            <select id="nYear" name="nYear_reports">
                                {foreach $oSitemapReportYears_arr as $oSitemapReportYear}
                                    <option value="{$oSitemapReportYear->year}"{if isset($nSitemapReportYear) && $nSitemapReportYear == $oSitemapReportYear->year} selected="selected"{/if}>{$oSitemapReportYear->year}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="btn-group">
                            <button name="action[year_reports]" type="submit" value="1" class="btn btn-info"><i class="fa fa-search"></i>&nbsp;{__('show')}</button>
                            <button type="button" class="btn btn-danger"
                                    data-form="#formDeleteSitemapReport" data-action="year_reports_delete" data-target="#confirmModal" data-toggle="modal" data-title="{__('sitemapReport')} löschen"><i class="fa fa-trash"></i>&nbsp;{__('delete')}</button>
                        </div>
                    </form>
                </div>
                {include file='tpl_inc/pagination.tpl' oPagination=$oSitemapReportPagination cParam_arr=['tab' => 'report', 'nYear_reports' => {$nSitemapReportYear}]}
            </div>
            {if isset($oSitemapReport_arr) && $oSitemapReport_arr|@count > 0}
                <div class="panel panel-default">
                    <form name="sitemapreport" method="post" action="sitemapexport.php">
                        {$jtl_token}
                        <input type="hidden" name="report_edit" value="1">
                        <input type="hidden" name="tab" value="report">
                        <input type="hidden" name="nYear_reports" value="{$nSitemapReportYear}" />
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <tr>
                                    <th class="check"></th>
                                    <th class="th-1"></th>
                                    <th class="tleft">{__('sitemapProcessTime')}</th>
                                    <th class="th-3">{__('sitemapTotalURL')}</th>
                                    <th class="th-5">{__('sitemapDate')}</th>
                                </tr>
                                {foreach $oSitemapReport_arr as $oSitemapReport}
                                    <tr>
                                        <td class="check">
                                            <input name="kSitemapReport[]" type="checkbox" value="{$oSitemapReport->kSitemapReport}">
                                        </td>
                                        {if isset($oSitemapReport->oSitemapReportFile_arr) && $oSitemapReport->oSitemapReportFile_arr|@count > 0}
                                            <td>
                                                <a href="#" onclick="$('#info_{$oSitemapReport->kSitemapReport}').toggle();return false;"><i class="fa fa-plus-circle"></i></a>
                                            </td>
                                        {else}
                                            <td>&nbsp;</td>
                                        {/if}
                                        <td class="tcenter">{$oSitemapReport->fVerarbeitungszeit}s</td>
                                        <td class="tcenter">{$oSitemapReport->nTotalURL}</td>
                                        <td class="tcenter">{$oSitemapReport->dErstellt_DE}</td>
                                    </tr>
                                    {if isset($oSitemapReport->oSitemapReportFile_arr) && $oSitemapReport->oSitemapReportFile_arr|@count > 0}
                                        <tr id="info_{$oSitemapReport->kSitemapReport}" style="display: none;">
                                            <td>&nbsp;</td>
                                            <td colspan="4">

                                                <table class="table-striped" border="0" cellspacing="1" cellpadding="0" width="100%">
                                                    <tr>
                                                        <th class="tleft">{__('sitemapName')}</th>
                                                        <th class="th-2">{__('sitemapCountURL')}</th>
                                                        <th class="th-3">{__('sitemapSize')}</th>
                                                    </tr>

                                                    {foreach $oSitemapReport->oSitemapReportFile_arr as $oSitemapReportFile}
                                                        <tr>
                                                            <td>{$oSitemapReportFile->cDatei}</td>
                                                            <td class="tcenter">{$oSitemapReportFile->nAnzahlURL}</td>
                                                            <td class="tcenter">{$oSitemapReportFile->fGroesse} KB</td>
                                                        </tr>
                                                    {/foreach}
                                                </table>

                                            </td>
                                        </tr>
                                    {/if}
                                {/foreach}
                                <tr>
                                    <td class="check">
                                        <input name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);">
                                    </td>
                                    <td colspan="4"><label for="ALLMSGS2">{__('sitemapSelectAll')}</label></td>
                                </tr>
                            </table>
                        </div>
                        <div class="panel-footer">
                            <div class="button-group">
                                <button name="loeschen" type="submit" value="{__('delete')}" class="btn btn-danger"><i class="fa fa-trash"></i> {__('deleteSelected')}</button>
                            </div>
                        </div>
                    </form>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
            {/if}
        </div>
        <div id="einstellungen" class="tab-pane fade {if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='sitemapexport.php' buttonCaption=__('save') title=__('settings') tab='einstellungen'}
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#confirmModal').on('show.bs.modal', function (ev) {
        var $btn = $(ev.relatedTarget);
        var $dlg = $(this);
        $dlg.find('.modal-title').text($btn.data('title'));
        $dlg.find('#formSubmit')
            .data('form', $btn.data('form'))
            .data('action', $btn.data('action'));
    });
    $('#formSubmit').on('click', function (ev) {
        var $form = $($(this).data('form'));
        if ($form.length) {
            $form.find('input[name="action"]').val($(this).data('action'));
            $form.submit();
        }
    });
</script>
{include file='tpl_inc/footer.tpl'}
