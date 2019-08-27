<div {$instance->getAttributeString()}
     {if $isPreview}{$instance->getDataAttributeString()}{/if}
     class="opc-Tabs">
    {tabs}
        {foreach $instance->getProperty('tabs') as $i => $tabTitle}
            {$tabId = $instance->getUid()|cat:'-'|cat:$i}
            {$areaId = 'tab-'|cat:$tabId}
            {tab id=$tabId title=$tabTitle active=$i==0}
                <div data-area-id="{$areaId}" class="opc-area">
                    {if $isPreview}
                        {$instance->getSubareaPreviewHtml($areaId)}
                    {else}
                        {$instance->getSubareaFinalHtml($areaId)}
                    {/if}
                </div>
            {/tab}
        {/foreach}
    {/tabs}
</div>