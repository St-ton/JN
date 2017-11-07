<div class="row">
    <div class="col-xs-12">
        <input type="hidden" id="img-url" name="url" value="{$properties.url}">
        <dl>
            <dt>
                <label for="image-btn-img">Bild:</label>
            </dt>
            <dd>
                <button type="button" class="btn btn-default jle-image-btn" onclick="jleHost.onOpenKCFinder(kcfinderCallback);">
                    {if isset($properties.url)}
                        <img src="{$properties.url}" id="image-btn-img" alt="einzufügendes Bild">
                    {else}
                        Bild auswählen
                    {/if}
                </button>
            </dd>
        </dl>
    </div>
    <div class="col-xs-6">
        <dl>
            <dt>
                <label for="config-image-alt">Alternativtext:</label>
            </dt>
            <dd>
                <input name="alt" value="{$properties.alt}" class="form-control" id="config-img-alt">
            </dd>
        </dl>
    </div>
    <div class="col-xs-6">
        <dl>
            <dt>
                <label for="config-image-title">Bildtitel:</label>
            </dt>
            <dd>
                <input name="title" value="{$properties.title}" class="form-control" id="config-img-title">
            </dd>
        </dl>
    </div>
    <div class="col-xs-6">
        <dl>
            <dt>
                <label for="config-image-shape">Bildform:</label>
            </dt>
            <dd>
                <select name="shape" class="form-control" id="config-image-shape">
                    <option value=""{if $properties.shape === ''} selected{/if}>flat</option>
                    <option value="img-rounded"{if $properties.shape === 'img-rounded'} selected{/if}>abgerundete Ecken</option>
                    <option value="img-circle">{if $properties.shape === 'img-circle'} selected{/if}rund</option>
                    <option value="img-thumbnail"{if $properties.shape === 'img-thumbnail'} selected{/if}>Thumbnail</option>
                </select>
            </dd>
        </dl>
    </div>
</div>
<script>
    function kcfinderCallback(url) {
        $('#img-url').val(url);
        $('#image-btn-img').attr('src', url);
    }
</script>