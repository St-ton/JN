{if !empty($logs)}
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th>geändert von</th>
            <th>wert alt</th>
            <th>wert neu</th>
            <th>Datum</th>
        </tr>
        </thead>
        {foreach $logs as $log}
            <tr class="text-vcenter">
                <td>{$log->getAdminName()}</td>
                <td>
                    {if $log->getSettingType() === 'selectbox'}
                        {__("{$log->getSettingName()}_value({$log->getValueOld()})")}
                        <span class="font-size-sm">({$log->getValueOld()})</span>
                    {else}
                        {$log->getValueOld()}
                    {/if}
                </td>
                <td>
                    {if $log->getSettingType() === 'selectbox'}
                        {__("{$log->getSettingName()}_value({$log->getValueNew()})")}
                        <span class="font-size-sm">({$log->getValueNew()})</span>
                    {else}
                        {$log->getValueNew()}
                    {/if}
                </td>
                <td>{$log->getDate()}</td>
            </tr>
        {/foreach}
    </table>
</div>
{else}
    <div class="alert alert-info">Keine Änderungen dieser Einstellung vorhanden.</div>
{/if}