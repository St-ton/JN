{if isset($Sektion) && $Sektion}
    {assign var="cTitel" value=#preferences#|cat:": "|cat:$Sektion->cName}
    {if isset($cSearch) && $cSearch|count_characters  > 0}
        {assign var="cTitel" value=$cSearch}
    {/if}
    {include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=$cPrefDesc cDokuURL=$cPrefURL}
{/if}
{if !isset($action) || !$action}
    {assign var="action" value="einstellungen.php"}
{/if}
<div id="content" class="container-fluid">
    <div id="settings">
        <form name="einstellen" method="post" action="{$action}" class="navbar-form">
            {$jtl_token}
            <input type="hidden" name="einstellungen_bearbeiten" value="1" />
            {if isset($cSuche) && $cSuche|count_characters > 0}
                <input type="hidden" name="cSuche" value="{$cSuche}" />
                <input type="hidden" name="einstellungen_suchen" value="1" />
            {/if}
            <input type="hidden" name="kSektion" value="{$kEinstellungenSektion}" />
            {if isset($Conf) && $Conf|@count > 0}
                {foreach name=conf from=$Conf item=cnf}
                    {if $cnf->cConf === 'Y'}
                        <div class="input-group {if isset($cSuche) && $cnf->kEinstellungenConf == $cSuche} highlight{/if}">
                            <span class="input-group-addon">
                                <label for="{$cnf->cWertName}">{$cnf->cName}</label>
                            </span>
                            <span class="input-group-wrap">
                            {if $cnf->cInputTyp === 'selectbox'}
                                <select class="form-control" name="{$cnf->cWertName}" id="{$cnf->cWertName}">
                                    {foreach name=selectfor from=$cnf->ConfWerte item=wert}
                                        <option value="{$wert->cWert}" {if $cnf->gesetzterWert==$wert->cWert}selected{/if}>{$wert->cName}</option>
                                    {/foreach}
                                </select>
                            {elseif $cnf->cInputTyp === 'listbox'}
                                <select name="{$cnf->cWertName}[]" id="{$cnf->cWertName}" multiple="multiple" class="form-control combo">
                                    {foreach name=selectfor from=$cnf->ConfWerte item=wert}
                                        <option value="{$wert->cWert}" {foreach name=werte from=$cnf->gesetzterWert item=gesetzterWert}{if $gesetzterWert->cWert == $wert->cWert}selected{/if}{/foreach}>{$wert->cName}</option>
                                    {/foreach}
                                </select>
                            {elseif $cnf->cInputTyp === 'pass'}
                                <input class="form-control" autocomplete="off" type="password" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                            {elseif $cnf->cInputTyp === 'number'}
                                <input class="form-control" type="number" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{if isset($cnf->gesetzterWert)}{$cnf->gesetzterWert}{/if}" tabindex="1" />
                            {else}
                                <input class="form-control" type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{if isset($cnf->gesetzterWert)}{$cnf->gesetzterWert}{/if}" tabindex="1" />
                            {/if}
                            </span>
                            <span class="input-group-addon">
                                {if $cnf->cBeschreibung}
                                    {getHelpDesc cDesc=$cnf->cBeschreibung cID=$cnf->kEinstellungenConf}
                                {/if}
                            </span>
                        </div>
                    {else}
                        {if $smarty.foreach.conf.index !== 0}
                            </div>
                        </div>
                        {/if}
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">{$cnf->cName} <span class="pull-right">{getHelpDesc cID=$cnf->kEinstellungenConf}</span>{if !empty($cnf->cSektionsPfad)} <span class="path right"><strong>{#settingspath#}:</strong> {$cnf->cSektionsPfad} </span> {/if}</h3>
                            </div>
                            <div class="panel-body">
                    {/if}
                {/foreach}
                    </div>
                </div>
                <div class="save_wrapper">
                    <button type="submit" value="{#savePreferences#}" class="btn btn-primary"><i class="fa fa-save"></i> Speichern</button>
                </div>
            {else}
                <p class="alert alert-info">{#noSearchResult#}</p>
            {/if}
        </form>
    </div>
</div>