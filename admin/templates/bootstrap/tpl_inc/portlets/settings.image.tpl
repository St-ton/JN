<input type="hidden" id="img-url" name="url" value="">
<button type="button" class="jle-image-btn" onclick="jleHost.onOpenKCFinder(this, kcfinderCallback);">
</button>
<script>
    function kcfinderCallback(url) {
        $('#img-url').val(url);
//            $('#img-src').attr('value', url);
    }
</script>