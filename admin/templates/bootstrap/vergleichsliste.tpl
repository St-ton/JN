{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='vergleichsliste'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('configureComparelist') cBeschreibung=__('configureComparelistDesc') cDokuURL=__('configureComparelistURL')}
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'letztenvergleiche'} active{/if}">
            <a data-toggle="tab" role="tab" href="#letztenvergleiche">{__('last20Compares')}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'topartikel'} active{/if}">
            <a data-toggle="tab" role="tab" href="#topartikel">{__('topCompareProducts')}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{__('compareSettings')}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="letztenvergleiche" class="tab-pane fade {if !isset($cTab) || $cTab === 'letztenvergleiche'} active in{/if}">
            {if $Letzten20Vergleiche && $Letzten20Vergleiche|@count > 0}
                {include file='tpl_inc/pagination.tpl' pagination=$pagination cAnchor='letztenvergleiche'}
                <div class="settings card table-responsive">
                    <table  class="table table-striped">
                        <tr>
                            <th class="th-1">{__('compareID')}</th>
                            <th class="tleft">{__('compareProducts')}</th>
                            <th class="th-3">{__('compareDate')}</th>
                        </tr>
                        {foreach $Letzten20Vergleiche as $oVergleichsliste20}
                            <tr>
                                <td class="tcenter">{$oVergleichsliste20->kVergleichsliste}</td>
                                <td class="">
                                    {foreach $oVergleichsliste20->oLetzten20VergleichslistePos_arr as $oVergleichslistePos20}
                                        <a href="{$shopURL}/index.php?a={$oVergleichslistePos20->kArtikel}" target="_blank">{$oVergleichslistePos20->cArtikelName}</a>{if !$oVergleichslistePos20@last}{/if}
                                        <br />
                                    {/foreach}
                                </td>
                                <td class="tcenter">{$oVergleichsliste20->Datum}</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
            {/if}
        </div>
        <div id="topartikel" class="tab-pane fade {if isset($cTab) && $cTab === 'topartikel'} active in{/if}">
            <form id="postzeitfilter" name="postzeitfilter" method="post" action="vergleichsliste.php">
                {$jtl_token}
                <input type="hidden" name="zeitfilter" value="1" />
                <input type="hidden" name="tab" value="topartikel" />
                <div class="block">
                    <div class="input-group p25 left" style="margin-right: 20px;">
                        <span class="input-group-addon">
                            <label for="nZeitFilter">{__('compareTimeFilter')}:</label>
                        </span>
                        <span class="input-group-wrap">
                            <select class="form-control" id="nZeitFilter" name="nZeitFilter" onchange="document.postzeitfilter.submit();">
                                <option value="1"{if isset($smarty.session.Vergleichsliste->nZeitFilter) && $smarty.session.Vergleichsliste->nZeitFilter == 1} selected{/if}>
                                    {__('last')} 24 {__('hours')}
                                </option>
                                <option value="7"{if isset($smarty.session.Vergleichsliste->nZeitFilter) && $smarty.session.Vergleichsliste->nZeitFilter == 7} selected{/if}>
                                    {__('last')} 7 {__('days')}
                                </option>
                                <option value="30"{if isset($smarty.session.Vergleichsliste->nZeitFilter) && $smarty.session.Vergleichsliste->nZeitFilter == 30} selected{/if}>
                                    {__('last')} 30 {__('days')}
                                </option>
                                <option value="365"{if isset($smarty.session.Vergleichsliste->nZeitFilter) && $smarty.session.Vergleichsliste->nZeitFilter == 365} selected{/if}>
                                    {__('lastYear')}
                                </option>
                            </select>
                        </span>
                    </div>

                    <div class="input-group p25 left">
                        <span class="input-group-addon">
                            <label for="nAnzahl">{__('compareTopCount')}:</label>
                        </span>
                        <span class="input-group-wrap">
                            <select class="form-control" id="nAnzahl" name="nAnzahl" onchange="document.postzeitfilter.submit();">
                                <option value="10"{if isset($smarty.session.Vergleichsliste->nAnzahl) && $smarty.session.Vergleichsliste->nAnzahl == 10} selected{/if}>
                                    10
                                </option>
                                <option value="20"{if isset($smarty.session.Vergleichsliste->nAnzahl) && $smarty.session.Vergleichsliste->nAnzahl == 20} selected{/if}>
                                    20
                                </option>
                                <option value="50"{if isset($smarty.session.Vergleichsliste->nAnzahl) && $smarty.session.Vergleichsliste->nAnzahl == 50} selected{/if}>
                                    50
                                </option>
                                <option value="100"{if isset($smarty.session.Vergleichsliste->nAnzahl) && $smarty.session.Vergleichsliste->nAnzahl == 100} selected{/if}>
                                    100
                                </option>
                                <option value="-1"{if isset($smarty.session.Vergleichsliste->nAnzahl) && $smarty.session.Vergleichsliste->nAnzahl == -1} selected{/if}>
                                    {__('all')}
                                </option>
                            </select>
                        </span>
                    </div>
                </div>
            </form>

            {if isset($TopVergleiche) && $TopVergleiche|@count > 0}
                <div class="settings card table-responsive">
                    <table class="bottom table table-striped">
                        <tr>
                            <th class="tleft">{__('compareProduct')}</th>
                            <th class="th-2">{__('compareCount')}</th>
                        </tr>
                        {foreach $TopVergleiche as $oVergleichslistePosTop}
                            <tr>
                                <td>
                                    <a href="{$shopURL}/index.php?a={$oVergleichslistePosTop->kArtikel}" target="_blank">{$oVergleichslistePosTop->cArtikelName}</a>
                                </td>
                                <td class="tcenter">{$oVergleichslistePosTop->nAnzahl}</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
            {/if}
        </div>
        <div id="einstellungen" class="tab-pane fade {if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='vergleichsliste.php' buttonCaption=__('save') title=__('settings') tab='einstellungen'}
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
