{include file='tpl_inc/header.tpl'}
{if $inserted !== 0}
    <div class="alert alert-info">{__('msgCreated')}</div>
{/if}
{if $deleted > 0}
    <div class="alert alert-info">{__('msgDeleted')}</div>
{/if}
{if $updated > 0}
    <div class="alert alert-info">{__('msgUpdated')}</div>
{/if}

{if $jobs|count > 0}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{__('queueEntries')}</h3>
        </div>
        <div class="panel-body">
            <form method="post">
                {$jtl_token}
                <table class="table table-striped">
                    <thead>
                    <th>{__('headingType')}</th>
                    <th>{__('headingStartTime')}</th>
                    <th>{__('headingLastStarted')}</th>
                    <th>{__('headingFrequency')}</th>
                    <th>{__('headingRunning')}</th>
                    <th>{__('headingAction')}</th>
                    </thead>
                    <tbody>
                        {foreach $jobs as $job}
                            <tr>
                                <td>{__($job->getType())}</td>
                                <td>{$job->getStartTime()->format('H:i')}</td>
                                <td>{if $job->getDateLastStarted() === null}&dash;{else}{$job->getDateLastStarted()->format('d.m.Y H:i')}{/if}</td>
                                <td>{$job->getFrequency()}h</td>
                                <td>{if $job->isRunning()}<i class="fa fa-check"></i>{else}<i class="fa fa-times"></i>{/if}</td>
                                <td>
                                    <span class="btn-group">
                                        <button class="btn btn-danger btn-xs" type="submit" name="delete" value="{$job->getCronID()}">
                                            <i class="fa fa-trash"></i> {__('btnDelete')}
                                        </button>
                                        {if $job->isRunning()}
                                            <button class="btn btn-default btn-xs" type="submit" name="reset" value="{$job->getQueueID()}">
                                                <i class="fa fa-refresh"></i> {__('btnReset')}
                                            </button>
                                        {/if}
                                    </span>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </form>
        </div>
    </div>
{else}
    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
{/if}

<div id="settings">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{__('createQueueEntry')}</h3>
        </div>
        <div class="panel-body">
            <form method="post">
                {$jtl_token}
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="cron-type">{__('headingType')}</label>
                    </span>
                    <select name="type" class="form-control" id="cron-type" required>
                        {foreach $available as $type}
                            <option value="{$type}">{__($type)}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="cron-type">{__('headingFrequency')}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="number" min="1" value="24" name="frequency" class="form-control" required>
                    </span>
                    <span class="input-group-addon">
                        <label for="cron-type">h</label>
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="cron-type">{__('headingStartTime')}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="time" name="time" value="00:00" class="form-control" required>
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="cron-type">{__('headingStartDate')}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input type="date" name="date" class="form-control" value="{$smarty.now|date_format:'%Y-%m-%d'}" required>
                    </span>
                </div>
                <button type="submit" class="btn btn-primary" name="add-cron" value="1">
                    <i class="fa fa-save"></i> {__('btnCreate')}
                </button>
            </form>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
