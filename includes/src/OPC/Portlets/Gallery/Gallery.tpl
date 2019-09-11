{if $isPreview}
    {$data = ['portlet' => $instance->getDataAttribute()]}
    {$dataStr = $instance->getDataAttributeString()}
{/if}

{$galleryStyle = $instance->getProperty('galleryStyle')}
{$images = $instance->getProperty('images')}

{if $isPreview && empty($images)}
    <div data-portlet="{$instance->getDataAttribute()}" class="opc-Gallery"
         style="{$instance->getStyleString()}">
        <div>
            {file_get_contents($portlet->getBaseUrl()|cat:'icon.svg')}
            <span>{__('Gallery')}</span>
        </div>
    </div>
{elseif $galleryStyle === 'columns'}
    <div class="gallery-columns" {$dataStr|default:''}
         id="{$instance->getUid()}"
         style="{$instance->getStyleString()}">
        {foreach $images as $key => $image}
            {$imgAttribs = $instance->getImageAttributes($image.url, $image.alt, '')}
            <a {if $isPreview === false}
                    {if $image.action === 'link'}
                        href="{$image.link}"
                    {elseif $image.action === 'lightbox'}
                        href="{$imgAttribs.src}"
                    {/if}
               {/if} class="img-gallery-btn {if $image.action === 'lightbox'}img-gallery-active-btn{/if}"
               data-caption="{$image.desc}">
                {image class='img-gallery-img'
                       srcset=$imgAttribs.srcset
                       sizes=$imgAttribs.srcsizes
                       src=$imgAttribs.src
                       alt=$imgAttribs.alt
                       title=$imgAttribs.title}
                {if $image.action === 'lightbox'}
                    <i class="img-gallery-zoom fa fa-search fa-2x"></i>
                {/if}
            </a>
        {/foreach}
    </div>
{else}
    {row
        id=$instance->getUid()
        class='img-gallery img-gallery-'|cat:$galleryStyle
        data=$data|default:null
        style=$instance->getStyleString()
    }
        {$xsSum = 0}
        {$smSum = 0}
        {$mdSum = 0}
        {$xlSum = 0}
        {foreach $images as $key => $image}
            {if $galleryStyle === 'alternate'}
                {if $image@last}
                    {$image.xs = 12 - $xsSum % 12}
                    {$image.sm = 12 - $smSum % 12}
                    {$image.md = 12 - $mdSum % 12}
                    {$image.xl = 12 - $xlSum % 12}
                {else}
                    {$image.xs = 6}
                    {$image.sm = 5}
                    {$image.md = 3}
                    {$image.xl = 3}
                    {if $key % 3 === 0}
                        {$image.xs = 12}
                    {/if}
                    {if $key % 4 === 0 || $key % 4 === 3}
                        {$image.sm = 7}
                    {/if}
                    {if $key % 6 === 0 || $key % 6 === 5}
                        {$image.md = 5}
                    {elseif $key % 6 === 1 || $key % 6 === 4}
                        {$image.md = 4}
                    {/if}
                    {if $key % 8 === 0 || $key % 8 === 7}
                        {$image.xl = 4}
                    {elseif $key % 8 === 1 || $key % 8 === 5}
                        {$image.xl = 2}
                    {/if}
                {/if}
                {$xsSum = $xsSum + $image.xs}
                {$smSum = $smSum + $image.sm}
                {$mdSum = $mdSum + $image.md}
                {$xlSum = $xlSum + $image.xl}
            {elseif $galleryStyle === 'grid'}
                {$image.xs = 6}
                {$image.sm = 4}
                {$image.md = 3}
                {$image.xl = 2}
            {/if}

            {$image.lg = $image.md}

            {$imgAttribs = $instance->getImageAttributes($image.url, $image.alt, '',['xs'=>$image.xs,'sm'=>$image.sm,'md'=>$image.md,'lg'=>$image.lg,'xl'=>$image.xl])}
            {col cols=$image.xs sm=$image.sm md=$image.md lg=$image.lg xl=$image.xl class="img-gallery-item"}
                <a {if $isPreview === false}
                        {if $image.action === 'link'}
                            href="{$image.link}"
                        {elseif $image.action === 'lightbox'}
                            href="{$imgAttribs.src}"
                        {/if}
                    {/if}
                   class="img-gallery-btn {if $image.action === 'lightbox'}img-gallery-active-btn{/if}"
                   data-caption="{$image.desc}"
                   aria-label="{$image.alt}"
                >
                    {image class='img-gallery-img'
                           srcset=$imgAttribs.srcset
                           sizes=$imgAttribs.srcsizes
                           src=$imgAttribs.src
                           alt=$imgAttribs.alt
                           title=$imgAttribs.title}
                    {if $image.action === 'lightbox'}
                        <i class="img-gallery-zoom fa fa-search fa-2x"></i>
                    {/if}
                </a>
            {/col}
        {/foreach}
    {/row}
{/if}

{if $isPreview === false}
    {inline_script}<script>
        var initGallery = function() {
            $('#{$instance->getUid()}').slickLightbox({
                itemSelector: '.img-gallery-active-btn',
                caption: 'caption',
                lazy: true,
            });
        };

        $(initGallery);
    </script>{/inline_script}
{/if}