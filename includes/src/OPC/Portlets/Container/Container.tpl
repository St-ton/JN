{$style = "{$instance->getStyleString()};min-height:{$instance->getProperty('min-height')}px; position:relative;"}
{$class = 'opc-Container '|cat:$instance->getAnimationClass()}
{$data  = $instance->getAnimationData()}
{$fluid = $instance->getProperty('boxed') === false}

{if $isPreview}
    {$data = $data|array_merge:['portlet' => $instance->getDataAttribute()]}
{/if}

{if $instance->getProperty('background-flag') === 'image' && !empty($instance->getProperty('src'))}
    {$name = basename($instance->getProperty('src'))}
    {$class = "{$class} parallax-window"}
    {$v = $instance->getImageAttributes("{Shop::getURL()}/{$smarty.const.PFAD_MEDIAFILES}Bilder/.xs/{$name}")}
    {if $isPreview}
        {$style = "{$style} background-image:url('{Shop::getURL()}/{$smarty.const.PFAD_MEDIAFILES}Bilder/.xs/{$name}');"}
        {$style = "{$style} background-size:cover;"}
    {else}
        {$data = $data|array_merge:[
            'parallax'  => 'scroll',
            'z-index'   => '1',
            'image-src' => "{Shop::getURL()}/{$smarty.const.PFAD_MEDIAFILES}Bilder/.lg/{$name}"
        ]}
    {/if}
{/if}

{if $instance->getProperty('background-flag') === 'video'}
    {$style          = "{$style} overflow:hidden;"}
    {$name           = basename($instance->getProperty('video-poster'))}
    {$videoPosterUrl = "{Shop::getURL()}/{$smarty.const.PFAD_MEDIAFILES}Bilder/.xs/{$name}"}
    {$name           = basename($instance->getProperty('video-src'))}
    {$videoSrcUrl    = "{Shop::getURL()}/{$smarty.const.PFAD_MEDIAFILES}Videos/{$name}"}
{/if}

{container style=$style class=$class data=$data fluid=$fluid}
    {if $instance->getProperty('background-flag') === 'video' && !empty($instance->getProperty('video-src'))}
        <video autoplay loop muted poster="{$videoPosterUrl}"
               style="display: inherit; width: 100%; position: absolute; opacity: 0.7;">
            {if !$isPreview}
                <source src="{$videoSrcUrl}" type="video/mp4">
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
