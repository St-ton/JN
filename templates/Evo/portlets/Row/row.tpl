{if $isPreview}
    {assign var=data value=['portlet' => $instance->getDataAttribute()]}
    {assign var=areaClass value='opc-area'}
{/if}
{row data=$data|default:[]
     class=$instance->getAttribute('class')
     style=$instance->getStyleString()|default:null}
    {foreach $portlet->getLayouts($instance) as $i => $colLayout}
        {assign var=areaId value="col-$i"}
        {col class=$areaClass|default:null
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
