<div class='radio'>
    {foreach $options as $value => $name}
        {if empty($inline)}<div class="radio{$class}">{/if}
            <label {if !empty($inline)}class="radio-inline"{/if}>
                <input type="radio" name="{$propname}" value="{$value}"
                       {if $prop === $value}checked{/if}
                       {if $required}required{/if}> {$name}
            </label>
        {if empty($inline)}</div>{/if}
    {/foreach}
</div>