{if $isPreview}
    {assign var=data value=['portlet' => $instance->getDataAttribute()]}
{/if}
{assign var=imgAttribs value=$instance->getImageAttributes()}
{image
    src=$imgAttribs.src
    srcset=$imgAttribs.srcset
    sizes=$imgAttribs.srcsizes
    alt=$imgAttribs.alt
    title=$imgAttribs.title
    data=$data|default:null
    fluid=$instance->getProperty('responsive')
    style=$instance->getStyleString()
    class=$instance->getProperty('shape')
}