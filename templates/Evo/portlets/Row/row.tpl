{if $isPreview}
    {assign var=dataValue value=['portlet' => $instance->getDataAttribute()]}
    {assign var=classValue value='opc-area'}
{/if}
{row data=$dataValue|default:[]
     class=$instance->getAttribute('class')
     style=$instance->getStyleString()|default:null}
    {foreach $portlet->getLayouts($instance) as $i => $colLayout}
        {assign var=areaId value="col-$i"}
        {col class=$classValue|default:null
                 cols=$colLayout.xs|default:false
                 sm=$colLayout.sm|default:false
                 md=$colLayout.md|default:false
                 lg=$colLayout.lg|default:false
                 data=['area-id' => $areaId]}
            {if $isPreview}
                {$instance->getSubareaPreviewHtml($areaId)}
            {else}
                {$instance->getSubareaFinalHtml($areaId)}
            {/if}
        {/col}
        {$portlet->getDividers($colLayout)}
    {/foreach}
{/row}
