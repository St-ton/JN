<div class='form-group'>
    <label for="config-{$propname}">{$propdesc.label}</label>
    <select class="form-control" id="config-{$propname}" name="{$propname}" {if $required === true}required{/if}>
        {foreach $propdesc.options as $value => $label}
            {if is_string($label)}
                <option value="{$value}" {if $value == $propval}selected{/if}>
                    {$label}
                </option>
            {else}
                {$subgroup = $label}

                <optgroup label="{$subgroup.label}">
                    {foreach $subgroup.options as $value => $label}
                        <option value="{$value}" {if $value == $propval}selected{/if}>
                            {$label}
                        </option>
                    {/foreach}
                </optgroup>
            {/if}
        {/foreach}
    </select>
</div>

{if isset($propdesc.childrenFor)}
    <script>
        var selectElm = $('#config-{$propname}');
        var option = selectElm.find(':selected').val();

        selectElm.on('change', function() {
            var option = selectElm.find(':selected').val();

            $('.childrenFor-{$propname}').collapse('hide');
            $('#childrenFor-' + option + '-{$propname}').collapse('show');
        });

        $(function() {
            $('#childrenFor-' + option + '-{$propname}').collapse('show');
        });
    </script>
{/if}