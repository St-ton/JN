{include file='tpl_inc/pluginverwaltung_uebersicht_aktiviert_tab.tpl'}
{if $smarty.const.SAFE_MODE}
<script>
    {literal}
    function invalidatePlugin(pluginID, msg) {
        let notify = '<span title="{/literal}{__('Plugin probably flawed')}{literal} ' + msg + '" class="label text-danger" data-toggle="tooltip">'
            + '    <span class="icon-hover">'
            + '      <span class="fal fa-exclamation-triangle"></span>'
            + '      <span class="fas fa-exclamation-triangle"></span>'
            + '    </span>'
            + '</span>';
        $('[for="plugin-check-' + pluginID + '"]:first').append($(notify));
    }
    function checkPlugin(pluginID) {
        simpleAjaxCall(BACKEND_URL + 'io', {
            jtl_token: JTL_TOKEN,
            io : JSON.stringify({
                name: 'pluginTestLoading',
                params : [pluginID]
            })
        }, function (result) {
            if (!result.code || result.code !== {/literal}{\JTL\Plugin\InstallCode::OK}{literal}) {
                invalidatePlugin(pluginID, result.message
                    ? result.message
                    : (result.error.message ? result.error.message : ''));
            }
        }, function (result) {
            invalidatePlugin(pluginID, result.responseJSON.message
                ? result.responseJSON.message
                : (result.responseJSON.error.message ? result.responseJSON.error.message : ''));
        }, undefined, true);
    }
    $('.check input').each(function () {
        let value = parseInt($(this).val());
        if (!isNaN(value)) {
            checkPlugin(value);
        }
    })
    {/literal}
</script>
{/if}
