<div class='form-group'>
    <label for="config-{$propname}">{$propdesc.label}</label>
    <textarea name="{$propname}" id="textarea-{$propname}" class="form-control" {if $required}required{/if}>
        {$propval|htmlspecialchars}
    </textarea>
    <script>
        CKEDITOR.replace(
            'textarea-{$propname}',
            { baseFloatZIndex: 9000 }
        );

        opc.setConfigSaveCallback(function() {
            $('#textarea-{$propname}').val(CKEDITOR.instances['textarea-{$propname}'].getData());
        });
    </script>
</div>