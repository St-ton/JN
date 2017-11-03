
<textarea name="text" id="settingsText" class="form-control">{$settings.text|escape}</textarea>

<script>
    CKEDITOR.replace('settingsText', {
        baseFloatZIndex: 9000
    });

    jleHost.settingsSaveCallback = function() {
        $('#settingsText').val(CKEDITOR.instances.settingsText.getData());
    };
</script>