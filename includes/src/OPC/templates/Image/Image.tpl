{$imgAttribs = $instance->getImageAttributes()}

{if $isPreview && empty($imgAttribs.src)}
    <div {$instance->getDataAttributeString()} class="opc-Image"
         style="{$instance->getStyleString()}">
        <div>
            <i class="far fa-image"></i>
            <span>{__('Image')}</span>
        </div>
    </div>
{elseif !empty($imgAttribs.src)}
    {if $isPreview}<div data-portlet="{$instance->getDataAttribute()}" class="opc-Image-with-image">{/if}
        {image
            src=$imgAttribs.src
            srcset=$imgAttribs.srcset
            sizes=$imgAttribs.srcsizes
            alt=$imgAttribs.alt
            title=$imgAttribs.title
            style=$instance->getStyleString()|cat:' display: block; width: 100%'
            rounded=$portlet->getRoundedProp($instance)
            thumbnail=$portlet->getThumbnailProp($instance)
        }
    {if $isPreview}</div>{/if}
{/if}