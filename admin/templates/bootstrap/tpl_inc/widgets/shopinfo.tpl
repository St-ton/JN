<div class="widget-custom-data">
    {if isset($oSubscription->kShop) && $oSubscription->kShop > 0 && isset($oSubscription->cUpdate)}
        <div class="alert alert-danger">
            <p>
              <i class="fa fa-warning"></i>
              {if $oSubscription->nDayDiff < 0}Subscription ist abgelaufen!{else}Subscription l&auml;uft{if $oSubscription->nDayDiff == 0} heute{else} in{if $oSubscription->nDayDiff > 1} {$oSubscription->nDayDiff} Tagen{else} einem Tag{/if}{/if} ab!{/if}
            </p>
            <p>
              <a href="{$oSubscription->cUpdate}" class="btn btn-danger" target="_blank" rel="noopener">Jetzt verl&auml;ngern</a>
            </p>
        </div>
    {/if}
    <table class="table table-condensed table-hover table-blank">
        <tbody>
            <tr>
                <td>Shopversion</td>
                <td id="current_shop_version">{$strFileVersion} {if $strMinorVersion != '0'}(Build: {$strMinorVersion}){/if}</td>
            </tr>
            <tr>
                <td>Templateversion</td>
                <td id="current_tpl_version">{$strTplVersion}</td>
            </tr>
            <tr>
                <td>Datenbankversion</td>
                <td>{$strDBVersion}</td>
            </tr>
            <tr>
                <td>Datenbank zuletzt aktualisiert</td>
                <td>{$strUpdated}</span>
            </tr>
            {if isset($oSubscription->kShop) && $oSubscription->kShop > 0}
                <tr>
                    <td>Subscription g&uuml;ltig bis</td>
                    <td>{$oSubscription->dDownloadBis_DE}</td>
                </tr>
            {/if}
            <tr id="version_data_wrapper">
                <td colspan="2" class="text-center">
                    <p class="ajax_preloader update ">Nach Updates suchen...</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        ioCall('getRemoteData', ['{$JTLURL_GET_SHOPVERSION}?v={$nVersionFile}', 'oVersion', 'widgets/shopinfo_version.tpl', 'version_data_wrapper']);
    });
</script>
