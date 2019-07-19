<div id="content">
    {if !isset($noModule) || !$noModule}
        <div class="card">
            <div class="card-body">
                {include file='tpl_inc/language_switcher.tpl' action='umfrage.php'}
            </div>
        </div>
        <div class="tabs">
            <nav class="tabs-nav">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {if !isset($cTab) || $cTab === 'umfrage'} active{/if}" data-toggle="tab" role="tab" href="#umfrage">
                            {__('umfrageOverview')}
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
                <div id="umfrage" class="tab-pane fade{if !isset($cTab) || $cTab === 'umfrage'} active show{/if}">
                    {if $oUmfrage_arr|@count > 0 && $oUmfrage_arr}
                        {include file='tpl_inc/pagination.tpl' pagination=$pagination cAnchor='umfrage'}
                        <form name="umfrage" method="post" action="umfrage.php">
                            {$jtl_token}
                            <input type="hidden" name="umfrage" value="1" />
                            <input type="hidden" name="umfrage_loeschen" value="1" />
                            <input type="hidden" name="tab" value="umfrage" />
                            <div id="payment">
                                <div id="tabellenLivesuche">
                                    <div>
                                        <div class="subheading1">{__('activePolls')}</div>
                                        <hr class="mb-3">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
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
                                                </thead>
                                                <tbody>
                                                {foreach $oUmfrage_arr as $oUmfrage}
                                                    <tr>
                                                        <td>
                                                            <div class="custom-control custom-checkbox">
                                                                <input class="custom-control-input" type="checkbox" name="kUmfrage[]" id="survey-id-{$oUmfrage->kUmfrage}" value="{$oUmfrage->kUmfrage}" />
                                                                <label class="custom-control-label" for="survey-id-{$oUmfrage->kUmfrage}"></label>
                                                            </div>
                                                        </td>
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
                                                            <a href="umfrage.php?umfrage=1&token={$smarty.session.jtl_token}&kUmfrage={$oUmfrage->kUmfrage}&umfrage_statistik=1" class="btn btn-default btn-circle" title="{__('umfrageStats')}">
                                                                <i class="fa fa-bar-chart"></i>
                                                            </a>
                                                            <a href="umfrage.php?umfrage=1&token={$smarty.session.jtl_token}&umfrage_editieren=1&kUmfrage={$oUmfrage->kUmfrage}&tab=umfrage" class="btn btn-primary btn-circle" title="{__('modify')}">
                                                                <i class="fal fa-edit"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                {/foreach}
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="card-footer save-wrapper">
                                            <button name="loeschen" type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> {__('deleteSelected')}</button>
                                            <form name="erstellen" method="post" action="umfrage.php">
                                                {$jtl_token}
                                                <input type="hidden" name="umfrage" value="1" />
                                                <input type="hidden" name="umfrage_erstellen" value="1" />
                                                <input type="hidden" name="tab" value="umfrage" />
                                                <button name="umfrageerstellen" type="submit" value="{__('umfrageAdd')}" class="btn btn-primary"><i class="fa fa-share"></i> {__('umfrageAdd')}</button>
                                            </form>
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
        </div>
    {else}
        <div class="alert alert-danger">{__('noModuleAvailable')}</div>
    {/if}
</div>
