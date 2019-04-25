{assign var=isleListFor value=__('isleListFor')}
{assign var=cVersandartName value=$Versandart->cName}
{assign var=cLandName value=$Land->cDeutsch}
{assign var=cLandISO value=$Land->cISO}

{include file='tpl_inc/seite_header.tpl' cTitel=$isleListFor|cat: ' '|cat:$cVersandartName|cat:', '|cat:$cLandName|cat:'('|cat:$cLandISO|cat:')' cBeschreibung=__('isleListsDesc')}
<div id="content" class="container-fluid">
    {foreach $Zuschlaege as $zuschlag}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{__('isleList')}: {$zuschlag->cName}</h3>
            </div>
            <div class="table-responsive">
                <table class="list table">
                    <tbody>
                    {foreach $sprachen as $sprache}
                        {assign var=cISO value=$sprache->cISO}
                        <tr>
                            <td width="35%">{__('showedName')} ({$sprache->name})</td>
                            <td>{$zuschlag->angezeigterName[$cISO]}</td>
                        </tr>
                    {/foreach}
                    <tr>
                        <td width="35%">{__('additionalFee')}</td>
                        <td>{getCurrencyConversionSmarty fPreisBrutto=$zuschlag->fZuschlag bSteuer=false}</td>
                    </tr>
                    <tr>
                        <td width="35%">{__('plz')}</td>
                        <td>
                            <div class="row">
                                {foreach $zuschlag->zuschlagplz as $plz}
                                    <p class="col-xs-6 col-md-4">
                                        {if $plz->cPLZ}{$plz->cPLZ}{elseif $plz->cPLZAb}{$plz->cPLZAb} - {$plz->cPLZBis}{/if}
                                        {if $plz->cPLZ || $plz->cPLZAb}
                                            <a href="versandarten.php?delplz={$plz->kVersandzuschlagPlz}&kVersandart={$Versandart->kVersandart}&cISO={$Land->cISO}&token={$smarty.session.jtl_token}" class="button plain remove">{__('delete')}</a>
                                        {/if}
                                    </p>
                                {/foreach}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <form name="zuschlagplz_neu_{$zuschlag->kVersandzuschlag}" method="post" action="versandarten.php">
                                {$jtl_token}
                                <input type="hidden" name="neueZuschlagPLZ" value="1" />
                                <input type="hidden" name="kVersandart" value="{$Versandart->kVersandart}" />
                                <input type="hidden" name="cISO" value="{$Land->cISO}" />
                                <input type="hidden" name="kVersandzuschlag" value="{$zuschlag->kVersandzuschlag}" />
                                {__('plz')} <input type="text" name="cPLZ" class="form-control zipcode" /> {__('orPlzRange')}
                                <div class="input-group">
                                    <input type="text" name="cPLZAb" class="form-control zipcode" />
                                    <span class="input-group-addon">&ndash;</span>
                                    <input type="text" name="cPLZBis" class="form-control zipcode" />
                                </div>
                                <input type="submit" value="{__('add')}" class="btn btn-default button plain add" />
                            </form>
                        </td>
                    </tr>
                    </tbody>
                    <tfoot class="light">
                    <tr>
                        <td colspan="2">
                            <div class="btn-group">
                                <a href="versandarten.php?delzus={$zuschlag->kVersandzuschlag}&token={$smarty.session.jtl_token}&kVersandart={$Versandart->kVersandart}&cISO={$Land->cISO}" class="btn btn-danger">
                                    <i class="fa fa-trash"></i> {__('additionalFeeDelete')}
                                </a>
                                <a href="versandarten.php?editzus={$zuschlag->kVersandzuschlag}&token={$smarty.session.jtl_token}&kVersandart={$Versandart->kVersandart}&cISO={$Land->cISO}" class="btn btn-default">
                                    <i class="fa fa-edit"></i> {__('additionalFeeEdit')}
                                </a>
                            </div>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    {/foreach}

    <div class="settings">
        <form name="zuschlag_neu" method="post" action="versandarten.php">
            {$jtl_token}
            <input type="hidden" name="neuerZuschlag" value="1" />
            {if isset($oVersandzuschlag->kVersandart) && $oVersandzuschlag->kVersandart > 0}
                <input type="hidden" name="kVersandart" value="{$oVersandzuschlag->kVersandart}" />
            {else}
                <input type="hidden" name="kVersandart" value="{$Versandart->kVersandart}" />
            {/if}
            <input type="hidden" name="cISO" value="{$Land->cISO}" />
            {if isset($oVersandzuschlag->kVersandzuschlag) && $oVersandzuschlag->kVersandzuschlag > 0}
                <input type="hidden" name="kVersandzuschlag" value="{$oVersandzuschlag->kVersandzuschlag}" />
            {/if}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{if isset($oVersandzuschlag->kVersandzuschlag) && $oVersandzuschlag->kVersandzuschlag > 0}{__('additionalFeeEdit')}{else}{__('createNewList')}{/if}</h3>
                </div>
                <div class="panel-body">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cName">{__('isleList')}</label>
                        </span>
                        <input class="form-control" type="text" id="cName" name="cName" value="{if isset($oVersandzuschlag->cName)}{$oVersandzuschlag->cName}{/if}" tabindex="1" required/>
                    </div>
                    {assign var=idx value=1}
                    {foreach $sprachen as $sprache}
                        {assign var=cISO value=$sprache->cISO}
                        {assign var=idx value=$idx+1}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <label for="cName_{$cISO}">{__('showedName')} ({$sprache->name})</label>
                            </span>
                            <input class="form-control" type="text" id="cName_{$cISO}" name="cName_{$cISO}" value="{if isset($oVersandzuschlag->oVersandzuschlagSprache_arr.$cISO->cName)}{$oVersandzuschlag->oVersandzuschlagSprache_arr.$cISO->cName}{/if}" tabindex="{$idx}" />
                        </div>
                    {/foreach}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="fZuschlag">{__('additionalFee')} ({__('amount')})</label>
                        </span>
                        <input type="text" id="fZuschlag" name="fZuschlag" value="{if isset($oVersandzuschlag->fZuschlag)}{$oVersandzuschlag->fZuschlag}{/if}" class="form-control price_large" tabindex="{$idx+1}" required>{* onKeyUp="setzePreisAjax(false, 'ajaxzuschlag', this)"/> <span id="ajaxzuschlag"></span>*}
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="btn-group">
                        <a href="versandarten.php" type="button" class="btn btn-warning">
                            <i class="fa fa-chevron-left"></i> {__('back2shippingtypes')}
                        </a>
                        <button type="submit" value="{if isset($oVersandzuschlag->kVersandart) && $oVersandzuschlag->kVersandart > 0}{__('createEditList')}{else}{__('createNewList')}{/if}" class="btn btn-primary">
                            <i class="fa fa-save"></i> {if isset($oVersandzuschlag->kVersandart) && $oVersandzuschlag->kVersandart > 0}{__('createEditList')}{else}{__('createNewList')}{/if}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{if isset($oVersandzuschlag->kVersandzuschlag) && $oVersandzuschlag->kVersandzuschlag > 0}
    <script type="text/javascript">
        ioCall('getCurrencyConversion', [0, $('#fZuschlag').val(), 'ajaxzuschlag']);
    </script>
{/if}
