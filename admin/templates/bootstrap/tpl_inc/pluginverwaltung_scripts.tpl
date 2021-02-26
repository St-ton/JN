<script>
    var vLicenses = {if isset($szLicenses)}{$szLicenses}{else}[]{/if};
    var pluginName;
    $(document).ready(function() {
        var token = $('input[name="jtl_token"]').val();
        $('.tab-content').on('click', '#verfuegbar .plugin-license-check', function (e) {
            var oTemp = $(e.currentTarget);
            pluginName = oTemp.val();
            var licensePath = vLicenses[pluginName];
            if (this.checked && typeof licensePath === 'string') { // it's checked yet, right after the click was fired
                $('input[id="plugin-check-' + pluginName + '"]').attr('disabled', 'disabled'); // block the checkbox!
                $('div[id="licenseModal"]').modal({ backdrop : 'static' }).on('hide.bs.modal', function (e) {
                    $('input[id=plugin-check-' + pluginName + ']').removeAttr('disabled');
                    // check, which element is 'active' before/during the modal goes hiding (to determine, which button closes it)
                    // (it is faster than check a var or bind an event to an element)
                    if ('ok' === document.activeElement.name) {
                        $('input[id=plugin-check-' + pluginName + ']').prop('checked', true);
                    } else {
                        $('input[id=plugin-check-' + pluginName + ']').prop('checked', false);
                    }
                });
                startSpinner();
                $('div[id="licenseModal"]').find('.modal-body').load(
                    'getMarkdownAsHTML.php',
                    { 'jtl_token' : '{$smarty.Session.jtl_token}', 'path': vLicenses[pluginName] },
                    function () {
                        stopSpinner();
                    }
                );
                $('div[id="licenseModal"]').modal('show');
            }
        });
    });
</script>
