<div class='form-group'>
    <label for="config-{$propname}">
        {if !empty($propdesc.label)}{$propdesc.label}{/if}
    </label>

    <input type="hidden" name="{$propname}" value="{$propval}">
    <button type="button" class="btn btn-default image-btn" onclick="opc.selectImageProp('{$propname}')">
        <img src="{$propval|default:$portlet->getDefaultPreviewImageUrl()}"
             alt="Chosen image" id="preview-img-{$propname}">
    </button>
</div>
