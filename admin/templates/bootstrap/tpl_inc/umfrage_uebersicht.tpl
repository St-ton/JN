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
                        <div class="row">
                            <div class="col-sm-6 col-lg-auto mb-3">
                                <form name="erstellen" method="post" action="umfrage.php">
                                    {$jtl_token}
                                    <input type="hidden" name="umfrage" value="1" />
                                    <input type="hidden" name="umfrage_erstellen" value="1" />
                                    <input type="hidden" name="tab" value="umfrage" />
                                    <button name="umfrageerstellen" type="submit" value="1" title="{__('umfrageAdd')}" class="btn btn-primary btn-block"><i class="fa fa-share"></i> {__('umfrageAdd')}</button>
                                </form>
                            </div>
                        </div>
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
                                                        <th class="th-5 text-center">{__('active')}</th>
                                                        <th class="th-6 text-center">{__('umfrageQCount')}</th>
                                                        <th class="th-7 text-center">{__('umfrageDate')}</th>
                                                        <th class="th-8 text-center">{__('actions')}</th>
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
                                                        <td>{$oUmfrage->dGueltigVon_de} - {if $oUmfrage->dGueltigBis === null}{__('umfrageInfinite')}{else}{$oUmfrage->dGueltigBis_de}{/if}</td>
                                                        <td class="text-center">
                                                            {if $oUmfrage->nAktiv}
                                                                <i class="fal fa-check text-success"></i>
                                                            {else}
                                                                <i class="fal fa-times text-danger"></i>
                                                            {/if}
                                                        </td>
                                                        <td class="text-center">{$oUmfrage->nAnzahlFragen}</td>
                                                        <td class="text-center">{$oUmfrage->dErstellt_de}</td>
                                                        <td class="text-center">
                                                            <div class="btn-group">
                                                                <a href="umfrage.php?umfrage=1&token={$smarty.session.jtl_token}&kUmfrage={$oUmfrage->kUmfrage}&umfrage_statistik=1"
                                                                   class="btn btn-link px-2"
                                                                   title="{__('umfrageStats')}"
                                                                   data-toggle="tooltip">
                                                                    <span class="icon-hover">
                                                                        <span class="fal fa-bar-chart"></span>
                                                                        <span class="fas fa-bar-chart"></span>
                                                                    </span>
                                                                </a>
                                                                <a href="umfrage.php?umfrage=1&token={$smarty.session.jtl_token}&umfrage_editieren=1&kUmfrage={$oUmfrage->kUmfrage}&tab=umfrage"
                                                                   class="btn btn-link px-2"
                                                                   title="{__('modify')}"
                                                                   data-toggle="tooltip">
                                                                    <span class="icon-hover">
                                                                        <span class="fal fa-edit"></span>
                                                                        <span class="fas fa-edit"></span>
                                                                    </span>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                {/foreach}
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="card-footer save-wrapper">
                                            <div class="row">
                                                <div class="ml-auto col-sm-6 col-xl-auto">
                                                    <button name="loeschen" type="submit" class="btn btn-danger btn-block mb-2"><i class="fas fa-trash-alt"></i> {__('deleteSelected')}</button>
                                                </div>
                                            </div>
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
                    {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='umfrage.php' buttonCaption=__('saveWithIcon') title=__('settings') tab='einstellungen'}
                </div>
            </div>
        </div>
    {else}
        <div class="alert alert-danger">{__('noModuleAvailable')}</div>
    {/if}
</div>
