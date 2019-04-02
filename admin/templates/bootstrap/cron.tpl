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
<ul class="nav nav-tabs" role="tablist">
    <li class="tab{if $tab === 'overview'} active{/if}">
        <a data-toggle="tab" role="tab" href="#overview">{__('queueEntries')}</a>
    </li>
    <li class="tab{if $tab === 'add-cron'} active{/if}">
        <a data-toggle="tab" role="tab" href="#add-cron">{__('createQueueEntry')}</a>
    </li>
    <li class="tab{if $tab === 'settings'} active{/if}">
        <a data-toggle="tab" role="tab" href="#config">{__('settings')}</a>
    </li>
</ul>
<div class="tab-content">
    <div id="overview" class="settings tab-pane fade{if $tab === 'overview'} active in{/if}">
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
                            <th>{__('action')}</th>
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
                                                    <i class="fa fa-trash"></i> {__('delete')}
                                                </button>
                                                {if $job->isRunning()}
                                                    <button class="btn btn-default btn-xs" type="submit" name="reset" value="{$job->getQueueID()}">
                                                        <i class="fa fa-refresh"></i> {__('reset')}
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
    </div>

    <div id="add-cron" class="settings tab-pane fade{if $tab === 'add-cron'} active in{/if}">
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
                        <span class="input-group-wrap">
                            <select name="type" class="form-control" id="cron-type" required>
                                {foreach $available as $type}
                                    <option value="{$type}">{__($type)}</option>
                                {/foreach}
                            </select>
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cron-freq">{__('headingFrequency')}</label>
                        </span>
                        <span class="input-group-wrap">
                            <input id="cron-freq" type="number" min="1" value="24" name="frequency" class="form-control" required>
                        </span>
                        <span class="input-group-addon">
                            <label for="cron-type">h</label>
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cron-start">{__('headingStartTime')}</label>
                        </span>
                        <span class="input-group-wrap">
                            <input id="cron-start" type="time" name="time" value="00:00" class="form-control" required>
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="cron-start-date">{__('headingStartDate')}</label>
                        </span>
                        <span class="input-group-wrap">
                            <input id="cron-start-date" type="date" name="date" class="form-control" value="{$smarty.now|date_format:'%Y-%m-%d'}" required>
                        </span>
                    </div>
                    <button type="submit" class="btn btn-primary" name="add-cron" value="1">
                        <i class="fa fa-save"></i> {__('create')}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div id="config" class="settings tab-pane fade{if $tab === 'settings'} active in{/if}">
        {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' a='saveSettings'
        action='cron.php' buttonCaption=__('save') tab='einstellungen'}
    </div>
</div>

{include file='tpl_inc/footer.tpl'}
