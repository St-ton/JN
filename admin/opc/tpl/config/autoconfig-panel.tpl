<div class="navbar">
    <div class="tabs">
        {foreach $tabs as $tabname => $tab}
            {$tabId = 'conftab'|cat:$tab@index}

            <button data-tab="{$tabId}" {if $tab@index === 0}class="active"{/if}>
                {$tabname}
            </button>
        {/foreach}
    </div>
</div>

<div class="tab-content">
    {foreach $tabs as $tabname => $tab}
        {$tabId = 'conftab'|cat:$tab@index}

        <div class="tab-pane fade {if $tab@index === 0}show active{/if}" id="{$tabId}">
            <div class="row">
                {$rowWidthAccu = 0}
                {include file='./autoconfig-props.tpl' props=$tab}
            </div>
        </div>
    {/foreach}
</div>