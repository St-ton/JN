{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='wunschliste'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('wishlistName') cBeschreibung=__('wishlistDesc') cDokuURL=__('wishlistURL')}
<div id="content">
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if !isset($cTab) || $cTab === 'wunschlistepos'} active{/if}" data-toggle="tab" role="tab" href="#wunschlistepos">
                        {__('wishlistTop100')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'wunschlisteartikel'} active{/if}" data-toggle="tab" role="tab" href="#wunschlisteartikel">
                        {__('wishlistPosTop100')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'wunschlistefreunde'} active{/if}" data-toggle="tab" role="tab" href="#wunschlistefreunde">
                        {__('wishlistSend')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'einstellungen'} active{/if}" data-toggle="tab" role="tab" href="#einstellungen">
                        {__('settings')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="wunschlistepos" class="tab-pane fade {if !isset($cTab) || $cTab === 'wunschlistepos'} active show{/if}">
                {if isset($CWunschliste_arr) && $CWunschliste_arr|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiPos cAnchor='wunschlistepos'}
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="tleft">{__('wishlistName')}</th>
                                    <th class="tleft">{__('customer')}</th>
                                    <th class="th-3">{__('wishlistPosCount')}</th>
                                    <th class="th-4">{__('date')}</th>
                                </tr>
                            </thead>
                            <tbody>
                            {foreach $CWunschliste_arr as $CWunschliste}
                                <tr>
                                    <td>
                                        {if $CWunschliste->nOeffentlich == 1}
                                            <a href="{$shopURL}/index.php?wlid={$CWunschliste->cURLID}" rel="external">{$CWunschliste->cName}</a>
                                        {else}
                                            <span>{$CWunschliste->cName}</span>
                                        {/if}
                                    </td>
                                    <td>{$CWunschliste->cVorname} {$CWunschliste->cNachname}</td>
                                    <td class="text-center">{$CWunschliste->Anzahl}</td>
                                    <td class="text-center">{$CWunschliste->Datum}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="wunschlisteartikel" class="tab-pane fade {if isset($cTab) && $cTab === 'wunschlisteartikel'} active show{/if}">
                {if isset($CWunschlistePos_arr) && $CWunschlistePos_arr|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiArtikel cAnchor='wunschlisteartikel'}
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="tleft">{__('wishlistPosName')}</th>
                                    <th class="th-2">{__('wishlistPosCount')}</th>
                                    <th class="th-3">{__('wishlistLastAdded')}</th>
                                </tr>
                            </thead>
                            <tbody>
                            {foreach $CWunschlistePos_arr as $CWunschlistePos}
                                <tr>
                                    <td>
                                        <a href="{$shopURL}/index.php?a={$CWunschlistePos->kArtikel}&" rel="external">{$CWunschlistePos->cArtikelName}</a>
                                    </td>
                                    <td class="text-center">{$CWunschlistePos->Anzahl}</td>
                                    <td class="text-center">{$CWunschlistePos->Datum}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="wunschlistefreunde" class="tab-pane fade {if isset($cTab) && $cTab === 'wunschlistefreunde'} active show{/if}">
                {if $CWunschlisteVersand_arr && $CWunschlisteVersand_arr|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiFreunde cAnchor='wunschlistefreunde'}
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="tleft">{__('wishlistName')}</th>
                                    <th class="tleft">{__('customer')}</th>
                                    <th class="th-3">{__('wishlistRecipientCount')}</th>
                                    <th class="th-4">{__('wishlistPosCount')}</th>
                                    <th class="th-5">{__('date')}</th>
                                </tr>
                            </thead>
                            <tbody>
                            {foreach $CWunschlisteVersand_arr as $CWunschlisteVersand}
                                <tr>
                                    <td>
                                        <a href="{$shopURL}/index.php?wlid={$CWunschlisteVersand->cURLID}" rel="external">{$CWunschlisteVersand->cName}</a>
                                    </td>
                                    <td>{$CWunschlisteVersand->cVorname} {$CWunschlisteVersand->cNachname}</td>
                                    <td class="text-center">{$CWunschlisteVersand->nAnzahlEmpfaenger}</td>
                                    <td class="text-center">{$CWunschlisteVersand->nAnzahlArtikel}</td>
                                    <td class="text-center">{$CWunschlisteVersand->Datum}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="einstellungen" class="tab-pane fade {if isset($cTab) && $cTab === 'einstellungen'} active show{/if}">
                {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='wunschliste.php' buttonCaption=__('save') title=__('settings') tab='einstellungen'}
            </div>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
