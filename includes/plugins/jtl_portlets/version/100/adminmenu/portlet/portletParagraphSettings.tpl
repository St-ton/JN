
<form id="portlet-settings-form">
    <textarea name="text" id="settingsText" class="form-control">{$settings.text|escape}</textarea>
</form>

<script>
    CKEDITOR.replace('settingsText', {
        baseFloatZIndex: 9000
    });

    jleHost.settingsSaveCallback = function() {
        $('#settingsText').val(CKEDITOR.instances.settingsText.getData());
    };
</script>