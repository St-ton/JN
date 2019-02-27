{if $isPreview}
    {$dataAttrib = ['portlet' => $instance->getDataAttribute()]}
{/if}

{link
    data=$dataAttrib|default:null
    style='display: block;'
}
    Helo
{/link}