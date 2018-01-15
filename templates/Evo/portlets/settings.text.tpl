<div>
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#general" aria-controls="general" role="tab" data-toggle="tab">general settings</a></li>
        <li role="presentation"><a aria-expanded="true" aria-controls="wow-animation" data-toggle="tab" role="tab" id="wow-animation-tab" href="#wow-animation">Animation</a></li>
        <li role="presentation" class=""><a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design" aria-expanded="false">Style</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="general">
            <div class="row">
                <div class="col-xs-12">
                    <dl>
                        <dt>
                            <label for="settingsText">Text:</label>
                        </dt>
                        <dd>
                            <textarea name="text" id="settingsText" class="form-control">{$properties.text|escape}</textarea>

                            <script>
                                CKEDITOR.replace('settingsText', {
                                    baseFloatZIndex: 9000,
                                });

                                editor.setConfigSaveCallback(function() {
                                    $('#settingsText').val(CKEDITOR.instances.settingsText.getData());
                                });
                            </script>
                        </dd>
                    </dl>
                </div>
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="config-heading-class">Class</label>
                        <input name="attr[class]" value="{$properties.attr['class']}" class="form-control" id="config-heading-class">
                    </div>
                </div>
            </div>
        </div>
        {include file='./settings.tabcontent.animation.tpl'}
        {include file='./settings.tabcontent.style.tpl'}
    </div>
</div>
