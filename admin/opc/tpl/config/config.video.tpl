{$previewVidUrl = $propval|default:$portlet->getDefaultPreviewImageUrl()}

<div class='form-group'>
    <label for="config-{$propname}">{$propdesc.label}</label>
    <input type="hidden" name="{$propname}" value="{$propval}">
    <button type="button" class="btn btn-default image-btn" onclick="opc.selectVideoProp('{$propname}')">
        <video width="300" height="160" controls controlsList="nodownload" id="cont-preview-vid-{$propname}">
            <source src="{$previewVidUrl}" id="preview-vid-{$propname}" type="video/mp4">
            {__('videoTagNotSupported')}
        </video>
    </button>
</div>