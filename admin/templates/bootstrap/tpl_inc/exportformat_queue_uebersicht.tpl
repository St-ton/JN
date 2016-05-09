{include file='tpl_inc/seite_header.tpl' cTitel=#exportformat# cBeschreibung=#exportformatDesc# cDokuURL=#exportformatUrl#}
<div id="content" class="container-fluid">
    <form method="post" action="exportformat_queue.php" style="margin-bottom: 15px;">
        {$jtl_token}
        <input name="navigation" type="hidden" value="1" />
        <div class="btn-group">
            <button name="submitErstellen" type="submit" value="{#exportformatAdd#}" class="btn btn-primary add">{#exportformatAdd#}</button>
            <button name="submitFertiggestellt" type="submit" value="{#exportformatTodaysWork#}" class="btn btn-default">{#exportformatTodaysWork#}</button>
            <button name="submitCronTriggern" type="submit" value="{#exportformatTriggerCron#}" class="btn btn-default">{#exportformatTriggerCron#}</button>
        </div>
    </form>

    {if $oExportformatCron_arr|@count > 0 && $oExportformatCron_arr}
        <form method="post" action="exportformat_queue.php">
            {$jtl_token}
            <input name="loeschen" type="hidden" value="1" />
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
                                        <a href="exportformat_queue.php?editieren=1&kCron={$oExportformatCron->kCron}&token={$smarty.session.jtl_token}" class="btn btn-default"><i class="fa fa-edit"></i></a>
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
                            <button name="submitloeschen" type="submit" value="{#exportformatDelete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#exportformatDelete#}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    {/if}
</div>