{include file='tpl_inc/header.tpl'}
{*{if $step === 'uebersicht'}*}
    {*{include file='tpl_inc/cache_uebersicht.tpl'}*}
{*{/if}*}
{if $jobs|count > 0}
    <form method="post">
        {$jtl_token}
        <table class="table table-striped">
            <thead>
            <th>Typ</th>
            <th>Startzeit</th>
            <th>zuletzt gestartet</th>
            <th>Frequenz</th>
            <th>Läuft aktuell</th>
            <th>Zurücksetzen</th>
            </thead>
            <tbody>
                {foreach $jobs as $job}
                    <tr>
                        <td>{$job->getType()}</td>
                        <td>{$job->getStartTime()}</td>
                        <td>{if $job->getDateLastStarted() === null}&dash;{else}{$job->getDateLastStarted()->format('d:m:Y h:i')}{/if}</td>
                        <td>{$job->getFrequency()}h</td>
                        <td>{if $job->isRunning()}<i class="fa fa-check"></i>{else}<i class="fa fa-times"></i>{/if}</td>
                        <td>{if $job->isRunning() || true}<button type="submit" name="reset" value="{$job->getQueueID()}">Zurücksetzen</button> {/if}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </form>
{else}
    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
{/if}
{include file='tpl_inc/footer.tpl'}