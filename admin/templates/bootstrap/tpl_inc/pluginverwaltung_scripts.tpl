<script>
    var vLicenses = {$licenseFiles|default:[]};
    var pluginName;
    $(document).ready(function() {
        $('.tab-content').on('click', '#verfuegbar .plugin-license-check', function (e) {
            pluginName = $(e.currentTarget).val();
            var licensePath = vLicenses[pluginName];
            if (this.checked && typeof licensePath === 'string') { // it's checked yet, right after the click was fired
                var modal = $('#licenseModal');
                $('input[id="plugin-check-' + pluginName + '"]').attr('disabled', 'disabled'); // block the checkbox!
                modal.modal({ backdrop : 'static' }).one('hide.bs.modal', function (e) {
                    $('input[id=plugin-check-' + pluginName + ']').removeAttr('disabled');
                });
                $('#licenseModal button[name=cancel], #licenseModal .close').one('click', function() {
                    $('input[id=plugin-check-' + pluginName + ']').prop('checked', false);
                });
                $('#licenseModal button[name=ok]').one('click', function() {
                    $('input[id=plugin-check-' + pluginName + ']').prop('checked', true);
                });
                startSpinner();
                modal.find('.modal-body').load(
                    '{$adminURL}/markdown',
                    { 'jtl_token' : '{$smarty.Session.jtl_token}', 'path': vLicenses[pluginName] },
                    stopSpinner
                );
                modal.modal('show');
            }
        });
    });
</script>
