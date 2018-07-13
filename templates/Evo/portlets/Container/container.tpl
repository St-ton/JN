<div id="{$instance->getProperty('uid')}" {$instance->getAttributeString()} {if $isPreview}{$instance->getDataAttributeString()}{/if}>
    {if $instance->getProperty('background-flag') === 'video' && !empty($instance->getProperty('video-src'))}
        <video class="media media--height" autoplay="autoplay"
               poster="{$instance->getProperty('video-poster-url')}" loop="loop" muted="muted"
               style="display: inherit; width: 100%; position: absolute; z-index: 1;opacity: 0.5;">
            {if !$isPreview}
                <source src="{$instance->getProperty('video-src-url')}" type="video/mp4">
            {/if}
        </video>
    {/if}
    <div {if $isPreview}class='opc-area' data-area-id='cntr-0' {/if}style="position: relative; z-index: 2;">
        {if $isPreview}
            {$instance->getSubareaPreviewHtml('cntr-0')}
        {else}
            {$instance->getSubareaFinalHtml('cntr-0')}
        {/if}
    </div>
</div>