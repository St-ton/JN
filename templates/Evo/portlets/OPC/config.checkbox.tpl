<div class="checkbox{$class}">
    <label>
        <input id="config-{$propname}" type="checkbox" name="{$propname}" value="1" {if $prop == '1'}checked{/if} {if $required}required{/if}>
        {if !empty($option)}{$option}{/if}
    </label>
</div>