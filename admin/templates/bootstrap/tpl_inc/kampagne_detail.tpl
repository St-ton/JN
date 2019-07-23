<script type="text/javascript">
    function changeSelect(currentSelect) {ldelim}
        switch (currentSelect.options[currentSelect.selectedIndex].value) {ldelim}
            case '1':
                document.getElementById('SelectFromDay').style.display = 'none';
                document.getElementById('SelectToDay').style.display = 'none';
                break;
            case '2':
                document.getElementById('SelectFromDay').style.display = 'none';
                document.getElementById('SelectToDay').style.display = 'none';
                break;
            case '3':
                document.getElementById('SelectFromDay').style.display = 'inline';
                document.getElementById('SelectToDay').style.display = 'inline';
                break;
            case '4':
                document.getElementById('SelectFromDay').style.display = 'inline';
                document.getElementById('SelectToDay').style.display = 'inline';
                break;
            {rdelim}
    {rdelim}

    function selectSubmit(currentSelect) {ldelim}
        var $kKampagne = currentSelect.options[currentSelect.selectedIndex].value;
        if ($kKampagne > 0) {ldelim}
            window.location.href = 'kampagne.php?detail=1&token={$smarty.session.jtl_token}&kKampagne=' + $kKampagne;
        {rdelim}
    {rdelim}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=__('kampagneDetailStats')|cat:' '|cat:$oKampagne->cName}

<div id="content" class="card container-fluid">

    <div id="payment" class="card-body">
        
        <form method="post" action="kampagne.php">
            {$jtl_token}
            <input type="hidden" name="detail" value="1" />
            <input type="hidden" name="zeitraum" value="1" />
            <input type="hidden" name="kKampagne" value="{$oKampagne->kKampagne}" />

            <div class="row">
                <div class="col-sm-6">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="nAnsicht">{__('kampagneDetailView')}</label>
                        </span>
                        <span class="input-group-wrap">
                            <select id="nAnsicht" name="nAnsicht" class="form-control combo" onChange="changeSelect(this);">
                                <option value="1"{if $smarty.session.Kampagne->nDetailAnsicht == 1} selected{/if}>{__('annual')}</option>
                                <option value="2"{if $smarty.session.Kampagne->nDetailAnsicht == 2} selected{/if}>{__('monthly')}</option>
                                <option value="3"{if $smarty.session.Kampagne->nDetailAnsicht == 3} selected{/if}>{__('weekly')}</option>
                                <option value="4"{if $smarty.session.Kampagne->nDetailAnsicht == 4} selected{/if}>{__('daily')}</option>
                            </select>
                        </span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="kKampagne">{__('kampagneSingle')}</label>
                        </span>
                        <span class="input-group-wrap">
                            <select id="kKampagne" name="kKampagne" class="form-control combo" onChange="selectSubmit(this);">
                                {if isset($oKampagne_arr) && $oKampagne_arr|@count > 0}
                                    {foreach $oKampagne_arr as $oKampagneTMP}
                                        <option value="{$oKampagneTMP->kKampagne}"{if $oKampagneTMP->kKampagne == $oKampagne->kKampagne} selected{/if}>{$oKampagneTMP->cName}</option>
                                    {/foreach}
                                {/if}
                            </select>
                        </span>
                    </div>
                </div>
                <div class="col-sm-5">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="SelectFromDay">{__('from')}</label>
                        </span>

                        <select name="cFromDay" class="form-control combo" id="SelectFromDay">
                            {section name=fromDay loop=32 start=1 step=1}
                                <option value="{$smarty.section.fromDay.index}"
                                        {if $smarty.session.Kampagne->cFromDate_arr.nTag == $smarty.section.fromDay.index} selected{/if}>
                                    {$smarty.section.fromDay.index}
                                </option>
                            {/section}
                        </select>

                        <span class="input-group-wrap">
                            <select name="cFromMonth" class="form-control combo">
                                <option value="1"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 1} selected{/if}>{__('january')}</option>
                                <option value="2"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 2} selected{/if}>{__('february')}</option>
                                <option value="3"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 3} selected{/if}>{__('march')}</option>
                                <option value="4"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 4} selected{/if}>{__('april')}</option>
                                <option value="5"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 5} selected{/if}>{__('may')}</option>
                                <option value="6"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 6} selected{/if}>{__('june')}</option>
                                <option value="7"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 7} selected{/if}>{__('july')}</option>
                                <option value="8"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 8} selected{/if}>{__('august')}</option>
                                <option value="9"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 9} selected{/if}>{__('september')}</option>
                                <option value="10"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 10} selected{/if}>{__('october')}</option>
                                <option value="11"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 11} selected{/if}>{__('november')}</option>
                                <option value="12"{if $smarty.session.Kampagne->cFromDate_arr.nMonat == 12} selected{/if}>{__('december')}</option>
                            </select>
                        </span>
                        <span class="input-group-wrap">
                            {assign var=cJahr value=$smarty.now|date_format:'%Y'}
                            <select name="cFromYear" class="form-control combo">
                                {section name=fromYear loop=$cJahr+1 start=2005 step=1}
                                    <option value="{$smarty.section.fromYear.index}"
                                            {if $smarty.session.Kampagne->cFromDate_arr.nJahr == $smarty.section.fromYear.index} selected{/if}>
                                        {$smarty.section.fromYear.index}
                                    </option>
                                {/section}
                            </select>
                        </span>
                    </div>
                </div>
                <div class="col-sm-5 col-xs-10">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="SelectToDay">{__('kampagneDateTill')}</label>
                        </span>
                        <select name="cToDay" class="form-control combo" id="SelectToDay">
                            {section name=toDay loop=32 start=1 step=1}
                                <option value="{$smarty.section.toDay.index}"
                                        {if $smarty.session.Kampagne->cToDate_arr.nTag == $smarty.section.toDay.index} selected{/if}>
                                    {$smarty.section.toDay.index}
                                </option>
                            {/section}
                        </select>
                        <span class="input-group-wrap">
                            <select name="cToMonth" class="form-control combo">
                                <option value="1"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 1} selected{/if}>{__('january')}</option>
                                <option value="2"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 2} selected{/if}>{__('february')}</option>
                                <option value="3"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 3} selected{/if}>{__('march')}</option>
                                <option value="4"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 4} selected{/if}>{__('april')}</option>
                                <option value="5"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 5} selected{/if}>{__('may')}</option>
                                <option value="6"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 6} selected{/if}>{__('june')}</option>
                                <option value="7"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 7} selected{/if}>{__('july')}</option>
                                <option value="8"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 8} selected{/if}>{__('august')}</option>
                                <option value="9"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 9} selected{/if}>{__('september')}</option>
                                <option value="10"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 10} selected{/if}>{__('october')}</option>
                                <option value="11"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 11} selected{/if}>{__('november')}</option>
                                <option value="12"{if $smarty.session.Kampagne->cToDate_arr.nMonat == 12} selected{/if}>{__('december')}</option>
                            </select>
                        </span>
                        {assign var=cJahr value=$smarty.now|date_format:'%Y'}
                        <span class="input-group-wrap">
                            <select name="cToYear" class="form-control combo">
                                {section name=toYear loop=$cJahr+1 start=2005 step=1}
                                    <option value="{$smarty.section.toYear.index}"
                                            {if $smarty.session.Kampagne->cToDate_arr.nJahr == $smarty.section.toYear.index} selected{/if}>
                                        {$smarty.section.toYear.index}
                                    </option>
                                {/section}
                            </select>
                        </span>
                    </div>
                </div>
                <div class="col-sm-2 col-xs-2">
                    <button name="submitZeitraum" type="submit" value="{__('kampagneDetailStatsBTN')}" class="btn btn-info"
                            title="{__('kampagneDetailStatsBTN')}">
                        <i class="fa fa-refresh"></i>
                    </button>
                </div>
            </div>

        </form>
        <ul class="nav nav-tabs" role="tablist">
            <li class="tab{if !isset($cTab) || $cTab === 'detailansicht'} active{/if}">
                <a data-toggle="tab" role="tab" href="#detailansicht">{__('kampagneDetailStats')}</a>
            </li>
            <li class="tab{if isset($cTab) && $cTab === 'detailgraphen'} active{/if}">
                <a data-toggle="tab" role="tab" href="#detailgraphen">{__('kampagneDetailGraph')}</a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="detailansicht" class="tab-pane fade {if !isset($cTab) || $cTab === 'detailansicht'} active show{/if}">
                {if isset($oKampagneStat_arr) && $oKampagneStat_arr|@count > 0 && isset($oKampagneDef_arr) && $oKampagneDef_arr|@count > 0}
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tr>
                                <th class="th-1"></th>
                                {foreach $oKampagneDef_arr as $oKampagneDef}
                                    <th class="th-2">{__($oKampagneDef->cName)}</th>
                                {/foreach}
                            </tr>

                            {foreach name='kampagnenstats' from=$oKampagneStat_arr key=kKey item=oKampagneStatDef_arr}
                                {if $kKey !== 'Gesamt'}
                                    <tr>
                                        {if isset($oKampagneStat_arr[$kKey].cDatum)}
                                            <td>{$oKampagneStat_arr[$kKey].cDatum}</td>
                                        {/if}
                                        {foreach name='kampagnendefs' from=$oKampagneStatDef_arr key=kKampagneDef item=oKampagneStatDef_arrItem}
                                            {if $kKampagneDef !== 'cDatum'}
                                                <td style="text-align: center;">
                                                    <a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&defdetail=1&kKampagneDef={$kKampagneDef}&cStamp={$kKey}&token={$smarty.session.jtl_token}">
                                                        {$oKampagneStat_arr[$kKey][$kKampagneDef]}
                                                    </a>
                                                </td>
                                            {/if}
                                        {/foreach}
                                    </tr>
                                {/if}
                            {/foreach}
                            <tr>
                                <td>{__('kampagneOverall')}</td>
                                {foreach name='kampagnendefs' from=$oKampagneStatDef_arr key=kKampagneDef item=oKampagneStatDef_arrItem}
                                    <td style="text-align: center;">
                                        {$oKampagneStat_arr.Gesamt[$kKampagneDef]}
                                    </td>
                                {/foreach}
                            </tr>
                        </table>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="detailgraphen" class="tab-pane fade{if isset($cTab) && $cTab === 'detailgraphen'} active show{/if}">
                {if $Charts|@count > 0}
                    {foreach name=charts from=$Charts key=key item=Chart}
                        <h3>{$TypeNames[$key]}:</h3>
                        {if isset($headline)}
                            {assign var=hl value=$headline}
                        {else}
                            {assign var=hl value=null}
                        {/if}
                        {if isset($headline)}
                            {assign var=ylabel value=$ylabel}
                        {else}
                            {assign var=ylabel value=null}
                        {/if}
                        {include file='tpl_inc/linechart_inc.tpl' linechart=$Chart headline=$hl id=$key width='100%' height='400px' ylabel=$ylabel href=false legend=false ymin='0'}
                    {/foreach}
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
        </div>
        
    </div>
    <div class="card-footer">
        <a href="kampagne.php?tab=globalestats&token={$smarty.session.jtl_token}" class="btn btn-default">{__('goBack')}</a>
    </div>
</div>

{if $smarty.session.Kampagne->nDetailAnsicht == 1 || $smarty.session.Kampagne->nDetailAnsicht == 2}
    <script type="text/javascript">
        document.getElementById('SelectFromDay').style.display = 'none';
        document.getElementById('SelectToDay').style.display = 'none';
    </script>
{/if}
 <script type="text/javascript">
    $(document).on('shown.bs.tab', 'a[href="#detailgraphen"]', function(e) {
        $(window).trigger('resize');
    });
</script>