<ul role="tablist" class="nav nav-tabs" id="portlet-design-tab">
    <li class="active" role="presentation"><a aria-expanded="true" aria-controls="general" data-toggle="tab" role="tab" id="general-tab" href="#general">General</a></li>
    <li role="presentation" class=""><a aria-controls="url-link" data-toggle="tab" id="url-link-tab" role="tab" href="#url-link" aria-expanded="false">Url (link)</a></li>
    <li role="presentation"><a aria-expanded="true" aria-controls="wow-animation" data-toggle="tab" role="tab" id="wow-animation-tab" href="#wow-animation">Animation</a></li>
    <li role="presentation" class=""><a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design" aria-expanded="false">Style</a></li>
</ul>
<div class="tab-content" id="portlet-design-tab-content">
    <div id="general" class="tab-pane fade active in" role="general" aria-labelledby="general-tab">
        <div class="form-group">
            <label for="image-btn-img">Bild</label>
            <input type="hidden" id="img-url" name="attr[src]" value="{$properties.attr['src']}">
            <button type="button" class="btn btn-default cle-image-btn" onclick="cmsLiveEditor.onOpenKCFinder(kcfinderCallback);">
                {if !empty($properties.attr['src'])}
                    <img src="{$properties.attr['src']}" id="image-btn-img" alt="einzufügendes Bild">
                {else}
                    <img src="../gfx/keinBild.gif" id="image-btn-img" alt="Bild wählen">
                {/if}
            </button>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="config-image-alt">Alternativtext</label>
                    <input name="attr[alt]" value="{$properties.attr['alt']}" class="form-control" id="config-img-alt">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="config-image-title">Bildtitel</label>
                    <input name="attr[title]" value="{$properties.attr['title']}" class="form-control" id="config-img-title">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="config-image-shape">Bildform</label>
                    <select name="shape" class="form-control" id="config-image-shape">
                        <option value=""{if $properties.shape === ''} selected{/if}>flat</option>
                        <option value="img-rounded"{if $properties.shape === 'img-rounded'} selected{/if}>abgerundete Ecken</option>
                        <option value="img-circle"{if $properties.shape === 'img-circle'} selected{/if}>rund</option>
                        <option value="img-thumbnail"{if $properties.shape === 'img-thumbnail'} selected{/if}>Thumbnail</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="class">Class name</label>
                    <input type="text"  id="class" name="attr[class]" class="form-control" value="{$properties.attr['class']}">
                </div>
            </div>
        </div>
        <script>
            function kcfinderCallback(url) {
                $('#img-url').val(url);
                $('#image-btn-img').attr('src', url);
            }
        </script>
    </div>
    {include file='./settings.tabcontent.url.tpl'}
    {include file='./settings.tabcontent.animation.tpl'}
    {include file='./settings.tabcontent.style.tpl'}
</div>
