<div class="form-group">
    <label for="config-{$propname}">{$propdesc.label}</label>
    <div id="config-{$propname}-picker"></div>
    <input type="hidden" id="config-{$propname}" name="{$propname}" value="">
    <script>
        $(() => {
            $('#config-{$propname}-picker').append(opc.gui.iconpicker);
            opc.gui.iconpicker.show();
            opc.gui.iconpicker.data('iconpicker').setSourceValue('{$propval}');
            opc.gui.iconpicker.data('iconpicker').update();
            opc.gui.setIconPickerCallback(faClass => {
                $('#config-{$propname}').val(faClass);
            })
        });
    </script>
</div>