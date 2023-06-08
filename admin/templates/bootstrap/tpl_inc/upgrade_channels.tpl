<div id="wrap-channels">
    <label class="col-form-label" for="channels">{__('choose channel')}:</label>
    {select
        name="channels"
        id="channels"
        class="onchangeSubmit custom-select"
    }
    {$selectedChannel = 'STABLE'}
    {foreach $channels as $channel}
        <option value="{$channel->name}"{if $channel->disabled} disabled{/if}{if $channel->selected} selected{/if}>
            {__($channel->name)}
            {if $channel->selected}
                {$selectedChannel = $channel->name}
            {/if}
        </option>
    {/foreach}
    {/select}
    {if $selectedChannel !== 'STABLE'}
        <div id="channel-warning">
            <hr>
            <div class="alert alert-warning">
                <i class="fa fa-warning"></i> {__('Dangerous channel selected')}
            </div>
        </div>
    {/if}
</div>
