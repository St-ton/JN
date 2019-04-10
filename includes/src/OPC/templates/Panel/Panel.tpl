{if $isPreview}
    {assign var=data value=['portlet' => $instance->getDataAttribute()]}
    {assign var=areaClass value='opc-area'}
{/if}
{if $instance->getProperty('panel-state') !== 'default'}
    {assign var=stateClass value=$instance->getProperty('panel-state')}
{/if}
{card no-body=true data=$data|default:null border-variant=$stateClass|default:null
        style=$instance->getStyleString()}
    {if $instance->getProperty('title-flag')}
        {cardheader class=$areaClass|default:null
                    data=['area-id' => 'header']}
            {if $isPreview}
                {$instance->getSubareaPreviewHtml('header')}
            {else}
                {$instance->getSubareaFinalHtml('header')}
            {/if}
        {/cardheader}
    {/if}
    {cardbody class=$areaClass|default:null
              data=['area-id' => 'body']}
        {if $isPreview}
            {$instance->getSubareaPreviewHtml('body')}
        {else}
            {$instance->getSubareaFinalHtml('body')}
        {/if}
    {/cardbody}
    {if $instance->getProperty('footer-flag')}
        {cardfooter class=$areaClass|default:null
                    data=['area-id' => 'footer']}
            {if $isPreview}
                {$instance->getSubareaPreviewHtml('footer')}
            {else}
                {$instance->getSubareaFinalHtml('footer')}
            {/if}
        {/cardfooter}
    {/if}
{/card}