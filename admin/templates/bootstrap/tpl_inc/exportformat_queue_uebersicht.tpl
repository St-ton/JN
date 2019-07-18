{include file='tpl_inc/seite_header.tpl' cTitel=__('exportformat') cBeschreibung=__('exportformatDesc') cDokuURL=__('exportformatUrl')}
<div id="content">
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if !isset($cTab) || empty($cTab) || $cTab === 'aktiv'} active{/if}" data-toggle="tab" role="tab" href="#aktiv">
                        {__('exportformatQueue')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'fertig'} active{/if}" data-toggle="tab" role="tab" href="#fertig">
                        {__('exportformatTodaysWork')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="aktiv" class="tab-pane fade{if !isset($cTab) || empty($cTab) || $cTab === 'aktiv'} active show{/if}">
                <form method="post" action="exportformat_queue.php">
                    {$jtl_token}
                    <div>
                        <div class="subheading1">{__('exportformatQueue')}</div>
                        <hr class="mb-3">
                        {if $oExportformatCron_arr && $oExportformatCron_arr|@count > 0}
                            <div id="tabellenLivesuche" class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="tleft" style="width: 10px;">&nbsp;</th>
                                            <th class="tleft">{__('exportformat')}</th>
                                            <th class="tleft">{__('exportformatOptions')}</th>
                                            <th class="tcenter">{__('exportformatStart')}</th>
                                            <th class="tcenter">{__('repetition')}</th>
                                            <th class="tcenter">{__('exportformatExported')}</th>
                                            <th class="tcenter">{__('exportformatLastStart')}</th>
                                            <th class="tcenter">{__('exportformatNextStart')}</th>
                                            <th class="tcenter">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $oExportformatCron_arr as $oExportformatCron}
                                        <tr>
                                            <td class="tleft">
                                                <input name="kCron[]" type="checkbox" value="{$oExportformatCron->cronID}" id="kCron-{$oExportformatCron->cronID}" />
                                            </td>
                                            <td class="tleft"><label for="kCron-{$oExportformatCron->cronID}">{$oExportformatCron->cName}</label></td>
                                            <td class="tleft">{$oExportformatCron->Sprache->getLocalizedName()}/{$oExportformatCron->Waehrung->cName}/{$oExportformatCron->Kundengruppe->cName}</td>
                                            <td class="tcenter">{$oExportformatCron->dStart_de}</td>
                                            <td class="tcenter">{$oExportformatCron->cAlleXStdToDays}</td>
                                            <td class="tcenter">
                                                {$oExportformatCron->oJobQueue->tasksExecuted|default:0}/{$oExportformatCron->nAnzahlArtikel->nAnzahl}
                                            </td>
                                            <td class="tcenter">{if $oExportformatCron->dLetzterStart_de === '00.00.0000 00:00'}-{else}{$oExportformatCron->dLetzterStart_de}{/if}</td>
                                            <td class="tcenter">{if $oExportformatCron->dNaechsterStart_de === null}sofort{else}{$oExportformatCron->dNaechsterStart_de}{/if}</td>
                                            <td class="tcenter">
                                                <a href="exportformat_queue.php?action=editieren&kCron={$oExportformatCron->cronID}&token={$smarty.session.jtl_token}"
                                                   class="btn btn-default" title="{__('modify')}"><i class="fal fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>
                                                <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                            </td>
                                            <td colspan="8"><label for="ALLMSGS">{__('globalSelectAll')}</label></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="card-footer save-wrapper">
                                <button name="action[loeschen]" type="submit" value="1" class="btn btn-danger"><i class="fas fa-trash-alt"></i> {__('exportformatDelete')}</button>
                                <button name="action[triggern]" type="submit" value="1" class="btn btn-default"><i class="fa fa-play-circle-o"></i> {__('exportformatTriggerCron')}</button>
                                <button name="action[uebersicht]" type="submit" value="1" class="btn btn-default"><i class="fa fa-refresh"></i> {__('refresh')}</button>
                                <button name="action[erstellen]" type="submit" value="1" class="btn btn-primary add"><i class="fa fa-share"></i> {__('exportformatAdd')}</button>
                            </div>
                        {else}
                            <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                            <div class="card-footer save-wrapper">
                                <button name="action[triggern]" type="submit" value="1" class="btn btn-default"><i class="fa fa-play-circle-o"></i> {__('exportformatTriggerCron')}</button>
                                <button name="action[erstellen]" type="submit" value="1" class="btn btn-primary add"><i class="fa fa-share"></i> {__('exportformatAdd')}</button>
                            </div>
                        {/if}
                    </div>
                </form>
            </div>
            <div id="fertig" class="tab-pane fade{if isset($cTab) && $cTab === 'fertig'} active show{/if}">
                <div class="mb-5">
                    <form method="post" action="exportformat_queue.php" class="form-inline">
                        {$jtl_token}
                        <div class="form-group">
                            <label for="nStunden">{__('exportformatLastXHourPre')}</label>
                            <input size="2" class="form-control" id="nStunden" name="nStunden" type="text" value="{$nStunden}" />
                            <label>{__('hours')}</label>
                        </div>
                        <div class="btn-group">
                            <button name="action[fertiggestellt]" type="submit" value="1" class="btn btn-primary"><i class="fal fa-search"></i></button>
                        </div>
                    </form>
                </div>
                <div>
                    <div class="subheading1">{__('exportformatTodaysWork')}</div>
                    <hr class="mb-3">
                    <div>
                    {if $oExportformatQueueBearbeitet_arr && $oExportformatQueueBearbeitet_arr|@count > 0}
                        <div id="tabellenLivesuche" class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="th-1">{__('exportformat')}</th>
                                        <th class="th-2">{__('filename')}</th>
                                        <th class="th-3">{__('exportformatOptions')}</th>
                                        <th class="th-4">{__('exportformatExported')}</th>
                                        <th class="th-5">{__('exportformatLastStart')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                {foreach $oExportformatQueueBearbeitet_arr as $oExportformatQueueBearbeitet}
                                    <tr>
                                        <td>{$oExportformatQueueBearbeitet->cName}</td>
                                        <td>{$oExportformatQueueBearbeitet->cDateiname}</td>
                                        <td>
                                            {$oExportformatQueueBearbeitet->name}/{$oExportformatQueueBearbeitet->cNameWaehrung}/{$oExportformatQueueBearbeitet->cNameKundengruppe}
                                        </td>
                                        <td>{$oExportformatQueueBearbeitet->nLimitN}</td>
                                        <td>{$oExportformatQueueBearbeitet->dZuletztGelaufen_DE}</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                    {else}
                        <div class="alert alert-info">{__('exportformatNoTodaysWork')}</div>
                    {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
