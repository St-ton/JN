<div {$instance->getAttributeString()}>
    {if !empty($instance->getProperty('video-title'))}
        <label>{$instance->getProperty('video-title')}</label>
    {/if}
    {if $instance->getProperty('video-vendor') === 'youtube'}
        <div{if $instance->getProperty('video-yt-responsive')} class="embed-responsive embed-responsive-16by9"{/if}>
            <iframe {strip}src="https://www.youtube.com/embed/{$instance->getProperty('video-yt-id')}
                ?controls={$instance->getProperty('video-yt-controls')}
                &loop={$instance->getProperty('video-yt-loop')}
                &rel={$instance->getProperty('video-yt-rel')}
                &showinfo=0&color={$instance->getProperty('video-yt-color')}
                &iv_load_policy=3
                &playlist={$instance->getProperty('video-yt-playlist')}
                {if !empty($instance->getProperty('video-yt-start'))}&start={$instance->getProperty('video-yt-start')}{/if}
                {if !empty($instance->getProperty('video-yt-end'))}&end={$instance->getProperty('video-yt-end')}{/if}"{/strip}
                    type="text/html"
                    {if $instance->getProperty('video-yt-responsive')}
                        class="embed-responsive-item"
                    {else}
                        width="{$instance->getProperty('video-yt-width')}"
                        height="{$instance->getProperty('video-yt-height')}"
                    {/if}
                    frameborder="0" allowfullscreen></iframe>
        </div>
    {else}
        <div{if $instance->getProperty('video-vim-responsive')} class="embed-responsive embed-responsive-16by9"{/if}>
            <iframe {strip}src="https://player.vimeo.com/video/{$instance->getProperty('video-vim-id')}
                ?color={$instance->getProperty('video-vim-color')|replace:'#':''}
                &portrait={$instance->getProperty('video-vim-img')}
                &title={$instance->getProperty('video-vim-title')}
                &byline={$instance->getProperty('video-vim-byline')}
                &loop={$instance->getProperty('video-vim-loop')}"{/strip}
                    frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen
                    {if $instance->getProperty('video-vim-responsive')}
                class="embed-responsive-item"
                    {else}
                width="{$instance->getProperty('video-vim-width')}" height="{$instance->getProperty('video-vim-height')}"
                    {/if}></iframe>
        </div>
    {/if}
</div>
