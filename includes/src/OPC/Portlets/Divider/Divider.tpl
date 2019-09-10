{$moreLink = $instance->getProperty('moreLink')}
{$moreTitle = $instance->getProperty('moreTitle')}
{$title = $instance->getProperty('title')}
{$id = $instance->getProperty('id')}

<div class="opc-Divider" {if $isPreview}{$instance->getDataAttributeString()}{/if} {if !empty($id)}id="{$id}"{/if}
     {$instance->getAttributeString()}>
    {if !empty($moreLink) && !$isPreview}
        {link class="more float-right" href=$moreLink title=$moreTitle data-toggle="tooltip"
              data=["placement"=>"auto right"] aria=["label"=>$moreTitle]}
            {$title}
        {/link}
    {else}
        {$title}
    {/if}
</div>