{$data = $instance->getAnimationData()}

{if $isPreview}
    {$areaClass = 'opc-area'}
{/if}

{row data=$data|default:[]
     class=$instance->getAnimationClass()|cat:' '|cat:$instance->getStyleClasses()
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
        {foreach $colLayout.divider as $size => $value}
            {if !empty($value)}
                {clearfix visible-size=$size}
            {/if}
        {/foreach}
    {/foreach}
{/row}
