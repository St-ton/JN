<div class="checkbox">
    <label>
        <input type="hidden" value="0" name="{$propname}">
        <input type="checkbox" value="1" id="config-{$propname}" name="{$propname}"
                {if $propval == '1'}checked{/if} {if $required === true}required{/if}>
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