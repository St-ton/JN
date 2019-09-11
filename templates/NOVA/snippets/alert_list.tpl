{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-alert-list'}
    {if !empty($alertList->getAlertlist())}
        {container id="alert-list"}
            {foreach $alertList->getAlertlist() as $alert}
                {if $alert->getShowInAlertListTemplate()}
                    {$alert->display()}
                {/if}
            {/foreach}
        {/container}
    {/if}
{/block}
