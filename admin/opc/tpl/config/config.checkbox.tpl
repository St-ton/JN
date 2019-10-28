<div class="form-group checkbox-standalone">
    <input type="hidden" value="0" name="{$propname}">
    <input type="checkbox" id="config-{$propname}" value="1" name="{$propname}"
           {if $propval == '1'}checked{/if} {if $required === true}required{/if}>
    <label for="config-{$propname}">
        {$propdesc.label}
    </label>
</div>

{if isset($propdesc.children)}
    <script>
        $('#config-{$propname}').on('change', function() {
            if (this.checked === true) {
                $('#children-{$propname}').collapse('show');
            } else {
                $('#children-{$propname}').collapse('hide');
            }
        });

        $(function() {
            {if $propval == '1'}
                $('#children-{$propname}').collapse('show');
            {else}
                $('#children-{$propname}').collapse('hide');
            {/if}
        });
    </script>
{/if}