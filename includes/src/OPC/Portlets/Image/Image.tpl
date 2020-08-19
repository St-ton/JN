{$imgAttribs = $instance->getImageAttributes()}

{if $isPreview && empty($imgAttribs.src)}
    <div class="opc-Image" style="{$instance->getStyleString()}">
        <div>
            <i class="far fa-image"></i>
            <span>{__('Image')}</span>
        </div>
    </div>
{elseif !empty($imgAttribs.src)}
    {$alignCSS = ''}
    {if $instance->getProperty('align') === 'left'}
        {$alignCSS = 'margin-right: auto;'}
    {elseif $instance->getProperty('align') === 'right'}
        {$alignCSS = 'margin-left: auto;'}
    {elseif $instance->getProperty('align') === 'center'}
        {$alignCSS = 'margin-left: auto;margin-right: auto;'}
    {/if}
    {if $isPreview}<div class="opc-Image-with-image">{/if}
    <div style="max-width: {$imgAttribs.realWidth}px; {$alignCSS}">
        {image
            src=$imgAttribs.src
            srcset=$imgAttribs.srcset
            sizes=$imgAttribs.srcsizes
            alt=$imgAttribs.alt|escape:'html'
            title=$imgAttribs.title
            style=$instance->getStyleString()|cat:' display: block; width: 100%'
            rounded=$portlet->getRoundedProp($instance)
            thumbnail=$portlet->getThumbnailProp($instance)
            class=$instance->getStyleClasses()
        }
    </div>
    {if $isPreview}</div>{/if}
{/if}