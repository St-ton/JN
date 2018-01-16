<div>
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#general" aria-controls="general" role="tab" data-toggle="tab">general settings</a></li>
        <li role="presentation"><a href="#style" aria-controls="style" role="tab" data-toggle="tab">style</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="general">
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
        <div role="tabpanel" class="tab-pane" id="style">
            css stuff
        </div>
    </div>
</div>
