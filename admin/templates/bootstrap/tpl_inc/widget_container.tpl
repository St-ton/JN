<ul id="{$eContainer}" class="column">
    {foreach from=$oActiveWidget_arr item=oWidget}
        {if $oWidget->eContainer == $eContainer}
            <li id="widget-{$oWidget->cTitle|replace:" ":"-"|replace:"(":""|replace:")":""|replace:"/":""|replace:"--":"-"|lower}" class="widget panel panel-default" ref="{$oWidget->kWidget}">
                <div class="widget-head panel-heading">
                    <h4>{$oWidget->cTitle}</h4>
                    <span class="options"></span>
                </div>
                <div class="widget-content panel-body{if !$oWidget->bExpanded} widget-hidden{/if}">
                    {$oWidget->cContent}
                </div>
            </li>
        {/if}
    {/foreach}
</ul>