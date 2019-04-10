<ul class="nav nav-tabs" role="tablist">
    {foreach $tabs as $tabname => $tab}
        {$tabId = 'conftab'|cat:$tab@index}

        <li role="presentation"{if $tab@index === 0} class="active"{/if}>
            <a href="#{$tabId}" aria-controls="{$tabId}" role="tab" data-toggle="tab">
                {$tabname}
            </a>
        </li>
    {/foreach}
</ul>

<div class="tab-content">
    {foreach $tabs as $tabname => $tab}
        {$tabId = 'conftab'|cat:$tab@index}

        <div role="tabpanel" class="tab-pane{if $tab@index === 0} active{/if}" id="{$tabId}">
            <div class="row">
                {$rowWidthAccu = 0}
                {include file='./autoconfig-props.tpl' props=$tab}
            </div>
        </div>
    {/foreach}
</div>