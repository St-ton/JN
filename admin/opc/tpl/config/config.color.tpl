{if !isset($propid)}
    {$propid = $propname}
{/if}
<div class="form-group no-pb">
    <label for="config-{$propid}"
            {if !empty($propdesc.desc)}
                data-toggle="tooltip" title="{$propdesc.desc|default:''}"
                data-placement="auto"
            {/if}>
        {$propdesc.label}
        {if !empty($propdesc.desc)}
            <i class="fas fa-info-circle fa-fw"></i>
        {/if}
    </label>
    <input type="text" class="form-control" name="{$propname}"
           value="{$propval|default:''|escape:'html'}"
           {if $required}required{/if} id="config-{$propid}" autocomplete="off"
           placeholder="{__('Default colour')}">
    <script type="module">
        import { enableColorpicker } from "./opc/js/colorpicker.js";
        enableColorpicker($('#config-{$propid}')[0]);
    </script>
</div>