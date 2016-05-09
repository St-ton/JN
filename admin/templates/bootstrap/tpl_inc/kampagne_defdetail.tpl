{include file='tpl_inc/seite_header.tpl' cTitel=#kampagneDetailStats#}
<div id="content" class="container-fluid">
    <div id="tabellenLivesuche">
        <table class="table">
            <tr>
                <th class="tleft"><strong>{$oKampagneDef->cName}</strong></th>
            </tr>
            <tr>
                <td class="TD1">
                    {#kampagnePeriod#}: {$cStampText}<br />
                    {#kampagneOverall#}: {$nGesamtAnzahlDefDetail}
                </td>
            </tr>
        </table>
    </div>

    <div id="payment">
        {if isset($oKampagneStat_arr) && $oKampagneStat_arr|@count > 0 && isset($oKampagneDef->kKampagneDef) && $oKampagneDef->kKampagneDef > 0}
            {include file='pagination.tpl' cSite=1 cUrl='kampagne.php' oBlaetterNavi=$oBlaetterNaviDefDetail hash=''}
            <div id="tabellenLivesuche">
                <table class="table">
                    <tr>
                        {foreach name="kampagnendefs" from=$cMember_arr key=cMember item=cMemberAnzeige}
                            <th class="th-2">{$cMemberAnzeige|truncate:50:"..."}</th>
                        {/foreach}
                    </tr>

                    {foreach name="kampagnenstats" from=$oKampagneStat_arr item=oKampagneStat}
                        <tr class="tab_bg{$smarty.foreach.kampagnenstats.iteration%2}">
                            {foreach name="kampagnendefs" from=$cMember_arr key=cMember item=cMemberAnzeige}
                                <td class="TD1" style="text-align: center;">{$oKampagneStat->$cMember|wordwrap:40:"<br />":true}</td>
                            {/foreach}
                        </tr>
                    {/foreach}
                </table>
            </div>
            {include file='pagination.tpl' cSite=1 cUrl='kampagne.php' oBlaetterNavi=$oBlaetterNaviDefDetail hash=''}
        {else}
            <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            <div class="container-fluid">
                <a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&detail=1&token={$smarty.session.jtl_token}"><i class="fa fa-angle-double-left"></i> {#kampagneBackBTN#}</a>
            </div>
        {/if}
    </div>
</div>