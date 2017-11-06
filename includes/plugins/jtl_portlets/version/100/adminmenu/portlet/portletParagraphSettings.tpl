
<textarea name="text" id="settingsText" class="form-control">{$properties.text|escape}</textarea>

<script>
    CKEDITOR.replace('settingsText', {
        baseFloatZIndex: 9000
                toolbar: ['bold', 'italic', 'link', '|', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo'],
                viewportTopOffset: 30
            }
        )
        .then(function(editor) {
            var ckEditor = editor;
        };

    jleHost.configSaveCallback = function() {
        $('#settingsText').val(CKEDITOR.instances.settingsText.getData());
    };
</script>