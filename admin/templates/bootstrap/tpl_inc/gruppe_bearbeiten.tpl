{assign var=cTitel value=__('gruppeNeu')}
{if isset($oAdminGroup) && $oAdminGroup->kAdminlogingruppe > 0}
    {assign var=cTitel value=__('gruppeBearbeiten')}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel cBeschreibung=__('benutzerDesc')}
<div id="content" class="container-fluid">
    <form class="settings navbar-form" action="benutzerverwaltung.php" method="post">
        {$jtl_token}
        <input type="hidden" name="tab" value="group_view" />
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Allgemein</h3>
            </div>
            <div class="panel-body">
                <div class="input-group{if isset($cError_arr.cGruppe)} error{/if}">
                    <span class="input-group-addon"><label for="cGruppe">Name</label></span>
                    <input class="form-control" type="text" name="cGruppe" id="cGruppe" value="{if isset($oAdminGroup->cGruppe)}{$oAdminGroup->cGruppe}{/if}" />
                    {if isset($cError_arr.cGruppe)}<span class="input-group-addon error" title="{__('FillOut')}><i class="fa fa-exclamation-triangle"></i></span>{/if}
                </div>
                <div class="input-group{if isset($cError_arr.cBeschreibung)} error{/if}">
                    <span class="input-group-addon"><label for="cBeschreibung">Beschreibung</label></span>
                    <input class="form-control" type="text" id="cBeschreibung" name="cBeschreibung" value="{if isset($oAdminGroup->cBeschreibung)}{$oAdminGroup->cBeschreibung}{/if}" />
                    {if isset($cError_arr.cBeschreibung)}<span class="input-group-addon error" title="{__('FillOut')}"><i class="fa fa-exclamation-triangle"></i></span>{/if}
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Berechtigungen</h3>
            </div>
            <div class="panel-body">
                {foreach $oAdminDefPermission_arr as $oGroup}
                    <div id="settings-{$oGroup@iteration}" class=" col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading"><h3 class="panel-title">{$oGroup->cName}</h3></div>
                            <div class="perm_list panel-body">
                                {foreach $oGroup->oPermission_arr as $oPerm}
                                    <div class="input">
                                    <input type="checkbox" name="perm[]" value="{$oPerm->cRecht}" id="{$oPerm->cRecht}" {if isset($cAdminGroupPermission_arr) && is_array($cAdminGroupPermission_arr)}{if $oPerm->cRecht|in_array:$cAdminGroupPermission_arr}checked="checked"{/if}{/if} />
                                    <label for="{$oPerm->cRecht}" class="perm">
                                        {if $oPerm->cBeschreibung|strlen > 0}{$oPerm->cBeschreibung}{if isset($bDebug) && $bDebug} - {$oPerm->cRecht}{/if}{else}{$oPerm->cRecht}{/if}
                                    </label>
                                    </div>
                                {/foreach}
                            </div>
                            <div class="panel-footer">
                                <input type="checkbox" onclick="checkToggle('#settings-{$oGroup@iteration}');" id="cbtoggle-{$oGroup@iteration}" /> <label for="cbtoggle-{$oGroup@iteration}">{__('globalSelectAll')}</label>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
            <div class="panel-footer">
                <input type="checkbox" onclick="AllMessages(this.form);" id="ALLMSGS" name="ALLMSGS" /> <label for="ALLMSGS">{__('globalSelectAll')}</label>
            </div>
        </div>

        <div class="panel-footer">
            <div class="btn-group">
                <input type="hidden" name="action" value="group_edit" />
                {if isset($oAdminGroup) && $oAdminGroup->kAdminlogingruppe > 0}
                    <input type="hidden" name="kAdminlogingruppe" value="{$oAdminGroup->kAdminlogingruppe}" />
                {/if}
                <input type="hidden" name="save" value="1" />
                <button type="submit" value="{$cTitel}" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                <a class="btn btn-danger" href="benutzerverwaltung.php?tab=group_view"><i class="fa fa-exclamation"></i> {__('cancel')}</a>
            </div>
        </div>
    </form>
</div>

