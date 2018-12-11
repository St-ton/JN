{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="wunschliste"}
{include file='tpl_inc/seite_header.tpl' cTitel=__('wishlistName') cBeschreibung=__('wishlistDesc') cDokuURL=__('wishlistURL')}
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'wunschlistepos'} active{/if}">
            <a data-toggle="tab" role="tab" href="#wunschlistepos">{__('wishlistTop100')}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'wunschlisteartikel'} active{/if}">
            <a data-toggle="tab" role="tab" href="#wunschlisteartikel">{__('wishlistPosTop100')}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'wunschlistefreunde'} active{/if}">
            <a data-toggle="tab" role="tab" href="#wunschlistefreunde">{__('wishlistSend')}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{__('wishlistSettings')}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="wunschlistepos" class="tab-pane fade {if !isset($cTab) || $cTab === 'wunschlistepos'} active in{/if}">
            {if isset($CWunschliste_arr) && $CWunschliste_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiPos cAnchor='wunschlistepos'}
                <div class="panel panel-default table-responsive">
                    <table class="table table-striped">
                        <tr>
                            <th class="tleft">{__('wishlistName')}</th>
                            <th class="tleft">{__('wishlistAccount')}</th>
                            <th class="th-3">{__('wishlistPosCount')}</th>
                            <th class="th-4">{__('wishlistDate')}</th>
                        </tr>
                        {foreach name=wunschliste from=$CWunschliste_arr item=CWunschliste}
                            <tr>
                                <td>
                                    {if $CWunschliste->nOeffentlich == 1}
                                        <a href="{$shopURL}/index.php?wlid={$CWunschliste->cURLID}" rel="external">{$CWunschliste->cName}</a>
                                    {else}
                                        <span>{$CWunschliste->cName}</span>
                                    {/if}
                                </td>
                                <td>{$CWunschliste->cVorname} {$CWunschliste->cNachname}</td>
                                <td class="tcenter">{$CWunschliste->Anzahl}</td>
                                <td class="tcenter">{$CWunschliste->Datum}</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
            {/if}
        </div>
        <div id="wunschlisteartikel" class="tab-pane fade {if isset($cTab) && $cTab === 'wunschlisteartikel'} active in{/if}">
            {if isset($CWunschlistePos_arr) && $CWunschlistePos_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiArtikel cAnchor='wunschlisteartikel'}
                <div class="panel panel-default table-responsive">
                    <table class="table table-striped">
                        <tr>
                            <th class="tleft">{__('wishlistPosName')}</th>
                            <th class="th-2">{__('wishlistPosCount')}</th>
                            <th class="th-3">{__('wishlistLastAdded')}</th>
                        </tr>
                        {foreach name=wunschlistepos from=$CWunschlistePos_arr item=CWunschlistePos}
                            <tr>
                                <td>
                                    <a href="{$shopURL}/index.php?a={$CWunschlistePos->kArtikel}&" rel="external">{$CWunschlistePos->cArtikelName}</a>
                                </td>
                                <td class="tcenter">{$CWunschlistePos->Anzahl}</td>
                                <td class="tcenter">{$CWunschlistePos->Datum}</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
            {/if}
        </div>
        <div id="wunschlistefreunde" class="tab-pane fade {if isset($cTab) && $cTab === 'wunschlistefreunde'} active in{/if}">
            {if $CWunschlisteVersand_arr && $CWunschlisteVersand_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiFreunde cAnchor='wunschlistefreunde'}
                <div class="panel panel-default table-responsive">
                    <table class="table table-striped">
                        <tr>
                            <th class="tleft">{__('wishlistName')}</th>
                            <th class="tleft">{__('wishlistAccount')}</th>
                            <th class="th-3">{__('wishlistReceiverCount')}</th>
                            <th class="th-4">{__('wishlistPosCount')}</th>
                            <th class="th-5">{__('wishlistDate')}</th>
                        </tr>
                        {foreach name=wunschlisteversand from=$CWunschlisteVersand_arr item=CWunschlisteVersand}
                            <tr>
                                <td>
                                    <a href="{$shopURL}/index.php?wlid={$CWunschlisteVersand->cURLID}" rel="external">{$CWunschlisteVersand->cName}</a>
                                </td>
                                <td>{$CWunschlisteVersand->cVorname} {$CWunschlisteVersand->cNachname}</td>
                                <td class="tcenter">{$CWunschlisteVersand->nAnzahlEmpfaenger}</td>
                                <td class="tcenter">{$CWunschlisteVersand->nAnzahlArtikel}</td>
                                <td class="tcenter">{$CWunschlisteVersand->Datum}</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
            {/if}
        </div>
        <div id="einstellungen" class="tab-pane fade {if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='wunschliste.php' buttonCaption=__('save') title='Einstellungen' tab='einstellungen'}
        </div>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}