<div id="wrap-channels">
    <label class="col-form-label" for="channels">{__('chooseChannel')}:</label>
    {select
    name="channels"
    id="channels"
    class="onchangeSubmit custom-select"
    }
    {foreach $channels as $channel}
        <option value="{$channel->name}"{if $channel->disabled} disabled{/if}{if $channel->selected} selected{/if}>
            {$channel->name}
        </option>
    {/foreach}
    {/select}
</div>
