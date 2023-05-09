<div class="widget-custom-data">
    {if $requestCount > 0}
        <div class="mb-3">
            {foreach $groups as $group}
                {if $group->kRequestCount > 0}
                    <div class="row">
                        <div class="col-6"><strong>{$group->cGroupName}:</strong></div>
                        <div class="col-auto">{$group->kRequestCount}</div>
                    </div>
                {/if}
            {/foreach}
        </div>
        <p>{__('See Approvals to manage your open requests.')}</p>
    {else}
        <div class="alert alert-info">
            {__('At the moment there are no open requests.')}
        </div>
    {/if}
</div>
