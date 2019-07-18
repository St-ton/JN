{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='warenkorbpers'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('warenkorbpers') cBeschreibung=__('warenkorbpersDesc') cDokuURL=__('warenkorbpersURL')}
<div id="content">
    {if $step === 'uebersicht'}
        <div class="tabs">
            <nav class="tabs-nav">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="na-item">
                        <a class="nav-link {if !isset($tab) || $tab === 'warenkorbpers'} active{/if}" data-toggle="tab" role="tab" href="#massaction">
                            {__('warenkorbpers')}
                        </a>
                    </li>
                    <li class="na-item">
                        <a class="nav-link {if isset($tab) && $tab === 'einstellungen'} active{/if}" data-toggle="tab" role="tab" href="#settings">
                            {__('warenkorbpersSettings')}
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="tab-content">
                <div id="massaction" class="tab-pane fade {if !isset($tab) || $tab === 'massaction' || $tab === 'uebersicht'} active show{/if}">
                    <form name="suche" method="post" action="warenkorbpers.php">
                        {$jtl_token}
                        <input type="hidden" name="Suche" value="1" />
                        <input type="hidden" name="tab" value="warenkorbpers" />
                        {if isset($cSuche) && $cSuche|strlen > 0}
                            <input type="hidden" name="cSuche" value="{$cSuche}" />
                        {/if}

                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cSuche">{__('warenkorbpersClientName')}:</label>
                            </span>
                            <input class="form-control" id="cSuche" name="cSuche" type="text" value="{if isset($cSuche) && $cSuche|strlen > 0}{$cSuche}{/if}" />
                            <span class="input-group-btn">
                                <button name="submitSuche" type="submit" value="{__('warenkorbpersSearchBTN')}" class="btn btn-primary ml-1"><i class="fal fa-search"></i></button>
                            </span>
                        </div>
                    </form>

                    {if isset($oKunde_arr) && $oKunde_arr|@count > 0}
                        {assign var=cParam_arr value=[]}
                        {if isset($cSuche)}
                            {append var=cParam_arr index='cSuche' value=$cSuche}
                        {/if}
                        {include file='tpl_inc/pagination.tpl' pagination=$oPagiKunden cParam_arr=$cParam_arr cAnchor='massaction'}
                        <div>
                            <div class="subheading1">{__('warenkorbpers')}</div>
                            <hr class="mb-3">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th class="tleft">{__('warenkorbpersCompany')}</th>
                                        <th class="tleft">{__('warenkorbpersClientName')}</th>
                                        <th class="th-3">{__('warenkorbpersCount')}</th>
                                        <th class="th-4">{__('warenkorbpersDate')}</th>
                                        <th class="th-5">{__('warenkorbpersAction')}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $oKunde_arr as $oKunde}
                                        <tr>
                                            <td>{$oKunde->cFirma}</td>
                                            <td>{$oKunde->cVorname} {$oKunde->cNachname}</td>
                                            <td class="tcenter">{$oKunde->nAnzahl}</td>
                                            <td class="tcenter">{$oKunde->Datum}</td>
                                            <td class="tcenter">
                                                <div class="btn-group">
                                                    <a href="warenkorbpers.php?a={$oKunde->kKunde}&token={$smarty.session.jtl_token}" class="btn btn-default">{__('warenkorbpersShow')}</a>
                                                    <a href="warenkorbpers.php?l={$oKunde->kKunde}&token={$smarty.session.jtl_token}" class="btn btn-danger"><i class="fas fa-trash-alt"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    {else}
                        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                    {/if}
                </div>
                <div id="settings" class="tab-pane fade {if isset($tab) && $tab === 'einstellungen'} active show{/if}">
                    {include file='tpl_inc/config_section.tpl' a='speichern' config=$oConfig_arr name='einstellen' action='warenkorbpers.php' buttonCaption=__('save') title=__('settings') tab='einstellungen'}
                </div>
            </div>
        </div>
    {elseif $step === 'anzeigen'}
        {assign var=pAdditional value="&a="|cat:$kKunde}
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{__('warenkorbpersClient')} {$oWarenkorbPersPos_arr[0]->cVorname} {$oWarenkorbPersPos_arr[0]->cNachname}</div>
                <hr class="mb-n3">
            </div>
            <div class="table-responsive card-body">
                {include file='tpl_inc/pagination.tpl' pagination=$oPagiWarenkorb cParam_arr=['a'=>$kKunde]}
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th class="tleft">{__('warenkorbpersProduct')}</th>
                        <th class="th-2">{__('warenkorbpersCount')}</th>
                        <th class="th-3">{__('warenkorbpersDate')}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $oWarenkorbPersPos_arr as $oWarenkorbPersPos}
                        <tr>
                            <td class="tleft">
                                <a href="{$shopURL}/index.php?a={$oWarenkorbPersPos->kArtikel}" target="_blank">{$oWarenkorbPersPos->cArtikelName}</a>
                            </td>
                            <td class="tcenter">{$oWarenkorbPersPos->fAnzahl}</td>
                            <td class="tcenter">{$oWarenkorbPersPos->Datum}</td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
