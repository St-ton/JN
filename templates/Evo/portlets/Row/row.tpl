<div {$instance->addClass('row')->getAttributeString()} {if $isPreview}{$instance->getDataAttributeString()}{/if}>
    {foreach $portlet->getLayouts($instance) as $i => $colLayout}
        {assign var="areaId" value="col-"|cat:$i}
        <div class="{if $isPreview}opc-area {/if}{$portlet->getColClasses($colLayout)}"
             {if $isPreview}data-area-id="{$areaId}"{/if}>
            {if $isPreview}
                {$instance->getSubareaPreviewHtml($areaId)}
            {else}
                {$instance->getSubareaFinalHtml($areaId)}
            {/if}
        </div>
        {$portlet->getDividers($colLayout)}
    {/foreach}
</div>