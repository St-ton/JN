{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('kundenwerbenkunden') cBeschreibung=__('kundenwerbenkundenDesc') cDokuURL=__('kundenwerbenkundenURL')}
<div id="content">
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if !isset($cTab) || $cTab === 'einladungen'} active{/if}" data-toggle="tab" role="tab" href="#einladungen">
                        {__('kundenwerbenkundenNotReggt')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'registrierung'} active{/if}" data-toggle="tab" role="tab" href="#registrierung">
                        {__('kundenwerbenkundenReggt')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'praemie'} active{/if}" data-toggle="tab" role="tab" href="#praemie">
                        {__('kundenwerbenkundenBonis')}
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
            <div id="einladungen" class="tab-pane fade {if !isset($cTab) || $cTab === 'einladungen'} active show{/if}">
                {if $oKwKNichtReg_arr|@count > 0 && $oKwKNichtReg_arr}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiNichtReg cAnchor='einladungen'}
                    <div class="table-responsive">
                        <form name="umfrage" method="post" action="kundenwerbenkunden.php">
                            {$jtl_token}
                            <input type="hidden" name="KwK" value="1" />
                            <input type="hidden" name="nichtreggt_loeschen" value="1" />
                            <input type="hidden" name="tab" value="einladungen" />
                            <div id="payment">
                                <div id="tabellenLivesuche" class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="check"></th>
                                                <th class="text-left">{__('name')}</th>
                                                <th class="text-left">{__('kundenwerbenkundenFromReg')}</th>
                                                <th class="text-center">{__('credit')}</th>
                                                <th class="text-center">{__('kundenwerbenkundenDateInvite')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $oKwKNichtReg_arr as $oKwKNichtReg}
                                            <tr>
                                                <td class="check">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" type="checkbox" name="kKundenWerbenKunden[]" id="customer-recruit-{$oKwKNichtReg->kKundenWerbenKunden}" value="{$oKwKNichtReg->kKundenWerbenKunden}">
                                                        <label class="custom-control-label" for="customer-recruit-{$oKwKNichtReg->kKundenWerbenKunden}"></label>
                                                    </div>
                                                </td>
                                                <td class="text-left">
                                                    <b>{$oKwKNichtReg->cVorname} {$oKwKNichtReg->cNachname}</b><br />{$oKwKNichtReg->cEmail}
                                                </td>
                                                <td class="text-left">
                                                    <b>{$oKwKNichtReg->cBestandVorname} {$oKwKNichtReg->cBestandNachname}</b><br />{$oKwKNichtReg->cMail}
                                                </td>
                                                <td class="text-center">{getCurrencyConversionSmarty fPreisBrutto=$oKwKNichtReg->fGuthaben}</td>
                                                <td class="text-center">{$oKwKNichtReg->dErstellt_de}</td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="save-wrapper submit">
                                <div class="row">
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <button name="loeschen" type="submit" value="{__('delete')}" class="btn btn-danger btn-block">
                                            <i class="fas fa-trash-alt"></i> {__('delete')}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="registrierung" class="tab-pane fade {if isset($cTab) && $cTab === 'registrierung'} active show{/if}">
                {if $oKwKReg_arr && $oKwKReg_arr|@count > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiReg cAnchor='registrierung'}
                    <div id="payment">
                        <div id="tabellenLivesuche" class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-left">{__('newCustomer')}</th>
                                        <th class="text-left">{__('kundenwerbenkundenFromReg')}</th>
                                        <th class="text-left">{__('credit')}</th>
                                        <th class="text-center">{__('kundenwerbenkundenDateInvite')}</th>
                                        <th class="text-center">{__('kundenwerbenkundenDateErstellt')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                {foreach $oKwKReg_arr as $oKwKReg}
                                    <tr>
                                        <td>
                                            <b>{$oKwKReg->cVorname} {$oKwKReg->cNachname}</b>
                                            <br />{$oKwKReg->cEmail}
                                        </td>
                                        <td>
                                            <b>{$oKwKReg->cBestandVorname} {$oKwKReg->cBestandNachname}</b>
                                            <br />{$oKwKReg->cMail}
                                        </td>
                                        <td>{getCurrencyConversionSmarty fPreisBrutto=$oKwKReg->fGuthaben}</td>
                                        <td class="text-center">{$oKwKReg->dErstellt_de}</td>
                                        <td class="text-center">{$oKwKReg->dBestandErstellt_de}</td>
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
            <div id="praemie" class="tab-pane fade {if isset($cTab) && $cTab === 'praemie'} active show{/if}">
                {if $oKwKBestandBonus_arr|@count > 0 && $oKwKBestandBonus_arr}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiPraemie cAnchor='praemie'}
                    <div id="payment">
                        <div id="tabellenLivesuche" class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-left">{__('kundenwerbenkundenFromReg')}</th>
                                        <th class="text-left">{__('credit')}</th>
                                        <th class="">{__('kundenwerbenkundenExtraPoints')}</th>
                                        <th class="th-4">{__('kundenwerbenkundenDateBoni')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                {foreach $oKwKBestandBonus_arr as $oKwKBestandBonus}
                                    <tr>
                                        <td>
                                            <b>{$oKwKBestandBonus->cBestandVorname} {$oKwKBestandBonus->cBestandNachname}</b><br />{$oKwKBestandBonus->cMail}
                                        </td>
                                        <td>{getCurrencyConversionSmarty fPreisBrutto=$oKwKBestandBonus->fGuthaben}</td>
                                        <td class="text-center">{$oKwKBestandBonus->nBonuspunkte}</td>
                                        <td class="text-center">{$oKwKBestandBonus->dErhalten_de}</td>
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
            <div id="einstellungen" class="tab-pane fade {if isset($cTab) && $cTab === 'einstellungen'} active show{/if}">
                {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' a='saveSettings' action='kundenwerbenkunden.php' buttonCaption=__('saveWithIcon') title='Einstellungen' tab='einstellungen'}
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    {foreach $oConfig_arr as $oConfig}
        {if $oConfig->cWertName|strpos:'_bestandskundenguthaben' || $oConfig->cWertName|strpos:'_neukundenguthaben'}
            ioCall('getCurrencyConversion', [0, $('#{$oConfig->cWertName}').val(), 'EinstellungAjax_{$oConfig->cWertName}']);
        {/if}
    {/foreach}
</script>
{include file='tpl_inc/footer.tpl'}
