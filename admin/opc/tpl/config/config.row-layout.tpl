<div class="row-layout-controls">
    <label for="config-{$propname}">{$propdesc.label}</label>
    <div class="row">
        <div class="col-4">
            <div class="select-wrapper">
                <select class="form-control" id="config-{$propname}" name="{$propname}[preset]">
                    {foreach $propdesc.presets as $i => $preset}
                        <option value="{$i}" data-layout="{$preset.layout|json_encode|htmlentities}"
                                {if $propval.preset == $i}selected{/if}>
                            {$preset.name}
                        </option>
                    {/foreach}
                    <option value="-1" data-layout="{['', '', '', '']|json_encode|htmlentities}"
                            {if $propval.preset == -1}selected{/if}>
                        Custom
                    </option>
                </select>
            </div>
        </div>
        <div class="col-2">
            <input type="text" class="form-control" id="config-{$propname}-xs" placeholder="XS"
                   name="{$propname}[xs]" value="{$propval.xs}">
        </div>
        <div class="col-2">
            <input type="text" class="form-control" id="config-{$propname}-sm" placeholder="SM"
                   name="{$propname}[sm]" value="{$propval.sm}">
        </div>
        <div class="col-2">
            <input type="text" class="form-control" id="config-{$propname}-md" placeholder="MD"
                   name="{$propname}[md]" value="{$propval.md}">
        </div>
        <div class="col-2">
            <input type="text" class="form-control" id="config-{$propname}-lg" placeholder="LG"
                   name="{$propname}[lg]" value="{$propval.lg}">
        </div>
    </div>
</div>

<label>Vorschau</label>

<div class="row-layout-previews" id="{$propname}-previews">
    <div>
        <div class="layout-preview layout-preview-0"></div>
        <div>Mobile (XS)</div>
    </div>
    <div>
        <div class="layout-preview layout-preview-1"></div>
        <div>Tablet (SM)</div>
    </div>
    <div>
        <div class="layout-preview layout-preview-2"></div>
        <div>Desktop (MD)</div>
    </div>
    <div>
        <div class="layout-preview layout-preview-3"></div>
        <div>Large Desktop (LG)</div>
    </div>
</div>

<script>
    (function() {
        let selectElm     = $('#config-{$propname}');
        let previews      = $('#{$propname}-previews');
        let configInputXS = $('#config-{$propname}-xs');
        let configInputSM = $('#config-{$propname}-sm');
        let configInputMD = $('#config-{$propname}-md');
        let configInputLG = $('#config-{$propname}-lg');

        selectElm.on('change', changeLayoutPreset);
        configInputXS.on('input', onChangeLayoutInput);
        configInputSM.on('input', onChangeLayoutInput);
        configInputMD.on('input', onChangeLayoutInput);
        configInputLG.on('input', onChangeLayoutInput);
        updateLayoutPreview();

        function onChangeLayoutInput()
        {
            selectElm.val(-1);
            updateLayoutPreview();
        }

        function changeLayoutPreset()
        {
            let optionElm = selectElm.find(':selected');
            let layout    = optionElm.data('layout');

            configInputXS.val(layout[0]);
            configInputSM.val(layout[1]);
            configInputMD.val(layout[2]);
            configInputLG.val(layout[3]);
            updateLayoutPreview();
        }

        function updateLayoutPreview()
        {
            let layouts = [
                configInputXS.val(), configInputSM.val(), configInputMD.val(), configInputLG.val(),
            ].map(l => l.split('+').map(x => parseInt(x)));

            layouts.forEach((layout, i) => {
                let previewContainer = previews.find('.layout-preview-' + i);
                previewContainer.empty();
                layout.forEach(col => {
                    if (!isNaN(col)) {
                        previewContainer.append($('<div>').css({ width: 'calc(' + (col * 100 / 12) + '% - 2px)' }));
                    }
                });
            });
        }
    })();
</script>