{$data = $instance->getAnimationData()}

{if $isPreview}
    {$data = $data|array_merge:['portlet' => $instance->getDataAttribute()]}
{/if}
{$classStr = $instance->getAnimationClass()}
{if $instance->getProperty('background-flag') === 'image'}
    {$classStr = $classStr|cat:" parallax-window"}
    {$data = $data|array_merge:['parallax'=> $instance->getAttribute('data-parallax')]}
    {$data = $data|array_merge:['z-index'=> $instance->getAttribute('data-z-index')]}
    {$data = $data|array_merge:['image-src'=> $instance->getAttribute('data-image-src')]}
{/if}

{container
    style=$instance->getStyleString()
    class=$classStr
    data=$data|default:[]
    fluid=($instance->getProperty('background-flag') !== 'boxed')
}
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
{/container}
