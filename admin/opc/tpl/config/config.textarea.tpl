<div class="form-group">
    <label for="config-{$propname}"
            {if !empty($propdesc.desc)}
                data-toggle="tooltip" title="{$propdesc.desc|default:''|escape:'html'}"
                data-placement="auto"
            {/if}>
        {$propdesc.label}
        {if !empty($propdesc.desc)}
            <i class="fas fa-info-circle fa-fw"></i>
        {/if}
    </label>
    <textarea class="form-control" rows="3" name="{$propname}" id="config-{$propname}">{$propval}</textarea>
</div>