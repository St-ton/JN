<div class="widget-custom-data">
    <ul class="infolist clearall">
        <li class="first">
            <p class="key"><strong>Domain:</strong> <span class="value">{$cShopHost}</span></p>
        </li>
        <li>
            <p class="key"><strong>Host:</strong> <span class="value">{$serverHTTPHost} ({$serverAddress})</span></p>
        </li>
        <li>
            <p class="key"><strong>System:</strong> <span class="value">{$phpOS}</span></p>
        </li>
        <li>
            <p class="key"><strong>PHP-Version:</strong> <span class="value">{$phpVersion}</span></p>
        </li>
        {if isset($mySQLStats) && $mySQLStats !== '-'}
            <li>
                <p class="key"><strong>MySQL-Statistik</strong> <span class="value">{$mySQLStats}</span></p>
            </li>
        {/if}
        <li class="last">
            <p class="key"><strong>MySQL-Version:</strong> <span class="value{if $mySQLVersion|truncate:1:'' < 5} error{/if}">{$mySQLVersion}</span></p>
        </li>
    </ul>
</div>