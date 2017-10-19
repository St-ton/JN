
<label>Text</label>
<form id="portlet-settings-form">
    <textarea name="text" id="editor" class="form-control">{$settings.text}</textarea>
</form>

<script>
    {literal}
    ClassicEditor
        .create( document.querySelector( '#editor' ),
            {
                toolbar: ['bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
                viewportTopOffset: 30
            }
        )
        .catch( error => {
            console.error( error );
        });
    {/literal}
</script>