{if $isPreview}
    {assign var=data value=['portlet' => $instance->getDataAttribute()]}
{/if}
{image
    src=$instance->getProperty('src')
    data=$data|default:null fluid=$instance->getProperty('responsive')}