<div class="form-group">
    <label for="config-{$propname}">
        {if !empty($propdesc.label)}{$propdesc.label}{/if}
    </label>

    <input type="hidden" name="{$propname}" value="{$propval}">
    <button type="button" class="image-btn" onclick="opc.selectImageProp('{$propname}')">
        <img src="{$propval|default:'opc/gfx/upload-stub.png'}"
             alt="Chosen image" id="preview-img-{$propname}" class="{if !empty($propdesc.thumb)}thumb{/if}">
    </button>
</div>
