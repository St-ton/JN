<h2>{__('roll back')}</h2>
{form id='rollback-form' method='post'}
    <label class="col-form-label" for="channels">{__('Previous upgrades')}:</label>
    {select
    name="backups"
    id="backups"
    class="custom-select"
    }
        <option value="">{__('please select')}</option>
        {foreach $upgrade_log as $logItem}
            {$canRollBackTo = $logItem->backup_db !== null && $logItem->backup_fs !== null}
            <option value="{$logItem->id}"{if !$canRollBackTo} disabled{/if}>
                {$logItem->timestamp}, {$logItem->version_from} --> {$logItem->version_to}
            </option>
        {/foreach}
    {/select}
        <hr>
    <button
        type="submit"
        name="rollback"
        value="1"
        class="btn btn-danger btn-block delete-confirm"
        data-toggle="tooltip"
        data-modal-title="{__('roll back')}"
        data-modal-submit="{__('OK, start roll back')}"
        data-modal-body="{__('rollbackWarningMsg')}">
        {__('start roll back')}
    </button>
{/form}
