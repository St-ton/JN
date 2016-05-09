<script type="text/javascript">
{literal}
$(document).ready(function() {
    $('#tmp_check').bind('click', function() {
        if ($(this).is(':checked')) {
            $('#tmp_date').show();
        } else {
            $('#tmp_date').hide();
        }
    });
    $('#dGueltigBis').datetimepicker({
        showSecond: true,
        timeFormat: 'hh:mm:ss',
        dateFormat: 'dd.mm.yy'
    });
});
{/literal}
</script>

{assign var="cTitel" value=#benutzerNeu#}
{if isset($oAccount) && $oAccount->kAdminlogin > 0}
    {assign var="cTitel" value=#benutzerBearbeiten#}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=#benutzerDesc#}
<div id="content" class="container-fluid">
    <form class="navbar-form" action="benutzerverwaltung.php" method="post">
        {$jtl_token}
        <div id="settings" class="settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Allgemein</h3>
                </div>
                <div class="panel-body">
                    <div class="item">
                        <div class="input-group{if isset($cError_arr.cName)} error{/if}">
                            <span class="input-group-addon">
                            <label for="cName">Vor- und Nachname</label></span>
                            <input id="cName" class="form-control" type="text" name="cName" value="{if isset($oAccount->cName)}{$oAccount->cName}{/if}" />
                            {if isset($cError_arr.cName)}<span class="input-group-addon error">Bitte ausf&uuml;llen</span>{/if}
                        </div>
                    </div>
                    <div class="item">
                        <div class="input-group{if isset($cError_arr.cMail)} error{/if}">
                            <span class="input-group-addon">
                                <label for="cMail">E-Mail Adresse</label>
                            </span>
                            <input id="cMail" class="form-control" type="text" name="cMail" value="{if isset($oAccount->cMail)}{$oAccount->cMail}{/if}" />
                            {if isset($cError_arr.cMail)}<span class="input-group-addon error">Bitte ausf&uuml;llen</span>{/if}
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Anmeldedaten</h3>
                </div>
                <div class="panel-body">
                    <div class="item">
                        <div class="input-group{if isset($cError_arr.cLogin)} error{/if}">
                            <span class="input-group-addon">
                                <label for="cLogin">Benutzername</label>
                            </span>
                            <input id="cLogin" class="form-control" type="text" name="cLogin" value="{if isset($oAccount->cLogin)}{$oAccount->cLogin}{/if}" />
                            {if isset($cError_arr.cLogin) && $cError_arr.cLogin == 1}
                                <span class="input-group-addon error">Bitte ausf&uuml;llen</span>
                            {elseif isset($cError_arr.cLogin) && $cError_arr.cLogin == 2}
                                <span class="input-group-addon error">Benutzername <strong>'{$oAccount->cLogin}'</strong> bereits vergeben</span>
                            {/if}
                        </div>
                    </div>

                    <div class="item">
                        <div class="input-group{if isset($cError_arr.cPass)} error{/if}">
                            <span class="input-group-addon">
                                <label for="cPass">Passwort</label>
                            </span>
                            <input id="cPass" class="form-control" type="text" name="cPass" autocomplete="off" />
                            <span class="input-group-addon">
                                <a href="#" onclick="xajax_getRandomPassword();return false;" class="button generate"><i class="fa fa-lock"></i> Passwort generieren</a>
                            </span>
                            {if isset($cError_arr.cPass)}<span class="input-group-addon error">Bitte ausf&uuml;llen</span>{/if}
                        </div>
                    </div>

                    {if isset($oAccount->kAdminlogingruppe) && $oAccount->kAdminlogingruppe > 1}
                        <div class="item">
                            <div class="input-group">
                            <span class="input-group-addon">
                                <label for="tmp_check">Zeitlich begrenzter Zugriff</label>
                            </span>
                            <span class="input-group-wrap">
                                <span class="input-group-checkbox-wrap">
                                    <input class="" type="checkbox" id="tmp_check" name="dGueltigBisAktiv" value="1"{if (isset($oAccount->dGueltigBis) && $oAccount->dGueltigBis !== '0000-00-00 00:00:00')} checked="checked"{/if} />
                                </span>
                            </span>
                            </div>
                        </div>

                        <div class="item{if !empty($cError_arr.dGueltigBis)} error{/if}"{if !$oAccount->dGueltigBis || $oAccount->dGueltigBis == '0000-00-00 00:00:00'} style="display: none;"{/if} id="tmp_date">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="dGueltigBis">... bis einschlie&szlig;lich</label>
                                </span>
                                <input class="form-control" type="text" name="dGueltigBis" value="{if $oAccount->dGueltigBis}{$oAccount->dGueltigBis|date_format:"%d.%m.%Y %H:%M:%S"}{/if}" id="dGueltigBis" />
                                {if !empty($cError_arr.dGueltigBis)}<span class="input-group-addon error">Bitte ausf&uuml;llen</span>{/if}
                            </div>
                        </div>
                    {/if}
                </div>
            </div>

            {if !isset($oAccount->kAdminlogingruppe) || (isset($nAdminCount) && !($oAccount->kAdminlogingruppe == 1 && $nAdminCount <= 1))}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Berechtigungen</h3>
                    </div>
                    <div class="panel-body">
                        <div class="item">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="kAdminlogingruppe">Benutzergruppe</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select id="kAdminlogingruppe" class="form-control" name="kAdminlogingruppe">
                                        {foreach from=$oAdminGroup_arr item="oGroup"}
                                            <option value="{$oGroup->kAdminlogingruppe}" {if isset($oAccount->kAdminlogingruppe) && $oAccount->kAdminlogingruppe == $oGroup->kAdminlogingruppe}selected="selected"{/if}>{$oGroup->cGruppe} ({$oGroup->nCount})</option>
                                        {/foreach}
                                    </select>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            {else}
                <input type="hidden" name="kAdminlogingruppe" value="1" />
            {/if}
        <div class="save_wrapper">
            <input type="hidden" name="action" value="account_edit" />
            {if isset($oAccount) && $oAccount->kAdminlogin > 0}
                <input type="hidden" name="kAdminlogin" value="{$oAccount->kAdminlogin}" />
            {/if}
            <input type="hidden" name="save" value="1" />
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
        </div>
    </form>
</div>