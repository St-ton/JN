{include file='tpl_inc/seite_header.tpl' cTitel=#exportformat# cBeschreibung=#exportformatDesc# cDokuURL=#exportformatUrl#}
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || empty($cTab) || $cTab === 'aktiv'} active{/if}">
            <a data-toggle="tab" role="tab" href="#aktiv">{#exportformatQueue#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'fertig'} active{/if}">
            <a data-toggle="tab" role="tab" href="#fertig">{#exportformatTodaysWork#}</a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="aktiv" class="tab-pane fade{if !isset($cTab) || empty($cTab) || $cTab === 'aktiv'} active in{/if}">
            <form method="post" action="exportformat_queue.php">
                {$jtl_token}
                {if $oExportformatCron_arr|@count > 0 && $oExportformatCron_arr}
                    <div id="payment">
                        <div id="tabellenLivesuche">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">{#exportformatQueue#}</h3>
                                </div>
                                <table class="table">
                                    <tr>
                                        <th class="tleft" style="width: 10px;">&nbsp;</th>
                                        <th class="tleft">{#exportformatFormatSingle#}</th>
                                        <th class="tleft">{#exportformatOptions#}</th>
                                        <th class="tcenter">{#exportformatStart#}</th>
                                        <th class="tcenter">{#exportformatEveryXHourShort#}</th>
                                        <th class="tcenter">{#exportformatExported#}</th>
                                        <th class="tcenter">{#exportformatLastStart#}</th>
                                        <th class="tcenter">{#exportformatNextStart#}</th>
                                        <th class="tcenter">&nbsp;</th>
                                    </tr>
                                    {foreach name=exportformatqueue from=$oExportformatCron_arr item=oExportformatCron}
                                        <tr class="tab_bg{$smarty.foreach.exportformatqueue.iteration%2}">
                                            <td class="tleft">
                                                <input name="kCron[]" type="checkbox" value="{$oExportformatCron->kCron}">
                                            </td>
                                            <td class="tleft">{$oExportformatCron->cName}</td>
                                            <td class="tleft">{$oExportformatCron->Sprache->cNameDeutsch}
                                                / {$oExportformatCron->Waehrung->cName}
                                                / {$oExportformatCron->Kundengruppe->cName}</td>
                                            <td class="tcenter">{$oExportformatCron->dStart_de}</td>
                                            <td class="tcenter">{$oExportformatCron->cAlleXStdToDays}</td>
                                            <td class="tcenter">{if isset($oExportformatCron->oJobQueue->nLimitN) && $oExportformatCron->oJobQueue->nLimitN > 0}{$oExportformatCron->oJobQueue->nLimitN}{else}0{/if}
                                                von {if $oExportformatCron->nSpecial == "1"}{$oExportformatCron->nAnzahlArtikelYatego->nAnzahl}{else}{$oExportformatCron->nAnzahlArtikel->nAnzahl}{/if}</td>
                                            <td class="tcenter">{$oExportformatCron->dLetzterStart_de}</td>
                                            <td class="tcenter">{$oExportformatCron->dNaechsterStart_de}</td>
                                            <td class="tcenter">
                                                <a href="exportformat_queue.php?action=editieren&kCron={$oExportformatCron->kCron}&token={$smarty.session.jtl_token}" class="btn btn-default"><i class="fa fa-edit"></i></a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    <tr>
                                        <td class="TD1">
                                            <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                        </td>
                                        <td colspan="8" class="TD7"><label for="ALLMSGS">{#globalSelectAll#}</label></td>
                                    </tr>
                                </table>
                                <div class="panel-footer">
                                    <div class="btn-group">
                                        <button name="action[erstellen]" type="submit" value="1" class="btn btn-primary add"><i class="fa fa-share"></i> {#exportformatAdd#}</button>
                                        <button name="action[loeschen]" type="submit" value="1" class="btn btn-danger"><i class="fa fa-trash"></i> {#exportformatDelete#}</button>
                                        <button name="action[triggern]" type="submit" value="1" class="btn btn-default"><i class="fa fa-play-circle-o"></i> {#exportformatTriggerCron#}</button>
                                        <button name="action[uebersicht]" type="submit" value="1" class="btn btn-default"><i class="fa fa-refresh"></i> {#exportformatRefresh#}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                    <div class="panel panel-default">
                        <div class="panel-footer">
                            <div class="btn-group">
                                <button name="action[erstellen]" type="submit" value="1" class="btn btn-primary add"><i class="fa fa-share"></i> {#exportformatAdd#}</button>
                            </div>
                        </div>
                    </div>
                {/if}
            </form>
        </div>
        <div id="fertig" class="tab-pane fade{if isset($cTab) && $cTab === 'fertig'} active in{/if}">
            <form method="post" action="exportformat_queue.php">
                {$jtl_token}
                <div class="block tcenter">
                    <div class="input-group p25">
                        <span class="input-group-addon">
                            <label for="nStunden">{#exportformatLastXHourPre#}</label>
                        </span>
                        <span class="input-group-btn">
                            <input size="2" style="width: 100px;" class="form-control" id="nStunden" name="nStunden" type="text" value="{$nStunden}" />
                        </span>
                        <span class="input-group-addon">
                            {#exportformatLastXHourPost#}
                        </span>
                        <span class="input-group-btn">
                            <button name="action[fertiggestellt]" type="submit" value="1" class="btn btn-info"><i class="fa fa-search"></i> {#exportformatShow#}</button>
                        </span>
                    </div>
                </div>
            </form>
            {if $oExportformatQueueBearbeitet_arr|@count > 0}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{#exportformatTodaysWork#}</h3>
                    </div>
                    <div id="payment">
                        <div id="tabellenLivesuche">
                            <table class="table">
                                <tr>
                                    <th class="th-1">{#exportformatFormatSingle#}</th>
                                    <th class="th-2">{#exportformatFilename#}</th>
                                    <th class="th-3">{#exportformatOptions#}</th>
                                    <th class="th-4">{#exportformatExported#}</th>
                                    <th class="th-5">{#exportformatLastStart#}</th>
                                </tr>
                                {foreach name=exportformatqueue from=$oExportformatQueueBearbeitet_arr item=oExportformatQueueBearbeitet}
                                    <tr class="tab_bg{$smarty.foreach.exportformatqueue.iteration%2}">
                                        <td class="TD1">{$oExportformatQueueBearbeitet->cName}</td>
                                        <td class="TD2">{$oExportformatQueueBearbeitet->cDateiname}</td>
                                        <td class="TD3">
                                            {$oExportformatQueueBearbeitet->cNameSprache}/{$oExportformatQueueBearbeitet->cNameWaehrung}/{$oExportformatQueueBearbeitet->cNameKundengruppe}
                                        </td>
                                        <td class="TD4">{$oExportformatQueueBearbeitet->nLimitN}</td>
                                        <td class="TD5">{$oExportformatQueueBearbeitet->dZuletztGelaufen_DE}</td>
                                    </tr>
                                {/foreach}
                            </table>
                        </div>
                    </div>
                </div>
            {else}
                <div class="alert alert-info">
                    {#exportformatNoTodaysWork#}
                </div>
            {/if}
        </div>
    </div>
</div>