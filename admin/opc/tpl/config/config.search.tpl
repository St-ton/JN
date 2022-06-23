<div class='form-group'>
    <label for="config-{$propname}">
        {$propdesc.label}
    </label>
    <input type="search" class="form-control" id="config-{$propname}"
           {if !empty($propdesc.placeholder)}placeholder="&#xF002; {$propdesc.placeholder}"{/if}>
</div>