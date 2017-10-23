
<label>Text</label>
<form id="portlet-settings-form">
    <textarea name="text" id="settings-text" class="form-control">{$settings.text}</textarea>
</form>

<script>
    ClassicEditor
        .create( document.querySelector( '#settings-text' ),
            {
                toolbar: ['bold', 'italic', 'link', '|', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo'],
                viewportTopOffset: 30
            }
        )
        .then(function(editor) {
            var ckEditor = editor;

            jleHost.settingsSaveCallback = function() {
                $('#settings-text').val(ckEditor.getData());
            };
        })
        .catch(function(error) {
            console.error( error );
        });
</script>