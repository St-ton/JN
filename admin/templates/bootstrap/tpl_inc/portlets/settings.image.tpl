<input type="hidden" id="img-url" name="url" value="{$properties.url}">
<button type="button" class="btn btn-default jle-image-btn" onclick="jleHost.onOpenKCFinder(kcfinderCallback);">
    {if isset($properties.url)}
        <img src="{$properties.url}" id="image-btn-img">
    {else}
        Bild auswählen
    {/if}
</button>
<script>
    function kcfinderCallback(url) {
        $('#img-url').val(url);
        $('#image-btn-img').attr('src', url);
    }
</script>