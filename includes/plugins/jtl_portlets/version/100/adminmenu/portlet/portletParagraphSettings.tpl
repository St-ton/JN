
<textarea name="text" id="settingsText" class="form-control">{$properties.text|escape}</textarea>

<script>
    CKEDITOR.replace('settingsText', {
        baseFloatZIndex: 9000,
    });

    jleHost.configSaveCallback = function() {
        $('#settingsText').val(CKEDITOR.instances.settingsText.getData());
    };
</script>