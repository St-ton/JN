{assign var=cPlugin value=__('plugin')}
{if $oPlugin !== null}
    {include file='tpl_inc/seite_header.tpl' cTitel=$cPlugin|cat:': '|cat:$oPlugin->getMeta()->getName()
        pluginMeta=$oPlugin->getMeta()}
{/if}
<div id="content" class="container-fluid">
    <div class="container2">
        <div id="update-status">
            {include file='tpl_inc/dbupdater_status.tpl' migrationURL='plugin.php' pluginID=$oPlugin->getID()}
            {include file='tpl_inc/dbupdater_scripts.tpl'}
        </div>
        {assign var=hasActiveMenuTab value=false}
        {assign var=hasActiveMenuItem value=false}
        <div class="tabs">
            {if $oPlugin !== null && $oPlugin->getAdminMenu()->getItems()->count() > 0}
                <nav class="tabs-nav">
                    <ul class="nav nav-tabs" role="tablist">
                        {foreach $oPlugin->getAdminMenu()->getItems()->toArray() as $oPluginAdminMenu}
                            <li class="tab-{$oPluginAdminMenu->id} nav-item">
                                <a class="tab-link-{$oPluginAdminMenu->id} nav-link {if (!isset($defaultTabbertab) && $oPluginAdminMenu@index === 0) || (isset($defaultTabbertab) && ($defaultTabbertab === $oPluginAdminMenu->id || $defaultTabbertab === $oPluginAdminMenu->cName))} {assign var=hasActiveMenuTab value=true}active{/if}" data-toggle="tab" role="tab" href="#plugin-tab-{$oPluginAdminMenu->id}">
                                    {$oPluginAdminMenu->cName}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </nav>
                <div class="tab-content">
                    {foreach $oPlugin->getAdminMenu()->getItems()->toArray() as $oPluginAdminMenu}
                        <div id="plugin-tab-{$oPluginAdminMenu->id}" class="settings tab-pane fade {if (!isset($defaultTabbertab) && $oPluginAdminMenu@index === 0) || isset($defaultTabbertab) && ($defaultTabbertab == $oPluginAdminMenu->id || $defaultTabbertab == $oPluginAdminMenu->cName)} {assign var=hasActiveMenuItem value=true}active show{/if}">
                            {$oPluginAdminMenu->html}
                        </div>
                    {/foreach}
                </div>
            {else}
                <div class="alert alert-info" role="alert">
                    <i class="fal fa-info-circle"></i> {__('noPluginDataAvailable')}
                </div>
            {/if}
        </div>
    </div>
</div>
