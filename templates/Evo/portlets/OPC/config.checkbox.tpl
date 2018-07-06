<div class="checkbox{$class}">
    <label>
        <input type="checkbox" name="{$propname}" value="1" {if $prop == '1'}checked{/if} {if $required}required{/if}>
        {if empty($option)}{$label}{else}{$option}{/if}
    </label>
</div>