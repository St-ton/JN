<div {$instance->addClass('row')->getAttributeString()} {if $isPreview}{$instance->getDataAttributeString()}{/if}>

    {foreach $portlet->getLayouts($instance) as $i => $colLayout}
        {assign var="areaId" value="col-"|cat:$i}
        <div class="opc-area {$portlet->getColClasses($colLayout)}"
             {if $isPreview}data-area-id="{$areaId}"{/if}>
            {if $isPreview}
                {$instance->getSubareaPreviewHtml($areaId)}
            {else}
                {$instance->getSubareaFinalHtml($areaId)}
            {/if}
        </div>
    {/foreach}

</div>