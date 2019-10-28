<div class="form-group">
    <label for="config-{$propname}">{$propdesc.label}</label>
    <div class="radio" id="config-{$propname}">
        {foreach $propdesc.options as $value => $name}
            <div>
                <input type="radio" name="{$propname}" value="{$value}" id="config-{$propname}-{$name@index}"
                       {if $propval == $value}checked{/if} {if $required}required{/if}>
                <label for="config-{$propname}-{$name@index}">{$name}</label>
            </div>
        {/foreach}
    </div>
</div>

{if isset($propdesc.childrenFor)}
    <script>
        var selectElm = $('#config-{$propname}');
        var option = selectElm.find(':checked').val();

        selectElm.on('change', function() {
            var option = selectElm.find(':checked').val();

            $('.childrenFor-{$propname}').collapse('hide');
            $('#childrenFor-' + option + '-{$propname}').collapse('show');
        });

        $(function() {
            $('#childrenFor-' + option + '-{$propname}').collapse('show');
        });
    </script>
{/if}