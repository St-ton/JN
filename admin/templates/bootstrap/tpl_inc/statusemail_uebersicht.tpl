{include file='tpl_inc/seite_header.tpl' cTitel=#statusemail# cBeschreibung=#statusemailDesc# cDokuURL=#statusemailURL#}
<div id="content" class="container-fluid">
    <form name="einstellen" method="post" action="statusemail.php">
        {$jtl_token}
        <input type="hidden" name="einstellungen" value="1" />
        <div id="settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#settings#}</h3>
                </div>
                <div class="panel-body">
                    <div class="item input-group">
                        <span class="input-group-addon">
                            <label for="nAktiv">{#statusemailUse#}</label>
                        </span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="nAktiv" id="nAktiv">
                                <option value="1" {if isset($oStatusemailEinstellungen->nAktiv) && $oStatusemailEinstellungen->nAktiv == 1}selected{/if}>Ja</option>
                                <option value="0" {if isset($oStatusemailEinstellungen->nAktiv) && $oStatusemailEinstellungen->nAktiv == 0}selected{/if}>Nein</option>
                            </select>
                        </span>
                        <span class="input-group-addon">
                            {getHelpDesc cDesc=#statusemailUseDesc#}
                        </span>
                    </div>

                    <div class="item input-group">
                        <span class="input-group-addon">
                            <label for="cEmail">{#statusemailEmail#}</label>
                        </span>
                        <input class="form-control" type="text" name="cEmail" id="cEmail" value="{if isset($oStatusemailEinstellungen->cEmail)}{$oStatusemailEinstellungen->cEmail}{/if}" tabindex="1" />
                        <span class="input-group-addon">
                            {getHelpDesc cDesc=#statusemailEmailDesc#}
                        </span>
                    </div>

                    <div class="item input-group">
                        <span class="input-group-addon">
                            <label for="cIntervall">{#statusemailIntervall#}</label>
                        </span>
                        <select name="cIntervall_arr[]" id="cIntervall" multiple="multiple" class="form-control multiple">
                            {foreach name=intervallmoeglich from=$oStatusemailEinstellungen->cIntervallMoeglich_arr key=key item=cIntervallMoeglich}
                                <option value="{$cIntervallMoeglich}"{foreach name=cintervall from=$oStatusemailEinstellungen->nIntervall_arr item=nIntervall}{if $nIntervall == $cIntervallMoeglich} selected{/if}{/foreach}>{$key}</option>
                            {/foreach}
                        </select>
                        <span class="input-group-addon">
                            {getHelpDesc cDesc=#statusemailIntervallDesc#}
                        </span>
                    </div>

                    <div class="item input-group">
                        <span class="input-group-addon">
                            <label for="cInhalt">{#statusemailContent#}</label>
                        </span>
                        <select name="cInhalt_arr[]" id="cInhalt" multiple="multiple" class="form-control multiple">
                            {foreach name=inhaltmoeglich from=$oStatusemailEinstellungen->cInhaltMoeglich_arr key=key item=cInhaltMoeglich}
                                <option value="{$cInhaltMoeglich}"{foreach name=cinhalt from=$oStatusemailEinstellungen->nInhalt_arr item=nInhalt}{if $nInhalt == $cInhaltMoeglich} selected{/if}{/foreach}>{$key}</option>
                            {/foreach}
                        </select>
                        <span class="input-group-addon">
                            {getHelpDesc cDesc=#statusemailContentDesc#}
                        </span>
                    </div>
                </div>
                <div class="panel-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {#statusemailSave#}</button>
                </div>
            </div>
        </div>
    </form>
</div>