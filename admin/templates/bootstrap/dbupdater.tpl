{config_load file="$lang.conf" section='dbupdater'}
{config_load file="$lang.conf" section='shopupdate'}
{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('dbupdater') cBeschreibung=__('dbupdaterDesc') cDokuURL=__('dbupdaterURL')}
<div id="content" class="container-fluid">
    <div class="container-fluid2">
        <div id="resultLog" {if !$updatesAvailable}style="display: none;"{/if}>
            <h4>{__('eventProtocol')}</h4>
            <pre id="debug">
{__('currentShopVersion')}
    {__('system')}: {$currentFileVersion}
    {__('database')}: {$currentDatabaseVersion}
{if $currentTemplateFileVersion != $currentTemplateDatabaseVersion}
    {__('currentTemplateVersion')}
        {__('system')}: {$currentTemplateFileVersion}
        {__('database')}: {$currentTemplateDatabaseVersion}
{/if}</pre>
            <br /><br />
        </div>
        <div id="update-status">
            {include file='tpl_inc/dbupdater_status.tpl'}
        </div>
    </div>
</div>

{include file='tpl_inc/dbupdater_scripts.tpl'}
{include file='tpl_inc/footer.tpl'}
