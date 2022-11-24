<div class="form-group">
    <label for="config-{$propname}">{$propdesc.label}</label>
    <div id="config-{$propname}-picker"></div>
    <input type="hidden" id="config-{$propname}" name="{$propname}" value="{$propval|escape:'html'}">
    <script>
        $(() => {
            $('#config-{$propname}-picker').append(opc.gui.iconpicker);
            opc.gui.iconpicker.find('.popover-title i').attr('class', {json_encode($propval)});
            opc.gui.iconpicker.show();
            opc.gui.iconpicker.data('iconpicker').setSourceValue({json_encode($propval)});
            opc.gui.iconpicker.data('iconpicker').update();
            opc.gui.setIconPickerCallback(faClass => {
                $('#config-{$propname}').val(faClass);
                opc.gui.iconpicker.find('.popover-title i').attr('class', faClass);
            })
        });
    </script>
</div>
