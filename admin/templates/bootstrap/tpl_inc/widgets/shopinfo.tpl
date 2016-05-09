<div class="widget-custom-data">
    {if isset($oSubscription->kShop) && $oSubscription->kShop > 0 && isset($oSubscription->cUpdate)}
        <div class="alert alert-danger">
            <p>
              <i class="fa fa-warning"></i>
              {if $oSubscription->nDayDiff < 0}Subscription ist abgelaufen!{else}Subscription l&auml;uft{if $oSubscription->nDayDiff == 0} heute{else} in{if $oSubscription->nDayDiff > 1} {$oSubscription->nDayDiff} Tagen{else} einem Tag{/if}{/if} ab!{/if}
            </p>
            <p>
              <a href="{$oSubscription->cUpdate}" class="btn btn-danger" target="_blank">Jetzt verl&auml;ngern</a>
            </p>
        </div>
    {/if}
    <ul class="infolist clearall">
        <li class="first">
            <p><strong>Shopversion:</strong> <span class="value" id="current_shop_version">{$strFileVersion} {if $strMinorVersion != '0'}(Build: {$strMinorVersion}){/if}</span></p>
        </li>
        <li>
            <p><strong>Templateversion:</strong> <span class="value" id="current_tpl_version">{$strTplVersion}</span></p>
        </li>
        <li>
            <p><strong>Datenbankversion:</strong> <span class="value">{$strDBVersion}</span></p>
        </li>
        <li>
            <p><strong>Datenbank zuletzt aktualisiert:</strong> <span class="value">{$strUpdated}</span></p>
        </li>
        {if isset($oSubscription->kShop) && $oSubscription->kShop > 0}
            <li>
                <p><strong>Subscription g&uuml;ltig bis:</strong> <span class="value">{$oSubscription->dDownloadBis_DE}</span></p>
            </li>
        {/if}
        <li class="last">
            <div id="version_data_wrapper">
                <p class="ajax_preloader update">Nach Aktualisierungen suchen...</p>
            </div>
        </li>
    </ul>
</div>
<script type="text/javascript">
    $(document).ready(function () {ldelim}
        xajax_getRemoteDataAjax('{$JTLURL_GET_SHOPVERSION}?v={$nVersionFile}', 'oVersion', 'widgets/shopinfo_version.tpl', 'version_data_wrapper');
    {rdelim});
</script>
