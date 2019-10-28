{if $isPreview}
    {assign var=data value=['portlet' => $instance->getDataAttribute()]}
{/if}
{card data=$data|default:null bg-variant='warning'}
    {$portlet->getTitle()}
{/card}