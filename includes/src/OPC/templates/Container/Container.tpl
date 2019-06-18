<div
    style="{$instance->getStyleString()}"
    class="{$instance->getAnimationClass()}"
    {$instance->getAnimationDataAttributeString()}
    {if $isPreview}{$instance->getDataAttributeString()}{/if}
>
    {if $instance->getProperty('background-flag') === 'video' && !empty($instance->getProperty('video-src'))}
        <video autoplay="autoplay"
               poster="{$instance->getProperty('video-poster-url')}" loop="loop" muted="muted"
               style="display: inherit; width: 100%; position: absolute; opacity: 0.7;">
            {if !$isPreview}
                <source src="{$instance->getProperty('video-src-url')}" type="video/mp4">
            {/if}
        </video>
    {/if}
    <div {if $isPreview}class='opc-area' data-area-id='container'{/if} style="position: relative;">
        {if $isPreview}
            {$instance->getSubareaPreviewHtml('container')}
        {else}
            {$instance->getSubareaFinalHtml('container')}
        {/if}
    </div>
</div>
