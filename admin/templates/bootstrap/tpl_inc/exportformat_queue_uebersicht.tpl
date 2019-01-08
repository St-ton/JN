{include file='tpl_inc/seite_header.tpl' cTitel=__('exportformat') cBeschreibung=__('exportformatDesc') cDokuURL=__('exportformatUrl')}
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || empty($cTab) || $cTab === 'aktiv'} active{/if}">
            <a data-toggle="tab" role="tab" href="#aktiv">{__('exportformatQueue')}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'fertig'} active{/if}">
            <a data-toggle="tab" role="tab" href="#fertig">{__('exportformatTodaysWork')}</a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="aktiv" class="tab-pane fade{if !isset($cTab) || empty($cTab) || $cTab === 'aktiv'} active in{/if}">
            <form method="post" action="exportformat_queue.php">
                {$jtl_token}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{__('exportformatQueue')}</h3>
                    </div>
                    {if $oExportformatCron_arr && $oExportformatCron_arr|@count > 0}
                        <div id="payment">
                            <div id="tabellenLivesuche" class="table-responsive">
                                <table class="table table-striped">
                                    <tr>
                                        <th class="tleft" style="width: 10px;">&nbsp;</th>
                                        <th class="tleft">{__('exportformatFormatSingle')}</th>
                                        <th class="tleft">{__('exportformatOptions')}</th>
                                        <th class="tcenter">{__('exportformatStart')}</th>
                                        <th class="tcenter">{__('exportformatEveryXHourShort')}</th>
                                        <th class="tcenter">{__('exportformatExported')}</th>
                                        <th class="tcenter">{__('exportformatLastStart')}</th>
                                        <th class="tcenter">{__('exportformatNextStart')}</th>
                                        <th class="tcenter">&nbsp;</th>
                                    </tr>
                                    {foreach name=exportformatqueue from=$oExportformatCron_arr item=oExportformatCron}
                                        <tr>
                                            <td class="tleft">
                                                <input name="kCron[]" type="checkbox" value="{$oExportformatCron->cronID}" id="kCron-{$oExportformatCron->cronID}" />
                                            </td>
                                            <td class="tleft"><label for="kCron-{$oExportformatCron->cronID}">{$oExportformatCron->cName}</label></td>
                                            <td class="tleft">{$oExportformatCron->Sprache->cNameDeutsch}/{$oExportformatCron->Waehrung->cName}/{$oExportformatCron->Kundengruppe->cName}</td>
                                            <td class="tcenter">{$oExportformatCron->dStart_de}</td>
                                            <td class="tcenter">{$oExportformatCron->cAlleXStdToDays}</td>
                                            <td class="tcenter">{if isset($oExportformatCron->oJobQueue->nLimitN) && $oExportformatCron->oJobQueue->nLimitN > 0}{$oExportformatCron->oJobQueue->nLimitN}{else}0{/if}
                                                von {$oExportformatCron->nAnzahlArtikel->nAnzahl}</td>
                                            <td class="tcenter">{if $oExportformatCron->dLetzterStart_de === '00.00.0000 00:00'}-{else}{$oExportformatCron->dLetzterStart_de}{/if}</td>
                                            <td class="tcenter">{if $oExportformatCron->dNaechsterStart_de === null}sofort{else}{$oExportformatCron->dNaechsterStart_de}{/if}</td>
                                            <td class="tcenter">
                                                <a href="exportformat_queue.php?action=editieren&kCron={$oExportformatCron->cronID}&token={$smarty.session.jtl_token}"
                                                   class="btn btn-default" title="{__('modify')}"><i class="fa fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    <tr>
                                        <td>
                                            <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                        </td>
                                        <td colspan="8"><label for="ALLMSGS">{__('globalSelectAll')}</label></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <div class="btn-group">
                                <button name="action[erstellen]" type="submit" value="1" class="btn btn-primary add"><i class="fa fa-share"></i> {__('exportformatAdd')}</button>
                                <button name="action[loeschen]" type="submit" value="1" class="btn btn-danger"><i class="fa fa-trash"></i> {__('exportformatDelete')}</button>
                                <button name="action[triggern]" type="submit" value="1" class="btn btn-default"><i class="fa fa-play-circle-o"></i> {__('exportformatTriggerCron')}</button>
                                <button name="action[uebersicht]" type="submit" value="1" class="btn btn-default"><i class="fa fa-refresh"></i> {__('exportformatRefresh')}</button>
                            </div>
                        </div>
                    {else}
                        <div class="panel-body">
                            <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                        </div>
                        <div class="panel-footer">
                            <div class="btn-group">
                                <button name="action[erstellen]" type="submit" value="1" class="btn btn-primary add"><i class="fa fa-share"></i> {__('exportformatAdd')}</button>
                                <button name="action[triggern]" type="submit" value="1" class="btn btn-default"><i class="fa fa-play-circle-o"></i> {__('exportformatTriggerCron')}</button>
                            </div>
                        </div>
                    {/if}
                </div>
            </form>
        </div>
        <div id="fertig" class="tab-pane fade{if isset($cTab) && $cTab === 'fertig'} active in{/if}">
            <div class="block well well-sm">
                <form method="post" action="exportformat_queue.php" class="form-inline">
                    {$jtl_token}
                    <div class="form-group">
                        <label for="nStunden">{__('exportformatLastXHourPre')}</label>
                        <input size="2" class="form-control" id="nStunden" name="nStunden" type="text" value="{$nStunden}" />
                        <label>{__('exportformatLastXHourPost')}</label>
                    </div>
                    <div class="btn-group">
                        <button name="action[fertiggestellt]" type="submit" value="1" class="btn btn-info"><i class="fa fa-search"></i> {__('exportformatShow')}</button>
                    </div>
                </form>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{__('exportformatTodaysWork')}</h3>
                </div>
                {if $oExportformatQueueBearbeitet_arr && $oExportformatQueueBearbeitet_arr|@count > 0}
                    <div id="payment">
                        <div id="tabellenLivesuche" class="table-responsive">
                            <table class="table table-striped">
                                <tr>
                                    <th class="th-1">{__('exportformatFormatSingle')}</th>
                                    <th class="th-2">{__('exportformatFilename')}</th>
                                    <th class="th-3">{__('exportformatOptions')}</th>
                                    <th class="th-4">{__('exportformatExported')}</th>
                                    <th class="th-5">{__('exportformatLastStart')}</th>
                                </tr>
                                {foreach name=exportformatqueue from=$oExportformatQueueBearbeitet_arr item=oExportformatQueueBearbeitet}
                                    <tr>
                                        <td>{$oExportformatQueueBearbeitet->cName}</td>
                                        <td>{$oExportformatQueueBearbeitet->cDateiname}</td>
                                        <td>
                                            {$oExportformatQueueBearbeitet->cNameSprache}/{$oExportformatQueueBearbeitet->cNameWaehrung}/{$oExportformatQueueBearbeitet->cNameKundengruppe}
                                        </td>
                                        <td>{$oExportformatQueueBearbeitet->nLimitN}</td>
                                        <td>{$oExportformatQueueBearbeitet->dZuletztGelaufen_DE}</td>
                                    </tr>
                                {/foreach}
                            </table>
                        </div>
                    </div>
                {else}
                    <div class="panel-body">
                        <div class="alert alert-info">{__('exportformatNoTodaysWork')}</div>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>