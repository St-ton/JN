{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<h1>{lang key='deleteAccount' section='login'}</h1>
{if !empty($openOrders)}
    <div class="alert alert-danger">
        {if $openOrders->ordersInCancellationTime > 0}
            {assign
                var='customerOrdersInCancellationTime'
                value={lang key='customerOrdersInCancellationTime'
                section='account data'
                printf=$openOrders->ordersInCancellationTime}
            }
        {/if}
        {lang key='customerOpenOrders' section='account data' printf=$openOrders->openOrders|cat:':::'|cat:{$customerOrdersInCancellationTime|default:''}}
    </div>
{/if}
{if empty($hinweis)}
    <div class="alert alert-danger">{lang key='reallyDeleteAccount' section='login'}</div>
{/if}

<form id="delete_account" action="{get_static_route id='jtl.php'}" method="post">
    {$jtl_token}
    <input type="hidden" name="del_acc" value="1" />
    <input type="submit" class="submit btn btn-danger" value="{lang key='deleteAccount' section='login'}" />
</form>
