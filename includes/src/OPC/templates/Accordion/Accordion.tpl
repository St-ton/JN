{if $isPreview}
    {$data = ['portlet' => $instance->getDataAttribute()]}
{/if}

{$uid = $instance->getUid()}

{accordion
    id=$uid
    data=$data|default:null
    style=$instance->getStyleString()
}
    {foreach $instance->getProperty('groups') as $i => $group}
        {$groupId = $uid|cat:'-'|cat:$i}
        {$areaId = 'group-'|cat:$i}

        {if $i === 0 && $instance->getProperty('expanded') === true}
            {$ariaExpanded = 'true'}
        {else}
            {$ariaExpanded = 'false'}
        {/if}

        {card no-body=true}
            {cardheader id='heading-'|cat:$groupId}
                <h2 style="margin-bottom: 0">
                    {button
                        variant='link'
                        data=['toggle' => 'collapse', 'target' => '#'|cat:$groupId, 'parent' => '#'|cat:$uid]
                        aria=['expanded' => $ariaExpanded, 'controls' => $groupId]
                    }
                        {$group}
                    {/button}
                </h2>
            {/cardheader}
            {collapse
                id=$groupId
                visible = $i === 0 && $instance->getProperty('expanded') === true
                data=['parent' => '#'|cat:$uid]
                aria=['labelledby' => 'heading-'|cat:$groupId]
            }
                {cardbody class='opc-area' data=['area-id' => $areaId]}
                    {if $isPreview}
                        {$instance->getSubareaPreviewHtml($areaId)}
                    {else}
                        {$instance->getSubareaFinalHtml($areaId)}
                    {/if}
                {/cardbody}
            {/collapse}
        {/card}
    {/foreach}
{/accordion}