<div class="form-group">
    <label for="config-{$propname}">{$propdesc.label}</label>
    <div class="input-group" id="config-{$propname}-group">
        <input type="text" class="form-control" name="{$propname}" value="{$propval}" {if $required}required{/if}
               id="config-{$propname}" autocomplete="off">
        <span class="input-group-append">
            <span class="input-group-text colorpicker-input-addon"><i></i></span>
        </span>
    </div>
    <script>
        $('#config-{$propname}-group').colorpicker({
            format: '{$propdesc.colorFormat|default:'rgba'}',
        });
    </script>
</div>