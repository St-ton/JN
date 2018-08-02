<div {$instance->getAttributeString()} {$instance->getDataAttributeString()}>
    {if $instance->getProperty('video-vendor') === 'youtube'}
        <img src="https://img.youtube.com/vi/{$instance->getProperty('video-yt-id')}/maxresdefault.jpg"
             alt="YouTube Video" class="img-responsive"
             {if !empty($instance->getProperty('video-responsive'))}
                 style="width:100%;"
             {else}
                 style="width: {$instance->getProperty('video-width')}px;
                         height: {$instance->getProperty('video-height')}px"
             {/if}>
    {elseif $instance->getProperty('video-vendor') === 'vimeo'}
        {assign var="imgid" value=$instance->getProperty('video-vim-id')}
        {assign var="hash" value=unserialize(file_get_contents("http://vimeo.com/api/v2/video/$imgid.php"))}
        <img src="{$hash[0].thumbnail_large}" alt="Vimeo Video" class="img-responsive"
             {if !empty($instance->getProperty('video-responsive'))}
                style="width: 100%;"
             {else}
                style="width: {$instance->getProperty('video-width')}px;
                        height: {$instance->getProperty('video-height')}px"
             {/if}>
    {else}
        <div class="text-center" style="width:
            {if !empty($instance->getProperty('video-responsive'))}100%;">
                <img src="gfx/keinBild.gif"><br>
            {else}
                {$instance->getProperty('video-width')}px; height: {$instance->getProperty('video-height')}px">
            {/if}
            <i class="fa fa-film"></i><br> Video
        </div>
    {/if}
</div>