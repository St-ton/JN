{config_load file="$lang.conf" section='kundenwerbenkunden'}
{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('kundenwerbenkunden') cBeschreibung=__('kundenwerbenkundenDesc') cDokuURL=__('kundenwerbenkundenURL')}
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'einladungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einladungen">{__('kundenwerbenkundenNotReggt')}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'registrierung'} active{/if}">
            <a data-toggle="tab" role="tab" href="#registrierung">{__('kundenwerbenkundenReggt')}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'praemie'} active{/if}">
            <a data-toggle="tab" role="tab" href="#praemie">{__('kundenwerbenkundenBonis')}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{__('settings')}</a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="einladungen" class="tab-pane fade {if !isset($cTab) || $cTab === 'einladungen'} active in{/if}">
            {if $oKwKNichtReg_arr|@count > 0 && $oKwKNichtReg_arr}
                {include file='tpl_inc/pagination.tpl' pagination=$oPagiNichtReg cAnchor='einladungen'}
                <form name="umfrage" method="post" action="kundenwerbenkunden.php">
                    {$jtl_token}
                    <input type="hidden" name="KwK" value="1" />
                    <input type="hidden" name="nichtreggt_loeschen" value="1" />
                    <input type="hidden" name="tab" value="einladungen" />
                    <div id="payment">
                        <div id="tabellenLivesuche" class="table-responsive">
                            <table class="table table-striped">
                                <tr>
                                    <th class="check"></th>
                                    <th class="tleft">{__('name')}</th>
                                    <th class="tleft">{__('kundenwerbenkundenFromReg')}</th>
                                    <th class="tleft">{__('credit')}</th>
                                    <th class="th-5">{__('kundenwerbenkundenDateInvite')}</th>
                                </tr>
                                {foreach $oKwKNichtReg_arr as $oKwKNichtReg}
                                    <tr>
                                        <td class="check">
                                            <input type="checkbox" name="kKundenWerbenKunden[]" value="{$oKwKNichtReg->kKundenWerbenKunden}">
                                        </td>
                                        <td class="tleft">
                                            <b>{$oKwKNichtReg->cVorname} {$oKwKNichtReg->cNachname}</b><br />{$oKwKNichtReg->cEmail}
                                        </td>
                                        <td class="tleft">
                                            <b>{$oKwKNichtReg->cBestandVorname} {$oKwKNichtReg->cBestandNachname}</b><br />{$oKwKNichtReg->cMail}
                                        </td>
                                        <td class="tleft">{getCurrencyConversionSmarty fPreisBrutto=$oKwKNichtReg->fGuthaben}</td>
                                        <td class="tcenter">{$oKwKNichtReg->dErstellt_de}</td>
                                    </tr>
                                {/foreach}
                            </table>
                        </div>
                    </div>
                    <p class="submit">
                        <button name="loeschen" type="submit" value="{__('delete')}" class="btn btn-danger"><i class="fa fa-trash"></i> {__('delete')}</button>
                    </p>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
            {/if}
        </div>
        <div id="registrierung" class="tab-pane fade {if isset($cTab) && $cTab === 'registrierung'} active in{/if}">
            {if $oKwKReg_arr && $oKwKReg_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' pagination=$oPagiReg cAnchor='registrierung'}
                <div id="payment">
                    <div id="tabellenLivesuche" class="table-responsive">
                        <table class="table table-striped">
                            <tr>
                                <th class="tleft">{__('newCustomer')}</th>
                                <th class="tleft">{__('kundenwerbenkundenFromReg')}</th>
                                <th class="tleft">{__('credit')}</th>
                                <th class="th-4">{__('kundenwerbenkundenDateInvite')}</th>
                                <th class="th-5">{__('kundenwerbenkundenDateErstellt')}</th>
                            </tr>
                            {foreach $oKwKReg_arr as $oKwKReg}
                                <tr>
                                    <td><b>{$oKwKReg->cVorname} {$oKwKReg->cNachname}</b><br />{$oKwKReg->cEmail}</td>
                                    <td>
                                        <b>{$oKwKReg->cBestandVorname} {$oKwKReg->cBestandNachname}</b><br />{$oKwKReg->cMail}
                                    </td>
                                    <td>{getCurrencyConversionSmarty fPreisBrutto=$oKwKReg->fGuthaben}</td>
                                    <td class="tcenter">{$oKwKReg->dErstellt_de}</td>
                                    <td class="tcenter">{$oKwKReg->dBestandErstellt_de}</td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
            {/if}
        </div>
        <div id="praemie" class="tab-pane fade {if isset($cTab) && $cTab === 'praemie'} active in{/if}">
            {if $oKwKBestandBonus_arr|@count > 0 && $oKwKBestandBonus_arr}
                {include file='tpl_inc/pagination.tpl' pagination=$oPagiPraemie cAnchor='praemie'}
                <div id="payment">
                    <div id="tabellenLivesuche" class="table-responsive">
                        <table class="table table-striped">
                            <tr>
                                <th class="tleft">{__('kundenwerbenkundenFromReg')}</th>
                                <th class="tleft">{__('credit')}</th>
                                <th class="">{__('kundenwerbenkundenExtraPoints')}</th>
                                <th class="th-4">{__('kundenwerbenkundenDateBoni')}</th>
                            </tr>
                            {foreach $oKwKBestandBonus_arr as $oKwKBestandBonus}
                                <tr>
                                    <td>
                                        <b>{$oKwKBestandBonus->cBestandVorname} {$oKwKBestandBonus->cBestandNachname}</b><br />{$oKwKBestandBonus->cMail}
                                    </td>
                                    <td>{getCurrencyConversionSmarty fPreisBrutto=$oKwKBestandBonus->fGuthaben}</td>
                                    <td class="tcenter">{$oKwKBestandBonus->nBonuspunkte}</td>
                                    <td class="tcenter">{$oKwKBestandBonus->dErhalten_de}</td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
            {/if}
        </div>
        <div id="einstellungen" class="tab-pane fade {if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' a='saveSettings' action='kundenwerbenkunden.php' buttonCaption=__('save') title='Einstellungen' tab='einstellungen'}
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
