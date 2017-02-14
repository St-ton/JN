{*
    Display a CSV import button for a CSV importer with the unique $importerId
*}
<script>
    function onClickCsvImport_{$importerId} ()
    {
        var $form           = $('<form>', { method: 'post', enctype: 'multipart/form-data' });
        var $importcsvInput = $('<input>', { type: 'hidden', name: 'importcsv', value: '{$importerId}' });
        var $fileInput      = $('<input>', { type: 'file', name: 'csvfile', accept: '.csv' });
        var $tokenInput     = $('{$jtl_token}');
        $form
            .append($importcsvInput, $fileInput, $tokenInput);
        $fileInput
            .change(function () { $form.submit(); })
            .click();
        $('body').append($form);
    }
</script>
<button type="button" class="btn btn-default" onclick="onClickCsvImport_{$importerId}()">
    <i class="fa fa-upload"></i> {#importCsv#}
</button>