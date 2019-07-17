<script type="text/javascript">
    function changeZeitSelect(currentSelect) {ldelim}
        if (currentSelect.options[currentSelect.selectedIndex].value > 0)
            window.location.href = "kampagne.php?tab=globalestats&nAnsicht=" + currentSelect.options[currentSelect.selectedIndex].value;
    {rdelim}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=__('kampagne') cBeschreibung=__('kampagneDesc') cDokuURL=__('kampagneURL')}
<div id="content" class="container-fluid">
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if !isset($cTab) || $cTab === 'uebersicht'} active{/if}" data-toggle="tab" role="tab" href="#uebersicht">
                        {__('kampagneOverview')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'globalestats'} active{/if}" data-toggle="tab" role="tab" href="#globalestats">
                        {__('kampagneGlobalStats')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="uebersicht" class="tab-pane fade {if !isset($cTab) || $cTab === 'uebersicht'} active show{/if}">
                <div class="card">
                    <div class="card-header">
                        <div class="subheading1">{__('kampagneIntern')}</div>
                        <hr class="mb-n3">
                    </div>
                    {if $oKampagne_arr|count > 0}
                        <div class="table-responsive card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="tleft">{__('kampagneName')}</th>
                                        <th class="tleft">{__('kampagneParam')}</th>
                                        <th class="tleft">{__('kampagneValue')}</th>
                                        <th class="th-4">{__('activated')}</th>
                                        <th class="th-5">{__('kampagnenDate')}</th>
                                        <th class="th-6"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                {foreach $oKampagne_arr as $oKampagne}
                                    {if isset($oKampagne->kKampagne) && $oKampagne->kKampagne < 1000}
                                        <tr>
                                            <td>
                                                <strong><a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&detail=1&token={$smarty.session.jtl_token}">{$oKampagne->cName}</a></strong>
                                            </td>
                                            <td>{$oKampagne->cParameter}</td>
                                            <td>
                                                {if isset($oKampagne->nDynamisch) && $oKampagne->nDynamisch == 1}
                                                    {__('dynamic')}
                                                {else}
                                                    {__('kampagneStatic')}
                                                    <br />
                                                    <strong>{__('kampagneValueStatic')}:</strong>
                                                    {$oKampagne->cWert}
                                                {/if}
                                            </td>
                                            <td class="tcenter">{if isset($oKampagne->nAktiv) && $oKampagne->nAktiv == 1}{__('yes')}{else}{__('no')}{/if}</td>
                                            <td class="tcenter">{$oKampagne->dErstellt_DE}</td>
                                            <td class="tcenter">
                                                <a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&editieren=1&token={$smarty.session.jtl_token}" title="{__('modify')}" class="btn btn-default"><i class="fal fa-edit"></i></a>
                                            </td>
                                        </tr>
                                    {/if}
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                    {else}
                        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                    {/if}
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="subheading1">{__('kampagneExtern')}</div>
                        <hr class="mb-n3">
                    </div>
                    <form name="kampagnen" method="post" action="kampagne.php">
                        {if isset($nGroessterKey) && $nGroessterKey >= 1000}
                            {$jtl_token}
                            <input type="hidden" name="tab" value="uebersicht" />
                            <input type="hidden" name="delete" value="1" />
                            <div class="table-responsive card-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="check"></th>
                                            <th class="tleft">{__('kampagneName')}</th>
                                            <th class="tleft">{__('kampagneParam')}</th>
                                            <th class="tleft">{__('kampagneValue')}</th>
                                            <th class="th-4">{__('activated')}</th>
                                            <th class="th-5">{__('kampagnenDate')}</th>
                                            <th class="th-6"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $oKampagne_arr as $oKampagne}
                                        {if $oKampagne->kKampagne >= 1000}
                                            <tr>
                                                <td class="check">
                                                    <input name="kKampagne[]" type="checkbox" value="{$oKampagne->kKampagne}">
                                                </td>
                                                <td>
                                                    <strong><a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&detail=1&token={$smarty.session.jtl_token}">{$oKampagne->cName}</a></strong>
                                                </td>
                                                <td>{$oKampagne->cParameter}</td>
                                                <td>
                                                    {if isset($oKampagne->nDynamisch) && $oKampagne->nDynamisch == 1}
                                                        {__('dynamic')}
                                                    {else}
                                                        {__('kampagneStatic')}
                                                        <br />
                                                        <strong>{__('kampagneValueStatic')}:</strong>
                                                        {$oKampagne->cWert}
                                                    {/if}
                                                </td>
                                                <td class="tcenter">{if isset($oKampagne->nAktiv) && $oKampagne->nAktiv == 1}{__('yes')}{else}{__('no')}{/if}</td>
                                                <td class="tcenter">{$oKampagne->dErstellt_DE}</td>
                                                <td class="tcenter">
                                                    <a href="kampagne.php?kKampagne={$oKampagne->kKampagne}&editieren=1&token={$smarty.session.jtl_token}"
                                                       class="btn btn-default" title="{__('modify')}">
                                                        <i class="fal fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        {/if}
                                    {/foreach}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td class="check">
                                                <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" />
                                            </td>
                                            <td colspan="6"><label for="ALLMSGS">{__('globalSelectAll')}</label></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        {else}
                            <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                        {/if}
                        <div class="card-footer">
                            <div class="btn-group">
                                {if isset($nGroessterKey) && $nGroessterKey >= 1000}
                                    <button name="submitDelete" type="submit" value="{__('delete')}" class="btn btn-danger"><i class="fas fa-trash-alt"></i> {__('deleteSelected')}</button>
                                {/if}
                                <a href="kampagne.php?neu=1&token={$smarty.session.jtl_token}" class="btn btn-primary">{__('kampagneNewBTN')}</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div id="globalestats" class="tab-pane fade {if isset($cTab) && $cTab === 'globalestats'} active show{/if}">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group form-row align-items-center">
                            <label class="col col-sm-3 col-lg-1 col-form-label text-sm-right" for="nAnsicht">{__('kampagneView')}:</label>
                            <div class="col-sm-4 col-lg-2">
                                <select id="nAnsicht" name="nAnsicht" class="custom-select combo" onchange="changeZeitSelect(this);">
                                    <option value="-1"></option>
                                    <option value="1"{if $smarty.session.Kampagne->nAnsicht == 1} selected{/if}>{__('monthly')}</option>
                                    <option value="2"{if $smarty.session.Kampagne->nAnsicht == 2} selected{/if}>{__('weekly')}</option>
                                    <option value="3"{if $smarty.session.Kampagne->nAnsicht == 3} selected{/if}>{__('daily')}</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <strong>{__('kampagnePeriod')}:</strong> {$cZeitraum}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    {if isset($oKampagne_arr) && $oKampagne_arr|@count > 0 && isset($oKampagneDef_arr) && $oKampagneDef_arr|@count > 0}
                        <div class="table-responsive card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="th-1"></th>
                                        {foreach $oKampagneDef_arr as $oKampagneDef}
                                            <th class="th-2">
                                                <a href="kampagne.php?tab=globalestats&nSort={$oKampagneDef->kKampagneDef}&token={$smarty.session.jtl_token}">{__($oKampagneDef->cName)}</a>
                                                {if $oKampagneDef->cName === 'Angeschaute Newsletter'}
                                                    {getHelpDesc cDesc=__('kampagnenNLInfo')}
                                                {/if}
                                            </th>
                                        {/foreach}
                                    </tr>
                                </thead>
                                <tbody>
                                {foreach name='kampagnenstats' from=$oKampagneStat_arr key=kKampagne item=oKampagneStatDef_arr}
                                    {if $kKampagne !== 'Gesamt'}
                                        <tr>
                                            <td>
                                                <a href="kampagne.php?detail=1&kKampagne={$oKampagne_arr[$kKampagne]->kKampagne}&cZeitParam={$cZeitraumParam}&token={$smarty.session.jtl_token}">{$oKampagne_arr[$kKampagne]->cName}</a>
                                            </td>
                                            {foreach name='kampagnendefs' from=$oKampagneStatDef_arr key=kKampagneDef item=oKampagneStatDef}
                                                <td>
                                                    <a href="kampagne.php?kKampagne={$kKampagne}&defdetail=1&kKampagneDef={$kKampagneDef}&cZeitParam={$cZeitraumParam}&token={$smarty.session.jtl_token}">{$oKampagneStat_arr[$kKampagne][$kKampagneDef]}</a>
                                                </td>
                                            {/foreach}
                                        </tr>
                                    {/if}
                                {/foreach}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>{__('kampagneOverall')}</td>
                                        {foreach name='kampagnendefs' from=$oKampagneDef_arr key=kKampagneDef item=oKampagneDef}
                                            <td>
                                                {$oKampagneStat_arr.Gesamt[$kKampagneDef]}
                                            </td>
                                        {/foreach}
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="btn-group">
                                <a href="kampagne.php?tab=globalestats&nStamp=-1&token={$smarty.session.jtl_token}" class="btn btn-default"><i class="fa fa-angle-double-left"></i> {__('earlier')}</a>
                                {if isset($bGreaterNow) && !$bGreaterNow}
                                    <a href="kampagne.php?tab=globalestats&nStamp=1&token={$smarty.session.jtl_token}" class="btn btn-default"><i class="fa fa-angle-double-right"></i> {__('later')}</a>
                                {/if}
                            </div>
                        </div>
                    {else}
                        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>
