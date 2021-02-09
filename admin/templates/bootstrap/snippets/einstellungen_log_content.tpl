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
                <td>{$log->getValueNew()}</td>
                <td>{$log->getValueOld()}</td>
                <td>{$log->getDate()}</td>
            </tr>
        {/foreach}
    </table>
</div>
