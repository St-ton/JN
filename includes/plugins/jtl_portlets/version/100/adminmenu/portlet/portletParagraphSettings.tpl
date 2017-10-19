
<label>Text</label>
<form id="portlet-settings-form">
    <textarea name="text" id="editor" class="form-control">{$settings.text}</textarea>
</form>

<script>
    jleHost.settingsSaveCallback = function() {
        $('#editor').val(ckEditor.getData());
        console.log("SAAVE");
    };

    var ckEditor = null;

    ClassicEditor
        .create( document.querySelector( '#editor' ),
            {
                toolbar: ['bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
                viewportTopOffset: 30
            }
        )
        .then(function(editor) {
            ckEditor = editor;
        })
        .catch( error => {
            console.error( error );
        });
</script>