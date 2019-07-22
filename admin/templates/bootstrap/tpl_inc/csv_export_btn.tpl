{*
    Display a CSV export button for a CSV exporter with the unique $exporterId
*}
<script>
    function onClickCsvExport_{$exporterId} ()
    {
        window.location = window.location.pathname + '?exportcsv={$exporterId}&token={$smarty.session.jtl_token}';
    }
</script>
<button type="button" class="btn btn-outline-primary btn-block mb-3" onclick="onClickCsvExport_{$exporterId}()">
    <i class="fa fa-download"></i> {__('exportCsv')}
</button>