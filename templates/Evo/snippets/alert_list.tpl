{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($alertList)}
    <div id="alert-list">
        {foreach $alertList as $alert}
            {if $alert->getShowInAlertListTemplate()}
                {$alert->display()}
            {/if}
        {/foreach}
    </div>
{/if}
