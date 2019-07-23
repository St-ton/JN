{assign var=title value=__('pluginverwaltungLicenceKeyInput')}
{include file='tpl_inc/seite_header.tpl' cTitel=$title|cat:': '|cat:$oPlugin->getMeta()->getName() cBeschreibung=__('pluginverwaltungDesc')}
<div id="content">
    <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
        {$jtl_token}
        <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
        <input type="hidden" name="lizenzkeyadd" value="1" />
        <input type="hidden" name="kPlugin" value="{$kPlugin}" />

        <div class="input-group">
            <span class="input-group-addon">
                <label for="cKey">{__('pluginverwaltungLicenceKey')}</label>
            </span>
            <input id="cKey" placeholder="{__('pluginverwaltungLicenceKey')}" class="form-control" name="cKey" type="text" value="{if isset($oPlugin->cLizenz)}{$oPlugin->cLizenz}{/if}" />
            <span class="input-group-btn">
                <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary">{__('saveWithIcon')}</button>
            </span>
        </div>
    </form>
</div>
