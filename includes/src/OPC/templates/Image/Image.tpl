{if $isPreview}
    {assign var=data value=['portlet' => $instance->getDataAttribute()]}
{/if}

{$imgAttribs = $instance->getImageAttributes()}

{if $isPreview}
    <div {$instance->getDataAttributeString()}
         class="opc-Image {if !empty($imgAttribs.src)}opc-Image-with-image{/if}"
         {if !empty($imgAttribs.src)}style="background-image: url('{$imgAttribs.src}')"{/if}>
        <div>
            <i class="far fa-image"></i>
            <span>{__('Image')}</span>
        </div>
    </div>
{else}
    {image
        src=$imgAttribs.src
        srcset=$imgAttribs.srcset
        sizes=$imgAttribs.srcsizes
        alt=$imgAttribs.alt
        title=$imgAttribs.title
        data=$data|default:null
        fluid=$instance->getProperty('responsive')
        style=$instance->getStyleString()|cat:' display: block'
        rounded=$portlet->getRoundedProp($instance)
        thumbnail=$portlet->getThumbnailProp($instance)
    }
{/if}