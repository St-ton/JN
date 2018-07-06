{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{block name='account-orders'}
    <h1 class="menu-title">{block name='account-orders-title'}{lang key='yourOrders' section='login'}{/block}</h1>

    {if $Bestellungen|@count > 0}
        {block name='account-orders-body'}
            {assign var=bDownloads value=false}
            {foreach $Bestellungen as $order}
                {if isset($order->bDownload) && $order->bDownload > 0}
                    {assign var=bDownloads value=true}
                {/if}
            {/foreach}

            {include file='snippets/pagination.tpl' oPagination=$orderPagination cThisUrl='jtl.php' cParam_arr=['bestellungen'=>1] parts=['pagi', 'label']}

            <table class="table table-striped">
                <thead class="hidden-xs">
                <tr>
                    <th>{lang key='orderNo' section='login'}</th>
                    <th>{lang key='value' section='login'}</th>
                    <th>{lang key='orderDate' section='login'}</th>
                    <th class="hidden-xs">{lang key='orderStatus' section='login'}</th>
                    {if $bDownloads}
                        <th class="hidden-xs">{lang key='downloads'}</th>
                    {/if}
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody class="small">
                {foreach $orderPagination->getPageItems() as $order}
                    <tr>
                        <td>{$order->cBestellNr}</td>
                        <td>{$order->cBestellwertLocalized}</td>
                        <td>{$order->dBestelldatum}</td>
                        <td class="hidden-xs">{$order->Status}</td>
                        {if $bDownloads}
                            <td class="hidden-xs">
                                {if isset($order->bDownload) && $order->bDownload > 0}
                                    <div class="dl_active"></div>
                                {/if}
                            </td>
                        {/if}
                        <td class="text-right">
                            <a class="btn btn-default btn-xs" href="{get_static_route id='jtl.php'}?bestellung={$order->kBestellung}" title="{lang key='showOrder' section='login'}: {lang key='orderNo' section='login'} {$order->cBestellNr}">
                                <span class="fa fa-list-alt"></span> <span class="hidden-xs">{lang key='showOrder' section='login'}</span>
                            </a>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        {/block}
    {else}
        <div class="alert alert-info">{lang key='noEntriesAvailable'}</div>
    {/if}
{/block}
