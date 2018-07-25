<div {$instance->getAttributeString()} {if $isPreview}{$instance->getDataAttributeString()}{/if}>
    <ul class="nav nav-tabs" role="tablist">
        {foreach $instance->getProperty('tabs') as $i => $tabTitle}
            {assign var="areaId" value="tab"|cat:$i}
            <li role="presentation"{if $i === 0} class="active"{/if}>
                <a href="#{$areaId}" aria-controls="home" role="tab" data-toggle="tab">
                    {$tabTitle}
                </a>
            </li>
        {/foreach}
    </ul>
    <div class="tab-content">
        {foreach $instance->getProperty('tabs') as $i => $tabTitle}
            {assign var="areaId" value="tab"|cat:$i}
            <div role="tabpanel" class="tab-pane{if $i === 0} active{/if} opc-area"
                 id="{$areaId}" {if $isPreview}data-area-id="{$areaId}"{/if}>
                {if $isPreview}
                    {$instance->getSubareaPreviewHtml($areaId)}
                {else}
                    {$instance->getSubareaFinalHtml($areaId)}
                {/if}
            </div>
        {/foreach}
    </div>
</div>