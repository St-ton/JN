{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($alertList->getAlertlist())}
    <div id="alert-list">
        {foreach $alertList->getAlertlist() as $alert}
            {if $alert->getShowInAlertListTemplate()}
                {$alert->display()}
            {/if}
        {/foreach}
    </div>
{/if}
