{block name='snippets-suggestion'}
    <div class="snippets-suggestion">
        {link href="{$ShopURL}/?qs={$result->keyword}"}
            {$result->keyword} {badge variant="primary" class="float-right"}{$result->quantity}{/badge}
        {/link}
    </div>
{/block}
