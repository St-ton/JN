{form id='rollback-form' method='post'}
    <label class="col-form-label" for="channels">{__('Previous upgrades')}:</label>
    {select
    name="backups"
    id="backups"
    class="custom-select"
    }
    {foreach $upgrade_log as $logItem}
        {$canRollBackTo = $logItem->backup_db !== null && $logItem->backup_fs !== null}
        <option value="{$logItem->id}"{if !$canRollBackTo} disabled{/if}>
            {$logItem->timestamp}, {$logItem->version_from} --> {$logItem->version_to}
        </option>
    {/foreach}
    {/select}
        <hr>
    {button type='submit' name='rollback' value='1' block=true variant='danger'}
        {__('roll back')}
    {/button}
{/form}
