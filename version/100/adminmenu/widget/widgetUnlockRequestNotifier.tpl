<div class="widget-custom-data">
    {if $kRequestCountTotal > 0}
        <ul class="infolist">
            {foreach from=$oUnlockRequest_arr key="i" item="oUnlockRequestGroup"}
                {if $oUnlockRequestGroup|@count > 0}
                    <li>
                        <p>
                            <strong>{$oUnlockRequestGroups_arr[$i]}:</strong>
                            <span class="value">{$oUnlockRequestGroup|@count}</span>
                        </p>
                    </li>
                {/if}
            {/foreach}
            <li>
                Verwalten Sie ausstehende Anfragen in der <a href="freischalten.php">Freischaltzentrale</a>.
            </li>
        </ul>
    {else}
        <div class="alert alert-info">
            Zur Zeit gibt es keine ausstehenden Anfragen die freigeschaltet werden m&uuml;ssen.
        </div>
    {/if}
</div>