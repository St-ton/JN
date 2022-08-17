<div class="alert alert-{$propdesc.class|default:'primary'}" role="alert">
    {if !empty($propdesc.text)}
        {$propdesc.text}
    {else}
        Assign hint text for property '{$propname}' with the 'text' field in getPropertyDesc()!
    {/if}
</div>