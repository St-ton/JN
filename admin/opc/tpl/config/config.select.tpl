{if !isset($propid)}
    {$propid = $propname}
{/if}
<div class="form-group">
    <label for="config-{$propid}">{$propdesc.label}</label>
    <div class="select-wrapper">
        <select class="form-control" id="config-{$propid}" name="{$propname}" {if $required === true}required{/if}>
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
</div>

{if isset($propdesc.childrenFor)}
    <script>
        (function() {
            let selectElm = $('#config-{$propid}');
            let option = selectElm.find(':selected').val();

            selectElm.on('change', () => {
                let option = selectElm.find(':selected').val();

                $('.childrenFor-{$propid}').collapse('hide');
                $('#childrenFor-' + option + '-{$propid}').collapse('show');
            });

            $(() => {
                $('#childrenFor-' + option + '-{$propid}').collapse('show');
            });
        })();
    </script>
{/if}