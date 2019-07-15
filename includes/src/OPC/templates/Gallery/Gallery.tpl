{if $isPreview}
    {$data = ['portlet' => $instance->getDataAttribute()]}
    {$dataStr = $instance->getDataAttributeString()}
{/if}

{$galleryStyle = $instance->getProperty('galleryStyle')}

{if $galleryStyle === 'columns'}
    <div class="gallery-columns" {$dataStr|default:''}
         id="{$instance->getUid()}">
        {foreach $instance->getProperty('images') as $key => $image}
            {$imgAttribs = $instance->getImageAttributes($image.url, '', '')}
            <a href="{$imgAttribs.src}" class="img-gallery-btn">
                {image class='img-gallery-img'
                       srcset=$imgAttribs.srcset
                       sizes=$imgAttribs.srcsizes
                       src=$imgAttribs.src
                       data=['index' => $key, 'desc' => $image.desc]
                       alt=$imgAttribs.alt
                       title=$imgAttribs.title}
                <i class="img-gallery-zoom fa fa-search fa-2x"></i>
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
        {foreach $instance->getProperty('images') as $key => $image}
            {$imgAttribs = $instance->getImageAttributes($image.url, '', '')}

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

            {col cols=$image.xs sm=$image.sm md=$image.md lg=$image.lg xl=$image.xl class="img-gallery-item"}
                <a href="{$imgAttribs.src}" class="img-gallery-btn img-gallery-active-btn">
                    {image class='img-gallery-img'
                           srcset=$imgAttribs.srcset
                           sizes=$imgAttribs.srcsizes
                           src=$imgAttribs.src
                           data=['index' => $key, 'desc' => $image.desc]
                           alt=$imgAttribs.alt
                           title=$imgAttribs.title}
                    <i class="img-gallery-zoom fa fa-search fa-2x"></i>
                </a>
            {/col}
        {/foreach}
    {/row}
{/if}

{if $isPreview === false}
    <script>
        $(function() {
            $('#{$instance->getUid()}').slickLightbox({
                itemSelector: '.img-gallery-active-btn'
            });
        });
    </script>
{/if}