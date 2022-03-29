{if $oSubscription || $oVersion}
    <table class="table table-condensed table-hover table-blank">
        <tbody>
            {if $oSubscription}
                <tr>
                    <td width="50%">{__('subscriptionValidUntil')}</td>
                    <td width="50%" id="subscription">
                        {if $oSubscription->nDayDiff < 0}
                            <a href="https://jtl-url.de/subscription" target="_blank">{__('expired')}</a>
                        {else}
                            {$oSubscription->dDownloadBis_DE}
                        {/if}
                    </td>
                </tr>
            {/if}
            {if $oVersion}
                <tr>
                    <td colspan="2" id="version" class="h1">
                        {if $bUpdateAvailable}
                            <span class="badge badge-info">{__('Version %s available')|sprintf:$strLatestVersion}</span>
                        {else}
                            <span class="badge badge-success">{__('shopVersionUpToDate')}</span>
                        {/if}
                    </td>
                </tr>
            {/if}
        </tbody>
    </table>
{/if}
