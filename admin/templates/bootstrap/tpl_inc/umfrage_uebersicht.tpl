<div id="content" class="container-fluid">
    {if !isset($noModule) || !$noModule}
        <form name="sprache" method="post" action="umfrage.php">
            {$jtl_token}
            <input type="hidden" name="sprachwechsel" value="1" />
            <div class="block">
                <div class="input-group p25 left">
                    {include file='tpl_inc/language_switcher.tpl'}
                </div>
            </div>
        </form>
        <ul class="nav nav-tabs" role="tablist">
            <li class="tab{if !isset($cTab) || $cTab === 'umfrage'} active{/if}">
                <a data-toggle="tab" role="tab" href="#umfrage">{__('umfrageOverview')}</a>
            </li>
            <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
                <a data-toggle="tab" role="tab" href="#einstellungen">{__('settings')}</a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="umfrage" class="tab-pane fade{if !isset($cTab) || $cTab === 'umfrage'} active show{/if}">
                <form name="erstellen" method="post" action="umfrage.php">
                    {$jtl_token}
                    <input type="hidden" name="umfrage" value="1" />
                    <input type="hidden" name="umfrage_erstellen" value="1" />
                    <input type="hidden" name="tab" value="umfrage" />
                    <p class="tcenter">
                        <button name="umfrageerstellen" type="submit" value="{__('umfrageAdd')}" class="btn btn-primary"><i class="fa fa-share"></i> {__('umfrageAdd')}</button>
                    </p>
                </form>
                {if $oUmfrage_arr|@count > 0 && $oUmfrage_arr}
                    {include file='tpl_inc/pagination.tpl' pagination=$pagination cAnchor='umfrage'}
                    <form name="umfrage" method="post" action="umfrage.php">
                        {$jtl_token}
                        <input type="hidden" name="umfrage" value="1" />
                        <input type="hidden" name="umfrage_loeschen" value="1" />
                        <input type="hidden" name="tab" value="umfrage" />
                        <div id="payment">
                            <div id="tabellenLivesuche">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="subheading1">{__('activePolls')}</div>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table table-striped">
                                            <tr>
                                                <th class="th-1"></th>
                                                <th class="th-2">{__('name')}</th>
                                                <th class="th-3">{__('customerGroup')}</th>
                                                <th class="th-4">{__('umfrageValidation')}</th>
                                                <th class="th-5">{__('active')}</th>
                                                <th class="th-6">{__('umfrageQCount')}</th>
                                                <th class="th-7">{__('umfrageDate')}</th>
                                                <th class="th-8">{__('actions')}</th>
                                            </tr>
                                            {foreach $oUmfrage_arr as $oUmfrage}
                                                <tr>
                                                    <td><input type="checkbox" name="kUmfrage[]" value="{$oUmfrage->kUmfrage}" /></td>
                                                    <td>
                                                        <a href="umfrage.php?umfrage=1&token={$smarty.session.jtl_token}&ud=1&kUmfrage={$oUmfrage->kUmfrage}&tab=umfrage">{$oUmfrage->cName}</a>
                                                    </td>
                                                    <td>
                                                        {$oUmfrage->cKundengruppe_arr|implode:','}
                                                    </td>
                                                    <td>{$oUmfrage->dGueltigVon_de}-{if $oUmfrage->dGueltigBis === null}{__('umfrageInfinite')}{else}{$oUmfrage->dGueltigBis_de}{/if}</td>
                                                    <td>{$oUmfrage->nAktiv}</td>
                                                    <td>{$oUmfrage->nAnzahlFragen}</td>
                                                    <td>{$oUmfrage->dErstellt_de}</td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="umfrage.php?umfrage=1&token={$smarty.session.jtl_token}&umfrage_editieren=1&kUmfrage={$oUmfrage->kUmfrage}&tab=umfrage" class="btn btn-default" title="{__('modify')}">
                                                                <i class="fa fa-edit"></i>
                                                            </a>
                                                            <a href="umfrage.php?umfrage=1&token={$smarty.session.jtl_token}&kUmfrage={$oUmfrage->kUmfrage}&umfrage_statistik=1" class="btn btn-default" title="{__('umfrageStats')}"><i class="fa fa-bar-chart"></i></a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            {/foreach}
                                        </table>
                                    </div>
                                    <div class="card-footer">
                                        <button name="loeschen" type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {__('deleteSelected')}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="einstellungen" class="tab-pane fade{if isset($cTab) && $cTab === 'einstellungen'} active show{/if}">
                {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='umfrage.php' buttonCaption=__('save') title=__('settings') tab='einstellungen'}
            </div>
        </div>
    {else}
        <div class="alert alert-danger">{__('noModuleAvailable')}</div>
    {/if}
</div>
