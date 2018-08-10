<select class="form-control {$class}" name="{$propname}" {if $required}required{/if}>
    {foreach $options as $key => $val}
        {if stripos($key, 'optgroup') !== false}
            <optgroup label="{$val.label}">
                {foreach $val['options'] as $gr_option}
                    <option value="{$gr_option}" {if $prop === $gr_option}selected{/if}>
                        {$gr_option}
                    </option>
                {/foreach}

            </optgroup>
        {else}
            <option value="{$key}" {if $prop == $key}selected{/if}>{$val}</option>
        {/if}
    {/foreach}
</select>