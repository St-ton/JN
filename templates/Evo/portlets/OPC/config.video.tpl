<input type="hidden" name="{$propname}" value="{$prop}">
<button type="button" class="btn btn-default image-btn" onclick="opc.selectVideoProp('{$propname}')">
    <video width="300" height="160" controls controlsList="nodownload" id="cont-preview-vid-{$propname}">
        <source src="{$previewVidUrl}" id="preview-vid-{$propname}" type="video/mp4">
        Your browser does not support the video tag.
    </video>
</button>