<div{$attribString}{$styleString}>
    <label>{$properties['video-title']}</label>
    {if $properties['video-vendor'] === 'youtube'}
        <div{if $properties['video-yt-responsive']} class="embed-responsive embed-responsive-16by9"{/if}>
            <iframe {strip}src="https://www.youtube.com/embed/{$properties['video-yt-id']}
                ?autoplay={$properties['video-yt-autoplay']}
                &controls={$properties['video-yt-controls']}
                &loop={$properties['video-yt-loop']}
                &rel={$properties['video-yt-rel']}
                &showinfo=0&color={$properties['video-yt-color']}
                &iv_load_policy=3
                &playlist={$properties['video-yt-playlist']}
                {if !empty($properties['video-yt-start'])}&start={$properties['video-yt-start']}{/if}
                {if !empty($properties['video-yt-end'])}&end={$properties['video-yt-end']}{/if}"{/strip}
                type="text/html"
                {if $properties['video-yt-responsive']}
                    class="embed-responsive-item"
                {else}
                    width="{$properties['video-yt-width']}"
                    height="{$properties['video-yt-height']}"
                {/if}
                frameborder="0" allowfullscreen></iframe>
        </div>
    {else}
        <div{if $properties['video-vim-responsive']} class="embed-responsive embed-responsive-16by9"{/if}>
            <iframe {strip}src="https://player.vimeo.com/video/{$properties['video-vim-id']}
                ?color={$properties['video-vim-color']|replace:'#':''}
                &portrait={$properties['video-vim-img']}
                &autoplay={$properties['video-vim-autoplay']}
                &title={$properties['video-vim-title']}
                &byline={$properties['video-vim-byline']}
                &loop={$properties['video-vim-loop']}"{/strip}
                frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen
                {if $properties['video-vim-responsive']}
                    class="embed-responsive-item"
                {else}
                    width="{$properties['video-vim-width']}" height="{$properties['video-vim-height']}"
                {/if}></iframe>
        </div>
    {/if}
</div>
