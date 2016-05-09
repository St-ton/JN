{include file='tpl_inc/seite_header.tpl' cTitel=#exportformatTodaysWork#}
<div id="content" class="container-fluid2">
    <form method="post" action="exportformat_queue.php">
        {$jtl_token}
        <input name="fertiggestellt" type="hidden" value="1" />

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
                    <input name="submitXHour" type="submit" value="{#exportformatShow#}" class="btn btn-info" />
                </span>
            </div>
        </div>

        {if $oExportformatQueueBearbeitet_arr|@count > 0}
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
        {else}
            <div class="alert alert-info">
                {#exportformatNoTodaysWork#}
            </div>
        {/if}
    </form>
</div>
