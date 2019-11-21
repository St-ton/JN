<div class='form-group'>
    <label for="config-{$propname}">{$propdesc.label}</label>
    <textarea name="{$propname}" id="textarea-{$propname}" class="form-control" {if $required}required{/if}>
        {$propval|htmlspecialchars}
    </textarea>
    <script>
        var adminLang = '{Shop::Container()->getGetText()->getLanguage()}'.toLowerCase();

        if(!CKEDITOR.lang.languages.hasOwnProperty(adminLang)) {
            adminLang = adminLang.split('-')[0]
        }

        CKEDITOR.replace(
            'textarea-{$propname}',
            {
                baseFloatZIndex: 9000,
                language: adminLang,
            },
        );

        opc.setConfigSaveCallback(function() {
            $('#textarea-{$propname}').val(CKEDITOR.instances['textarea-{$propname}'].getData());
        });
    </script>
</div>