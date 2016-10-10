{function csvExportButton}
    {assign var='csvFilename' value=$csvFilename|default:'export.csv'}
    {assign var='exporterId' value=$exporterId|default:'csvexport'}
    <input type="hidden" name="csvFilename" value="{$csvFilename}">
    <button type="submit" class="btn btn-default" name="exportcsv" value="{$exporterId}">
        <i class="fa fa-download"></i> Exportiere Eintr&auml;ge
    </button>
{/function}