{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($smarty.session.alerts)}
    <div id="alert-list">
    {foreach $smarty.session.alerts->getAlertList() as $alert}
        {if $alert->getShowInAlertListTemplate()}
            {$alert->display()}
        {/if}
    {/foreach}
    </div>
{/if}
