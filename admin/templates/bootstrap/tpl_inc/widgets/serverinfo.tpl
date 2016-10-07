<div class="widget-custom-data">
    <table class="table table-condensed table-hover table-blank">
        <tbody>
            <tr>
                <td>Domain</td>
                <td>{$cShopHost}</td>
                <td></td>
            </tr>
            <tr>
                <td>Host</td>
                <td>{$serverHTTPHost} ({$serverAddress})</td>
                <td></td>
            </tr>
            <tr>
                <td>System</td>
                <td>{$phpOS}</td>
                <td></td>
            </tr>
            <tr>
                <td>PHP-Version</td>
                <td>{$phpVersion}</td>
                <td></td>
            </tr>
            {if isset($mySQLStats) && $mySQLStats !== '-'}
                <tr>
                    <td class="nowrap">MySQL-Statistik</td>
                    <td class="small">{$mySQLStats}</td>
                    <td></td>
                </tr>
            {/if}
            <tr>
                <td class="nowrap">MySQL-Version:</td>
                <td>{$mySQLVersion}</td>
                <td class="text-right">{if $mySQLVersion < 5} 
                        <a class="label label-warning" href="status.php" title="Mehr Informationen">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i><span class="sr-only">Warnung</span>
                        </a>
                    {/if}
                </td>
            </tr>
        </tbody>
    </table>
</div>