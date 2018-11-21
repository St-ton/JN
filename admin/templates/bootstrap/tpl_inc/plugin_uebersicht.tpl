{assign var=cPlugin value=#plugin#}
{if $oPlugin !== null}
    {include file='tpl_inc/seite_header.tpl' cTitel=$cPlugin|cat:": "|cat:$oPlugin->getMeta()->getName() pluginMeta=$oPlugin->getMeta()}
{/if}
<div id="content" class="container-fluid">
    <div class="container2">
        {assign var=hasActiveMenuTab value=false}
        {assign var=hasActiveMenuItem value=false}
        {if $oPlugin !== null && $oPlugin->getAdminMenu()->getItems()->count() > 0}
            <ul class="nav nav-tabs" role="tablist">
                {foreach $customPluginTabs as $oPluginAdminMenu}
                    <li class="tab-{$oPluginAdminMenu->id} tab{if (!isset($defaultTabbertab) && $oPluginAdminMenu@index === 0) || isset($defaultTabbertab) && ($defaultTabbertab == $oPluginAdminMenu->kPluginAdminMenu || $defaultTabbertab == $oPluginAdminMenu->cName)} {assign var=hasActiveMenuTab value=true}active{/if}">
                        <a class="tab-link-{$oPluginAdminMenu->id}" data-toggle="tab" role="tab" href="#plugin-tab-{$oPluginAdminMenu->kPluginAdminMenu}">{$oPluginAdminMenu->cName}</a>
                    </li>
                {/foreach}
            </ul>
            <div class="tab-content">
                {foreach $customPluginTabs as $oPluginAdminMenu}
                    <div id="plugin-tab-{$oPluginAdminMenu->kPluginAdminMenu}" class="settings tab-pane fade {if (!isset($defaultTabbertab) && $oPluginAdminMenu@index === 0) || isset($defaultTabbertab) && ($defaultTabbertab == $oPluginAdminMenu->kPluginAdminMenu || $defaultTabbertab == $oPluginAdminMenu->cName)} {assign var=hasActiveMenuItem value=true}active in{/if}">
                        {$oPluginAdminMenu->html}
                    </div>
                {/foreach}
            </div>
        {else}
            <div class="alert alert-info" role="alert"><i class="fa fa-info-circle"></i> {#noPluginDataAvailable#}</div>
        {/if}
    </div>
</div>