{if !isset($propid)}
    {$propid = $propname}
{/if}
<div class="form-group">
    <label for="config-{$propid}"
            {if !empty($propdesc.desc)}
                data-tooltip title="{$propdesc.desc|default:''}"
                data-placement="auto"
            {/if}>
        {$propdesc.label}
        {if !empty($propdesc.desc)}
            <i class="fas fa-info-circle fa-fw"></i>
        {/if}
    </label>
        <input type="text" class="control" name="{$propname}"
               value="{$propval|default:''|escape:'html'}"
               {if $required}required{/if} id="config-{$propid}" autocomplete="off"
               data-colorpicker placeholder="{__('Default colour')}"
               data-presets="[[248,191,0,1], [82,82,82,1]]">
</div>