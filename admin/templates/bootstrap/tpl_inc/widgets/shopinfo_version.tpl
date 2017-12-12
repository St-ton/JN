<table class="table table-condensed table-hover table-blank">
    <tbody>
        {if $oSubscription}
            <tr>
                <td width="50%">Subscription g&uuml;ltig bis</td>
                <td width="50%" id="subscription">
                    {if $oSubscription->nDayDiff < 0}Abgelaufen{else}{$oSubscription->dDownloadBis_DE}{/if}
                </td>
            </tr>
        {/if}
        {if $oVersion}
            <tr>
                <td width="50%"></td>
                <td width="50%" id="version">
                    {if $bUpdateAvailable}
                        <span class="text-info">Version {$strLatestVersion} {if $oVersion->build > 0}(Build: {$oVersion->build}){/if} verfügbar.</span>
                    {else}
                        <span class="text-success">Aktuellste Version bereits vorhanden</span>
                    {/if}
                </td>
            </tr>
        {/if}
    </tbody>
</table>