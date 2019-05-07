{$tag = $portlet->getListTag($instance)}

<{$tag} {$instance->getAttributeString()} {if $isPreview}{$instance->getDataAttributeString()}{/if}>
    {for $i = 1 to $instance->getProperty('count')}
        <li {if $isPreview}class="opc-area" data-area-id="li{$i}"{/if}>
            {if $isPreview}
                {$instance->getSubareaPreviewHtml("li{$i}")}
            {else}
                {$instance->getSubareaFinalHtml("li{$i}")}
            {/if}
        </li>
    {/for}
</{$tag}>